@php
    $systemToasts = [];
    $addSystemToast = static function (string $type, mixed $messages, int $duration) use (&$systemToasts): void {
        $messages = array_values(array_unique(array_filter(array_map(
            static fn($value) => is_string($value) ? trim($value) : '',
            is_array($messages) ? $messages : [$messages]
        ), static fn($value) => $value !== '')));
        if (!$messages) {
            return;
        }
        $key = $type . ':' . implode("\n", $messages);
        $systemToasts[$key] = compact('type', 'messages', 'duration');
    };

    $addSystemToast('success', session('success'), 5000);
    $addSystemToast('error', session('error'), 8000);
    $addSystemToast('warning', session('warning'), 8000);
    $addSystemToast('info', session('info'), 5000);
    $status = session('status');
    if (is_string($status) && $status !== '') {
        $statusMessage = match ($status) {
            'profile-updated', 'password-updated' => t('plans.saved'),
            'verification-link-sent' => request()->routeIs('verification.notice')
                ? t('auth.verify_sent') : t('profile.verification_link_sent'),
            default => $status,
        };
        $addSystemToast('success', $statusMessage, 5000);
    }
    if (session('mascot_saved')) {
        $addSystemToast('success', t('mascot.saved'), 5000);
    }
    $addSystemToast('completion', session('completion_notice'), 5000);

    $validationMessages = [];
    foreach (($errors ?? new \Illuminate\Support\ViewErrorBag())->getBags() as $bag) {
        $validationMessages = array_merge($validationMessages, $bag->all());
    }
    $addSystemToast('error', $validationMessages, 0);
@endphp
<div id="aurora-system-toasts" class="aurora-system-toasts" role="region" aria-label="{{ t('system.toast_region') }}"
    data-close-label="{{ t('system.toast_close') }}">
    @foreach($systemToasts as $toast)
        <div class="aurora-system-toast aurora-system-toast--{{ $toast['type'] }}" data-toast-type="{{ $toast['type'] }}"
            data-toast-duration="{{ $toast['duration'] }}">
            <span class="aurora-system-toast__icon" aria-hidden="true">
                @if(in_array($toast['type'], ['success', 'completion'], true))
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="m8 12 2.5 2.5L16 9" />
                    </svg>
                @elseif(in_array($toast['type'], ['error', 'warning'], true))
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7.5v5M12 16h.01" />
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 11v5M12 8h.01" />
                    </svg>
                @endif
            </span>
            <div class="aurora-system-toast__message"
                role="{{ in_array($toast['type'], ['error', 'warning'], true) ? 'alert' : 'status' }}" aria-atomic="true">
                @if(count($toast['messages']) > 1)
                    <ul>@foreach($toast['messages'] as $message)
                    <li>{{ $message }}</li>@endforeach
                    </ul>
                @else
                    <span>{{ $toast['messages'][0] }}</span>
                @endif
            </div>
            <button type="button" class="aurora-system-toast__close" data-toast-close
                aria-label="{{ t('system.toast_close') }}" title="{{ t('system.toast_close') }}"><svg viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" />
                </svg></button>
        </div>
    @endforeach
</div>