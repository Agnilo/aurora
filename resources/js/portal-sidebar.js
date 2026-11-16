export function initPortalSidebar(doc = document) {
    const toggle = doc.querySelector('[data-rail-toggle]');
    if (!toggle || toggle.dataset.ready === 'true') return;
    toggle.dataset.ready = 'true';
    const root = doc.documentElement;

    function syncToggle() {
        const expanded = root.dataset.auroraSidebar === 'expanded';
        const label = expanded ? toggle.dataset.collapseLabel : toggle.dataset.expandLabel;
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
    }

    syncToggle();
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        root.dataset.auroraSidebar = root.dataset.auroraSidebar === 'expanded' ? 'collapsed' : 'expanded';
        syncToggle();
        try {
            doc.defaultView.localStorage.setItem('aurora.sidebar', root.dataset.auroraSidebar);
        } catch {}
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initPortalSidebar(), { once: true });
} else {
    initPortalSidebar();
}
