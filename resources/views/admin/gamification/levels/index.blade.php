@extends('layouts.admin')

@section('content')

<div class="container py-4">

    <h1 class="fw-bold mb-4">{{ t('gamification.levels') }}</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.gamification.levels.create', app()->getLocale()) }}"
    class="btn btn-primary mb-3">
        + {{ t('button.add_new')}}
    </a>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px">{{ t('gamification.levels') }}</th>
                        <th>{{ t('lookup.name')}}</th>
                        <th>{{ t('gamification.xp_needed') }}</th>
                        <th>{{ t('gamification.monetery_award') }}</th>
                        <th class="text-end">{{ t('preferences.admin.actions')}}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($levels as $level)
                        <tr>
                            <td class="fw-bold">
                                @if($level->level_to)
                                    {{ $level->level_from }}–{{ $level->level_to }}
                                @else
                                    {{ $level->level_from }}+
                                @endif
                            </td>

                            <td>
                                {{ t('gamification.' . $level->translation_key) }}
                            </td>

                            <td>
                                {{ number_format($level->xp_required) }}
                            </td>

                            <td>
                                {{ $level->reward_coins }}
                            </td>

                            <td class="text-end">
                                @php
                                    $cannotDelete = $level->hasUsers();
                                @endphp

                                <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                    <a href="{{ route('admin.gamification.levels.edit', [
                                        'locale' => app()->getLocale(),
                                        'level' => $level->id
                                    ]) }}"
                                       class="btn btn-sm btn-outline-primary text-nowrap">
                                        {{ t('button.edit') }}
                                    </a>

                                    @if($cannotDelete)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary text-nowrap"
                                                disabled>
                                            {{ t('button.delete') }}
                                        </button>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.gamification.levels.destroy', [
                                                  'locale' => app()->getLocale(),
                                                  'level' => $level->id
                                              ]) }}"
                                              class="m-0"
                                              onsubmit="return confirm('Ar tikrai norite ištrinti šį lygio intervalą?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                                {{ t('button.delete') }}
                                            </button>
                                        </form>
                                    @endif

                                    @if($cannotDelete)
                                        <button type="button"
                                                class="btn btn-sm border-0 bg-transparent text-muted p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                                data-level-delete-info
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="{{ t('errors.cannot_delete') }}"
                                                aria-label="{{ t('errors.cannot_delete') }}">
                                            <x-lucide-info width="12" height="12" aria-hidden="true" />
                                        </button>
                                    @else
                                        {{-- Reserve the same space so every row's buttons align. --}}
                                        <span class="btn btn-sm border-0 p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0 invisible"
                                              aria-hidden="true">
                                            <x-lucide-info width="12" height="12" aria-hidden="true" />
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Lygių nerasta
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-level-delete-info]').forEach((element) => {
        if (window.bootstrap?.Tooltip) {
            bootstrap.Tooltip.getOrCreateInstance(element, {
                container: 'body',
                trigger: 'hover focus',
            });
        }
    });
</script>
@endsection
