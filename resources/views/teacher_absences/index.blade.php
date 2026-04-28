@extends('layouts.app')
@push('styles')
<style>
    .absence-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 500;
        background: #fee2e2;
        color: #991b1b;
    }
    .absence-badge.order { background: #fef3c7; color: #92400e; }
    .absence-badge.vacation { background: #e0f2fe; color: #0369a1; }
    .absence-badge.travel { background: #ecfccb; color: #365314; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Отсутствия преподавателей</h1>
        <p class="page-subtitle">Больничные, приказы, отпуска, командировки</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Кабинеты</a>
        <a href="{{ route('first.schedule.index') }}" class="btn btn-secondary">К расписанию</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить период отсутствия</h2>
    <form method="POST" action="{{ route('teacher_absences.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Преподаватель</label>
                    <input type="search" class="field-input teacher-filter" data-target="#absenceTeacherSelect" placeholder="Поиск преподавателя" style="margin-bottom:6px">
                    <select name="teacher_id" id="absenceTeacherSelect" class="field-input" required>
                        <option value="">— выберите</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->teacher_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Тип</label>
                    <select name="type" class="field-input" required>
                        @foreach($absenceTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Начало</label>
                    <input type="date" name="start_date" class="field-input" value="{{ old('start_date') }}" required>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Окончание</label>
                    <input type="date" name="end_date" class="field-input" value="{{ old('end_date') }}" required>
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
        <div style="margin-top:12px">
            <div class="field-group">
                <label class="field-label">Комментарий</label>
                <textarea name="notes" class="field-input" rows="2" placeholder="Основание, номер приказа" style="resize:vertical">{{ old('notes') }}</textarea>
            </div>
        </div>
    </form>
    <div style="margin-top:12px">
        <input type="search" id="absenceSearch" class="field-input" placeholder="Поиск по преподавателю">
    </div>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Список отсутствий</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table" id="absenceTable">
            <thead>
                <tr>
                    <th>Преподаватель</th>
                    <th>Тип</th>
                    <th>Период</th>
                    <th>Комментарий</th>
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                    <tr data-name="{{ mb_strtolower($absence->teacher_name ?? '') }}">
                        <td>{{ $absence->teacher_name }}</td>
                        <td><span class="absence-badge {{ $absence->type }}">{{ $absenceTypes[$absence->type] ?? $absence->type }}</span></td>
                        <td class="td-muted">{{ $absence->start_date }} — {{ $absence->end_date }}</td>
                        <td class="td-muted">{{ $absence->notes ?? '—' }}</td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editAbsence{{ $absence->id }}">Изменить</button>
                                <form method="POST" action="{{ route('teacher_absences.destroy', $absence->id) }}" onsubmit="return confirm('Удалить запись?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-clipboard-check"></i>
                                <div class="empty-state-title">Пока нет записей</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
@endsection

@push('scripts')
<script>
    const absenceSearch = document.getElementById('absenceSearch');
    absenceSearch?.addEventListener('input', () => {
        const term = absenceSearch.value.trim().toLowerCase();
        document.querySelectorAll('#absenceTable tbody tr').forEach(row => {
            row.style.display = (row.dataset.name || '').includes(term) ? '' : 'none';
        });
    });

    document.querySelectorAll('.teacher-filter').forEach(input => {
        const targetSelector = input.getAttribute('data-target');
        const select = targetSelector ? document.querySelector(targetSelector) : null;
        if (!select) return;
        const options = Array.from(select.options);
        options.forEach(opt => {
            opt.dataset.name = opt.dataset.name || (opt.textContent || '').trim().toLowerCase();
        });
        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            options.forEach((opt, idx) => {
                if (!opt.value && idx === 0) { opt.hidden = false; opt.disabled = false; return; }
                const visible = opt.selected || (opt.dataset.name || '').includes(term);
                opt.hidden = !visible;
                opt.disabled = !visible;
            });
        });
    });
</script>
@endpush
