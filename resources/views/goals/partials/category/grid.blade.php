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
                    <x-category-lucide :category="$category"  class="aurora-icon" width="25" height="25" aria-hidden="true" focusable="false" />
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
