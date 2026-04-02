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
        vertical-align: middle;
    }
    .empty-note {
        color: var(--muted);
        font-size: 14px;
        padding: 16px 0;
        text-align: center;
    }
    .subject-name-cell {
        white-space: normal;
        word-break: break-word;
        line-height: 1.35;
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Предметы — {{ $course ?? 1 }} курс</h1>
            <p class="page-subtitle">Справочник предметов для расписания и формы 2</p>
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

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить предмет</div>
        <form method="POST" action="{{ route('subjects.store') }}">
            @csrf
            <input type="hidden" name="course" value="{{ $course ?? 1 }}">
            <div class="form-row">
                @if($hasModules)
                    <div class="form-field">
                        <label for="moduleTitle">Модуль (например, ОММ или ПМ)</label>
                        <input id="moduleTitle" name="module_title" class="search-input w-100" value="{{ old('module_title') }}" placeholder="ОММ">
                    </div>
                @endif
                <div class="form-field">
                    <label for="subjectName">Название предмета</label>
                    <input id="subjectName" name="subject_name" class="search-input w-100" required value="{{ old('subject_name') }}" placeholder="{{ $hasModules ? 'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни' : 'Например: Математика' }}">
                </div>
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
        </form>
        <div class="mt-3">
            <input type="search" id="subjectSearch" class="search-input w-100" placeholder="Поиск по предмету или модулю">
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список предметов</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="subjectsTable">
                <thead>
                    <tr>
                        @if($hasModules)
                            <th>Модуль</th>
                        @endif
                        <th>Название предмета</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        @php
                            $searchKey = trim(($subject->module_title ?? '') . ' ' . ($subject->subject_name ?? ''));
                        @endphp
                        <tr data-name="{{ mb_strtolower($searchKey) }}">
                            @if($hasModules)
                                <td>{{ $subject->module_title ?? '—' }}</td>
                            @endif
                            <td class="subject-name-cell">{{ $subject->subject_name ?? '—' }}</td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editSubject{{ $subject->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('subjects.destroy', $subject->id) }}" onsubmit="return confirm('Удалить предмет?');" class="d-inline">
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
                            <td colspan="{{ $hasModules ? 3 : 2 }}" class="empty-note">Пока нет предметов для этого курса.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($subjects as $subject)
    <div class="modal fade" id="editSubject{{ $subject->id }}" tabindex="-1" aria-labelledby="editSubjectLabel{{ $subject->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('subjects.update', $subject->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSubjectLabel{{ $subject->id }}">Редактировать предмет</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        @if($hasModules)
                            <div class="mb-3">
                                <label class="form-label">Модуль</label>
                                <input name="module_title" class="form-control" value="{{ old('module_title', $subject->module_title) }}">
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Название предмета</label>
                            <input name="subject_name" class="form-control" required value="{{ old('subject_name', $subject->subject_name) }}">
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

    const subjectSearch = document.getElementById('subjectSearch');
    const subjectRows = Array.from(document.querySelectorAll('#subjectsTable tbody tr'))
        .filter(row => row.dataset.name !== undefined);

    subjectSearch?.addEventListener('input', () => {
        const query = subjectSearch.value.trim().toLowerCase();
        subjectRows.forEach(row => {
            const name = row.dataset.name || '';
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });
</script>
@endpush
