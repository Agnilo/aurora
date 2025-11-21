@extends('admin.lookups.layout')

@section('lookups-content')

@foreach($blocks as $block)
<div class="lookup-card mb-4 p-3 shadow-sm bg-white rounded-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 m-0">{{ $block['title'] }}</h2>

        <a href="{{ route('admin.lookups.create', [
                'locale' => app()->getLocale(),
                'section' => $section,
                'type' => $block['type']
        ]) }}" class="btn btn-primary btn-sm">
            + {{ t('button.add_new') }}
        </a>
    </div>

    <table class="table table-hover mb-4">
        <thead>
            <tr>
                <th style="width:40px;"></th>
                <th style="width:25%">{{ t('lookup.column.technical_key') }}</th>
                <th style="width:25%">{{ t('lookup.column.translation') }}</th>
                <th style="width:15%">{{ t('lookup.column.appearance') }}</th>
                <th class="text-end" style="width:25%">{{ t('lookup.column.actions') }}</th>
            </tr>
        </thead>

        <tbody id="sortable-{{ $block['type'] }}">
        @foreach($block['items'] as $item)
            <tr data-id="{{ $item['row']->id }}">
                <td class="drag-handle" style="cursor: grab; font-size:18px;">⋮⋮</td>

                <td class="text-muted small">{{ $item['full_key'] }}</td>

                <td class="text-muted small">{{ $item['label'] }}</td>

                {{-- spalva / ikona --}}
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if(($block['has_color'] ?? false) && !empty($item['row']->color))
                            <span style="
                                display:inline-block;
                                width:16px;
                                height:16px;
                                border-radius:50%;
                                border: 1px solid rgba(0,0,0,0.08);
                                background: {{ $item['row']->color }};
                            "></span>
                        @endif

                        @if(($block['has_icon'] ?? false) && !empty($item['row']->icon))
                            <span style="font-size: 1.2rem;">
                                {!! $item['row']->icon !!}
                            </span>
                        @endif
                    </div>
                </td>

                <td class="text-end">
                    <a href="{{ route('admin.lookups.edit', [
                        'locale'  => app()->getLocale(),
                        'section' => $section,
                        'type'    => $block['type'],
                        'id'      => $item['row']->id
                    ]) }}" class="btn btn-link btn-sm">{{ t('button.edit') }}</a>

                    <form action="{{ route('admin.lookups.destroy', [
                        'locale'  => app()->getLocale(),
                        'section' => $section,
                        'type'    => $block['type'],
                        'id'      => $item['row']->id
                    ]) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('{{ t('lookup.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-link btn-sm text-danger p-0">{{ t('button.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endforeach

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    @foreach($blocks as $block)
        new Sortable(document.getElementById('sortable-{{ $block['type'] }}'), {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function () {

                let order = [];
                document.querySelectorAll('#sortable-{{ $block['type'] }} tr').forEach((el, index) => {
                    order.push({
                        id: el.dataset.id,
                        order: index + 1
                    });
                });

                fetch(`/{{ app()->getLocale() }}/admin/lookups/{{ $section }}/{{ $block['type'] }}/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order })
                });
            }
        });
    @endforeach

});
</script>

