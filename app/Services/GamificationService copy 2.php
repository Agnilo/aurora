<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Goal;
use App\Models\PointsLog;

class GamificationService
{
    /**
     * When a task becomes completed/uncompleted:
     * - Recalculate milestone.is_completed
     * - Recalculate goal.is_completed
     * - ADD milestone XP if milestone becomes complete
     * - REMOVE milestone XP if milestone becomes uncomplete
     * - ADD goal XP if goal becomes complete
     * - REMOVE goal XP if goal becomes uncomplete
     */
    public static function taskCompleted(Task $task)
    {
        $milestone = $task->milestone;
        $goal = $milestone->goal;
        $user = $goal->user;

        $milestone->load('tasks');
        $goal->load('milestones.tasks');

        $milestoneWasCompleted = $milestone->is_completed;
        $goalWasCompleted = $goal->is_completed;

        // --- UPDATE STATES ---
        $milestone->is_completed = self::isMilestoneComplete($milestone);
        $milestone->save();

        $goal->is_completed = self::isGoalComplete($goal);
        $goal->save();

        // --- BONUS LOGIC ---

        // A) milestone bonus
        if (!$milestoneWasCompleted && $milestone->is_completed) {
            self::milestoneCompleted($milestone);
        }

        // B) goal bonus
        if (!$goalWasCompleted && $goal->is_completed) {
            self::goalCompleted($goal);
        }
    }



    public static function taskUncompleted(Task $task, bool $milestoneWasCompleted, bool $goalWasCompleted)
    {
        $milestone = $task->milestone;
        $goal = $milestone->goal;
        $user = $goal->user;

        $milestone->load('tasks');
        $goal->load('milestones.tasks');

        // --- UPDATE STATES ---
        $milestone->is_completed = self::isMilestoneComplete($milestone);
        $milestone->save();

        $goal->is_completed = self::isGoalComplete($goal);
        $goal->save();

        // --- REMOVE BONUSES ---

        // A) milestone uncompleted
        if ($milestoneWasCompleted && !$milestone->is_completed) {
            self::milestoneUncompleted($milestone);
        }

        // B) goal uncompleted
        if ($goalWasCompleted && !$goal->is_completed) {
            self::goalUncompleted($goal);
        }
    }



    /* -----------------------------
       BONUS XP APPLY / REMOVE
       ----------------------------- */

    public static function milestoneCompleted(Milestone $milestone)
    {
        $goal = $milestone->goal;
        $user = $goal->user;

        $xp = self::calculateMilestoneXp($milestone);

        PointsService::grantRawXP($user, $goal->category_id, $xp);

        PointsLog::create([
            'user_id' => $user->id,
            'category_id' => $goal->category_id,
            'milestone_id' => $milestone->id,
            'points' => $xp,
            'amount' => $xp,
            'type' => 'milestone_completed',
        ]);
    }


    public static function milestoneUncompleted(Milestone $milestone)
    {
        $goal = $milestone->goal;
        $user = $goal->user;

        $xp = self::calculateMilestoneXp($milestone);

        PointsService::removeRawXP($user, $goal->category_id, $xp);

        PointsLog::where('milestone_id', $milestone->id)
            ->where('type', 'milestone_completed')
            ->delete();
    }


    public static function goalCompleted(Goal $goal)
    {
        $user = $goal->user;

        $xp = self::calculateGoalXp($goal);

        PointsService::grantRawXP($user, $goal->category_id, $xp);

        PointsLog::create([
            'user_id' => $user->id,
            'category_id' => $goal->category_id,
            'goal_id' => $goal->id,
            'points' => $xp,
            'amount' => $xp,
            'type' => 'goal_completed',
        ]);
    }


    public static function goalUncompleted(Goal $goal)
    {
        $user = $goal->user;

        $xp = self::calculateGoalXp($goal);

        PointsService::removeRawXP($user, $goal->category_id, $xp);

        PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->delete();
    }



    /* -----------------------------
       XP FORMULOS
       ----------------------------- */

    public static function calculateMilestoneXp($milestone)
    {
        $taskXps = $milestone->tasks->map(fn($task) =>
            PointsService::calculateXp($task)
        );

        if ($taskXps->count() === 0) return 0;

        return intval($taskXps->avg() * 1.2);
    }


    public static function calculateGoalXp($goal)
    {
        $milestoneXps = $goal->milestones->map(fn($m) =>
            self::calculateMilestoneXp($m)
        );

        if ($milestoneXps->count() === 0) return 0;

        return intval($milestoneXps->avg() * 1.3);
    }


    /* -----------------------------
       STATE CHECKS
       ----------------------------- */

    private static function isMilestoneComplete(Milestone $milestone)
    {
        return $milestone->tasks()->whereNull('completed_at')->count() === 0;
    }

    public static function isGoalComplete(Goal $goal)
    {
        return $goal->milestones()->where('is_completed', false)->count() === 0;
    }

    public static function recalcGoalProgress(Goal $goal)
    {
        $goal->load('milestones.tasks');

        foreach ($goal->milestones as $milestone) {

            // Perskaičiuojam milestone progress
            $totalTasks = $milestone->tasks->count();
            $completed  = $milestone->tasks->whereNotNull('completed_at')->count();

            $milestone->progress = $totalTasks > 0 ? intval(($completed / $totalTasks) * 100) : 0;
            $milestone->is_completed = ($milestone->progress === 100);
            $milestone->save();
        }

        // Perskaičiuojam goal progress
        $milesTotal = $goal->milestones->count();
        $milesDone  = $goal->milestones->where('is_completed', true)->count();

        $goal->progress = $milesTotal > 0 ? intval(($milesDone / $milesTotal) * 100) : 0;
        $goal->is_completed = ($goal->progress === 100);
        $goal->save();
    }

}
