@extends('layouts.admin')

@section('content')
    <div class="container py-4 admin-mascot admin-mascot-list">
        <h1 class="fw-bold mb-4">{{ t('mascot.admin.title') }}</h1>
        @if(session('success') || $errors->any())
            <div class="admin-mascot-toast-stack" data-mascot-admin-toasts>
                @if(session('success'))
                    <div class="admin-mascot-toast admin-mascot-toast--success" role="status" data-dismiss-after="5000">
                        <x-lucide-check-circle class="aurora-icon" width="19" height="19" aria-hidden="true" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="admin-mascot-toast admin-mascot-toast--error" role="alert" data-dismiss-after="8000">
                        <x-lucide-alert-circle class="aurora-icon" width="19" height="19" aria-hidden="true" />
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
        <a class="btn btn-primary mb-3"
            href="{{ route('admin.gamification.mascot-items.create', ['locale' => app()->getLocale()]) }}">
            + {{ t('button.add_new') }}
        </a>
        <div class="admin-mascot-filters mb-3">
            @foreach(['' => 'mascot.admin.all', 'hair' => 'mascot.slot.hair', 'outfit' => 'mascot.slot.outfit', 'top' => 'mascot.slot.top', 'bottom' => 'mascot.slot.bottom', 'shoes' => 'mascot.slot.shoes', 'body' => 'mascot.slot.body', 'accessory' => 'mascot.slot.accessory'] as $slot => $label)
                <a class="btn {{ request('slot', '') === $slot ? 'btn-primary' : 'btn-outline-secondary' }}"
                    href="{{ route('admin.gamification.mascot-items.index', array_filter(['locale' => app()->getLocale(), 'slot' => $slot, 'status' => request('status'), 'per_page' => request('per_page')])) }}">{{ t($label) }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}"
            class="admin-no-surface admin-mascot-status-filter d-flex flex-wrap align-items-end gap-2 mb-3">
            @if(request()->filled('slot'))<input type="hidden" name="slot" value="{{ request('slot') }}">@endif
            <div class="admin-mascot-status-field">
                <label for="mascot-status-filter" class="form-label">{{ t('mascot.admin.status') }}</label>
                <select id="mascot-status-filter" name="status" class="form-select">
                    <option value="">{{ t('mascot.admin.all') }}</option>
                    <option value="published" @selected(request('status') === 'published')>{{ t('mascot.admin.published') }}
                    </option>
                    <option value="draft" @selected(request('status') === 'draft')>{{ t('mascot.admin.draft') }}</option>
                </select>
            </div>
            <input type="hidden" name="per_page" value="{{ $items->perPage() }}">
            <button class="btn btn-outline-secondary" type="submit">{{ t('mascot.admin.apply_filters') }}</button>
        </form>
        <form id="mascot-bulk-form" method="POST"
            action="{{ route('admin.gamification.mascot-items.bulk', ['locale' => app()->getLocale()]) }}"
            class="admin-no-surface admin-mascot-bulkbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"
            data-mascot-bulk data-delete-confirm="{{ t('mascot.admin.confirm_delete') }}"
            data-draft-confirm="{{ t('mascot.admin.confirm_draft') }}"
            data-price-confirm="{{ t('mascot.admin.confirm_price') }}">
            @csrf
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong>{{ t('mascot.admin.selected') }}: <span data-selected-count>0</span></strong>
                <select class="form-select w-auto" name="action" aria-label="{{ t('mascot.admin.bulk_action') }}"
                    data-bulk-action required>
                    <option value="">{{ t('mascot.admin.bulk_action') }}</option>
                    <option value="publish">{{ t('mascot.admin.bulk_publish') }}</option>
                    <option value="draft">{{ t('mascot.admin.bulk_draft') }}</option>
                    <option value="price">{{ t('mascot.admin.bulk_price') }}</option>
                    <option value="delete">{{ t('mascot.admin.delete') }}</option>
                </select>
                <div data-bulk-price hidden>
                    <input type="number" class="form-control admin-mascot-number" name="price" min="0" max="1000000"
                        step="1" placeholder="{{ t('mascot.admin.price') }}" aria-label="{{ t('mascot.admin.price') }}"
                        disabled>
                </div>
                <button class="btn btn-primary" type="submit" data-bulk-submit
                    disabled>{{ t('mascot.admin.apply') }}</button>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-clear-selection
                disabled>{{ t('mascot.admin.clear_selection') }}</button>
            <p class="w-100 mb-0 small text-danger" data-bulk-blocked role="status" hidden>
                {{ t('mascot.admin.delete_used') }}</p>
        </form>
        <div class="card shadow-sm admin-mascot-catalog">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><input class="form-check-input m-0" type="checkbox" data-select-page
                                        aria-label="{{ t('mascot.admin.select_page') }}"
                                        title="{{ t('mascot.admin.select_page') }}"></th>
                                <th scope="col">{{ t('mascot.admin.item') }}</th>
                                <th scope="col">{{ t('mascot.admin.type') }}</th>
                                <th scope="col">{{ t('mascot.admin.price') }}</th>
                                <th scope="col">{{ t('mascot.admin.status') }}</th>
                                <th scope="col" class="text-end">{{ t('admin.game_overview.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $protected = $item->key === config('mascot.defaults.' . $item->slot);
                                    $cannotDelete = $protected || in_array((int) $item->id, $usedIds, true);
                                    $deleteReason = $protected ? t('mascot.admin.default_protected') : t('errors.cannot_delete');
                                @endphp
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" form="mascot-bulk-form"
                                            class="form-check-input m-0" data-item-check
                                            data-cannot-delete="{{ $cannotDelete ? 'true' : 'false' }}" @disabled($protected)
                                            aria-label="{{ t($item->translation_key) }}" @if($protected)
                                            title="{{ t('mascot.admin.default_protected') }}" @endif></td>
                                    <td>
                                        <div class="admin-mascot-item">
                                            <span class="admin-mascot-thumb admin-mascot-thumb--{{ $item->slot }}"
                                                aria-hidden="true">
                                                @foreach($item->layers ?? [] as $path)
                                                    <img src="{{ asset($path) }}" alt="" loading="lazy">
                                                @endforeach
                                            </span>
                                            <strong>{{ t($item->translation_key) }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ t('mascot.slot.' . $item->slot) }}</td>
                                    <td><span class="admin-mascot-number">{{ $item->price }}</span></td>
                                    <td>{{ t($item->active ? 'mascot.admin.published' : 'mascot.admin.draft') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                            <a class="btn btn-sm btn-outline-primary text-nowrap"
                                                href="{{ route('admin.gamification.mascot-items.edit', ['locale' => app()->getLocale(), 'mascot_item' => $item->id]) }}">{{ t('button.edit') }}</a>
                                            @if($cannotDelete)
                                                <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap"
                                                    disabled>{{ t('button.delete') }}</button>
                                                <button type="button"
                                                    class="btn btn-sm border-0 bg-transparent text-muted p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                    data-mascot-delete-info data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ $deleteReason }}" aria-label="{{ $deleteReason }}">
                                                    <x-lucide-info width="12" height="12" aria-hidden="true" />
                                                </button>
                                            @else
                                                <form method="POST" class="m-0 d-inline"
                                                    action="{{ route('admin.gamification.mascot-items.destroy', ['locale' => app()->getLocale(), 'mascot_item' => $item->id]) }}"
                                                    data-aurora-confirm-delete
                                                    data-confirm="{{ t('mascot.admin.confirm_delete') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger text-nowrap">{{ t('button.delete') }}</button>
                                                </form>
                                                <span
                                                    class="btn btn-sm border-0 p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 invisible"
                                                    aria-hidden="true"><x-lucide-info width="12" height="12"
                                                        aria-hidden="true" /></span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ t('mascot.admin.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="admin-mascot-pagination d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
            <form method="GET"
                action="{{ route('admin.gamification.mascot-items.index', ['locale' => app()->getLocale()]) }}"
                class="d-flex flex-nowrap align-items-center gap-2 mb-0">
                @foreach(request()->only('slot', 'status') as $filterName => $filterValue)
                    <input type="hidden" name="{{ $filterName }}" value="{{ $filterValue }}">
                @endforeach
                <label for="mascot-page-size" class="small text-nowrap mb-0">{{ t('mascot.admin.per_page') }}</label>
                <select id="mascot-page-size" name="per_page" class="form-select form-select-sm w-auto"
                    onchange="this.form.requestSubmit()">
                    @foreach([24, 48, 100] as $size)
                        <option value="{{ $size }}" @selected($items->perPage() === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </form>
            <div class="ms-auto">
                @include('admin.translations.pagination', ['keyPage' => $items])
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('admin.partials.confirm-dialog')
    <script>
        (() => {
            const toasts = document.querySelector('[data-mascot-admin-toasts]');
            if (toasts) {
                document.body.append(toasts);
                for (const toast of toasts.querySelectorAll('[data-dismiss-after]')) {
                    window.setTimeout(() => {
                        toast.remove();
                        if (!toasts.children.length) toasts.remove();
                    }, Number(toast.dataset.dismissAfter));
                }
            }
            document.querySelectorAll('[data-mascot-delete-info]').forEach(element => {
                if (window.bootstrap?.Tooltip) {
                    bootstrap.Tooltip.getOrCreateInstance(element, { container: 'body', trigger: 'hover focus' });
                }
            });
            const form = document.querySelector('[data-mascot-bulk]');
            if (!form) return;
            const checks = [...document.querySelectorAll('[data-item-check]:not(:disabled)')];
            const all = document.querySelector('[data-select-page]');
            const count = form.querySelector('[data-selected-count]');
            const action = form.querySelector('[data-bulk-action]');
            const priceBox = form.querySelector('[data-bulk-price]');
            const price = priceBox.querySelector('input');
            const submit = form.querySelector('[data-bulk-submit]');
            const clear = form.querySelector('[data-clear-selection]');
            const sync = () => {
                const selected = checks.filter(check => check.checked).length;
                count.textContent = String(selected);
                all.checked = selected > 0 && selected === checks.length;
                all.indeterminate = selected > 0 && selected < checks.length;
                all.disabled = !checks.length;
                priceBox.hidden = action.value !== 'price';
                price.disabled = priceBox.hidden;
                price.required = !priceBox.hidden;
                const blockedDelete = action.value === 'delete' && checks.some(check => check.checked && check.dataset.cannotDelete === 'true');
                submit.disabled = selected === 0 || !action.value || blockedDelete || (!priceBox.hidden && !price.checkValidity());
                form.querySelector('[data-bulk-blocked]').hidden = !blockedDelete;
                clear.disabled = selected === 0;
            };
            for (const check of checks) check.addEventListener('change', sync);
            all.addEventListener('change', () => { for (const check of checks) check.checked = all.checked; sync(); });
            clear.addEventListener('click', () => { for (const check of checks) check.checked = false; sync(); });
            action.addEventListener('change', sync);
            price.addEventListener('input', sync);
            let submitting = false;
            form.addEventListener('submit', async event => {
                sync();
                if (submitting || submit.disabled) { event.preventDefault(); return; }
                const selectedAction = action.value;
                const message = { delete: form.dataset.deleteConfirm, draft: form.dataset.draftConfirm, price: form.dataset.priceConfirm }[selectedAction];
                if (!message) { submitting = true; return; }
                event.preventDefault();
                if (!await window.auroraAdminConfirm(message, { danger: selectedAction === 'delete' })) return;
                sync();
                if (submitting || submit.disabled || action.value !== selectedAction || !form.reportValidity()) return;
                submitting = true;
                submit.disabled = true;
                HTMLFormElement.prototype.submit.call(form);
            });
            window.addEventListener('pageshow', () => { submitting = false; sync(); });
            sync();
        })();
    </script>
@endsection