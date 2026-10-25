document.addEventListener('click', (event) => {
    if (event.target.closest('[data-plan-create]')) {
        document.getElementById('plan-chooser')?.showModal();
    }
});
document.addEventListener('change', (event) => {
    if (event.target.matches('[data-habit-frequency]')) {
        const days = event.target.form.querySelector('[data-habit-days]');
        if (days) days.hidden = event.target.value !== 'daily';
    }
});
document.addEventListener('submit', (event) => {
    if (!event.target.matches('[data-plan-submit]')) return;
    if (event.target.dataset.busy) { event.preventDefault(); return; }
    event.target.dataset.busy = '1';
    event.target.querySelector('button').disabled = true;
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-plan-submit]').forEach(form => {
        delete form.dataset.busy;
        form.querySelector('button').disabled = false;
    });
});

function enhancePlanEditor(form) {
    if (form.dataset.enhanced) return;
    form.dataset.enhanced = '1';

    // Reveal optional fields before native validation tries to focus them.
    form.addEventListener('invalid', (event) => {
        const details = event.target.closest('details');
        if (details) details.open = true;
    }, true);

    const habitPresets = form.querySelector('[data-habit-presets]');
    if (habitPresets) {
        const frequency = form.querySelector('[data-habit-frequency]');
        const days = form.querySelector('[data-habit-days]');
        const boxes = [...days.querySelectorAll('input[type="checkbox"]')];
        const summary = form.querySelector('[data-schedule-summary]');
        const radios = [...habitPresets.querySelectorAll('input')];
        const selectedDays = () => boxes.filter(box => box.checked).map(box => Number(box.value));
        const matches = values => values.join(',') === selectedDays().join(',');
        const initial = frequency.value === 'weekly' ? 'weekly'
            : matches([1, 2, 3, 4, 5, 6, 7]) ? 'everyday'
            : matches([1, 2, 3, 4, 5]) ? 'workdays' : 'custom';

        const render = preset => {
            days.hidden = preset !== 'custom';
            summary.textContent = summary.dataset[preset];
            // Native validation ensures a custom daily schedule has a day.
            boxes[0].required = !frequency.disabled && frequency.value === 'daily'
                && selectedDays().length === 0;
        };

        radios.find(radio => radio.value === initial).checked = true;
        render(initial);
        habitPresets.hidden = false;
        form.querySelector('[data-frequency-fallback]').hidden = true;

        habitPresets.addEventListener('change', (event) => {
            if (frequency.disabled || !event.target.matches('input[type="radio"]')) return;
            const preset = event.target.value;
            frequency.value = preset === 'weekly' ? 'weekly' : 'daily';
            if (preset === 'everyday' || preset === 'workdays') {
                boxes.forEach(box => {
                    box.checked = preset === 'everyday' || Number(box.value) <= 5;
                });
            }
            render(preset);
        });
        days.addEventListener('change', () => render('custom'));
    }

    const taskPresets = form.querySelector('[data-task-presets]');
    if (taskPresets) {
        const dateLabel = form.querySelector('[data-task-date]');
        const date = dateLabel.querySelector('input');
        const radios = [...taskPresets.querySelectorAll('input')];
        const initial = !date.value ? 'anytime'
            : date.value === taskPresets.dataset.today ? 'today'
            : date.value === taskPresets.dataset.tomorrow ? 'tomorrow' : 'pick_date';
        radios.find(radio => radio.value === initial).checked = true;
        dateLabel.hidden = initial !== 'pick_date';
        date.required = initial === 'pick_date';
        taskPresets.hidden = false;

        taskPresets.addEventListener('change', (event) => {
            if (!event.target.matches('input[type="radio"]')) return;
            const preset = event.target.value;
            dateLabel.hidden = preset !== 'pick_date';
            date.required = preset === 'pick_date';
            if (preset === 'anytime') date.value = '';
            if (preset === 'today' || preset === 'tomorrow') date.value = taskPresets.dataset[preset];
            if (preset === 'pick_date') date.focus();
        });
    }
}

const initializePlanEditors = () => {
    document.querySelectorAll('[data-plan-editor]').forEach(enhancePlanEditor);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePlanEditors);
} else {
    initializePlanEditors();
}
