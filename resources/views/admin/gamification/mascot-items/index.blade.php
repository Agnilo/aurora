@extends('layouts.admin')

@section('content')
    <section class="admin-game admin-mascot">
        <header class="admin-game__header">
            <div>
                <h1 class="admin-title">{{ t('mascot.admin.title') }}</h1>
                <p class="admin-subtitle mb-0">{{ t('mascot.admin.description') }}</p>
            </div>
            <a class="btn btn-primary"
                href="{{ route('admin.gamification.mascot-items.create', ['locale' => app()->getLocale()]) }}">
                <x-lucide-plus class="aurora-icon" width="16" height="16" aria-hidden="true" />
                {{ t('mascot.admin.create') }}
            </a>
        </header>
        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        <div class="admin-mascot-filters mb-3">
            @foreach(['' => 'mascot.admin.all', 'hair' => 'mascot.slot.hair', 'outfit' => 'mascot.slot.outfit'] as $slot => $label)
                <a class="btn {{ request('slot', '') === $slot ? 'btn-primary' : 'btn-outline-secondary' }}"
                    href="{{ route('admin.gamification.mascot-items.index', array_filter(['locale' => app()->getLocale(), 'slot' => $slot])) }}">{{ t($label) }}</a>
            @endforeach
        </div>
        <div class="admin-panel admin-mascot-catalog">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">{{ t('mascot.admin.item') }}</th>
                            <th scope="col">{{ t('mascot.admin.type') }}</th>
                            <th scope="col">{{ t('mascot.admin.price') }}</th>
                            <th scope="col">{{ t('mascot.admin.status') }}</th>
                            <th scope="col" class="text-end">{{ t('admin.game_overview.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <div class="admin-mascot-item">
                                        <span class="admin-mascot-thumb" aria-hidden="true">
                                            @foreach($item->layers ?? [] as $path)
                                                <img src="{{ asset($path) }}" alt="" loading="lazy">
                                            @endforeach
                                        </span>
                                        <strong>{{ t($item->translation_key) }}</strong>
                                    </div>
                                </td>
                                <td>{{ t('mascot.slot.' . $item->slot) }}</td>
                                <td><span class="admin-mascot-number">{{ $item->price }}</span></td>
                                <td>{{ t($item->active ? 'mascot.admin.published' : 'mascot.admin.draft') }}</td>
                                <td class="text-end"><a class="btn btn-outline-secondary btn-sm"
                                        href="{{ route('admin.gamification.mascot-items.edit', ['locale' => app()->getLocale(), 'mascot_item' => $item->id]) }}">{{ t('admin.game_overview.edit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ t('mascot.admin.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="mt-3">{{ $items->links() }}</div>@endif
    </section>
@endsection