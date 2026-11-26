@props(['selection' => null, 'pose' => null, 'customizable' => true])
@php
    $service = app(\App\Services\MascotService::class);
    $isLink = $customizable && $selection === null && $pose === null && auth()->check() && \Illuminate\Support\Facades\Route::has('mascot.edit');
    $resolved = $selection ?? (auth()->check() ? $service->forUser(auth()->user()) : []);
    $mascot = $service->render($resolved, $pose);
@endphp
@if($mascot['mode'] === 'layered' || $mascot['image'])
    @if($isLink)
        <a {{ $attributes->class(['dashboard-ui-mascot', 'aurora-mascot-stack', 'aurora-mascot-link']) }}
            href="{{ route('mascot.edit', ['locale' => app()->getLocale()]) }}" aria-label="{{ t('mascot.title') }}"
            title="{{ t('mascot.title') }}">
    @else
            <div {{ $attributes->class(['dashboard-ui-mascot', 'aurora-mascot-stack']) }} aria-hidden="true">
        @endif
            @if($mascot['mode'] === 'layered')
                @foreach($mascot['layers'] as $slot => $path)
                    <img class="aurora-mascot-layer" data-mascot-layer="{{ $slot }}" src="{{ asset($path) }}" alt="" loading="eager"
                        draggable="false">
                @endforeach
            @else
                <img class="aurora-mascot-layer" src="{{ asset($mascot['image']) }}" alt="" loading="eager" draggable="false">
            @endif
            @if($isLink)
                </a>
            @else
        </div>
    @endif
@endif