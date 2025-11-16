@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 1100px;">
    <h3 class="fw-bold text-warning mb-4">Sukurti naują tikslą</h3>

    <form action="{{ route('goals.store', ['locale' => app()->getLocale()]) }}" method="POST">
        @csrf

        {{-- Viršutinė dalis --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Sritis</label>
                <select name="category_id" class="form-select">
                    <option value="">— pasirinkti —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Spalva</label>
                <input type="color" name="color" value="#ffb347" class="form-control form-control-color">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Prioritetas</label>
                <select name="priority_id" class="form-select">
                    <option value="">— pasirinkti —</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Pagrindiniai laukeliai --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Tikslas</label>
            <input type="text" name="title" class="form-control" placeholder="Įvesk savo tikslą...">
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Aprašymas</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Deadline</label>
            <input type="date" name="deadline" class="form-control" value="{{ date('Y-m-d') }}">
        </div>

        {{-- Milestones & Tasks --}}
        <div class="mt-4">
            <h5 class="fw-bold text-warning mb-3">Milestones</h5>

            <div id="milestones-wrapper">
                {{-- čia automatiškai generuosis milestone blokai --}}
            </div>

            <button type="button" class="btn btn-outline-warning border-dashed px-4 py-2 mt-2" id="add-milestone">
                + Pridėti naują milestone
            </button>
        </div>

        {{-- Hidden milestone template --}}
        <template id="milestone-template">
            <div class="milestone-block border rounded p-3 mt-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold">🏁 Naujas milestone</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">✖</button>
                </div>

                <input type="text" name="__NAME__[title]" class="form-control mb-3" placeholder="Milestone pavadinimas">

                <div class="tasks-wrapper">
                    {{-- čia generuosis tasks --}}
                </div>

                <button type="button" class="btn btn-sm btn-outline-warning add-task">+ Pridėti užduotį</button>
            </div>
        </template>

        {{-- Hidden task template --}}
        <template id="task-template">
            <div class="task-block d-flex gap-2 align-items-center mt-2">
                <input type="text" name="__NAME__[tasks][__INDEX__][title]" class="form-control" placeholder="Task pavadinimas">
                <input type="number" name="__NAME__[tasks][__INDEX__][points]" class="form-control" placeholder="t." style="width:80px;">
                <button type="button" class="btn btn-sm btn-outline-danger remove-task">✖</button>
            </div>
        </template>


        {{-- Mygtukai apačioje --}}
        <div class="mt-4 text-end">
            <a href="{{ route('goals.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary me-2">Atšaukti</a>
            <button type="submit" class="btn btn-warning text-white fw-semibold px-4">Išsaugoti</button>
        </div>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const milestonesWrapper = document.getElementById('milestones-wrapper');
    const milestoneTemplate = document.getElementById('milestone-template').innerHTML;
    const taskTemplate = document.getElementById('task-template').innerHTML;

    let milestoneCount = 0;

    document.getElementById('add-milestone').addEventListener('click', () => {
        const milestoneHtml = milestoneTemplate.replaceAll('__NAME__', `milestones[${milestoneCount}]`);
        const milestoneEl = document.createElement('div');
        milestoneEl.innerHTML = milestoneHtml;
        milestonesWrapper.appendChild(milestoneEl);
        milestoneCount++;
    });

    // Dynamic event delegation
    document.addEventListener('click', (e) => {
        // Add new task
        if (e.target.classList.contains('add-task')) {
            const milestoneBlock = e.target.closest('.milestone-block');
            const tasksWrapper = milestoneBlock.querySelector('.tasks-wrapper');
            const taskCount = tasksWrapper.children.length;

            const milestoneIndex = Array.from(milestonesWrapper.children).indexOf(milestoneBlock);
            const newTaskHtml = taskTemplate
                .replaceAll('__NAME__', `milestones[${milestoneIndex}]`)
                .replaceAll('__INDEX__', taskCount);

            const taskEl = document.createElement('div');
            taskEl.innerHTML = newTaskHtml;
            tasksWrapper.appendChild(taskEl);
        }

        // Remove milestone
        if (e.target.classList.contains('remove-milestone')) {
            e.target.closest('.milestone-block').remove();
        }

        // Remove task
        if (e.target.classList.contains('remove-task')) {
            e.target.closest('.task-block').remove();
        }
    });
});
</script>

