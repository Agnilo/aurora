<?php

namespace App\Http\Controllers;

use App\Models\{Task, Habit};
use App\Services\TaskXpService;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $tab = $request->query('tab', 'goals');
        if (!in_array($tab, ['goals', 'tasks', 'habits'], true)) $tab = 'goals';
        if ($tab === 'goals') return app(GoalController::class)->index($locale, $request);
        app(TaskXpService::class)->syncOpenTasks($request->user());
        $status = $request->query('status', 'active');
        if (!in_array($status, ['active', 'completed', 'archived', 'all'], true)) $status = 'active';
        $query = $tab === 'tasks' ? Task::ownedBy($request->user())->with('goal', 'category')
            : Habit::where('user_id', $request->user()->id)->with('category');
        if ($status === 'archived') $query->whereNotNull('archived_at');
        else {
            $query->whereNull('archived_at');
            if ($tab === 'tasks' && $status !== 'all') {
                $status === 'completed' ? $query->whereNotNull('completed_at') : $query->whereNull('completed_at');
            }
        }
        $items = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(15)->withQueryString();
        if ($tab === 'habits') {
            foreach ($items as $habit) $habit->setRelation('currentCompletion', $habit->completions()->where('period_start', $habit->period())->first());
        }
        return view('plans.index', compact('tab', 'status', 'items'));
    }
}
