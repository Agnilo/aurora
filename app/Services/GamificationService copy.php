<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Goal;
use App\Models\PointsLog;


class GamificationService
{
    public static function taskCompleted(Task $task)
    {

        $milestone = $task->milestone;
        $goal = $milestone->goal;

        $milestone->load('tasks');
        $goal->load('milestones.tasks');

        if (self::isMilestoneComplete($milestone) && !$milestone->is_completed) {
            self::milestoneCompleted($milestone);
        }

        if (self::isGoalComplete($goal) && !$goal->is_completed) {
            self::goalCompleted($goal);
        }
    }

    public static function taskUncompleted(Task $task, bool $milestoneWasCompleted, bool $goalWasCompleted)
    {

        $milestone = $task->milestone;
        $goal = $milestone->goal;

        $milestone->load('tasks');
        $goal->load('milestones.tasks');

        if ($milestoneWasCompleted && !self::isMilestoneComplete($milestone)) {
            self::milestoneUncompleted($milestone);
        }

        if ($goalWasCompleted && !self::isGoalComplete($goal)) {
            self::goalUncompleted($goal);
        }
    }

    public static function calculateMilestoneXp($milestone)
    {
        $taskXps = $milestone->tasks->map(fn($task) =>
            PointsService::calculateXp($task)
        );

        if ($taskXps->count() === 0) return 0;

        return intval($taskXps->avg() * 1.2);
    }

    public static function milestoneCompleted(Milestone $milestone)
    {
        if ($milestone->is_completed) return;

        $xp = self::calculateMilestoneXp($milestone);
        $user = $milestone->goal->user;

        PointsService::grantRawXP($user, $milestone->goal->category_id, $xp);

        PointsLog::create([
            'user_id'      => $user->id,
            'milestone_id' => $milestone->id,
            'category_id'  => $milestone->goal->category_id,
            'points'       => $xp,
            'amount'       => $xp,
            'type'         => 'milestone_completed',
        ]);

        $milestone->is_completed = true;
        $milestone->save();
    }

    public static function milestoneUncompleted(Milestone $milestone)
    {
        if (!$milestone->is_completed) return;

        $xp = self::calculateMilestoneXp($milestone);

        PointsService::removeRawXP(
            $milestone->goal->user,
            $milestone->goal->category_id,
            $xp
        );

        PointsLog::where('milestone_id', $milestone->id)
            ->where('type', 'milestone_completed')
            ->delete();

        $milestone->is_completed = false;
        $milestone->save();
    }

    public static function calculateGoalXp($goal)
    {
        $milestoneXps = $goal->milestones->map(fn($m) =>
            self::calculateMilestoneXp($m)
        );

        if ($milestoneXps->count() === 0) return 0;

        return intval($milestoneXps->avg() * 1.3);
    }

    public static function goalCompleted(Goal $goal)
    {
        if ($goal->is_completed) return;

        $xp = self::calculateGoalXp($goal);
        $user = $goal->user;

        PointsService::grantRawXP($user, $goal->category_id, $xp);

        PointsLog::create([
            'user_id'     => $user->id,
            'goal_id'     => $goal->id,
            'category_id' => $goal->category_id,
            'points'      => $xp,
            'amount'      => $xp,
            'type'        => 'goal_completed',
        ]);

        $goal->is_completed = true;
        $goal->save();
    }

    public static function goalUncompleted(Goal $goal)
    {
        if (!$goal->is_completed) return;

        $xp = self::calculateGoalXp($goal);

        PointsService::removeRawXP(
            $goal->user,
            $goal->category_id,
            $xp
        );

        PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->delete();

        $goal->is_completed = false;
        $goal->save();
    }

    private static function isMilestoneComplete(Milestone $milestone)
    {
        return $milestone->tasks()->whereNull('completed_at')->count() === 0;
    }

    public static function isGoalComplete(Goal $goal)
    {
        foreach ($goal->milestones as $m) {
            if ($m->tasks()->whereNull('completed_at')->count() > 0) {
                return false;
            }
        }
        return true;
    }

    public static function recalcGoal(Goal $goal)
    {
        $goal->load('milestones.tasks');
        $user = $goal->user;

        // 1) Remove old milestone XP
        foreach ($goal->milestones as $milestone) {

            $milestone->load('tasks');

            if ($milestone->is_completed) {
                $xp = self::calculateMilestoneXp($milestone);
                PointsService::removeRawXP($user, $goal->category_id, $xp);
            }
        }

        // 2) Remove old goal XP
        if ($goal->is_completed) {
            $xp = self::calculateGoalXp($goal);
            PointsService::removeRawXP($user, $goal->category_id, $xp);
        }

        // 3) Add XP back based on current data
        foreach ($goal->milestones as $milestone) {

            $milestone->load('tasks');

            if (self::isMilestoneComplete($milestone)) {
                $xp = self::calculateMilestoneXp($milestone);
                PointsService::grantRawXP($user, $goal->category_id, $xp);

                $milestone->is_completed = true;
                $milestone->save();
            } else {
                $milestone->is_completed = false;
                $milestone->save();
            }
        }

        // 4) Regrant goal XP
        if (self::isGoalComplete($goal)) {
            $xp = self::calculateGoalXp($goal);
            PointsService::grantRawXP($user, $goal->category_id, $xp);

            $goal->is_completed = true;
        } else {
            $goal->is_completed = false;
        }

        $goal->save();
    }


}
