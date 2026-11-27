const allowedTypes = new Set(['success', 'error', 'warning', 'info', 'completion']);
const storageKey = 'aurora.system-toast.reload.v1';
const timers = new WeakMap();
const root = document.getElementById('aurora-system-toasts');
const isProminent = () => document.body.dataset.gamification === 'prominent';
const normalize = value => [...new Set((Array.isArray(value) ? value : [value])
    .filter(message => typeof message === 'string').map(message => message.trim()).filter(Boolean))];
const defaultDuration = type => ['error', 'warning'].includes(type) ? 8000 : 5000;

function svg(paths) {
    const ns = 'http://www.w3.org/2000/svg';
    const node = document.createElementNS(ns, 'svg');
    for (const [key, value] of Object.entries({ viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.7', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'aria-hidden': 'true' })) node.setAttribute(key, value);
    for (const [tag, attrs] of paths) {
        const child = document.createElementNS(ns, tag);
        Object.entries(attrs).forEach(([key, value]) => child.setAttribute(key, value));
        node.append(child);
    }
    return node;
}

function icon(type) {
    const shape = ['success', 'completion'].includes(type) ? 'm8 12 2.5 2.5L16 9'
        : ['error', 'warning'].includes(type) ? 'M12 7.5v5M12 16h.01' : 'M12 11v5M12 8h.01';
    return svg([['circle', { cx: '12', cy: '12', r: '9' }], ['path', { d: shape }]]);
}

function signature(type, messages) { return `${type}:${normalize(messages).join('\n')}`; }

function dismiss(toast) {
    const state = timers.get(toast);
    if (state) clearTimeout(state.timer);
    timers.delete(toast);
    toast.remove();
}

function activate(toast) {
    if (timers.has(toast)) return;
    const type = toast.dataset.toastType;
    if (type === 'completion' && !isProminent()) { toast.remove(); return; }
    const duration = Number(toast.dataset.toastDuration);
    const messages = [...toast.querySelectorAll('.aurora-system-toast__message li, .aurora-system-toast__message > span')].map(node => node.textContent);
    toast.dataset.toastSignature = signature(type, messages);
    const state = { remaining: Number.isFinite(duration) && duration >= 0 ? duration : defaultDuration(type), timer: null, started: 0, pointer: false, focus: false };
    timers.set(toast, state);
    const pause = () => {
        if (state.timer === null) return;
        clearTimeout(state.timer);
        state.timer = null;
        state.remaining = Math.max(0, state.remaining - (performance.now() - state.started));
    };
    const resume = () => {
        if (state.pointer || state.focus || state.timer !== null || duration === 0) return;
        if (state.remaining <= 0) { dismiss(toast); return; }
        state.started = performance.now();
        state.timer = window.setTimeout(() => dismiss(toast), state.remaining);
    };
    toast.addEventListener('pointerenter', () => { state.pointer = true; pause(); });
    toast.addEventListener('pointerleave', () => { state.pointer = false; resume(); });
    toast.addEventListener('focusin', () => { state.focus = true; pause(); });
    toast.addEventListener('focusout', event => { if (!toast.contains(event.relatedTarget)) { state.focus = false; resume(); } });
    toast.querySelector('[data-toast-close]')?.addEventListener('click', () => dismiss(toast));
    resume();
}

function show(message, type = 'info', options = {}) {
    if (!root || !allowedTypes.has(type) || (type === 'completion' && !isProminent())) return null;
    const messages = normalize(message);
    if (!messages.length) return null;
    const key = signature(type, messages);
    const existing = [...root.children].find(node => node.dataset.toastSignature === key);
    if (existing) return existing;
    const toast = document.createElement('div');
    toast.className = `aurora-system-toast aurora-system-toast--${type}`;
    toast.dataset.toastType = type;
    toast.dataset.toastDuration = String(options.duration === 0 ? 0 : Number.isFinite(options.duration) && options.duration > 0 ? options.duration : defaultDuration(type));
    const symbol = document.createElement('span');
    symbol.className = 'aurora-system-toast__icon';
    symbol.setAttribute('aria-hidden', 'true');
    symbol.append(icon(type));
    const content = document.createElement('div');
    content.className = 'aurora-system-toast__message';
    content.setAttribute('role', ['error', 'warning'].includes(type) ? 'alert' : 'status');
    content.setAttribute('aria-atomic', 'true');
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'aurora-system-toast__close';
    close.dataset.toastClose = '';
    close.setAttribute('aria-label', root.dataset.closeLabel);
    close.title = root.dataset.closeLabel;
    close.append(svg([['path', { d: 'm6 6 12 12M18 6 6 18' }]]));
    toast.append(symbol, content, close);
    root.append(toast);
    if (messages.length > 1) {
        const list = document.createElement('ul');
        for (const value of messages) {
            const item = document.createElement('li');
            item.textContent = value;
            list.append(item);
        }
        content.append(list);
    } else {
        const text = document.createElement('span');
        text.textContent = messages[0];
        content.append(text);
    }
    activate(toast);
    return toast;
}

function persistForReload(message, type = 'info') {
    const messages = normalize(message);
    if (!messages.length || !allowedTypes.has(type) || (type === 'completion' && !isProminent())) return false;
    try {
        sessionStorage.setItem(storageKey, JSON.stringify({ messages, type, path: location.pathname, time: Date.now() }));
        return true;
    } catch { return false; }
}

function consumeReload() {
    try {
        const raw = sessionStorage.getItem(storageKey);
        if (!raw) return;
        sessionStorage.removeItem(storageKey);
        const pending = JSON.parse(raw);
        if (pending.path === location.pathname && Date.now() - pending.time < 30000 && Date.now() >= pending.time) {
            show(pending.messages, pending.type);
        }
    } catch { }
}

if (root) {
    [...root.children].forEach(activate);
    window.AuroraToast = Object.freeze({ show, persistForReload });
    document.addEventListener('aurora:toast', event => {
        const { type = 'info', message, duration } = event.detail || {};
        show(message, type, { duration });
    });
    document.addEventListener('aurora:completion', event => show(event.detail, 'completion'));
    consumeReload();
}
