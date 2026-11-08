@extends('admin.translation-groups.layout')

@section('translations-content')
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ t('group.group.group') }}</th>
                        <th scope="col" class="text-end">{{ t('admin.consistency.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $grp)
                                    <tr>
                                        <td>{{ $grp->label() }}</td>
                                        <td class="text-end">
                                            <div class="admin-table-actions">
                                                <a href="{{ route('admin.translation-groups.edit', [
                            'locale' => app()->getLocale(),
                            'translation_group' => $grp->id
                        ]) }}" class="btn btn-sm btn-outline-primary text-nowrap">
                                                    {{ t('button.edit') }}
                                                </a>
                                                <form action="{{ route('admin.translation-groups.destroy', [
                            'locale' => app()->getLocale(),
                            'translation_group' => $grp->id
                        ]) }}" method="POST" class="m-0" data-confirm="{{ t('dashboard.confirm_delete') }}"
                                                    onsubmit="return confirm(this.dataset.confirm)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                                        {{ t('button.delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="admin-empty">{{ t('admin.consistency.no_translation_groups') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection