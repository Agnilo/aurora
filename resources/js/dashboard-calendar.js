import './aurora-date-picker.js';

export function initDashboardCalendar(doc = document) {
    if (doc.documentElement.dataset.calendarNavigationReady === 'true') return;
    doc.documentElement.dataset.calendarNavigationReady = 'true';
    const win = doc.defaultView;
    let pending = false;

    doc.addEventListener('click', async event => {
        if (event.defaultPrevented || (event.button !== undefined && event.button !== 0)
            || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest?.('.dashboard-ui-calendar-navigation a');
        if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const panel = link.closest('.dashboard-ui-calendar, .dashboard-ui-weekly');
        if (!panel) return;

        const target = new URL(link.href, win.location.href);
        const current = new URL(win.location.href);
        const parameter = panel.matches('.dashboard-ui-weekly') ? 'week' : 'month';
        if (target.origin !== current.origin || target.pathname !== current.pathname
            || !target.searchParams.has(parameter)) return;
        event.preventDefault();
        await navigate(panel, target, link);
    });

    doc.addEventListener('change', async event => {
        const picker = event.target.closest?.('[data-week-picker]');
        if (!picker) return;
        if (!picker.value || !picker.validity.valid || pending) {
            picker.value = picker.defaultValue;
            return;
        }
        const panel = picker.closest('.dashboard-ui-weekly');
        if (!panel) return;
        const target = new URL(win.location.href);
        target.searchParams.set('week', picker.value);
        await navigate(panel, target, picker);
    });

    async function navigate(panel, target, trigger) {
        const weekly = panel.matches('.dashboard-ui-weekly');
        const parameter = weekly ? 'week' : 'month';
        const selector = weekly ? '.dashboard-ui-weekly' : '.dashboard-ui-calendar';
        const contentSelector = weekly ? '.dashboard-ui-chart' : '.dashboard-ui-calendar-grid';
        const current = new URL(win.location.href);
        if (pending) return;
        pending = true;

        let value = target.searchParams.get(parameter);
        current.searchParams.set(parameter, value);
        const navLinks = [...panel.querySelectorAll('.dashboard-ui-calendar-navigation a')];
        const picker = panel.querySelector('[data-week-picker]');
        const keepFocus = panel.contains(doc.activeElement);
        if (picker) picker.disabled = true;
        panel.querySelector('[data-calendar-load-error]')?.remove();
        panel.setAttribute('aria-busy', 'true');
        navLinks.forEach(anchor => anchor.setAttribute('aria-disabled', 'true'));
        const controller = new win.AbortController();
        const timeout = win.setTimeout(() => controller.abort(), 15000);

        try {
            const response = await win.fetch(current.href, {
                headers: { Accept: 'text/html' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
            });
            if (!response.ok || response.redirected) throw new Error('Dashboard request failed');
            const html = await response.text();
            const parsed = new win.DOMParser().parseFromString(html, 'text/html');
            const replacement = parsed.querySelector(selector);
            if (!replacement || !replacement.querySelector(contentSelector)
                || !replacement.querySelector('.dashboard-ui-calendar-navigation')) {
                throw new Error('Dashboard panel missing from response');
            }
            if (!panel.isConnected) return;

            replacement.querySelectorAll('script').forEach(script => script.remove());
            replacement.removeAttribute('aria-busy');
            replacement.querySelectorAll('[aria-disabled="true"] a, a[aria-disabled="true"]').forEach(anchor => anchor.removeAttribute('aria-disabled'));
            if (weekly) {
                value = replacement.querySelector('[data-week-picker]')?.value || value;
                current.searchParams.set(parameter, value);
            }
            const direction = navLinks.indexOf(trigger);
            const scroll = { left: win.scrollX, top: win.scrollY, behavior: 'instant' };
            panel.replaceWith(replacement);

            doc.querySelectorAll('a[href]').forEach(anchor => {
                if (replacement.contains(anchor)) return;
                const url = new URL(anchor.href, win.location.href);
                if (url.origin !== current.origin || url.pathname !== current.pathname) return;
                if (anchor.closest('.dashboard-ui-calendar-navigation') && anchor.closest(selector)) return;
                if (url.searchParams.has(parameter) || url.searchParams.has('tasks') || url.searchParams.has('page')) {
                    url.searchParams.set(parameter, value);
                    anchor.href = url.href;
                }
            });
            try {
                win.history.replaceState(win.history.state, '', current.href);
            } catch { }
            if (keepFocus) {
                const links = replacement.querySelectorAll('.dashboard-ui-calendar-navigation a');
                const focusTarget = trigger === picker ? replacement.querySelector('[data-week-picker]') : (links[direction] || links[0]);
                focusTarget?.focus({ preventScroll: true });
            }
            win.scrollTo(scroll);
        } catch {
            if (panel.isConnected) {
                const message = doc.createElement('p');
                message.className = 'dashboard-ui-error';
                message.dataset.calendarLoadError = '';
                message.setAttribute('role', 'alert');
                message.textContent = doc.body.dataset.calendarLoadErrorMessage;
                panel.append(message);
            }
        } finally {
            win.clearTimeout(timeout);
            panel.removeAttribute('aria-busy');
            navLinks.forEach(anchor => anchor.removeAttribute('aria-disabled'));
            if (picker) { picker.disabled = false; picker.value = picker.defaultValue; }
            pending = false;
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initDashboardCalendar(), { once: true });
} else {
    initDashboardCalendar();
}

function positionDayPopover(panel) {
    const trigger = document.querySelector(`[popovertarget="${panel.id}"].dashboard-day-trigger`);
    if (!trigger) return;
    const anchor = trigger.getBoundingClientRect();
    const box = panel.getBoundingClientRect();
    const width = document.documentElement.clientWidth;
    const height = document.documentElement.clientHeight;
    const below = anchor.bottom + 5;
    const above = anchor.top - box.height - 5;
    panel.style.left = Math.max(8, Math.min(anchor.left + (anchor.width - box.width) / 2, width - box.width - 8)) + 'px';
    panel.style.top = Math.max(8, Math.min(below + box.height <= height ? below : above, height - box.height - 8)) + 'px';
}

document.addEventListener('toggle', event => {
    if (event.newState === 'open' && event.target.matches('.dashboard-day-popover')) {
        positionDayPopover(event.target);
    }
}, true);

function repositionOpenDay() {
    const panel = document.querySelector('.dashboard-day-popover:popover-open');
    if (panel) positionDayPopover(panel);
}
window.addEventListener('resize', repositionOpenDay);
window.addEventListener('scroll', repositionOpenDay, { passive: true });
