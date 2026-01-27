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
        flex: 1 1 220px;
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
    .empty-note {
        color: var(--muted);
        font-size: 14px;
        padding: 16px 0;
        text-align: center;
    }
    .type-pill {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        background: #fee2e2;
        color: #991b1b;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .type-pill.order { background: #fef3c7; color: #92400e; }
    .type-pill.vacation { background: #e0f2fe; color: #0369a1; }
    .type-pill.travel { background: #ecfccb; color: #365314; }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Отсутствия преподавателей</h1>
            <p class="page-subtitle">Больничные, приказы, отпуска, командировки</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('rooms.index') }}" class="btn-pill ghost">Кабинеты</a>
            <a href="{{ route('first.schedule.index') }}" class="btn-pill ghost">К расписанию</a>
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

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить период отсутствия</div>
        <form method="POST" action="{{ route('teacher_absences.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-field">
                    <label>Преподаватель</label>
                    <input type="search" class="search-input w-100 mb-2 teacher-filter" data-target="#absenceTeacherSelect" placeholder="Поиск преподавателя">
                    <select name="teacher_id" class="search-input w-100" required>
                        <option value="">— выберите</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->teacher_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Тип</label>
                    <select name="type" class="search-input w-100" required>
                        @foreach($absenceTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Начало</label>
                    <input type="date" name="start_date" class="search-input w-100" value="{{ old('start_date') }}" required>
                </div>
                <div class="form-field">
                    <label>Окончание</label>
                    <input type="date" name="end_date" class="search-input w-100" value="{{ old('end_date') }}" required>
                </div>
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label text-muted small">Комментарий</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Основание, номер приказа">{{ old('notes') }}</textarea>
            </div>
        </form>
        <div class="mt-3">
            <input type="search" id="absenceSearch" class="search-input w-100" placeholder="Поиск по преподавателю">
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список отсутствий</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="absenceTable">
                <thead>
                    <tr>
                        <th>Преподаватель</th>
                        <th>Тип</th>
                        <th>Период</th>
                        <th>Комментарий</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences as $absence)
                        <tr data-name="{{ mb_strtolower($absence->teacher_name ?? '') }}">
                            <td>{{ $absence->teacher_name }}</td>
                            <td>
                                <span class="type-pill {{ $absence->type }}">{{ $absenceTypes[$absence->type] ?? $absence->type }}</span>
                            </td>
                            <td>{{ $absence->start_date }} — {{ $absence->end_date }}</td>
                            <td>{{ $absence->notes ?? '—' }}</td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editAbsence{{ $absence->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('teacher_absences.destroy', $absence->id) }}" onsubmit="return confirm('Удалить запись?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-note">Пока нет записей.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($absences as $absence)
    <div class="modal fade" id="editAbsence{{ $absence->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('teacher_absences.update', $absence->id) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать отсутствие</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 teacher-filter" data-target="#editTeacherSelect{{ $absence->id }}" placeholder="Поиск преподавателя">
                            <select name="teacher_id" class="form-select" id="editTeacherSelect{{ $absence->id }}">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected($absence->teacher_id == $teacher->id)>{{ $teacher->teacher_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Тип</label>
                            <select name="type" class="form-select">
                                @foreach($absenceTypes as $key => $label)
                                    <option value="{{ $key }}" @selected($absence->type === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Начало</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $absence->start_date }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Окончание</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $absence->end_date }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Комментарий</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $absence->notes }}</textarea>
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
@endforeach

@push('scripts')
<script>
    const absenceSearch = document.getElementById('absenceSearch');
    const absenceTable = document.getElementById('absenceTable');
    const addTeacherSelect = document.querySelector('form[action="{{ route('teacher_absences.store') }}"] select[name="teacher_id"]');
    if (addTeacherSelect) {
        addTeacherSelect.id = 'absenceTeacherSelect';
        Array.from(addTeacherSelect.options).forEach((opt) => {
            opt.dataset.name = (opt.textContent || '').trim().toLowerCase();
        });
    }

    absenceSearch?.addEventListener('input', () => {
        const term = absenceSearch.value.trim().toLowerCase();
        absenceTable?.querySelectorAll('tbody tr').forEach((row) => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(term) ? '' : 'none';
        });
    });

    document.querySelectorAll('.teacher-filter').forEach((input) => {
        const targetSelector = input.getAttribute('data-target');
        const select = targetSelector ? document.querySelector(targetSelector) : null;
        if (!select) return;

        const options = Array.from(select.options);
        options.forEach((opt) => {
            opt.dataset.name = opt.dataset.name || (opt.textContent || '').trim().toLowerCase();
        });

        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            options.forEach((opt, idx) => {
                if (!opt.value && idx === 0) {
                    opt.hidden = false;
                    opt.disabled = false;
                    return;
                }
                const name = opt.dataset.name || '';
                const keepSelected = opt.selected;
                const visible = keepSelected || name.includes(term);
                opt.hidden = !visible;
                opt.disabled = !visible;
            });
        });
    });
</script>
@endpush
@endsection
