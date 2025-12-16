<div class="life-aspects-row category-grid {{ isset($activeCategory) ? 'has-active' : '' }}"
     id="categoryGrid">

@foreach($categories as $category)
    @php
        $isActive = isset($activeCategory) && $activeCategory?->id === $category->id;

        $targetUrl = $isActive
            ? route('goals.index', ['locale' => app()->getLocale()])
            : route('goals.index', [
                'locale' => app()->getLocale(),
                'category' => $category->id
            ]);

        $lvl = $categoryLevels[$category->id] ?? null;
    @endphp

    <a href="{{ $targetUrl }}"
       class="category-link text-decoration-none {{ $isActive ? 'active' : '' }}"
       data-category-id="{{ $category->id }}">

        <div class="life-area-card" style="--accent: {{ $category->color }}">

            <div class="life-area-bg"
                 style="
                    background-image: url('{{ $category->image ? asset('storage/categories/'.$category->image) : '' }}');
                    background-color: {{ $category->color }};
                 ">
                <div class="visual-waves"></div>
            </div>

            <div class="life-area-content">

                <div class="life-area-title" style="margin-bottom: 16px">
                    {!! $category->icon !!} {{ $category->translated_name }}
                </div>

                @if($lvl)
                    <div class="life-area-progress">
                        <div class="life-area-progress-row" style="justify-content: center">
                            <div class="xp-squares-goals">
                                @for ($i = 1; $i <= 10; $i++)
                                    <span class="{{ $i <= $lvl->full_squares ? 'filled' : '' }}"
                                        style="
                                            {{ $i == $lvl->full_squares + 1 && $lvl->partial_fill > 0
                                                ? 'background: linear-gradient(to right, var(--accent) '.$lvl->partial_fill.'%, rgba(0,0,0,.08) '.$lvl->partial_fill.'%)'
                                                : '' }}">
                                    </span>
                                @endfor
                            </div>
                        </div>
                        <div style="text-align: center">
                          <small>
                                {{ $lvl->xp }} / {{ $category->max_points ?? 100 }} {{ t('goals.points.short') }}
                            </small>  
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </a>
@endforeach

</div>
