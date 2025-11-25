@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<div class="container">

    <a href="{{ route('first.schedule.index') }}" class="btn btn-outline-secondary mb-3">← Назад к расписанию</a>

    <h2 class="mb-4">Добавить запись в расписание (1 курс)</h2>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('first.schedule.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">День</label>
            <select name="study_day" class="form-select" required>
                @foreach($days as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Номер пары</label>
            <select name="lesson_number" class="form-select">
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">{{ $i }} пара</option>
                @endfor
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Группа</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#groupSelect" placeholder="Поиск группы">
            <select name="group_id" id="groupSelect" class="form-select filterable" required>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Предмет (подгруппа 1)</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#subjectSelect1" placeholder="Поиск предмета">
            <select name="subject_id" id="subjectSelect1" class="form-select filterable">
                <option value="">—</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name_ru ?? $s->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Предмет (знаменатель)</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#subjectSelect1Den" placeholder="Поиск предмета">
            <select name="subject_id_denominator" id="subjectSelect1Den" class="form-select filterable">
                <option value="">— если пары чередуются</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name_ru ?? $s->subject_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Преподаватель</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#teacherSelect1" placeholder="Поиск преподавателя">
            <select name="teacher_id" id="teacherSelect1" class="form-select filterable">
                <option value="">—</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Преподаватель (знаменатель)</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#teacherSelect1Den" placeholder="Поиск преподавателя">
            <select name="teacher_id_denominator" id="teacherSelect1Den" class="form-select filterable">
                <option value="">— если нужен другой преподаватель</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Аудитория</label>
            <input type="number" name="room_id" class="form-control" placeholder="Например, 32">
        </div>
        <div class="mb-3">
            <label class="form-label">Аудитория (знаменатель)</label>
            <input type="number" name="room_id_denominator" class="form-control" placeholder="Если аудитория другая">
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="hasSubgroups" name="has_subgroups" value="1">
            <label class="form-check-label" for="hasSubgroups">Разделить на 2 подгруппы</label>
        </div>

        <div id="secondSubgroup" class="mb-3 d-none">
            <label class="form-label">Предмет (подгруппа 2)</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#subjectSelect2" placeholder="Поиск предмета">
            <select name="subject_id_second" id="subjectSelect2" class="form-select filterable">
                <option value="">— выберите предмет для 2 подгруппы</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name_ru ?? $s->subject_name }}</option>
                @endforeach
            </select>
            <div class="mt-3">
                <label class="form-label">Преподаватель (подгруппа 2)</label>
                <input type="search" class="form-control mb-2 filter-input" data-target="#teacherSelect2" placeholder="Поиск преподавателя">
                <select name="teacher_id_second" id="teacherSelect2" class="form-select filterable">
                    <option value="">— можно выбрать другого преподавателя</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Преподаватель (знаменатель, подгруппа 2)</label>
                <input type="search" class="form-control mb-2 filter-input" data-target="#teacherSelect2Den" placeholder="Поиск преподавателя">
                <select name="teacher_id_second_denominator" id="teacherSelect2Den" class="form-select filterable">
                    <option value="">— опционально для второй недели</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Аудитория (подгруппа 2)</label>
                <input type="number" name="room_id_second" class="form-control" placeholder="Например, 33">
            </div>
            <div class="mt-3">
                <label class="form-label">Аудитория (знаменатель, подгруппа 2)</label>
                <input type="number" name="room_id_second_denominator" class="form-control" placeholder="Если аудитория меняется">
            </div>
            <small class="text-muted">Будут созданы две записи: для подгруппы A (основной предмет) и B (этот предмет/преподаватель/аудитория).</small>
        </div>

        <button class="btn btn-success">Сохранить</button>
    </form>

</div>
@endsection

@push('scripts')
<script>
    const switchEl = document.getElementById('hasSubgroups');
    const secondSubgroup = document.getElementById('secondSubgroup');

    const toggleSubgroup = () => {
        secondSubgroup.classList.toggle('d-none', !switchEl.checked);
    };

    switchEl.addEventListener('change', toggleSubgroup);
    toggleSubgroup();

    // Быстрый поиск по select
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-target');
        const selectEl = document.querySelector(targetSelector);
        const originalOptions = Array.from(selectEl.options);

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            selectEl.innerHTML = '';

            originalOptions
                .filter(opt => opt.text.toLowerCase().includes(term))
                .forEach(opt => selectEl.appendChild(opt.cloneNode(true)));
        });
    });
</script>
@endpush
