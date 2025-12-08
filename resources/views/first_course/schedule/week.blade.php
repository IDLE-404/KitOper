@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/week-schedule.css') }}">
<style>
    body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; }
</style>
@endpush

@section('content')
<div class="week-shell">
    <div class="week-card">
        @if($errors->any())
            <div class="alert alert-danger mb-3" role="alert">
                {{ $errors->first() }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success mb-3" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('first.schedule.week.save') }}" method="POST" id="weekForm">
            @csrf
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            <div class="week-header">
                <div>
                    <h1 class="week-title">Редактор недельного расписания</h1>
                    <p class="week-subtitle">Заполните пары с понедельника по пятницу</p>
                </div>
                <div class="group-select">
                    <label class="form-label mb-1 text-muted">Группа</label>
                    <select class="select-soft" name="group_id" id="groupSelect">
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ $g->id == $selectedGroupId ? 'selected' : '' }}>{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="group-select">
                    <label class="form-label mb-1 text-muted">Начало недели</label>
                    <input type="date" class="select-soft" name="week_start" id="weekStartInput" value="{{ $weekStart ?? '' }}">
                </div>
                <div class="group-select">
                    <label class="form-label mb-1 text-muted">Режим</label>
                    <select class="select-soft" name="week_mode" id="weekModeSelect">
                        <option value="auto" @selected(($weekModeInput ?? 'auto') === 'auto')>Авто (по чётности недели)</option>
                        <option value="numerator" @selected(($weekModeInput ?? $weekMode ?? 'numerator') === 'numerator')>Числитель</option>
                        <option value="denominator" @selected(($weekModeInput ?? '') === 'denominator')>Знаменатель</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-outline-secondary">← Назад к списку расписания</a>
            </div>

            <div class="day-tabs" id="dayTabs">
                @foreach($days as $index => $day)
                    <button type="button" class="day-tab {{ $index === 0 ? 'active' : '' }}" data-target="{{ $day['key'] }}">
                        <span class="short">{{ $day['label'] }}</span>
                        <span class="full-name">{{ $day['full'] }}</span>
                    </button>
                @endforeach
            </div>

            @foreach($days as $index => $day)
                @php
                    $dayKey = $day['key'];
                @endphp
                <div class="day-pane {{ $index === 0 ? 'active' : '' }}" id="pane-{{ $dayKey }}">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="width:80px;">№ пары</th>
                                <th>Предмет (ч/з)</th>
                                <th>Преподаватель (ч/з)</th>
                                <th style="width:160px;">Аудитория (ч/з)</th>
                                <th style="width:140px;">Подгруппа</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pairs as $pair)
                                @php
                                    $rowA = $existing[$dayKey][$pair]['1'] ?? $existing[$dayKey][$pair]['A'] ?? $existing[$dayKey][$pair][''] ?? null;
                                    $rowB = $existing[$dayKey][$pair]['2'] ?? $existing[$dayKey][$pair]['B'] ?? null;
                                @endphp
                                <tr>
                                    <td><span class="pill-badge">{{ $pair }}</span></td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-a">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-a" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id]">
                                                <option value="">Выберите предмет</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowA && $rowA->subject_id == $s->id)>{{ $s->name_ru ?? $s->subject_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-a-den">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-a-den" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_denominator]">
                                                <option value="">Если предмет чередуется</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowA && ($rowA->subject_id_denominator ?? null) == $s->id)>{{ $s->name_ru ?? $s->subject_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#teach-{{ $dayKey }}-{{ $pair }}-a">
                                            <select class="select-soft filterable" id="teach-{{ $dayKey }}-{{ $pair }}-a" name="schedule[{{ $dayKey }}][{{ $pair }}][teacher_id]">
                                                <option value="">Выберите преподавателя</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected($rowA && $rowA->teacher_id == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#teach-{{ $dayKey }}-{{ $pair }}-a-den">
                                            <select class="select-soft filterable" id="teach-{{ $dayKey }}-{{ $pair }}-a-den" name="schedule[{{ $dayKey }}][{{ $pair }}][teacher_id_denominator]">
                                                <option value="">Можно выбрать другого преподавателя</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected($rowA && ($rowA->teacher_id_denominator ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="text" class="input-soft" name="schedule[{{ $dayKey }}][{{ $pair }}][room_id]" value="{{ $rowA->room_id ?? '' }}" placeholder="Каб. 301">
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="text" class="input-soft" name="schedule[{{ $dayKey }}][{{ $pair }}][room_id_denominator]" value="{{ $rowA->room_id_denominator ?? '' }}" placeholder="Если аудитория меняется">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="pill-badge sub">1</span>
                                            <small class="text-muted">Подгруппа 1</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="split-{{ $dayKey }}-{{ $pair }}" name="schedule[{{ $dayKey }}][{{ $pair }}][has_subgroups]" value="1" {{ $rowB ? 'checked' : '' }}>
                                            <label class="form-check-label" for="split-{{ $dayKey }}-{{ $pair }}">Добавить подгруппу 2</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="subgroup-row {{ $rowB ? '' : 'd-none' }}" data-split="split-{{ $dayKey }}-{{ $pair }}">
                                    <td><span class="pill-badge sub">2</span></td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-b">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-b" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_second]">
                                                <option value="">Предмет подгруппы 2</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowB && $rowB->subject_id == $s->id)>{{ $s->name_ru ?? $s->subject_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-b-den">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-b-den" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_second_denominator]">
                                                <option value="">Если предмет чередуется</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowB && ($rowB->subject_id_denominator ?? null) == $s->id)>{{ $s->name_ru ?? $s->subject_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#teach-{{ $dayKey }}-{{ $pair }}-b">
                                            <select class="select-soft filterable" id="teach-{{ $dayKey }}-{{ $pair }}-b" name="schedule[{{ $dayKey }}][{{ $pair }}][teacher_id_second]">
                                                <option value="">Преподаватель 2 (опционально)</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected($rowB && $rowB->teacher_id == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#teach-{{ $dayKey }}-{{ $pair }}-b-den">
                                            <select class="select-soft filterable" id="teach-{{ $dayKey }}-{{ $pair }}-b-den" name="schedule[{{ $dayKey }}][{{ $pair }}][teacher_id_second_denominator]">
                                                <option value="">Можно выбрать другого преподавателя</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected($rowB && ($rowB->teacher_id_denominator ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="text" class="input-soft" name="schedule[{{ $dayKey }}][{{ $pair }}][room_id_second]" value="{{ $rowB->room_id ?? '' }}" placeholder="Каб. 302">
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="text" class="input-soft" name="schedule[{{ $dayKey }}][{{ $pair }}][room_id_second_denominator]" value="{{ $rowB->room_id_denominator ?? '' }}" placeholder="Если аудитория меняется">
                                        </div>
                                    </td>
                                    <td class="text-muted">Подгруппа 2</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="save-bar">
                <button class="btn-save" type="submit">Сохранить расписание</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const tabs = Array.from(document.querySelectorAll('.day-tab'));
    const panes = Array.from(document.querySelectorAll('.day-pane'));

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.target;

            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(`pane-${target}`).classList.add('active');
        });
    });

    // Переключение подгруппы B
    document.querySelectorAll('[id^="split-"]').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const row = document.querySelector(`tr.subgroup-row[data-split="${toggle.id}"]`);
            if (!row) return;
            if (toggle.checked) {
                row.classList.remove('d-none');
            } else {
                row.classList.add('d-none');
                row.querySelectorAll('input, select').forEach(el => {
                    if (el.tagName === 'SELECT') { el.selectedIndex = 0; }
                    else { el.value = ''; }
                });
            }
        });
    });

    // Быстрый поиск по select
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-target');
        const selectEl = document.querySelector(targetSelector);
        if (!selectEl) return;
        const originalOptions = Array.from(selectEl.options).map(opt => ({ value: opt.value, text: opt.text, selected: opt.selected }));

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            selectEl.innerHTML = '';
            originalOptions
                .filter(opt => opt.text.toLowerCase().includes(term))
                .forEach(opt => {
                    const node = document.createElement('option');
                    node.value = opt.value;
                    node.text = opt.text;
                    if (opt.selected) node.selected = true;
                    selectEl.appendChild(node);
                });
        });
    });

    // Смена группы — перезагрузка с параметром
    const groupSelect = document.getElementById('groupSelect');
    if (groupSelect) {
        groupSelect.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('group_id', groupSelect.value);
            window.location.search = params.toString();
        });
    }
</script>
@endpush
