@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
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
        vertical-align: middle;
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
    .subject-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 8px 12px;
        max-height: none;
        overflow: visible;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }
    .subject-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    .subject-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Преподаватели — {{ $course ?? 1 }} курс</h1>
            <p class="page-subtitle">Управление списком преподавателей для расписания и формы 2</p>
            <div class="mt-2 d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Курс:</label>
                <select id="courseSelect" class="search-input" style="width:auto;">
                    @for($c = 1; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
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
    @endphp

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить преподавателя</div>
        <form method="POST" action="{{ route('teachers.store') }}">
            @csrf
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            @php
                $selectedCreateSubjects = old('subject_ids', []);
            @endphp
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
                <div class="form-field">
                    <label for="teacherSubjects">Предметы</label>
                    <div class="subject-picker" id="subjectPickerCreate">
                        <div class="subject-list">
                            @forelse($subjects as $subject)
                                @php
                                    $subjectId = $subject->id;
                                    $subjectLabel = $subject->title ?? $subject->subject_name;
                                @endphp
                                <label class="subject-item">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subjectId }}" @checked(in_array($subjectId, $selectedCreateSubjects))>
                                    <span>{{ $subjectLabel }}</span>
                                </label>
                            @empty
                                <div class="text-muted">Нет доступных предметов</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
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
                            $assigned = $teacherSubjects[$teacher->id] ?? [];
                            $assignedTitles = array_values(array_filter(array_map(
                                fn($id) => $subjectTitleMap[$id] ?? null,
                                $assigned
                            )));
                            $assignedLabel = $assignedTitles ? implode(', ', $assignedTitles) : '—';
                            $isDuplicate = $isDuplicateTeacher($teacher);
                        @endphp
                        <tr data-name="{{ mb_strtolower($teacher->teacher_name ?? '') }}" class="{{ $isDuplicate ? 'teacher-duplicate' : '' }}">
                            <td>
                                {{ $teacher->teacher_name ?? '—' }}
                                @if($isDuplicate)
                                    <span class="duplicate-badge">Дубль по инициалам</span>
                                @endif
                            </td>
                            @if($hasInitials)
                                <td>{{ $teacher->initials ?? '—' }}</td>
                            @endif
                            <td>{{ $assignedLabel }}</td>
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
                            <td colspan="2" class="empty-note">Пока нет преподавателей для этого курса.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($teachers as $teacher)
    <div class="modal fade" id="editTeacher{{ $teacher->id }}" tabindex="-1" aria-labelledby="editTeacherLabel{{ $teacher->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.update', $teacher->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTeacherLabel{{ $teacher->id }}">Редактировать преподавателя</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $selectedSubjects = old('subject_ids', $teacherSubjects[$teacher->id] ?? []);
                        @endphp
                        <div class="mb-3">
                            <label class="form-label">ФИО преподавателя</label>
                            <input name="teacher_name" class="form-control" required value="{{ old('teacher_name', $teacher->teacher_name) }}">
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
                        <div class="mb-3">
                            <label class="form-label">Предметы</label>
                            <div class="subject-picker" id="subjectPicker{{ $teacher->id }}">
                                <div class="subject-list">
                                    @forelse($subjects as $subject)
                                        @php
                                            $subjectId = $subject->id;
                                            $subjectLabel = $subject->title ?? $subject->subject_name;
                                        @endphp
                                        <label class="subject-item">
                                            <input type="checkbox" name="subject_ids[]" value="{{ $subjectId }}" @checked(in_array($subjectId, $selectedSubjects))>
                                            <span>{{ $subjectLabel }}</span>
                                        </label>
                                    @empty
                                        <div class="text-muted">Нет доступных предметов</div>
                                    @endforelse
                                </div>
                            </div>
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
    const courseSelect = document.getElementById('courseSelect');
    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        window.location.search = params.toString();
    });

    const teacherSearch = document.getElementById('teacherSearch');
    const teacherRows = Array.from(document.querySelectorAll('#teachersTable tbody tr'))
        .filter(row => row.dataset.name !== undefined);

    teacherSearch?.addEventListener('input', () => {
        const query = teacherSearch.value.trim().toLowerCase();
        teacherRows.forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

</script>
@endpush
