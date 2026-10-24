<div
    id="categoryGrid"
    class="life-aspects-row category-grid {{ isset($activeCategory) ? 'has-active' : '' }}"
>
    @foreach ($categories as $category)
        @php
            $isActive = isset($activeCategory)
                && $activeCategory->id === $category->id;

            $parameters = [
                'locale' => app()->getLocale(),
                'status' => $statusFilter ?? 'active',
            ];

            if (! $isActive) {
                $parameters['category'] = $category->id;
            }

            $level = $categoryLevels[$category->id] ?? null;
            $xp = $level?->xp ?? 0;
            $max = $level?->xp_next ?? 100;
            $percent = $max > 0
                ? max(0, min(100, ($xp / $max) * 100))
                : 0;

            $color = preg_match(
                '/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/',
                $category->color ?? ''
            ) ? $category->color : '#D9909C';

            $name = mb_strtolower($category->name);

            $icon = match (true) {
                str_contains($name, 'emotion'),
                str_contains($name, 'emoc') => 'heart',

                str_contains($name, 'relation'),
                str_contains($name, 'santyk') => 'people',

                str_contains($name, 'career'),
                str_contains($name, 'karjer') => 'briefcase',

                default => 'leaf',
            };
        @endphp

        <a
            class="category-link {{ $isActive ? 'active' : '' }}"
            href="{{ route('goals.index', $parameters) }}"
            data-category-id="{{ $category->id }}"
        >
            <div
                class="life-area-card"
                style="--accent: {{ $color }}"
            >
                <span class="gc-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        @switch($icon)
                            @case('heart')
                                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>
                                @break

                            @case('people')
                                <circle cx="9" cy="7" r="3"/>
                                <path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v3"/>
                                @break

                            @case('briefcase')
                                <rect x="3" y="7" width="18" height="14" rx="2"/>
                                <path d="M8 7V3h8v4M3 12h18M10 12v3h4v-3"/>
                                @break

                            @default
                                <path d="M20 3C8 2 2 7 4 15c2 7 15 6 16-12ZM5 20 16 8M10 15v-5M10 15h5"/>
                        @endswitch
                    </svg>
                </span>

                <span class="life-area-title">
                    {{ $category->translated_name }}
                </span>

                <div class="life-area-progress">
                    <div
                        class="gc-progress"
                        data-max="{{ $max }}"
                        role="progressbar"
                        aria-label="{{ $category->translated_name }}"
                        aria-valuenow="{{ $percent }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <span style="width: {{ $percent }}%"></span>
                    </div>

                    <small data-unit="{{ t('goals.points.short') }}">
                        {{ $xp }} / {{ $max }} {{ t('goals.points.short') }}
                    </small>
                </div>
            </div>
        </a>
    @endforeach
</div>
