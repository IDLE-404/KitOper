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

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="hasDenominator" name="has_denominator" value="1">
            <label class="form-check-label" for="hasDenominator">Добавить варианты для знаменателя (неделя B)</label>
        </div>

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
            @for($i = 1; $i <= 7; $i++)
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
                    <option value="{{ $s->id }}"
                            data-group="{{ $s->group_type ?? 'both' }}"
                            data-title-ru="{{ $s->name_ru ?? $s->subject_name }}"
                            data-title-kz="{{ $s->name_kz ?? ($s->name_ru ?? $s->subject_name) }}">
                        {{ $s->name_ru ?? $s->subject_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Предмет (знаменатель)</label>
            <input type="search" class="form-control mb-2 filter-input" data-target="#subjectSelect1Den" placeholder="Поиск предмета">
            <select name="subject_id_denominator" id="subjectSelect1Den" class="form-select filterable denom-block d-none">
                <option value="">— если пары чередуются</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}"
                            data-group="{{ $s->group_type ?? 'both' }}"
                            data-title-ru="{{ $s->name_ru ?? $s->subject_name }}"
                            data-title-kz="{{ $s->name_kz ?? ($s->name_ru ?? $s->subject_name) }}">
                        {{ $s->name_ru ?? $s->subject_name }}
                    </option>
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
            <select name="teacher_id_denominator" id="teacherSelect1Den" class="form-select filterable denom-block d-none">
                <option value="">— если нужен другой преподаватель</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Аудитория</label>
            <select name="room_id" class="form-select">
                <option value="">—</option>
                @foreach(($rooms ?? collect()) as $room)
                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Аудитория (знаменатель)</label>
            <select name="room_id_denominator" class="form-select denom-block d-none">
                <option value="">—</option>
                @foreach(($rooms ?? collect()) as $room)
                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                @endforeach
            </select>
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
                    <option value="{{ $s->id }}"
                            data-group="{{ $s->group_type ?? 'both' }}"
                            data-title-ru="{{ $s->name_ru ?? $s->subject_name }}"
                            data-title-kz="{{ $s->name_kz ?? ($s->name_ru ?? $s->subject_name) }}">
                        {{ $s->name_ru ?? $s->subject_name }}
                    </option>
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
                <select name="teacher_id_second_denominator" id="teacherSelect2Den" class="form-select filterable denom-block d-none">
                    <option value="">— опционально для второй недели</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Предмет (знаменатель, подгруппа 2)</label>
                <input type="search" class="form-control mb-2 filter-input" data-target="#subjectSelect2Den" placeholder="Поиск предмета">
                <select name="subject_id_second_denominator" id="subjectSelect2Den" class="form-select filterable denom-block d-none">
                    <option value="">— если предмет отличается для знаменателя</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}"
                            data-group="{{ $s->group_type ?? 'both' }}"
                            data-title-ru="{{ $s->name_ru ?? $s->subject_name }}"
                            data-title-kz="{{ $s->name_kz ?? ($s->name_ru ?? $s->subject_name) }}">
                        {{ $s->name_ru ?? $s->subject_name }}
                    </option>
                @endforeach
            </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Аудитория (подгруппа 2)</label>
                <select name="room_id_second" class="form-select">
                    <option value="">—</option>
                    @foreach(($rooms ?? collect()) as $room)
                        <option value="{{ $room->code }}">{{ $room->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3">
                <label class="form-label">Аудитория (знаменатель, подгруппа 2)</label>
                <select name="room_id_second_denominator" class="form-select denom-block d-none">
                    <option value="">—</option>
                    @foreach(($rooms ?? collect()) as $room)
                        <option value="{{ $room->code }}">{{ $room->code }}</option>
                    @endforeach
                </select>
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
    const groupSelect = document.getElementById('groupSelect');
    const groupLocalePreference = @json($groupLocalePreference ?? []);

    const toggleSubgroup = () => {
        secondSubgroup.classList.toggle('d-none', !switchEl.checked);
    };

    switchEl.addEventListener('change', toggleSubgroup);
    toggleSubgroup();

    const denomToggle = document.getElementById('hasDenominator');
    const denomBlocks = document.querySelectorAll('.denom-block');
    const toggleDenominator = () => {
        denomBlocks.forEach(el => {
            el.classList.toggle('d-none', !denomToggle.checked);
            if (!denomToggle.checked) {
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = '';
                }
            }
        });
    };
    denomToggle.addEventListener('change', toggleDenominator);
    toggleDenominator();

    const subjectSelects = [
        document.getElementById('subjectSelect1'),
        document.getElementById('subjectSelect1Den'),
        document.getElementById('subjectSelect2'),
        document.getElementById('subjectSelect2Den'),
    ].filter(Boolean);

    let currentSubjectMode = 'ru';

    const applySubjectFilter = (groupId) => {
        const useKazakh = groupLocalePreference[String(groupId)] === true;
        currentSubjectMode = useKazakh ? 'kz' : 'ru';

        subjectSelects.forEach((select) => {
            Array.from(select.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const groupType = option.dataset.group || 'both';
                const allowed = groupType === 'both' || groupType === currentSubjectMode;
                const keepSelected = option.value === select.value;
                const title = currentSubjectMode === 'kz'
                    ? (option.dataset.titleKz || option.textContent)
                    : (option.dataset.titleRu || option.textContent);

                option.textContent = title;
                option.hidden = !(allowed || keepSelected);
                option.disabled = !(allowed || keepSelected);
            });
        });
    };

    groupSelect?.addEventListener('change', () => {
        applySubjectFilter(groupSelect.value);
    });

    applySubjectFilter(groupSelect?.value);

    // Быстрый поиск по select
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-target');
        const selectEl = document.querySelector(targetSelector);
        if (!selectEl) return;

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            Array.from(selectEl.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const groupType = option.dataset.group || 'both';
                const allowed = groupType === 'both' || groupType === currentSubjectMode;
                const match = option.text.toLowerCase().includes(term);
                const keepSelected = option.value === selectEl.value;
                option.hidden = !(allowed && match) && !keepSelected;
                option.disabled = !(allowed && match) && !keepSelected;
            });
        });
    });
</script>
@endpush
