<div class="category-grid my-4 {{ isset($activeCategory) ? 'has-active' : '' }}" id="categoryGrid">

    @foreach($categories as $category)

        @php
            $isActive = isset($activeCategory) && $activeCategory?->id === $category->id;

            $targetUrl = $isActive
                ? route('goals.index', ['locale' => app()->getLocale()])
                : route('goals.index', ['locale' => app()->getLocale(), 'category' => $category->id]);

            $gradient = $category->gradient ?? 'linear-gradient(135deg, #ffe8a3, #ffc764)';
        @endphp

        <a href="{{ $targetUrl }}" 
           class="category-tile {{ $isActive ? 'active' : '' }} category-link">

            <div class="category-tile-inner">

                <div class="category-icon">
                    {!! $category->emoji ?? '🌟' !!}
                </div>

                <div class="category-name">
                    {{ $category->name }}
                </div>

                <div class="category-gradient" style="background: {{ $gradient }};">
                </div>

                <div class="category-score">
                    35 taškai / 100 taškų
                </div>

            </div>
        </a>

    @endforeach

</div>


