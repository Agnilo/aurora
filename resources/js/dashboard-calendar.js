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
        const calendar = link.closest('.dashboard-ui-calendar');
        if (!calendar) return;

        const target = new URL(link.href, win.location.href);
        const current = new URL(win.location.href);
        if (target.origin !== current.origin || target.pathname !== current.pathname
            || !target.searchParams.has('month')) return;
        event.preventDefault();
        if (pending) return;
        pending = true;

        const month = target.searchParams.get('month');
        current.searchParams.set('month', month);
        const navLinks = [...calendar.querySelectorAll('.dashboard-ui-calendar-navigation a')];
        calendar.querySelector('[data-calendar-load-error]')?.remove();
        calendar.setAttribute('aria-busy', 'true');
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
            if (!response.ok) throw new Error('Calendar request failed');
            if (response.redirected) throw new Error('Calendar request redirected');
            const html = await response.text();
            const parsed = new win.DOMParser().parseFromString(html, 'text/html');
            const replacement = parsed.querySelector('.dashboard-ui-calendar');
            if (!replacement || !replacement.querySelector('.dashboard-ui-calendar-grid')
                || !replacement.querySelector('.dashboard-ui-calendar-navigation')) {
                throw new Error('Calendar missing from response');
            }
            if (!calendar.isConnected) return;

            replacement.querySelectorAll('script').forEach(script => script.remove());
            replacement.removeAttribute('aria-busy');
            replacement.querySelectorAll('[aria-disabled]').forEach(anchor => anchor.removeAttribute('aria-disabled'));
            replacement.style.minHeight = `${calendar.getBoundingClientRect().height}px`;
            const keepFocus = calendar.contains(doc.activeElement);
            const direction = navLinks.indexOf(link);
            const scroll = { left: win.scrollX, top: win.scrollY, behavior: 'instant' };
            calendar.replaceWith(replacement);

            doc.querySelectorAll('a[href]').forEach(anchor => {
                if (replacement.contains(anchor)) return;
                const url = new URL(anchor.href, win.location.href);
                if (url.origin === current.origin && url.pathname === current.pathname
                    && url.searchParams.has('month')) {
                    url.searchParams.set('month', month);
                    anchor.href = url.href;
                }
            });
            try {
                win.history.replaceState(win.history.state, '', current.href);
            } catch { }
            if (keepFocus) replacement.querySelectorAll('.dashboard-ui-calendar-navigation a')[direction]?.focus({ preventScroll: true });
            win.scrollTo(scroll);
        } catch {
            if (calendar.isConnected) {
                const message = doc.createElement('p');
                message.className = 'dashboard-ui-error';
                message.dataset.calendarLoadError = '';
                message.setAttribute('role', 'alert');
                message.textContent = doc.body.dataset.calendarLoadErrorMessage;
                calendar.append(message);
            }
        } finally {
            win.clearTimeout(timeout);
            calendar.removeAttribute('aria-busy');
            navLinks.forEach(anchor => anchor.removeAttribute('aria-disabled'));
            pending = false;
        }
    });
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
