@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Предметы — {{ $course ?? 1 }} курс</h1>
        <p class="page-subtitle">Справочник предметов для расписания и формы 2</p>
        <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
            <span class="field-label">Курс:</span>
            <select id="courseSelect" class="field-input" style="width:auto">
                @for($c = 1; $c <= 4; $c++)
                    <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                @endfor
            </select>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-secondary">К расписанию</a>
        <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn btn-secondary">Форма 2</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить предмет</h2>
    <form method="POST" action="{{ route('subjects.store') }}">
        @csrf
        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
        <div class="form-row">
            @if($hasModules)
                <div class="form-field">
                    <div class="field-group">
                        <label class="field-label" for="moduleTitle">Модуль</label>
                        <input id="moduleTitle" name="module_title" class="field-input" value="{{ old('module_title') }}" placeholder="ОММ">
                    </div>
                </div>
            @endif
            <div class="form-field" style="flex:2 1 300px">
                <div class="field-group">
                    <label class="field-label" for="subjectName">Название предмета</label>
                    <input id="subjectName" name="subject_name" class="field-input" required value="{{ old('subject_name') }}" placeholder="{{ $hasModules ? 'РО 1.1 Укреплять здоровье...' : 'Например: Математика' }}">
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </form>
    <div style="margin-top:12px">
        <input type="search" id="subjectSearch" class="field-input" placeholder="Поиск по предмету или модулю">
    </div>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Список предметов</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table" id="subjectsTable">
            <thead>
                <tr>
                    @if($hasModules)
                        <th>Модуль</th>
                    @endif
                    <th>Название предмета</th>
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    @php
                        $searchKey = trim(($subject->module_title ?? '') . ' ' . ($subject->subject_name ?? ''));
                    @endphp
                    <tr data-name="{{ mb_strtolower($searchKey) }}">
                        @if($hasModules)
                            <td class="td-muted">{{ $subject->module_title ?? '—' }}</td>
                        @endif
                        <td style="white-space:normal;word-break:break-word;line-height:1.35">{{ $subject->subject_name ?? '—' }}</td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editSubject{{ $subject->id }}">Изменить</button>
                                <form method="POST" action="{{ route('subjects.destroy', $subject->id) }}" onsubmit="return confirm('Удалить предмет?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="course" value="{{ $course ?? 1 }}">
                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $hasModules ? 3 : 2 }}">
                            <div class="empty-state">
                                <i class="bi bi-journal-bookmark"></i>
                                <div class="empty-state-title">Пока нет предметов для этого курса</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
    document.getElementById('courseSelect')?.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        params.set('course', this.value);
        window.location.search = params.toString();
    });

    const subjectSearch = document.getElementById('subjectSearch');
    const subjectRows = Array.from(document.querySelectorAll('#subjectsTable tbody tr'))
        .filter(row => row.dataset.name !== undefined);

    subjectSearch?.addEventListener('input', () => {
        const query = subjectSearch.value.trim().toLowerCase();
        subjectRows.forEach(row => {
            row.style.display = (row.dataset.name || '').includes(query) ? '' : 'none';
        });
    });
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/tours/subjects.js') }}"></script>
@endpush
