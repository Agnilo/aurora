@extends('layouts.admin')

@section('content')
<div class="container py-4">

<h1 class="fw-bold mb-4">Badge kategorijos</h1>

<a href="{{ route('admin.gamification.badge-categories.create', app()->getLocale()) }}"
   class="btn btn-primary mb-3">
    + Pridėti kategoriją
</a>

<table class="table table-hover">
<thead>
<tr>
    <th>Key</th>
    <th>Pavadinimas</th>
    <th>Statusas</th>
    <th class="text-end">Veiksmai</th>
</tr>
</thead>
<tbody>
@foreach($categories as $cat)
<tr>
    <td>{{ $cat->key }}</td>
    <td>{{ t('gamification.' . $cat->label) }}</td>
    <td>
        @if($cat->active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td class="text-end">
        <a href="{{ route('admin.gamification.badge-categories.edit', [app()->getLocale(), $cat]) }}"
           class="btn btn-sm btn-outline-primary">
            {{ t('button.edit') }}
        </a>
    </td>
</tr>
@endforeach
</tbody>
</table>

</div>
@endsection
