<div class="d-flex gap-2 mb-3">

    {{-- Kategorijų mygtukai --}}
    @foreach($categories as $category)
        @php
            $isActive = $activeCategory && $activeCategory->id == $category->id;
        @endphp

        <a href="{{ $isActive 
                ? route('goals.index', ['locale' => app()->getLocale()]) 
                : route('goals.index', ['locale' => app()->getLocale(), 'category' => $category->id]) }}"
                
            class="btn btn-sm {{ $isActive ? 'btn-warning text-white' : 'btn-outline-warning' }}">

            {{-- Spalvotas taškas --}}
            <span class="rounded-circle d-inline-block me-1"
                style="width: 10px; height: 10px; background-color: {{ $category->color }}">
            </span>

            {{ $category->name }}
        </a>
    @endforeach
</div>
