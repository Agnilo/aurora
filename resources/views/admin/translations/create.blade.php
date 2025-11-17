@extends('layouts.admin')

@section('content')

<h1 class="h3 mb-3">Add Translation</h1>

<div class="card p-4">

    <form action="{{ ar('admin.translations.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Group</label>
            <input type="text" name="group" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Key</label>
            <input type="text" name="key" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Language</label>
            <select name="language_code" class="form-select" required>
                @foreach(\App\Models\Localization\Language::where('is_active', true)->get() as $lang)
                    <option value="{{ $lang->code }}">{{ strtoupper($lang->code) }} — {{ $lang->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Value</label>
            <textarea name="value" rows="2" class="form-control" required></textarea>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ ar('admin.translations.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>

    </form>

</div>

@endsection
