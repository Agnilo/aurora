@if ($keyPage->total() > 0)
    <nav class="tp-pagination mt-4" aria-label="{{ t('pagination.page') }}">
        <div class="tp-control">
            @foreach ([
                ['url' => $keyPage->url(1), 'disabled' => $keyPage->onFirstPage(), 'label' => 'first', 'symbol' => '«'],
                ['url' => $keyPage->previousPageUrl(), 'disabled' => $keyPage->onFirstPage(), 'label' => 'previous', 'symbol' => '‹'],
            ] as $item)
                @if ($item['disabled'])
                    <span class="tp-arrow is-disabled" aria-disabled="true" aria-label="{{ t('pagination.'.$item['label']) }}">{{ $item['symbol'] }}</span>
                @else
                    <a class="tp-arrow" href="{{ $item['url'] }}" aria-label="{{ t('pagination.'.$item['label']) }}">{{ $item['symbol'] }}</a>
                @endif
            @endforeach

            <form class="tp-jump" method="GET" action="{{ url()->current() }}">
                @foreach (request()->query() as $name => $value)
                    @if ($name !== 'page' && is_scalar($value))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input class="tp-page-input" type="number" name="page"
                    value="{{ $keyPage->currentPage() }}" min="1" max="{{ $keyPage->lastPage() }}"
                    step="1" required inputmode="numeric" aria-label="{{ t('pagination.page') }}">
                <span class="tp-total">{{ t('pagination.of') }} {{ $keyPage->lastPage() }}</span>
                <button type="submit" class="visually-hidden">{{ t('pagination.go') }}</button>
            </form>

            @foreach ([
                ['url' => $keyPage->nextPageUrl(), 'disabled' => !$keyPage->hasMorePages(), 'label' => 'next', 'symbol' => '›'],
                ['url' => $keyPage->url($keyPage->lastPage()), 'disabled' => !$keyPage->hasMorePages(), 'label' => 'last', 'symbol' => '»'],
            ] as $item)
                @if ($item['disabled'])
                    <span class="tp-arrow is-disabled" aria-disabled="true" aria-label="{{ t('pagination.'.$item['label']) }}">{{ $item['symbol'] }}</span>
                @else
                    <a class="tp-arrow" href="{{ $item['url'] }}" aria-label="{{ t('pagination.'.$item['label']) }}">{{ $item['symbol'] }}</a>
                @endif
            @endforeach
        </div>
    </nav>
@endif
