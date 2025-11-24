<div class="category-grid my-4 {{ isset($activeCategory) ? 'has-active' : '' }}" id="categoryGrid">
    @foreach($categories as $category)

        @php
            $isActive = isset($activeCategory) && $activeCategory?->id === $category->id;

            $targetUrl = $isActive
                ? route('goals.index', ['locale' => app()->getLocale()])
                : route('goals.index', ['locale' => app()->getLocale(), 'category' => $category->id]);

            $color = $category->color ?? '#ffcc5f';
            $light = lightenColor($color, 35);
            $gradient = "linear-gradient(90deg, $light, $color)";

            $points = 45;
            $max = 100;
            $percent = ($points / $max) * 100;
        @endphp

        <a href="{{ $targetUrl }}" class="category-tile {{ $isActive ? 'active' : '' }} category-link">
            <div class="category-tile-inner">

                <div class="category-icon">{!! $category->icon !!}</div>
                <div class="category-name">{{ $category->translated_name }}</div>

                <div class="category-progress">
                    <div class="category-progress-fill"
                         style="--bar-bg: {{ $gradient }}; width: {{ $percent }}%;">
                    </div>
                </div>

                <div class="category-score">
                    {{ $points }} t. / {{ $max }} t.
                </div>

            </div>
        </a>

    @endforeach
</div>
