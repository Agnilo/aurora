import '../css/frontend/components/aurora-date-picker.css';

const DAY = 86400000;
const locale = document.documentElement.lang.toLowerCase().startsWith('lt') ? 'lt-LT' : 'en-GB';
const lt = locale === 'lt-LT';
const words = lt ? {
    previous: 'Ankstesnis mėnuo', next: 'Kitas mėnuo', month: 'Mėnuo', year: 'Metai',
    choose: 'Pasirink datą', week: 'Pasirink savaitę', today: 'Šiandien',
    currentWeek: 'Dabartinė savaitė', clear: 'Išvalyti', close: 'Uždaryti'
} : {
    previous: 'Previous month', next: 'Next month', month: 'Month', year: 'Year',
    choose: 'Choose a date', week: 'Choose a week', today: 'Today',
    currentWeek: 'Current week', clear: 'Clear', close: 'Close'
};
try {
    const translations = JSON.parse(document.querySelector('meta[name="aurora-date-picker-labels"]')?.content || '{}');
    for (const key of Object.keys(words)) {
        if (typeof translations[key] === 'string' && translations[key].trim()) words[key] = translations[key];
    }
} catch { }
const monthFormat = new Intl.DateTimeFormat(locale, { month: 'long', timeZone: 'UTC' });
const weekdayFormat = new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: 'UTC' });
const dateFormat = new Intl.DateTimeFormat(locale, { dateStyle: 'full', timeZone: 'UTC' });
const iso = date => `${String(date.getUTCFullYear()).padStart(4, '0')}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}`;
function parse(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;
    const date = new Date(`${value}T00:00:00Z`);
    return Number.isFinite(+date) && iso(date) === value ? date : null;
}
const add = (date, days) => new Date(+date + days * DAY);
const monday = date => add(date, -((date.getUTCDay() + 6) % 7));
function monthDate(year, month, day = 1) {
    const d = new Date(0);
    d.setUTCFullYear(year, month, day);
    d.setUTCHours(0, 0, 0, 0);
    return d;
}
function today() {
    const parts = new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Vilnius', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
    const part = type => parts.find(p => p.type === type).value;
    return parse(`${part('year')}-${part('month')}-${part('day')}`);
}
const el = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
};
function button(text, label, fn, className = '') {
    const node = el('button', className, text);
    node.type = 'button';
    if (label) node.setAttribute('aria-label', label);
    node.addEventListener('click', fn);
    return node;
}

let state = null;
let sequence = 0;
function eligible(input) {
    return input instanceof HTMLInputElement && input.type === 'date' && !input.disabled
        && !input.readOnly && !input.matches('[data-native-date-picker]');
}
function close(restore = false) {
    if (!state) return;
    const { input, popup } = state;
    state = null;
    input.removeAttribute('aria-controls');
    input.setAttribute('aria-expanded', 'false');
    if (popup.matches(':popover-open')) popup.hidePopover();
    popup.remove();
    if (restore && input.isConnected && !input.disabled) input.focus({ preventScroll: true });
}
function valid(date) {
    if (!state || !date || date.getUTCFullYear() < 1 || date.getUTCFullYear() > 9999) return false;
    const { input } = state;
    const probe = document.createElement('input');
    probe.type = 'date';
    for (const key of ['min', 'max', 'step']) {
        if (input.hasAttribute(key)) probe.setAttribute(key, input.getAttribute(key));
    }
    if (input.hasAttribute('value')) probe.setAttribute('value', input.getAttribute('value'));
    probe.value = iso(date);
    return probe.value !== '' && probe.validity.valid;
}
function choose(date) {
    if (!state || (date && !valid(date))) return;
    const { input, week } = state;
    if (!date && (week || input.required)) return;
    const value = date ? iso(date) : '';
    const changed = input.value !== value;
    close(true);
    if (!changed) return;
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}
function position() {
    if (!state) return;
    const { input, popup } = state;
    if (!input.isConnected || !eligible(input) || !input.getClientRects().length) { close(); return; }
    const anchor = (input.closest('.dashboard-week-picker') || input).getBoundingClientRect();
    const rect = popup.getBoundingClientRect();
    const width = document.documentElement.clientWidth;
    const height = document.documentElement.clientHeight;
    if (anchor.bottom < 0 || anchor.top > height) { close(); return; }
    const x = Math.max(8, Math.min(anchor.right - rect.width, width - rect.width - 8));
    const y = anchor.bottom + 8 + rect.height <= height ? anchor.bottom + 8 : anchor.top - rect.height - 8;
    popup.style.left = `${x}px`;
    popup.style.top = `${Math.max(8, Math.min(y, height - rect.height - 8))}px`;
}
function setView(date, focus = null) {
    if (!state) return;
    state.year = date.getUTCFullYear();
    state.month = date.getUTCMonth();
    render(focus);
}
function withinMonth(year, month) {
    const first = monthDate(year, month);
    const last = add(monthDate(year, month + 1), -1);
    return (!state.min || last >= state.min) && (!state.max || first <= state.max) && year >= 1 && year <= 9999;
}
function moveMonth(delta, focusControl) {
    const next = monthDate(state.year, state.month + delta);
    if (withinMonth(next.getUTCFullYear(), next.getUTCMonth())) setView(next, focusControl);
}
function render(focus = null) {
    if (!state) return;
    const { popup, input, week, year, month, min, max } = state;
    const selected = parse(input.value);
    const selectedStart = selected && week ? monday(selected) : selected;
    const now = today();
    popup.replaceChildren();
    const heading = el('div', 'aurora-date-heading');
    const title = el('strong', '', week ? words.week : words.choose);
    title.id = `${popup.id}-title`;
    popup.setAttribute('aria-labelledby', title.id);
    heading.append(title, button('×', words.close, () => close(true), 'aurora-date-close'));
    popup.append(heading);
    const nav = el('div', 'aurora-date-navigation');
    const prev = button('‹', words.previous, () => moveMonth(-1, 'prev'), 'aurora-date-arrow');
    const next = button('›', words.next, () => moveMonth(1, 'next'), 'aurora-date-arrow');
    prev.dataset.control = 'prev'; next.dataset.control = 'next';
    const prevDate = monthDate(year, month - 1), nextDate = monthDate(year, month + 1);
    prev.disabled = !withinMonth(prevDate.getUTCFullYear(), prevDate.getUTCMonth());
    next.disabled = !withinMonth(nextDate.getUTCFullYear(), nextDate.getUTCMonth());
    const months = el('select', 'aurora-date-month');
    months.setAttribute('aria-label', words.month); months.dataset.control = 'month';
    for (let m = 0; m < 12; m++) {
        const option = el('option', '', monthFormat.format(monthDate(year, m)));
        option.value = String(m); option.disabled = !withinMonth(year, m); option.selected = m === month;
        months.append(option);
    }
    months.addEventListener('change', () => setView(monthDate(year, Number(months.value)), 'month'));
    const years = el('select', 'aurora-date-year');
    years.setAttribute('aria-label', words.year); years.dataset.control = 'year';
    const low = min?.getUTCFullYear() ?? Math.max(1, Math.min(1900, year - 100));
    const high = max?.getUTCFullYear() ?? Math.min(9999, Math.max(now.getUTCFullYear() + 100, year + 100));
    for (let y = Math.min(low, year); y <= Math.max(high, year); y++) {
        const option = el('option', '', String(y)); option.value = String(y); option.selected = y === year; years.append(option);
    }
    years.addEventListener('change', () => {
        const y = Number(years.value);
        const m = Array.from({ length: 12 }, (_, i) => i).filter(i => withinMonth(y, i));
        if (m.length) setView(monthDate(y, m.reduce((a, b) => Math.abs(a - month) <= Math.abs(b - month) ? a : b)), 'year');
    });
    nav.append(prev, months, years, next); popup.append(nav);
    const weekdays = el('div', 'aurora-date-weekdays'); weekdays.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 7; i++) weekdays.append(el('span', '', weekdayFormat.format(add(monthDate(2024, 0, 1), i)).replace(/\.$/, '')));
    popup.append(weekdays);
    const grid = el('div', 'aurora-date-grid');
    const first = monday(monthDate(year, month));
    const last = add(monthDate(year, month + 1), -1);
    const rowCount = Math.ceil((last - first + DAY) / (7 * DAY));
    const dayButtons = [];
    for (let row = 0; row < rowCount; row++) {
        const rowStart = add(first, row * 7);
        const line = el('div', 'aurora-date-row');
        if (week) {
            line.classList.add('is-week');
            if (selectedStart && +rowStart === +selectedStart) line.classList.add('is-selected-week');
        }
        for (let i = 0; i < 7; i++) {
            const date = add(rowStart, i);
            const active = week ? selectedStart && +rowStart === +selectedStart : selected && +date === +selected;
            const b = button(String(date.getUTCDate()), dateFormat.format(date), () => choose(date), 'aurora-date-day');
            b.dataset.date = iso(date);
            b.disabled = !valid(date);
            b.classList.toggle('is-outside', date.getUTCMonth() !== month);
            b.classList.toggle('is-today', +date === +now);
            b.classList.toggle('is-selected', !!active && (!week || i === 0));
            b.setAttribute('aria-pressed', String(!!active));
            if (+date === +now) b.setAttribute('aria-current', 'date');
            b.tabIndex = -1;
            dayButtons.push(b); line.append(b);
        }
        grid.append(line);
    }
    popup.append(grid);
    const initial = dayButtons.find(b => b.dataset.date === (selectedStart && iso(selectedStart)) && !b.disabled)
        || dayButtons.find(b => b.dataset.date === iso(now) && !b.disabled)
        || dayButtons.find(b => !b.disabled && !b.classList.contains('is-outside')) || dayButtons.find(b => !b.disabled);
    if (initial) initial.tabIndex = 0;
    grid.addEventListener('keydown', event => {
        const target = event.target.closest('[data-date]');
        if (!target) return;
        const date = parse(target.dataset.date);
        let destination;
        if (event.key === 'ArrowLeft') destination = add(date, -1);
        else if (event.key === 'ArrowRight') destination = add(date, 1);
        else if (event.key === 'ArrowUp') destination = add(date, -7);
        else if (event.key === 'ArrowDown') destination = add(date, 7);
        else if (event.key === 'Home') destination = monday(date);
        else if (event.key === 'End') destination = add(monday(date), 6);
        else if (event.key === 'PageUp' || event.key === 'PageDown') { event.preventDefault(); moveMonth(event.key === 'PageUp' ? -1 : 1, 'day'); return; }
        else return;
        event.preventDefault();
        if (!valid(destination)) return;
        const b = dayButtons.find(b => b.dataset.date === iso(destination));
        if (b) { dayButtons.forEach(n => n.tabIndex = -1); b.tabIndex = 0; b.focus(); }
        else setView(destination, iso(destination));
    });
    const footer = el('div', 'aurora-date-footer');
    if (!input.required && !week) footer.append(button(words.clear, null, () => choose(null), 'aurora-date-clear'));
    const current = week ? monday(now) : now;
    const currentButton = button(week ? words.currentWeek : words.today, null, () => choose(current), 'aurora-date-current');
    currentButton.disabled = !valid(current);
    footer.append(currentButton); popup.append(footer);
    position();
    if (!state) return;
    if (focus) {
        let target = focus === 'day' ? initial : dayButtons.find(b => b.dataset.date === focus) || nav.querySelector(`[data-control="${focus}"]`);
        if (!target || target.disabled) target = initial || months;
        if (target?.dataset.date) { dayButtons.forEach(b => b.tabIndex = -1); target.tabIndex = 0; }
        target?.focus({ preventScroll: true });
    }
}
function open(input) {
    if (!eligible(input) || typeof HTMLElement.prototype.showPopover !== 'function') return;
    if (state?.input === input) { close(true); return; }
    close();
    const min = parse(input.min), max = parse(input.max);
    let date = parse(input.value) || today();
    if (min && date < min) date = min;
    if (max && date > max) date = max;
    const popup = el('div', 'aurora-date-popover');
    popup.id = `aurora-date-popover-${++sequence}`;
    popup.setAttribute('popover', 'manual');
    popup.setAttribute('role', 'dialog');
    const host = input.closest('.modal, dialog, [popover]:popover-open') || document.body;
    host.append(popup);
    state = { input, popup, min, max, week: input.hasAttribute('data-week-picker'), year: date.getUTCFullYear(), month: date.getUTCMonth() };
    input.setAttribute('aria-haspopup', 'dialog');
    input.setAttribute('aria-controls', popup.id);
    input.setAttribute('aria-expanded', 'true');
    popup.showPopover();
    render('day');
}
function init() {
    if (document.documentElement.dataset.auroraDatePickerReady === 'true' || typeof HTMLElement.prototype.showPopover !== 'function') return;
    document.documentElement.dataset.auroraDatePickerReady = 'true';
    document.addEventListener('pointerdown', event => {
        if (state && !state.popup.contains(event.target) && event.target !== state.input) close();
        if (eligible(event.target) && event.button === 0) event.preventDefault();
    }, true);
    document.addEventListener('click', event => {
        if (!eligible(event.target)) return;
        event.preventDefault();
        event.stopPropagation();
        open(event.target);
    }, true);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && state) { event.preventDefault(); event.stopPropagation(); close(true); return; }
        if (eligible(event.target) && (event.key === 'Enter' || (event.altKey && event.key === 'ArrowDown') || event.key === 'F4')) {
            event.preventDefault(); open(event.target);
        }
    }, true);
    document.addEventListener('focusin', event => {
        if (state && event.target !== state.input && !state.popup.contains(event.target)) close();
    });
    document.addEventListener('change', event => { if (state?.input === event.target) close(); });
    window.addEventListener('resize', position);
    window.addEventListener('scroll', position, { capture: true, passive: true });
    new MutationObserver(() => { if (state && !state.input.isConnected) close(); }).observe(document.body, { childList: true, subtree: true });
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true }); else init();
