<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Milestone;
use App\Models\PointsLog;

class GamificationService
{
    public static function recalcGoalAndMilestones(Goal $goal): void
    {
        $goal->loadMissing(['milestones.tasks.priority']);

        self::recalcGoalProgress($goal);

        $goal->refresh();
        $goal->loadMissing(['milestones.tasks.priority']);

        self::syncMilestoneBonusLogs($goal);
        self::syncGoalBonusLog($goal);
    }

    public static function recalcGoalProgress(Goal $goal): void
    {
        $goal->loadMissing('milestones.tasks');

        foreach ($goal->milestones as $milestone) {
            $total = $milestone->tasks->count();
            $done  = $milestone->tasks->whereNotNull('completed_at')->count();

            $milestone->progress = $total > 0 ? (int) floor(($done / $total) * 100) : 0;
            $milestone->is_completed = ($milestone->progress >= 100);
            $milestone->save();
        }

        $mTotal = $goal->milestones->count();
        $mDone  = $goal->milestones->where('is_completed', true)->count();

        $goal->progress = $mTotal > 0 ? (int) floor(($mDone / $mTotal) * 100) : 0;
        $goal->is_completed = ($goal->progress >= 100);
        $goal->save();
    }

    private static function syncMilestoneBonusLogs(Goal $goal): void
    {
        foreach ($goal->milestones as $milestone) {
            $milestone->loadMissing('tasks.priority');

            $shouldHave = (bool) $milestone->is_completed;
            $xp = self::calculateMilestoneXp($milestone);

            $log = PointsLog::where('milestone_id', $milestone->id)
                ->where('type', 'milestone_completed')
                ->first();

            if ($shouldHave && $xp > 0) {
                if (!$log) {
                    PointsLog::create([
                        'user_id'      => $goal->user_id,
                        'category_id'  => $goal->category_id,
                        'milestone_id' => $milestone->id,
                        'points'       => $xp,
                        'amount'       => $xp,
                        'type'         => 'milestone_completed',
                    ]);
                } else {
                    $log->category_id = $goal->category_id;
                    $log->points = $xp;
                    $log->amount = $xp;
                    $log->save();
                }
            } else {
                if ($log) $log->delete();
            }
        }
    }

    private static function syncGoalBonusLog(Goal $goal): void
    {
        $shouldHave = (bool) $goal->is_completed;
        $xp = self::calculateGoalXp($goal);

        $log = PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->first();

        if ($shouldHave && $xp > 0) {
            if (!$log) {
                PointsLog::create([
                    'user_id'     => $goal->user_id,
                    'category_id' => $goal->category_id,
                    'goal_id'     => $goal->id,
                    'points'      => $xp,
                    'amount'      => $xp,
                    'type'        => 'goal_completed',
                ]);
            } else {
                $log->category_id = $goal->category_id;
                $log->points = $xp;
                $log->amount = $xp;
                $log->save();
            }
        } else {
            if ($log) $log->delete();
        }
    }

    public static function calculateMilestoneXp(Milestone $milestone): int
    {
        $milestone->loadMissing('tasks.priority');

        if ($milestone->tasks->count() === 0) return 0;

        $avg = $milestone->tasks
            ->map(fn($t) => PointsService::calculateXp($t))
            ->avg();

        return (int) floor(((float) $avg) * 1.2);
    }

    public static function calculateGoalXp(Goal $goal): int
    {
        $goal->loadMissing('milestones.tasks.priority');

        if ($goal->milestones->count() === 0) return 0;

        $avg = $goal->milestones
            ->map(fn($m) => self::calculateMilestoneXp($m))
            ->avg();

        return (int) floor(((float) $avg) * 1.3);
    }
}
