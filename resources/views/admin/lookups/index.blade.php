@extends('admin.lookups.layout')
@section('lookups-content')
    @foreach($blocks as $block)
        <div class="admin-panel admin-lookup-panel mb-4">
            <div class="admin-section-heading">
                <h2>{{ $block['title'] }}</h2>
                <a href="{{ route('admin.lookups.create', ['locale' => app()->getLocale(), 'section' => $section, 'type' => $block['type']]) }}"
                    class="btn btn-sm btn-primary">
                    <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" /> {{ t('button.add_new') }}
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th>{{ t('lookup.column.technical_key') }}</th>
                            <th>{{ t('lookup.column.translation') }}</th>
                            <th>{{ t('lookup.column.appearance') }}</th>
                            <th class="text-end">{{ t('lookup.column.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-{{ $block['type'] }}">
                        @foreach($block['items'] as $item)
                            <tr data-id="{{ $item['row']->id }}">
                                <td class="drag-handle" aria-label="{{ t('admin.consistency.reorder') }}">⋮⋮</td>
                                <td><code>{{ $item['full_key'] }}</code></td>
                                <td>{{ $item['label'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(($block['has_color'] ?? false) && !empty($item['row']->color))
                                            <span class="color-dot" style="background: {{ $item['row']->color }}"
                                                aria-label="{{ t('lookup.column.appearance') }}"></span>
                                        @endif
                                        @if(($block['has_icon'] ?? false) && !empty($item['row']->icon))
                                            <x-stored-lucide :name="$item['row']->icon" class="aurora-icon" width="20" height="20"
                                                aria-hidden="true" />
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="admin-table-actions">
                                        <a href="{{ route('admin.lookups.edit', ['locale' => app()->getLocale(), 'section' => $section, 'type' => $block['type'], 'id' => $item['row']->id]) }}"
                                            class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                        <form method="POST"
                                            action="{{ route('admin.lookups.destroy', ['locale' => app()->getLocale(), 'section' => $section, 'type' => $block['type'], 'id' => $item['row']->id]) }}"
                                            onsubmit="return confirm(this.dataset.confirm)"
                                            data-confirm="{{ t('lookup.confirm_delete') }}">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger">{{ t('button.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @foreach($blocks as $block)
                const block_{{ $loop->index }} = document.getElementById('sortable-{{ $block['type'] }}');
                if (block_{{ $loop->index }} && window.Sortable) {
                    new Sortable(block_{{ $loop->index }}, {
                        animation: 150,
                        handle: '.drag-handle',
                        onEnd: () => {
                            const order = Array.from(block_{{ $loop->index }}.querySelectorAll('tr')).map((row, index) => ({ id: row.dataset.id, order: index + 1 }));
                            fetch(`/{{ app()->getLocale() }}/admin/lookups/{{ $section }}/{{ $block['type'] }}/reorder`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ order }),
                            });
                        },
                    });
                }
            @endforeach
    });
    </script>
@endsection