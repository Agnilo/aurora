@if ($paginator->hasPages())
    <nav class="pl-pager" aria-label="{{ t('pagination.page') }}">
        <div class="pl-pager-control">
            @foreach ([
                ['url' => $paginator->url(1), 'disabled' => $paginator->onFirstPage(), 'label' => 'first', 'symbol' => 'chevrons-left'],
                ['url' => $paginator->previousPageUrl(), 'disabled' => $paginator->onFirstPage(), 'label' => 'previous', 'symbol' => 'chevron-left'],
            ] as $item)
                @if ($item['disabled'])
                    <span class="pl-pager-arrow is-disabled" aria-disabled="true" aria-label="{{ t('pagination.'.$item['label']) }}"><x-dynamic-component :component="'lucide-'.($item['symbol'])"  class="aurora-icon" width="18" height="18" aria-hidden="true" focusable="false" /></span>
                @else
                    <a class="pl-pager-arrow" href="{{ $item['url'] }}" aria-label="{{ t('pagination.'.$item['label']) }}"><x-dynamic-component :component="'lucide-'.($item['symbol'])"  class="aurora-icon" width="18" height="18" aria-hidden="true" focusable="false" /></a>
                @endif
            @endforeach

            <form class="pl-pager-jump" method="GET" action="{{ url()->current() }}">
                @foreach (request()->query() as $name => $value)
                    @if ($name !== $paginator->getPageName() && is_scalar($value))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input class="pl-pager-page-input" type="number" name="{{ $paginator->getPageName() }}"
                    value="{{ $paginator->currentPage() }}" min="1" max="{{ $paginator->lastPage() }}"
                    step="1" required inputmode="numeric" aria-label="{{ t('pagination.page') }}">
                <span class="pl-pager-total">{{ t('pagination.of') }} {{ $paginator->lastPage() }}</span>
                <button type="submit" class="visually-hidden">{{ t('pagination.go') }}</button>
            </form>

            @foreach ([
                ['url' => $paginator->nextPageUrl(), 'disabled' => !$paginator->hasMorePages(), 'label' => 'next', 'symbol' => 'chevron-right'],
                ['url' => $paginator->url($paginator->lastPage()), 'disabled' => !$paginator->hasMorePages(), 'label' => 'last', 'symbol' => 'chevrons-right'],
            ] as $item)
                @if ($item['disabled'])
                    <span class="pl-pager-arrow is-disabled" aria-disabled="true" aria-label="{{ t('pagination.'.$item['label']) }}"><x-dynamic-component :component="'lucide-'.($item['symbol'])"  class="aurora-icon" width="18" height="18" aria-hidden="true" focusable="false" /></span>
                @else
                    <a class="pl-pager-arrow" href="{{ $item['url'] }}" aria-label="{{ t('pagination.'.$item['label']) }}"><x-dynamic-component :component="'lucide-'.($item['symbol'])"  class="aurora-icon" width="18" height="18" aria-hidden="true" focusable="false" /></a>
                @endif
            @endforeach
        </div>
    </nav>
@endif
