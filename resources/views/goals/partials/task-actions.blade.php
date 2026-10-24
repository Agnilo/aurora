@once
@php
    $taskToggleUrl = route('tasks.toggle-complete', [
        'locale' => app()->getLocale(),
        'task' => '__TASK__',
    ]);
    $taskSaveError = t('dashboard_v2.save_error');
@endphp

<script>
(() => {
    // This partial belongs outside #goalsDynamic. Keep only this one handler.
    if (window.auroraGoalTasksInitialised) return;
    window.auroraGoalTasksInitialised = true;

    const toggleUrl = @json($taskToggleUrl);
    const csrfToken = @json(csrf_token());
    const errorMessage = @json($taskSaveError);
    const pendingTasks = new Set();
    const completedStates = new Map();

    const percent = (value) => {
        const number = Number(value);
        return Number.isFinite(number) ? Math.max(0, Math.min(100, number)) : 0;
    };

    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element && value !== undefined && value !== null) {
            element.textContent = value;
        }
    };

    const updateProgress = (bar, label, value) => {
        if (value === undefined || value === null) return;
        const progress = percent(value);
        if (bar) {
            bar.style.width = `${progress}%`;
            bar.setAttribute('aria-valuenow', String(progress));
        }
        if (label) label.textContent = `${progress}%`;
    };

    const matchingCheckboxes = (taskId) => [...document.querySelectorAll('.task-done-toggle')]
        .filter(input => input.dataset.taskId === taskId);

    const refreshTask = (checkbox, data) => {
        checkbox.checked = Boolean(data.completed_at);

        const row = checkbox.closest('.list-group-item');
        const badge = row?.querySelector('.task-status-badge');
        if (badge) {
            if (data.status_label !== undefined) badge.textContent = data.status_label;
            if (data.status_color) badge.style.backgroundColor = data.status_color;
        }

        const milestone = checkbox.closest('.milestone-item');
        updateProgress(
            milestone?.querySelector('.milestone-progress-bar'),
            milestone?.querySelector('.milestone-progress-label'),
            data.milestone_progress
        );

        const goal = checkbox.closest('.accordion-item');
        updateProgress(
            goal?.querySelector('.goal-progress-bar'),
            goal?.querySelector('.goal-progress-badge'),
            data.goal_progress
        );
    };

    const refreshCategories = (categories) => {
        const grid = document.getElementById('categoryGrid');
        if (!grid || !categories) return;

        Object.entries(categories).forEach(([categoryId, values]) => {
            const link = [...grid.querySelectorAll('.category-link')]
                .find(item => item.dataset.categoryId === String(categoryId));
            if (!link) return;

            const bar = link.querySelector('.gc-progress');
            const fill = bar?.querySelector('span');
            const label = link.querySelector('.life-area-progress small');
            const xp = Number(values.xp ?? 0);
            const maximum = Number(values.xp_next ?? bar?.dataset.max ?? 100);
            if (bar) bar.dataset.max = String(maximum);
            const progress = percent(maximum > 0 ? xp / maximum * 100 : 0);

            updateProgress(fill, null, progress);
            if (bar) bar.setAttribute('aria-valuenow', String(progress));

            // Retain compatibility with the earlier square progress indicators.
            link.querySelectorAll('.xp-squares-goals span').forEach((square, index) => {
                const fillPercent = percent((progress - index * 10) * 10);
                square.classList.toggle('filled', fillPercent === 100);
                square.style.background = fillPercent > 0 && fillPercent < 100
                    ? `linear-gradient(to right, var(--accent) ${fillPercent}%, rgba(0,0,0,.08) ${fillPercent}%)`
                    : '';
            });

            if (label) {
                const unit = label.dataset.unit ?? label.textContent.trim().split(/\s+/).pop();
                label.textContent = `${xp} / ${maximum} ${unit ?? ''}`.trim();
            }
        });
    };

    // Delegation also handles checkboxes inserted by category filtering/AJAX.
    document.addEventListener('change', async (event) => {
        const checkbox = event.target;
        if (!(checkbox instanceof HTMLInputElement) || !checkbox.matches('.task-done-toggle')) return;

        const taskId = checkbox.dataset.taskId;
        const previousState = completedStates.has(taskId)
            ? completedStates.get(taskId)
            : !checkbox.checked;

        if (pendingTasks.has(taskId)) {
            checkbox.checked = previousState;
            return;
        }
        if (!taskId) {
            checkbox.checked = previousState;
            console.error('Task checkbox is missing data-task-id.', checkbox);
            alert(errorMessage);
            return;
        }

        pendingTasks.add(taskId);
        completedStates.set(taskId, previousState);
        matchingCheckboxes(taskId).forEach(input => {
            input.disabled = true;
            input.dataset.loading = '1';
        });

        let data;
        try {
            const response = await fetch(toggleUrl.replace('__TASK__', encodeURIComponent(taskId)), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) throw new Error(`Task update failed: HTTP ${response.status}`);
            data = await response.json();
            if (data.success !== true || !Object.hasOwn(data, 'completed_at')) {
                throw new Error('Task update returned an unexpected response.');
            }
        } catch (error) {
            checkbox.checked = previousState;
            matchingCheckboxes(taskId).forEach(input => { input.checked = previousState; });
            console.error(error);
            alert(errorMessage);
        } finally {
            pendingTasks.delete(taskId);
            checkbox.disabled = false;
            checkbox.dataset.loading = '0';
            matchingCheckboxes(taskId).forEach(input => {
                input.disabled = false;
                input.dataset.loading = '0';
            });
        }

        if (!data || data.success !== true || !Object.hasOwn(data, 'completed_at')) return;

        completedStates.set(taskId, Boolean(data.completed_at));
        // Update the original element and any current copy inserted during a request.
        new Set([checkbox, ...matchingCheckboxes(taskId)]).forEach(input => refreshTask(input, data));

        setText('g-level', data.level);
        setText('g-coins', data.coins);
        setText('g-xp', data.xp);
        setText('g-xp-next', data.xp_next);
        if (data.xp !== undefined && Number(data.xp_next) > 0) {
            updateProgress(document.getElementById('g-xp-bar'), null, data.xp / data.xp_next * 100);
        }
        refreshCategories(data.category_xp);
    });
})();
</script>
@endonce
