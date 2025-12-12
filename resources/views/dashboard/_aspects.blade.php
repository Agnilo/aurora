<h5 class="fw-bold mb-3">Gyvenimo aspektai</h5>

<div class="d-flex flex-wrap gap-3 mb-5">
    @foreach($categoryLevels as $level)
        <div class="aspect-tile text-center">
            <div class="aspect-icon mb-1">
                {{-- vėliau: SVG / emoji / icon --}}
            </div>
            <div class="small fw-semibold">
                {{ $level->category->name }}
            </div>
            <div class="text-muted small">
                {{ $level->xp }} XP
            </div>
        </div>
    @endforeach
</div>
