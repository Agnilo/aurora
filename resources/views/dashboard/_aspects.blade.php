<h5 class="fw-bold mb-3">Gyvenimo aspektai</h5>

<div class="life-aspects-row">
    @foreach($categoryLevels as $level)

        <div class="life-area-card" style="--accent: {{ $level->category->color }}">

            {{-- VISUAL HEADER --}}
            <div class="life-area-bg"
                style="
                    background-image: url('{{ $level->category->image
                        ? asset('storage/categories/'.$level->category->image)
                        : '' }}');
                    background-color: {{ $level->category->color }};
                ">
                <div class="visual-waves"></div>
            </div>

            <div class="life-area-content">

            {{-- TITLE --}}
            <div class="life-area-title">
                {{ $level->category->icon }} {{ $level->category->translated_name }}
            </div>

            {{-- PURPOSE --}}
            <div class="life-area-purpose">
                {{ $level->category->purpose
                    ?? 'Ši gyvenimo sritis padeda tau augti ir išlaikyti balansą.' }}
            </div>

            {{-- PROGRESS --}}
            <div class="life-area-progress">

                <div class="life-area-progress-row">

                    {{-- XP SQUARES --}}
                    <div class="xp-squares">
                        {{-- DEBUG --}}
                        @for ($i = 1; $i <= 10; $i++)
                            <span class="xp-square">
                                @if ($i <= $level->full_squares)
                                    <span class="xp-fill-inner" style="width:100%"></span>
                                @elseif ($i == $level->full_squares + 1 && $level->partial_fill > 0)
                                    <span class="xp-fill-inner" style="width:{{ $level->partial_fill }}%"></span>
                                @endif
                            </span>
                        @endfor
                    </div>

                    {{-- XP TEXT --}}
                    <small>
                        {{ $level->xp }} / {{ $level->category->max_points ?? 100 }} {{ t('goals.points.short') }}
                    </small>
                </div>

                {{-- LEVEL LABEL --}}
                <div class="life-area-level">
                    Lygis {{ $level->level }} · {{ $level->level_name }}
                </div>

            </div>

        </div>
    </div>

@endforeach
</div>
