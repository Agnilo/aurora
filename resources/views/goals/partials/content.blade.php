    <section class="gp-panel" aria-labelledby="gp-list-title">
        <div class="gp-toolbar">
            <h2 id="gp-list-title">{{ $activeCategory?->translated_name ?? t('goals.your_goals') }}</h2>
            <nav class="gp-statuses" aria-label="{{ t('goals.status_filter') }}">
                @foreach (['active', 'planned', 'completed', 'all'] as $status)
                    <a href="{{ route('goals.index', array_filter(['locale' => app()->getLocale(), 'category' => $activeCategory?->id, 'status' => $status], fn ($value) => $value !== null)) }}"
                       @if ($statusFilter === $status) aria-current="page" @endif>{{ t('goals.'.$status) }}</a>
                @endforeach
            </nav>
        </div>

        @if ($importantGoals->isNotEmpty())
            <h3 class="gp-section-label">{{ t('goals.importantGoals') }}</h3>
            @include('goals.partials.list.all.full', ['goals' => $importantGoals])
        @endif

        @if ($goals->isNotEmpty())
            @if ($importantGoals->isNotEmpty())
                <h3 class="gp-section-label">{{ t('goals.other_goals') }}</h3>
            @endif
            @include('goals.partials.list.all.full', ['goals' => $goals])
        @elseif ($importantGoals->isEmpty())
            <div class="gp-empty">
                <span class="gp-empty-mark" aria-hidden="true">↗</span>
                <h3>{{ t('goals.empty_title') }}</h3>
                <p>{{ t('goals.empty_description') }}</p>
                <a class="gp-primary" href="{{ route('goals.create', ['locale' => app()->getLocale()]) }}">+ {{ t('goals.new') }}</a>
            </div>
        @endif
    </section>
