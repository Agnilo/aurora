@extends('admin.translations.layout')
@section('translations-content')
    @if($translations->isEmpty())
        <div class="admin-panel">
            <div class="admin-empty">{{ t('errors.no_translation') }}</div>
        </div>
    @else
        <div class="admin-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ t('admin.consistency.key') }}</th>
                            @foreach($languages as $lang)
                                <th>{{ strtoupper($lang) }}</th>
                            @endforeach
                            <th class="text-end">{{ t('admin.consistency.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($translations as $rows)
                            @php $entry = $rows->first(); @endphp
                            <tr>
                                <td class="fw-semibold"><code>{{ $entry->group }}.{{ $entry->key }}</code></td>
                                @foreach($languages as $lang)
                                    @php $t = $rows->firstWhere('language_code', $lang); @endphp
                                    <td class="{{ $t ? '' : 'text-danger' }}">
                                        @if($t) {{ Str::limit($t->value, 60) }} @else <em>{{ t('admin.consistency.missing') }}</em>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <div class="admin-table-actions">
                                        <a href="{{ route('admin.translations.edit', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
                                            class="btn btn-sm btn-outline-primary">{{ t('button.edit') }}</a>
                                        <form method="POST"
                                            action="{{ route('admin.translations.destroy', ['locale' => app()->getLocale(), 'translationGroup' => $entry->group, 'translationKey' => $entry->key]) }}"
                                            onsubmit="return confirm(this.dataset.confirm)"
                                            data-confirm="{{ t('admin.consistency.delete_translation_confirm') }}">
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
    @endif
    @include('admin.translations.pagination')
@endsection