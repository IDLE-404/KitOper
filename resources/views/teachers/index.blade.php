@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule/main.css') }}">
<style>
    .panel-card {
        background: var(--panel);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid #ecf0f6;
        padding: 18px 20px;
    }
    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 12px;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    .form-field {
        flex: 1 1 240px;
        min-width: 200px;
    }
    .form-field label {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
        display: inline-block;
    }
    .form-field--actions {
        flex: 0 0 auto;
    }
    .table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }
    .table thead th {
        color: var(--muted);
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #e6ebf2;
    }
    .table td {
        vertical-align: top;
    }
    .empty-note {
        color: var(--muted);
        font-size: 14px;
        padding: 16px 0;
        text-align: center;
    }
    .teacher-duplicate td {
        background: #fff7ed;
    }
    .duplicate-badge {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        color: #9a3412;
        background: #ffedd5;
        border: 1px solid #fed7aa;
    }
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 10px;
    }
    .subject-col {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .subject-col-head {
        background: #f8fafc;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
    }
    .subject-filter-wrap {
        padding: 8px 10px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }
    .subject-filter-input {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 12px;
        line-height: 1.2;
        color: #0f172a;
    }
    .subject-filter-input:focus {
        outline: none;
        border-color: #7aa2f7;
        box-shadow: 0 0 0 2px rgba(122, 162, 247, 0.18);
    }
    .subject-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px;
        max-height: 220px;
        overflow: auto;
        padding: 10px 12px;
        background: #fff;
    }
    .subject-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 13px;
        line-height: 1.35;
        padding: 2px 0;
    }
    .subject-item span {
        white-space: normal;
        word-break: break-word;
    }
    .subject-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }
    .subject-summary {
        font-size: 14px;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .subject-summary-muted {
        color: #64748b;
        margin-left: 6px;
    }
    .subject-accordion {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.28s ease, opacity 0.24s ease;
    }
    .subject-accordion.is-open {
        opacity: 1;
    }
    .subject-accordion-toggle {
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 12px;
    }
    .subject-pill-list {
        list-style: none;
        margin: 0;
        padding: 10px;
        display: grid;
        gap: 6px;
    }
    .subject-pill {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #1f2937 !important;
        border-radius: 16px;
        padding: 8px 10px;
        font-size: 13px;
        line-height: 1.4;
        text-decoration: none;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .subject-empty {
        color: #94a3b8;
        font-size: 12px;
        padding: 10px;
    }
    @media (max-width: 1200px) {
        .subjects-grid {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }
    }
    @media (max-width: 768px) {
        .subjects-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Преподаватели (общий справочник 1–4 курсов)</h1>
            <p class="page-subtitle">Один преподаватель, предметы сразу по всем курсам</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn-pill ghost">К расписанию</a>
            <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn-pill ghost">Форма 2</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $duplicateInitialsSet = [];
        foreach (($duplicateInitials ?? []) as $dup) {
            $key = mb_strtolower(trim((string) $dup));
            if ($key !== '') {
                $duplicateInitialsSet[$key] = true;
            }
        }
        $isDuplicateTeacher = function ($teacher) use ($duplicateInitialsSet) {
            $initials = mb_strtolower(trim((string) ($teacher->initials ?? '')));
            return $initials !== '' && isset($duplicateInitialsSet[$initials]);
        };
        $courses = [1, 2, 3, 4];
    @endphp

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить преподавателя</div>
        <form method="POST" action="{{ route('teachers.store') }}">
            @csrf
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            <input type="hidden" name="subjects_by_course_mode" value="1">
            <div class="form-row">
                <div class="form-field">
                    <label for="teacherName">ФИО преподавателя</label>
                    <input id="teacherName" name="teacher_name" class="search-input w-100" required value="{{ old('teacher_name') }}" placeholder="Например: Иванова И.Н.">
                </div>
                @if(!empty($rooms) && $rooms->count())
                    <div class="form-field">
                        <label for="defaultRoom">Закрепленный кабинет</label>
                        <select id="defaultRoom" name="default_room_id" class="search-input w-100">
                            <option value="">— не задан</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('default_room_id') == $room->id)>
                                    {{ $room->code }} — {{ $room->title ?? 'без названия' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-3">
                <label class="form-label">Предметы по курсам</label>
                <div class="subjects-grid">
                    @foreach($courses as $courseNo)
                        @php
                            $courseSubjects = $subjectsByCourse[$courseNo] ?? collect();
                            $selectedCreateSubjects = old("subject_ids_by_course.$courseNo", []);
                        @endphp
                        <div class="subject-col">
                            <div class="subject-col-head">{{ $courseNo }} курс</div>
                            <div class="subject-filter-wrap">
                                <input
                                    type="search"
                                    class="subject-filter-input"
                                    data-target-list="create-course-{{ $courseNo }}"
                                    placeholder="Поиск предмета"
                                >
                            </div>
                            <div class="subject-list" id="create-course-{{ $courseNo }}">
                                @forelse($courseSubjects as $subject)
                                    @php
                                        $subjectId = (int) $subject->id;
                                        $subjectLabel = $subject->title ?? $subject->subject_name;
                                    @endphp
                                    <label class="subject-item" data-subject-title="{{ mb_strtolower($subjectLabel) }}">
                                        <input type="checkbox" name="subject_ids_by_course[{{ $courseNo }}][]" value="{{ $subjectId }}" @checked(in_array($subjectId, array_map('intval', $selectedCreateSubjects), true))>
                                        <span>{{ $subjectLabel }}</span>
                                    </label>
                                @empty
                                    <div class="text-muted">Нет предметов</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn-pill primary">Добавить</button>
            </div>
        </form>
        <div class="mt-3">
            <input type="search" id="teacherSearch" class="search-input w-100" placeholder="Поиск по преподавателю">
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список преподавателей</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="teachersTable">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        @if($hasInitials)
                            <th>Инициалы</th>
                        @endif
                        <th>Предметы</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        @php
                            $isDuplicate = $isDuplicateTeacher($teacher);
                            $allTitles = [];
                            $titlesByCourse = [];
                            foreach ($courses as $courseNo) {
                                $ids = $teacherSubjectsByCourse[$courseNo][$teacher->id] ?? [];
                                $map = $subjectTitleMapByCourse[$courseNo] ?? [];
                                $titlesByCourse[$courseNo] = array_values(array_filter(array_map(
                                    fn($id) => $map[$id] ?? null,
                                    $ids
                                )));
                                foreach ($titlesByCourse[$courseNo] as $title) {
                                    $allTitles[] = $title;
                                }
                            }
                            $firstSubject = $allTitles[0] ?? '—';
                            $extraCount = max(count($allTitles) - 1, 0);
                            $accordionId = 'subjects-' . $teacher->id;
                        @endphp
                        <tr data-name="{{ mb_strtolower($teacher->teacher_name ?? '') }}" class="teacher-row {{ $isDuplicate ? 'teacher-duplicate' : '' }}">
                            <td>
                                {{ $teacher->teacher_name ?? '—' }}
                                @if($isDuplicate)
                                    <span class="duplicate-badge">Дубль по инициалам</span>
                                @endif
                            </td>
                            @if($hasInitials)
                                <td>{{ $teacher->initials ?? '—' }}</td>
                            @endif
                            <td>
                                <div class="subject-summary">
                                    {{ $firstSubject }}
                                    @if($extraCount > 0)
                                        <span class="subject-summary-muted">+{{ $extraCount }} еще</span>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm subject-accordion-toggle" data-target="{{ $accordionId }}" aria-expanded="false">
                                    Развернуть предметы
                                </button>
                                <div id="{{ $accordionId }}" class="subject-accordion mt-2">
                                    <div class="subjects-grid">
                                        @foreach($courses as $courseNo)
                                            @php $courseTitles = $titlesByCourse[$courseNo] ?? []; @endphp
                                            <div class="subject-col">
                                                <div class="subject-col-head">{{ $courseNo }} курс</div>
                                                @if(!empty($courseTitles))
                                                    <ul class="subject-pill-list">
                                                        @foreach($courseTitles as $title)
                                                            <li class="subject-pill">{{ $title }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="subject-empty">Нет предметов</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTeacher{{ $teacher->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('teachers.destroy', $teacher->id) }}" onsubmit="return confirm('Удалить преподавателя?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $hasInitials ? 4 : 3 }}" class="empty-note">Пока нет преподавателей.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($teachers as $teacher)
    <div class="modal fade" id="editTeacher{{ $teacher->id }}" tabindex="-1" aria-labelledby="editTeacherLabel{{ $teacher->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.update', $teacher->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <input type="hidden" name="subjects_by_course_mode" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTeacherLabel{{ $teacher->id }}">Редактировать преподавателя</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">ФИО преподавателя</label>
                            <input name="teacher_name" class="form-control" required value="{{ $teacher->teacher_name }}">
                        </div>
                        @if(!empty($rooms) && $rooms->count())
                            <div class="mb-3">
                                <label class="form-label">Закрепленный кабинет</label>
                                <select name="default_room_id" class="form-select">
                                    <option value="">— не задан</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" @selected(($teacher->default_room_id ?? null) == $room->id)>
                                            {{ $room->code }} — {{ $room->title ?? 'без названия' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-2">
                            <label class="form-label">Предметы по курсам</label>
                        </div>
                        <div class="subjects-grid">
                            @foreach($courses as $courseNo)
                                @php
                                    $courseSubjects = $subjectsByCourse[$courseNo] ?? collect();
                                    $selectedSubjects = array_map('intval', $teacherSubjectsByCourse[$courseNo][$teacher->id] ?? []);
                                @endphp
                                <div class="subject-col">
                                    <div class="subject-col-head">{{ $courseNo }} курс</div>
                                    <div class="subject-filter-wrap">
                                        <input
                                            type="search"
                                            class="subject-filter-input"
                                            data-target-list="edit-{{ $teacher->id }}-course-{{ $courseNo }}"
                                            placeholder="Поиск предмета"
                                        >
                                    </div>
                                    <div class="subject-list" id="edit-{{ $teacher->id }}-course-{{ $courseNo }}">
                                        @forelse($courseSubjects as $subject)
                                            @php
                                                $subjectId = (int) $subject->id;
                                                $subjectLabel = $subject->title ?? $subject->subject_name;
                                            @endphp
                                            <label class="subject-item" data-subject-title="{{ mb_strtolower($subjectLabel) }}">
                                                <input type="checkbox" name="subject_ids_by_course[{{ $courseNo }}][]" value="{{ $subjectId }}" @checked(in_array($subjectId, $selectedSubjects, true))>
                                                <span>{{ $subjectLabel }}</span>
                                            </label>
                                        @empty
                                            <div class="text-muted">Нет предметов</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
    const teacherSearch = document.getElementById('teacherSearch');
    const teacherRows = Array.from(document.querySelectorAll('#teachersTable tbody tr.teacher-row'));

    teacherSearch?.addEventListener('input', () => {
        const query = teacherSearch.value.trim().toLowerCase();
        teacherRows.forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    const toggleButtons = Array.from(document.querySelectorAll('.subject-accordion-toggle'));
    const setAccordionState = (button, panel, open) => {
        if (!panel) return;
        panel.classList.toggle('is-open', open);
        panel.style.maxHeight = open ? `${panel.scrollHeight}px` : '0px';
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        button.textContent = open ? 'Свернуть предметы' : 'Развернуть предметы';
    };

    toggleButtons.forEach(button => {
        const targetId = button.dataset.target;
        const panel = targetId ? document.getElementById(targetId) : null;
        if (!panel) return;
        setAccordionState(button, panel, false);
        button.addEventListener('click', () => {
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            setAccordionState(button, panel, !isOpen);
        });
    });

    window.addEventListener('resize', () => {
        toggleButtons.forEach(button => {
            if (button.getAttribute('aria-expanded') !== 'true') return;
            const targetId = button.dataset.target;
            const panel = targetId ? document.getElementById(targetId) : null;
            if (!panel) return;
            panel.style.maxHeight = `${panel.scrollHeight}px`;
        });
    });

    const subjectFilterInputs = Array.from(document.querySelectorAll('.subject-filter-input[data-target-list]'));
    subjectFilterInputs.forEach(input => {
        input.addEventListener('input', () => {
            const listId = input.dataset.targetList;
            const list = listId ? document.getElementById(listId) : null;
            if (!list) return;

            const query = input.value.trim().toLowerCase();
            const items = Array.from(list.querySelectorAll('.subject-item'));
            items.forEach(item => {
                const title = item.dataset.subjectTitle || item.textContent.toLowerCase();
                item.style.display = title.includes(query) ? '' : 'none';
            });
        });
    });
    
    // Table selection for AI context
    document.querySelectorAll('.teachers-table tbody tr').forEach(row => {
        row.style.cursor = 'pointer';
        row.title = 'Кликните чтобы выбрать строку для ИИ-ассистента';
        row.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button')) return;
            
            // Remove previous selection
            document.querySelectorAll('.teachers-table tbody tr').forEach(r => r.style.background = '');
            
            // Highlight this row
            this.style.background = 'rgba(79, 124, 255, 0.1)';
            
            // Get headers from thead
            const table = this.closest('table');
            if (!table) return;
            
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).filter(t => t);
            
            // Get only this row's data
            const cells = Array.from(this.querySelectorAll('td')).map(td => td.textContent.trim()).filter(t => t);
            const rowData = headers.map((h, i) => h + ': ' + (cells[i] || '')).join('\n');
            
            // Get row identifier (first cell - usually ID or name)
            const identifier = cells[0] || 'строка';
            const tableTitle = 'Преподаватели - ' + identifier;
            
            if (typeof window.selectTableForAI === 'function') {
                window.selectTableForAI(rowData, tableTitle);
            }
        });
    });
</script>
@endpush
