@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/week-schedule.css') }}">
<style>
    body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; }
    .semester-expand {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 1px solid #e2e8f0;
    }
    .semester-expand h2 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .semester-expand .subtitle {
        color: #64748b;
        margin-bottom: 24px;
    }
    .semester-expand .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }
    .semester-expand .form-grid .form-group {
        display: flex;
        flex-direction: column;
    }
    .semester-expand label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }
    .semester-expand .input-soft,
    .semester-expand .select-soft {
        width: 100%;
    }
    .semester-expand .options-row {
        display: flex;
        gap: 24px;
        margin-top: 16px;
        flex-wrap: wrap;
    }
    .semester-expand .options-row .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-expand {
        margin-top: 24px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: box-shadow 0.2s ease;
    }
    .btn-expand:hover {
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
    }
    .holiday-banner {
        margin: 1rem 0;
        padding: 0.8rem 1rem;
        border-radius: 12px;
        background: #fff7d6;
        border: 1px solid #fcd34d;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.85rem;
    }
    .holiday-banner__title {
        font-weight: 600;
        color: #92400e;
    }
    .pair-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .remove-pair-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #b91c1c;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        padding: 0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .remove-pair-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(185, 28, 28, 0.2);
    }
    .holiday-pill {
        background: #fff;
        border-radius: 999px;
        padding: 0.2rem 0.6rem;
        border: 1px solid #fbbf24;
        color: #92400e;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .day-fieldset {
        border: none;
        padding: 0;
        margin: 0;
    }
    .day-fieldset[disabled] {
        opacity: 0.8;
    }
    .holiday-day-indicator {
        font-size: 0.65rem;
        color: #92400e;
        border: 1px solid #fcd34d;
        border-radius: 999px;
        padding: 0 0.4rem;
        margin-left: 0.4rem;
    }
    .holiday-row {
        background-color: #fefce8;
    }
    .holiday-note-block {
        margin-top: 0.6rem;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        background: #fef9c3;
        font-size: 0.8rem;
        color: #7c2d12;
    }
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
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="weekPrev">Предыдущая неделя</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="weekNext">Следующая неделя</button>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-outline-secondary">← Назад к списку расписания</a>
            </div>

            @if(!empty($weeklyHolidays))
                <div class="holiday-banner">
                    <div class="holiday-banner__title">Праздники недели:</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($weeklyHolidays as $holiday)
                            <span class="holiday-pill" title="{{ $holiday['name'] ?? '' }}">
                                <span>🎉</span>
                                {{ $holiday['label'] ?? '' }} ({{ $holiday['day'] ?? 'день' }}) — {{ $holiday['name'] ?? 'праздник' }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="day-tabs" id="dayTabs">
                @foreach($days as $index => $day)
                    <button type="button" class="day-tab {{ $index === 0 ? 'active' : '' }}" data-target="{{ $day['key'] }}">
                        <span class="short">{{ $day['label'] }}</span>
                        <span class="full-name">{{ $day['full'] }}</span>
                        @if(!empty($day['holiday']))
                            <span class="holiday-day-indicator" title="Праздник — {{ $day['holiday']['name'] }}">🎉</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @foreach($days as $index => $day)
                @php
                    $dayKey = $day['key'];
                    $isHoliday = !empty($day['holiday']);
                @endphp
                <div class="day-pane {{ $index === 0 ? 'active' : '' }}" id="pane-{{ $dayKey }}">
                    <fieldset class="day-fieldset" @disabled($isHoliday)>
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
                                        $showPair = (bool) ($rowA || $rowB);
                                    @endphp
                                    <tr class="pair-row {{ $showPair ? '' : 'd-none' }}" data-day="{{ $dayKey }}" data-pair="{{ $pair }}">
                                    <td>
                                        <div class="pair-actions">
                                            <span class="pill-badge">{{ $pair }}</span>
                                            <button type="button" class="remove-pair-btn" data-day="{{ $dayKey }}" data-pair="{{ $pair }}" title="Убрать пару">
                                                X
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-a">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-a" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id]" data-teacher-target="#teach-{{ $dayKey }}-{{ $pair }}-a">
                                                <option value="">Выберите предмет</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowA && $rowA->subject_id == $s->id)>{{ $s->title ?? ($s->name_ru ?? $s->subject_name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-a-den">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-a-den" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_denominator]" data-teacher-target="#teach-{{ $dayKey }}-{{ $pair }}-a-den">
                                                <option value="">Если предмет чередуется</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowA && ($rowA->subject_id_denominator ?? null) == $s->id)>{{ $s->title ?? ($s->name_ru ?? $s->subject_name) }}</option>
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
                                <tr class="subgroup-row {{ $showPair && $rowB ? '' : 'd-none' }}" data-day="{{ $dayKey }}" data-pair="{{ $pair }}" data-split="split-{{ $dayKey }}-{{ $pair }}">
                                    <td><span class="pill-badge sub">2</span></td>
                                    <td>
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Числитель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-b">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-b" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_second]" data-teacher-target="#teach-{{ $dayKey }}-{{ $pair }}-b">
                                                <option value="">Предмет подгруппы 2</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowB && $rowB->subject_id == $s->id)>{{ $s->title ?? ($s->name_ru ?? $s->subject_name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <div class="text-muted small mb-1">Знаменатель</div>
                                            <input type="search" class="input-soft mb-2 filter-input" placeholder="Поиск" data-target="#subj-{{ $dayKey }}-{{ $pair }}-b-den">
                                            <select class="select-soft filterable" id="subj-{{ $dayKey }}-{{ $pair }}-b-den" name="schedule[{{ $dayKey }}][{{ $pair }}][subject_id_second_denominator]" data-teacher-target="#teach-{{ $dayKey }}-{{ $pair }}-b-den">
                                                <option value="">Если предмет чередуется</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected($rowB && ($rowB->subject_id_denominator ?? null) == $s->id)>{{ $s->title ?? ($s->name_ru ?? $s->subject_name) }}</option>
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
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm add-pair-btn" data-day="{{ $dayKey }}">
                                Добавить пару
                            </button>
                        </div>
                    </fieldset>
                    @if($isHoliday)
                        <div class="holiday-note-block">
                            В {{ $day['full'] ?? 'день' }} ({{ $day['label'] ?? '' }}) — {{ $day['holiday']['name'] ?? 'праздник' }}.
                            Записи сюда блокируются автоматически.
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="save-bar">
                <button class="btn-save" type="submit">Сохранить расписание</button>
            </div>
        </form>

        <div class="semester-expand" id="semesterExpandSection">
            <h2>Развернуть на семестр</h2>
            <p class="subtitle">Скопируйте выбранную неделю вперёд на весь диапазон месяцев.</p>
            <form action="{{ route('first.schedule.semester.expand') }}" method="POST" class="semester-expand-form">
                @csrf
                <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="expandGroup">Группа</label>
                        <select class="select-soft" id="expandGroup" name="group_id" required>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" @selected(old('group_id', $selectedGroupId) == $g->id)>{{ $g->group_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="templateWeek">Эталонная неделя</label>
                        <input type="date"
                               id="templateWeek"
                               name="template_week_start"
                               class="select-soft"
                               value="{{ old('template_week_start', $weekStart ?? '') }}"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="semesterStart">Начало семестра</label>
                        <input type="date"
                               id="semesterStart"
                               name="semester_start"
                               class="select-soft"
                               value="{{ old('semester_start', $weekStart ?? '') }}"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="semesterEnd">Окончание семестра</label>
                        <input type="date"
                               id="semesterEnd"
                               name="semester_end"
                               class="select-soft"
                               value="{{ old('semester_end') }}"
                               required>
                    </div>
                </div>
                <div class="options-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="skipExisting" name="skip_existing" value="1" @checked(old('skip_existing'))>
                        <label class="form-check-label" for="skipExisting">Пропускать недели, где уже есть расписание</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="syncFormTwo" name="sync_form_two" value="1" @checked(old('sync_form_two', true))>
                        <label class="form-check-label" for="syncFormTwo">Синхронизировать Форму 2</label>
                    </div>
                </div>
                <button type="submit" class="btn-expand">Развернуть расписание</button>
            </form>
        </div>
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

    const clearRowInputs = (row) => {
        row.querySelectorAll('input, select').forEach(el => {
            if (el.tagName === 'SELECT') { el.selectedIndex = 0; }
            else { el.value = ''; }
            if (el.type === 'checkbox') { el.checked = false; }
        });
    };

    const updateAddButtons = () => {
        document.querySelectorAll('.add-pair-btn').forEach(btn => {
            const day = btn.dataset.day;
            const hidden = document.querySelector(`tr.pair-row.d-none[data-day="${day}"]`);
            btn.disabled = !hidden;
        });
    };

    document.querySelectorAll('.add-pair-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            const nextRow = document.querySelector(`tr.pair-row.d-none[data-day="${day}"]`);
            if (!nextRow) return;
            nextRow.classList.remove('d-none');
            updateAddButtons();
        });
    });

    document.querySelectorAll('.remove-pair-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            const pair = btn.dataset.pair;
            const row = document.querySelector(`tr.pair-row[data-day="${day}"][data-pair="${pair}"]`);
            const subRow = document.querySelector(`tr.subgroup-row[data-day="${day}"][data-pair="${pair}"]`);
            if (!row) return;
            clearRowInputs(row);
            row.classList.add('d-none');
            if (subRow) {
                clearRowInputs(subRow);
                subRow.classList.add('d-none');
            }
            const toggle = document.getElementById(`split-${day}-${pair}`);
            if (toggle) {
                toggle.checked = false;
            }
            updateAddButtons();
        });
    });

    updateAddButtons();

    const subjectTeacherMap = @json($teacherSubjectMap ?? []);
    const selectOptionsMap = new Map();

    document.querySelectorAll('select.filterable').forEach(selectEl => {
        selectOptionsMap.set(selectEl, Array.from(selectEl.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        })));
    });

    const rebuildSelectOptions = (selectEl, allowedValues = null, term = '') => {
        const options = selectOptionsMap.get(selectEl) || Array.from(selectEl.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        }));
        const allowedSet = allowedValues && allowedValues.length ? new Set(allowedValues.map(String)) : null;
        const previousValue = selectEl.value;
        const search = term.toLowerCase();
        selectEl.innerHTML = '';

        let hasSelection = false;
        options.forEach(opt => {
            if (allowedSet && opt.value && !allowedSet.has(String(opt.value))) {
                return;
            }
            if (search && !opt.text.toLowerCase().includes(search)) {
                return;
            }
            const node = document.createElement('option');
            node.value = opt.value;
            node.text = opt.text;
            if (opt.value === previousValue) {
                node.selected = true;
                hasSelection = true;
            }
            selectEl.appendChild(node);
        });

        if (!hasSelection && selectEl.options.length) {
            selectEl.selectedIndex = 0;
        }

        if (allowedSet) {
            selectEl.dataset.allowedValues = JSON.stringify(Array.from(allowedSet));
        } else {
            delete selectEl.dataset.allowedValues;
        }
    };

    const getAllowedTeachers = (subjectId) => {
        if (!subjectId) {
            return null;
        }
        const allowed = subjectTeacherMap[subjectId];
        if (!Array.isArray(allowed) || allowed.length === 0) {
            return null;
        }
        return allowed.map(String);
    };

    document.querySelectorAll('select[data-teacher-target]').forEach(subjectSelect => {
        const target = subjectSelect.dataset.teacherTarget;
        const teacherSelect = document.querySelector(target);
        if (!teacherSelect) return;

        const applyFilter = () => {
            const allowed = getAllowedTeachers(subjectSelect.value);
            const filterInput = document.querySelector(`.filter-input[data-target="${target}"]`);
            if (filterInput) {
                filterInput.value = '';
            }
            rebuildSelectOptions(teacherSelect, allowed, '');
        };

        subjectSelect.addEventListener('change', applyFilter);
        applyFilter();
    });

    // Быстрый поиск по select
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-target');
        const selectEl = document.querySelector(targetSelector);
        if (!selectEl) return;

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            let allowedValues = null;
            if (selectEl.dataset.allowedValues) {
                try {
                    allowedValues = JSON.parse(selectEl.dataset.allowedValues);
                } catch (err) {
                    allowedValues = null;
                }
            }
            const previousValue = selectEl.value;
            rebuildSelectOptions(selectEl, allowedValues, term);
            if (selectEl.dataset.teacherTarget && selectEl.value !== previousValue) {
                selectEl.dispatchEvent(new Event('change'));
            }
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

    const weekStartInput = document.getElementById('weekStartInput');
    const weekNext = document.getElementById('weekNext');
    const weekPrev = document.getElementById('weekPrev');
    const applyWeekStart = (value) => {
        const params = new URLSearchParams(window.location.search);
        if (groupSelect) {
            params.set('group_id', groupSelect.value);
        }
        if (value) {
            params.set('week_start', value);
        } else {
            params.delete('week_start');
        }
        window.location.search = params.toString();
    };

    if (weekNext && weekStartInput) {
        weekNext.addEventListener('click', () => {
            let baseDate = new Date();
            if (weekStartInput.value) {
                const [year, month, day] = weekStartInput.value.split('-').map(Number);
                baseDate = new Date(year, (month || 1) - 1, day || 1);
            }
            if (Number.isNaN(baseDate.getTime())) return;
            baseDate.setDate(baseDate.getDate() + 7);
            const y = baseDate.getFullYear();
            const m = String(baseDate.getMonth() + 1).padStart(2, '0');
            const d = String(baseDate.getDate()).padStart(2, '0');
            const isoDate = `${y}-${m}-${d}`;
            weekStartInput.value = isoDate;
            applyWeekStart(isoDate);
        });
    }

    if (weekPrev && weekStartInput) {
        weekPrev.addEventListener('click', () => {
            let baseDate = new Date();
            if (weekStartInput.value) {
                const [year, month, day] = weekStartInput.value.split('-').map(Number);
                baseDate = new Date(year, (month || 1) - 1, day || 1);
            }
            if (Number.isNaN(baseDate.getTime())) return;
            baseDate.setDate(baseDate.getDate() - 7);
            const y = baseDate.getFullYear();
            const m = String(baseDate.getMonth() + 1).padStart(2, '0');
            const d = String(baseDate.getDate()).padStart(2, '0');
            const isoDate = `${y}-${m}-${d}`;
            weekStartInput.value = isoDate;
            applyWeekStart(isoDate);
        });
    }
</script>
@endpush
