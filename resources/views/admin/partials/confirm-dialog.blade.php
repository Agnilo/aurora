<dialog id="aurora-admin-confirm" class="aurora-admin-confirm" aria-labelledby="aurora-admin-confirm-title"
    aria-describedby="aurora-admin-confirm-message" data-confirm-label="{{ t('admin.confirm_dialog.confirm') }}"
    data-delete-label="{{ t('button.delete') }}">
    <form method="dialog" class="aurora-admin-confirm-form">
        <h2 id="aurora-admin-confirm-title">{{ t('admin.confirm_dialog.title') }}</h2>
        <p id="aurora-admin-confirm-message" data-confirm-message></p>
        <div class="aurora-admin-confirm-actions">
            <button type="submit" value="cancel" class="btn btn-outline-secondary"
                autofocus>{{ t('button.cancel') }}</button>
            <button type="submit" value="confirm" class="btn btn-primary"
                data-confirm-accept>{{ t('admin.confirm_dialog.confirm') }}</button>
        </div>
    </form>
</dialog>
<script>
    (() => {
        const dialog = document.getElementById('aurora-admin-confirm');
        if (!dialog || window.auroraAdminConfirm) return;
        document.body.append(dialog);
        const message = dialog.querySelector('[data-confirm-message]');
        const accept = dialog.querySelector('[data-confirm-accept]');
        let pending = null;
        dialog.addEventListener('close', () => {
            const resolve = pending;
            pending = null;
            if (resolve) resolve(dialog.returnValue === 'confirm');
        });
        dialog.addEventListener('cancel', event => {
            event.preventDefault();
            dialog.close('cancel');
        });
        window.auroraAdminConfirm = (text, { danger = false } = {}) => {
            if (pending || dialog.open || typeof dialog.showModal !== 'function') return Promise.resolve(false);
            message.textContent = text;
            accept.textContent = danger ? dialog.dataset.deleteLabel : dialog.dataset.confirmLabel;
            accept.classList.toggle('btn-primary', !danger);
            accept.classList.toggle('aurora-admin-confirm-danger', danger);
            dialog.returnValue = 'cancel';
            return new Promise(resolve => {
                pending = resolve;
                try {
                    dialog.showModal();
                } catch (error) {
                    pending = null;
                    resolve(false);
                }
            });
        };
        const submitted = new WeakSet();
        for (const form of document.querySelectorAll('form[data-aurora-confirm-delete]')) {
            form.addEventListener('submit', async event => {
                event.preventDefault();
                if (submitted.has(form)) return;
                if (!await window.auroraAdminConfirm(form.dataset.confirm, { danger: true })) return;
                if (submitted.has(form) || !form.reportValidity()) return;
                submitted.add(form);
                for (const button of form.querySelectorAll('button[type="submit"]')) button.disabled = true;
                HTMLFormElement.prototype.submit.call(form);
            });
        }
        window.addEventListener('pageshow', event => {
            if (!event.persisted) return;
            for (const form of document.querySelectorAll('form[data-aurora-confirm-delete]')) {
                if (!submitted.has(form)) continue;
                submitted.delete(form);
                for (const button of form.querySelectorAll('button[type="submit"]')) button.disabled = false;
            }
        });
    })();
</script>