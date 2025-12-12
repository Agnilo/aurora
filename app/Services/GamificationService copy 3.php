<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Goal;
use App\Models\PointsLog;

class GamificationService
{
    /**
     * Kai task'as tampa completed/uncompleted per AJAX
     * – dabar tiesiog permesim į bendrą per-skaičiavimą.
     */
    public static function taskCompleted(Task $task)
    {
        $goal = $task->milestone->goal;
        $goal->load('milestones.tasks');

        self::recalcGoalAndMilestones($goal);
    }

    public static function taskUncompleted(Task $task, bool $milestoneWasCompleted = false, bool $goalWasCompleted = false)
    {
        $goal = $task->milestone->goal;
        $goal->load('milestones.tasks');

        self::recalcGoalAndMilestones($goal);
    }

    public static function taskUpdated(Task $task)
    {
        $goal = $task->milestone->goal;
        $goal->load('milestones.tasks');

        self::recalcGoalAndMilestones($goal);
    }

    public static function recalcGoalAndMilestones(Goal $goal)
    {
        // 1) Pirmiausia: perskaičiuojam progress + is_completed flagus
        self::recalcGoalProgress($goal);

        // 2) Atsikeliam šviežiausius duomenis
        $goal->refresh();
        $goal->load('milestones.tasks');

        $user = $goal->user;

        /* ------------------------------------
        * 3) MILESTONE BONUSŲ SINCHRONIZAVIMAS
        * ------------------------------------ */
        foreach ($goal->milestones as $milestone) {
            // saugumo dėlei
            $milestone->load('tasks');

            $isCompleted = (bool) $milestone->is_completed;
            $xp          = self::calculateMilestoneXp($milestone);

            // ar jau turim logą šitam milestone?
            $log = PointsLog::where('milestone_id', $milestone->id)
                ->where('type', 'milestone_completed')
                ->first();

            if ($isCompleted) {
                if (!$log && $xp > 0) {
                    // nebuvo log'o → uždedam visą bonusą
                    PointsService::grantRawXP($user, $goal->category_id, $xp);

                    PointsLog::create([
                        'user_id'      => $user->id,
                        'category_id'  => $goal->category_id,
                        'milestone_id' => $milestone->id,
                        'points'       => $xp,
                        'amount'       => $xp,
                        'type'         => 'milestone_completed',
                    ]);
                } elseif ($log && $log->amount != $xp) {
                    // buvo log'as, bet XP pasikeitė → koreguojam skirtumą
                    $diff = $xp - $log->amount;

                    if ($diff > 0) {
                        PointsService::grantRawXP($user, $goal->category_id, $diff);
                    } elseif ($diff < 0) {
                        PointsService::removeRawXP($user, $goal->category_id, -$diff);
                    }

                    $log->amount = $xp;
                    $log->points = $xp;
                    $log->save();
                }
            } else {
                // milestone nebe completed → jei turėjom bonusą, nuimam
                if ($log) {
                    PointsService::removeRawXP($user, $goal->category_id, $log->amount);
                    $log->delete();
                }
            }
        }

        /* --------------------------------
        * 4) GOAL BONUSO SINCHRONIZAVIMAS
        * -------------------------------- */
        $goal->refresh(); // kad turėtume atnaujintą is_completed
        $isGoalCompleted = (bool) $goal->is_completed;
        $goalXp          = self::calculateGoalXp($goal);

        $goalLog = PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->first();

        if ($isGoalCompleted) {
            if (!$goalLog && $goalXp > 0) {
                PointsService::grantRawXP($user, $goal->category_id, $goalXp);

                PointsLog::create([
                    'user_id'     => $user->id,
                    'category_id' => $goal->category_id,
                    'goal_id'     => $goal->id,
                    'points'      => $goalXp,
                    'amount'      => $goalXp,
                    'type'        => 'goal_completed',
                ]);
            } elseif ($goalLog && $goalLog->amount != $goalXp) {
                $diff = $goalXp - $goalLog->amount;

                if ($diff > 0) {
                    PointsService::grantRawXP($user, $goal->category_id, $diff);
                } elseif ($diff < 0) {
                    PointsService::removeRawXP($user, $goal->category_id, -$diff);
                }

                $goalLog->amount = $goalXp;
                $goalLog->points = $goalXp;
                $goalLog->save();
            }
        } else {
            if ($goalLog) {
                PointsService::removeRawXP($user, $goal->category_id, $goalLog->amount);
                $goalLog->delete();
            }
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
            'user_id'      => $user->id,
            'category_id'  => $goal->category_id,
            'milestone_id' => $milestone->id,
            'points'       => $xp,
            'amount'       => $xp,
            'type'         => 'milestone_completed',
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
            'user_id'     => $user->id,
            'category_id' => $goal->category_id,
            'goal_id'     => $goal->id,
            'points'      => $xp,
            'amount'      => $xp,
            'type'        => 'goal_completed',
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
       STATE CHECKS (paliekam jei kur nors naudoji)
       ----------------------------- */

    private static function isMilestoneComplete(Milestone $milestone)
    {
        return $milestone->tasks()->whereNull('completed_at')->count() === 0;
    }

    public static function isGoalComplete(Goal $goal)
    {
        return $goal->milestones()->where('is_completed', false)->count() === 0;
    }

    /* -----------------------------
       PROGRESS RE-CALC
       ----------------------------- */

    public static function recalcGoalProgress(Goal $goal)
    {
        $goal->load('milestones.tasks');

        // Milestone progress + is_completed
        foreach ($goal->milestones as $milestone) {
            $totalTasks = $milestone->tasks->count();
            $completed  = $milestone->tasks->whereNotNull('completed_at')->count();
            $milestone->progress = $totalTasks > 0 ? intval(($completed / $totalTasks) * 100) : 0;
            $milestone->is_completed = ($milestone->progress === 100) ? 1 : 0;
            $milestone->save();
        }

        // Goal progress + is_completed
        $milesTotal = $goal->milestones->count();
        $milesDone  = $goal->milestones->where('is_completed', true)->count();

        $goal->progress = $milesTotal > 0 ? intval(($milesDone / $milesTotal) * 100) : 0;
        $goal->is_completed  = ($goal->progress === 100) ? 1 : 0;
        $goal->save();


    }

    public static function moveGoalBonusesToNewCategory(Goal $goal, int $oldCategoryId, int $newCategoryId)
    {
        // Load visus reikiamus ryšius
        $goal->load('milestones.tasks', 'user.categoryLevels');
        $user = $goal->user;

        // 1) Suskaičiuojam BONUS XP iš milestone ir goal
        $totalBonus = 0;

        foreach ($goal->milestones as $milestone) {
            if ($milestone->is_completed) {
                $totalBonus += self::calculateMilestoneXp($milestone);
            }
        }

        if ($goal->is_completed) {
            $totalBonus += self::calculateGoalXp($goal);
        }

        // 2) PointsLog įrašų kategorija → nauja
        PointsLog::where('goal_id', $goal->id)
            ->where('type', 'goal_completed')
            ->update(['category_id' => $newCategoryId]);

        PointsLog::whereIn('milestone_id', $goal->milestones->pluck('id'))
            ->where('type', 'milestone_completed')
            ->update(['category_id' => $newCategoryId]);

        // Jei nebuvo jokio bonuso – viskas, baigėm
        if ($totalBonus <= 0) {
            return;
        }

        // 3) BONUS XP pridedam PRIE NAUJOS kategorijos
        //    (senai kategorijai jų jau nebėra, nes recalcCategory buvo paleistas)
        $newCat = $user->categoryLevels()->firstOrCreate(
            ['category_id' => $newCategoryId],
            ['level' => 1, 'xp' => 0, 'xp_next' => 100]
        );

        $newCat->xp += $totalBonus;

        // Level up logika – tokia pati, kaip applyCategoryXp
        while ($newCat->xp >= $newCat->xp_next) {
            $newCat->xp -= $newCat->xp_next;
            $newCat->level++;
            $newCat->xp_next = intval($newCat->xp_next * 1.15);
        }

        $newCat->save();
    }
}
