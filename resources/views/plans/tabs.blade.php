<nav class="pl-tabs" aria-label="{{ t('plans.title') }}">
    @foreach (['goals', 'tasks', 'habits'] as $item)
        <a href="{{ route('plans.index', ['locale' => app()->getLocale(), 'tab' => $item]) }}"
           @if($tab === $item) aria-current="page" @endif>{{ t('plans.'.$item) }}</a>
    @endforeach
</nav>
