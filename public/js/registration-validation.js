(() => {
    const form = document.getElementById('registration-form');
    if (!form) return;
    const messages = JSON.parse(form.dataset.messages);
    const names = ['name', 'email', 'password', 'password_confirmation'];
    const inputs = Object.fromEntries(names.map(name => [name, form.elements.namedItem(name)]));
    const touched = new Set();
    const serverErrors = new Map();
    const button = form.querySelector('[type="submit"]');
    let pending = false;
    let cooldown = 0;
    const boxes = {};
    for (const name of names) {
        let box = document.getElementById(`${name}-error`);
        if (!box) {
            box = document.createElement('div');
            box.id = `${name}-error`;
            inputs[name].closest('.auth-field').append(box);
        }
        box.setAttribute('aria-live', 'polite');
        boxes[name] = box;
        inputs[name].setAttribute('aria-describedby', `${name === 'password' ? 'password-hint ' : ''}${box.id}`);
        if (box.textContent.trim()) touched.add(name);
    }
    const text = key => messages[key];
    function errors(name) {
        const value = inputs[name].value;
        const result = [];
        if (name === 'name') {
            if (!value.trim()) result.push(text('register_name_required'));
            else if ([...value].length > 255) result.push(text('register_name_long'));
        } else if (name === 'email') {
            if (!value.trim()) result.push(text('register_email_required'));
            else if ([...value].length > 255) result.push(text('register_email_long'));
            else if (inputs.email.validity.typeMismatch) result.push(text('register_email_invalid'));
            else if (value !== value.toLowerCase()) result.push(text('register_email_lowercase'));
        } else if (name === 'password') {
            if (!value) return [text('register_password_required')];
            if (value.includes('\0')) return [text('password_invalid_character')];
            if (new TextEncoder().encode(value).length > 72) return [text('password_too_long')];
            if ([...value].length < 8) result.push(text('password_too_short'));
            if (!/\p{L}/u.test(value)) result.push(text('password_letter'));
            if (!/[0-9]/.test(value)) result.push(text('password_number'));
            if (!/[\p{P}\p{S}]/u.test(value)) result.push(text('password_symbol'));
        } else {
            if (!value) result.push(text('register_password_repeat'));
            else if (value !== inputs.password.value) result.push(text('register_password_mismatch'));
        }
        return result;
    }
    function show(name, list) {
        const box = boxes[name];
        box.replaceChildren();
        for (const message of list) {
            const item = document.createElement('span');
            item.className = 'auth-error';
            item.style.display = 'block';
            item.textContent = message;
            box.append(item);
        }
        box.hidden = list.length === 0;
        inputs[name].setAttribute('aria-invalid', list.length ? 'true' : 'false');
    }
    function validate(name) {
        const list = errors(name);
        if (serverErrors.has(name)) list.push(...serverErrors.get(name));
        show(name, list);
        return list.length === 0;
    }
    for (const name of names) {
        inputs[name].addEventListener('blur', () => {
            touched.add(name);
            validate(name);
        });
        inputs[name].addEventListener('input', () => {
            serverErrors.delete(name);
            if (touched.has(name)) validate(name);
            if (name === 'password' && touched.has('password_confirmation')) {
                serverErrors.delete('password_confirmation');
                validate('password_confirmation');
            }
        });
    }
    function announce(message) {
        if (message) {
            document.dispatchEvent(new CustomEvent('aurora:toast', { detail: { type: 'error', message } }));
        }
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (pending || Date.now() < cooldown) return;
        announce('');
        let valid = true;
        for (const name of names) {
            touched.add(name);
            if (!validate(name)) valid = false;
        }
        if (!valid) {
            names.map(name => inputs[name]).find(input => input.getAttribute('aria-invalid') === 'true')?.focus();
            return;
        }
        pending = true;
        button.disabled = true;
        form.setAttribute('aria-busy', 'true');
        const payload = new FormData(form);
        names.forEach(name => inputs[name].readOnly = true);
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: payload, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (response.ok && typeof data.redirect === 'string') {
                const destination = new URL(data.redirect, location.href);
                if (destination.origin !== location.origin) throw new Error('Invalid destination');
                location.assign(destination.href);
                return;
            }
            if (response.status === 422 && data.errors) {
                let fieldFound = false;
                for (const name of names) {
                    const list = Array.isArray(data.errors[name]) ? data.errors[name].filter(item => typeof item === 'string') : [];
                    if (list.length) { serverErrors.set(name, list); fieldFound = true; }
                    show(name, list);
                }
                if (!fieldFound) announce(text('register_request_failed'));
                names.map(name => inputs[name]).find(input => input.getAttribute('aria-invalid') === 'true')?.focus();
            } else if (response.status === 419 || response.status === 409) {
                announce(text('register_session_expired'));
            } else if (response.status === 429) {
                announce(text('register_try_later'));
                const seconds = Number(response.headers.get('Retry-After'));
                cooldown = Date.now() + (Number.isFinite(seconds) && seconds > 0 ? seconds : 60) * 1000;
            } else {
                announce(text('register_request_failed'));
            }
        } catch {
            announce(text('register_request_failed'));
        } finally {
            pending = false;
            button.disabled = false;
            form.removeAttribute('aria-busy');
            names.forEach(name => inputs[name].readOnly = false);
        }
    });
})();
