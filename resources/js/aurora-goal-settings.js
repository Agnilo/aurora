const validColor = value => /^#[0-9a-f]{6}$/i.test(value);
const initColor = input => {
    if (input.dataset.auroraColorReady || input.disabled) return;
    const template = document.getElementById('aurora-color-picker-template');
    if (!template) return;
    const control = template.content.firstElementChild.cloneNode(true);
    const trigger = control.querySelector('.aurora-color-trigger');
    const panel = control.querySelector('.aurora-color-panel');
    const palette = control.querySelector('[data-color-palette]');
    const colors = JSON.parse(template.dataset.palette).flat();
    const reset = '#ffffff';
    let opened = false;
    const buttons = colors.map(color => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'aurora-color-option';
        button.style.backgroundColor = color;
        button.dataset.color = color;
        button.title = color.toUpperCase();
        button.setAttribute('aria-label', color.toUpperCase());
        button.addEventListener('click', () => select(color));
        palette.append(button);
        return button;
    });
    function sync() {
        const color = validColor(input.value) ? input.value.toLowerCase() : reset;
        trigger.querySelector('.aurora-color-swatch').style.backgroundColor = color;
        trigger.querySelector('[data-color-value]').textContent = color.toUpperCase();
        buttons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.color === color)));
    }
    function position() {
        const rect = trigger.getBoundingClientRect();
        const width = Math.min(310, window.innerWidth - 24);
        panel.style.width = `${width}px`;
        panel.style.left = `${Math.max(12, Math.min(rect.right - width, window.innerWidth - width - 12))}px`;
        const height = panel.getBoundingClientRect().height;
        panel.style.top = `${Math.max(12, rect.bottom + height + 8 <= window.innerHeight ? rect.bottom + 6 : rect.top - height - 6)}px`;
    }
    function close() {
        if (panel.matches(':popover-open')) panel.hidePopover();
    }
    function select(color) {
        input.value = color;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        sync();
        close();
        trigger.focus();
    }
    if (typeof panel.showPopover !== 'function') return;
    trigger.addEventListener('click', () => {
        if (panel.matches(':popover-open')) close();
        else { panel.showPopover(); position(); }
    });
    panel.addEventListener('toggle', () => {
        opened = panel.matches(':popover-open');
        trigger.setAttribute('aria-expanded', String(opened));
    });
    window.addEventListener('resize', () => { if (opened) position(); });
    window.addEventListener('scroll', () => { if (opened) close(); }, true);
    control.querySelector('[data-color-reset]').addEventListener('click', () => select(reset));
    input.addEventListener('change', sync);
    input.form?.addEventListener('reset', () => requestAnimationFrame(sync));
    input.insertAdjacentElement('afterend', control);
    input.dataset.auroraColorReady = '1';
    input.hidden = true;
    const label = input.labels?.[0];
    if (label && input.id) { trigger.id = input.id + '-trigger'; label.htmlFor = trigger.id; }
    sync();
};
const initTags = input => {
    const template = document.getElementById('aurora-tags-template');
    if (!template || input.dataset.auroraTagsReady) return;
    const control = template.content.firstElementChild.cloneNode(true);
    const list = control.querySelector('.aurora-tags-list');
    const entry = control.querySelector('.aurora-tag-entry');
    const parse = value => [...new Set(value.split(',').map(tag => tag.trim().replace(/^#+/, '')).filter(Boolean))];
    let tags = parse(input.value);
    function render() {
        list.replaceChildren();
        tags.forEach(tag => {
            const chip = document.createElement('span');
            chip.className = 'aurora-tag-chip';
            chip.textContent = '#' + tag;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = '×';
            remove.setAttribute('aria-label', template.dataset.removeLabel + ': ' + tag);
            remove.addEventListener('pointerdown', event => event.preventDefault());
            remove.addEventListener('click', () => { tags = tags.filter(value => value !== tag); sync(); render(); entry.focus(); });
            chip.append(remove);
            list.append(chip);
        });
    }
    function sync() {
        const value = [...new Set([...tags, ...parse(entry.value)])].join(',');
        input.value = value;
        entry.setCustomValidity(value.length > 1000 ? template.dataset.tooLong : '');
    }
    function commit() {
        if (!entry.value.trim()) return;
        const value = [...new Set([...tags, ...parse(entry.value)])].join(',');
        if (value.length > 1000) { sync(); return; }
        tags = parse(value); entry.value = ''; sync(); render();
    }
    entry.addEventListener('input', sync);
    entry.addEventListener('blur', commit);
    entry.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ',') { event.preventDefault(); commit(); }
    });
    input.form?.addEventListener('submit', sync);
    input.form?.addEventListener('reset', () => requestAnimationFrame(() => { tags = parse(input.value); entry.value = ''; sync(); render(); }));
    input.insertAdjacentElement('afterend', control);
    input.hidden = true;
    input.dataset.auroraTagsReady = '1';
    const label = input.labels?.[0];
    if (label && input.id) { entry.id = input.id + '-entry'; label.htmlFor = entry.id; }
    render();
};
function initialize() {
    document.querySelectorAll('#goal-form input[type="color"], input[type="color"][data-aurora-color-picker]').forEach(initColor);
    document.querySelectorAll('#goal-form input[name="tags"]').forEach(initTags);
    const icons = document.getElementById('aurora-goal-flag-icons');
    const goal = document.getElementById('goal-form');
    if (goal && icons) {
        const progress = goal.querySelector('#gf-progress');
        if (progress && !progress.readOnly) {
            progress.readOnly = true;
            progress.removeAttribute('name');
            const hint = document.createElement('small');
            hint.className = 'aurora-progress-hint';
            hint.id = 'gf-progress-auto-hint';
            hint.textContent = icons.dataset.progressAuto;
            progress.setAttribute('aria-describedby', hint.id);
            progress.insertAdjacentElement('afterend', hint);
        }
        goal.querySelectorAll('input[name="is_completed"]').forEach(input => {
            if (input.type === 'checkbox') input.closest('label')?.remove();
            else if (input.type === 'hidden') input.remove();
        });
    }
    if (icons) document.querySelectorAll('#goal-form .gf-check input:is([name="is_favorite"], [name="is_important"])').forEach(input => {
        const label = input.closest('label');
        if (!label || label.classList.contains('aurora-flag')) return;
        label.classList.add('aurora-flag', `aurora-flag--${input.name}`);
        const icon = icons.content.querySelector(`[data-flag-icon="${input.name}"]`)?.cloneNode(true);
        if (icon) input.insertAdjacentElement('afterend', icon);
    });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
else initialize();
