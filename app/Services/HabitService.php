<?php

namespace App\Services;

use App\Models\{Habit, PointsLog};
use App\Models\Preferences\UserPreference;
use Illuminate\Support\Facades\DB;

class HabitService
{
    public function complete(Habit $habit, bool $completed): void
    {
        DB::transaction(function () use ($habit, $completed) {
            $habit = Habit::whereKey($habit->id)->lockForUpdate()->firstOrFail();
            abort_unless($habit->dueToday(), 422, t('plans.not_due'));
            $entry = $habit->completions()->where('period_start', $habit->period())->first();
            if (!$completed) {
                if ($entry) {
                    $entry->update(['completed_at' => null]);
                    PointsLog::where('habit_completion_id', $entry->id)->delete();
                }
            } else {
                if (!$entry) {
                    $xp = app(TaskXpService::class)->mode($habit->user) === UserPreference::XP_SYSTEM
                        ? app(TaskXpService::class)->pointsForPriority((int) ($habit->priority_id
                            ?? \App\Models\TaskPriority::where('name', 'Medium')->value('id')))
                        : (int) $habit->points;
                    $xp = BonusService::applyForContext($habit->user, 'task_multiplication', $xp, $habit);
                    $entry = $habit->completions()->create([
                        'period_start' => $habit->period(), 'awarded_xp' => $xp,
                        'category_id' => $habit->category_id,
                    ]);
                }
                $newCompletion = !$entry->completed_at;
                if ($newCompletion) $entry->update(['completed_at' => now()]);
                PointsLog::firstOrCreate(['habit_completion_id' => $entry->id], [
                    'user_id' => $habit->user_id, 'category_id' => $entry->category_id,
                    'type' => 'habit_completed', 'points' => $entry->awarded_xp, 'amount' => $entry->awarded_xp,
                ]);
                if ($newCompletion) {
                    GamificationService::registerStreak($habit->user);
                    BadgeAwardingService::check($habit->user, 'streak');
                }
            }
            PointsService::syncUserGamification($habit->user);
        });
    }
}
