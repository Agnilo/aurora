@props(['selection' => null, 'pose' => null, 'customizable' => true])
@php
    $service = app(\App\Services\MascotService::class);
    $isLink = $customizable && $selection === null && $pose === null && auth()->check() && \Illuminate\Support\Facades\Route::has('mascot.edit');
    $resolved = $selection ?? (auth()->check() ? $service->forUser(auth()->user()) : []);
    $mascot = $service->render($resolved, $pose);
    $roomBackground = $isLink && $mascot['mode'] === 'layered' ? ($mascot['layers']['background'] ?? null) : null;
@endphp
@if($mascot['mode'] === 'layered' || $mascot['image'])
    @if($roomBackground)
        <img class="dashboard-ui-room-background" src="{{ asset($roomBackground) }}" alt="" aria-hidden="true" loading="eager"
            draggable="false">
    @endif
    @if($isLink)
        <a {{ $attributes->class(['dashboard-ui-mascot', 'aurora-mascot-stack', 'aurora-mascot-link', 'aurora-mascot-room-link' => $roomBackground !== null]) }}
            href="{{ route('mascot.edit', ['locale' => app()->getLocale()]) }}" aria-label="{{ t('mascot.title') }}"
            title="{{ t('mascot.title') }}">
    @else
            <div {{ $attributes->class(['dashboard-ui-mascot', 'aurora-mascot-stack']) }} aria-hidden="true">
        @endif
            @if($mascot['mode'] === 'layered')
                @foreach($mascot['layers'] as $slot => $path)
                    @continue($roomBackground && $slot === 'background')
                    <img class="aurora-mascot-layer" data-mascot-layer="{{ $slot }}" src="{{ asset($path) }}" alt="" loading="eager"
                        draggable="false">
                @endforeach
            @else
                <img class="aurora-mascot-layer" src="{{ asset($mascot['image']) }}" alt="" loading="eager" draggable="false">
            @endif
            @if($isLink)
                        <span class="aurora-mascot-edit-indicator" aria-hidden="true">
                            <x-lucide-pencil class="aurora-icon" width="16" height="16" aria-hidden="true" focusable="false" />
                        </span>
                </a>
            @else
        </div>
    @endif
@endif