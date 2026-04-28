@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Праздники и каникулы</h1>
        <p class="page-subtitle">Управление праздничными и каникулярными днями для формы 2</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('first.schedule.index') }}" class="btn btn-secondary">К расписанию</a>
        <a href="{{ route('first.schedule.form_two') }}" class="btn btn-secondary">Форма 2</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить праздник / каникулы</h2>
    <form method="POST" action="{{ route('holidays.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-field" style="flex:2 1 240px">
                <div class="field-group">
                    <label class="field-label" for="holidayName">Название</label>
                    <input id="holidayName" name="name" class="field-input" required value="{{ old('name') }}" placeholder="Например: Наурыз">
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label" for="holidayStart">Начало</label>
                    <input id="holidayStart" name="start_date" type="date" class="field-input" required value="{{ old('start_date') }}">
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label" for="holidayEnd">Конец</label>
                    <input id="holidayEnd" name="end_date" type="date" class="field-input" required value="{{ old('end_date') }}">
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </form>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Список праздников</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Период</th>
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                    @php
                        $start = str_pad($holiday->start_day, 2, '0', STR_PAD_LEFT) . '.' . str_pad($holiday->start_month, 2, '0', STR_PAD_LEFT);
                        $end = str_pad($holiday->end_day, 2, '0', STR_PAD_LEFT) . '.' . str_pad($holiday->end_month, 2, '0', STR_PAD_LEFT);
                    @endphp
                    <tr>
                        <td>{{ $holiday->name }}</td>
                        <td class="td-muted">{{ $start }}@if($start !== $end) — {{ $end }}@endif</td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editHoliday{{ $holiday->id }}">Изменить</button>
                                <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('Удалить запись?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <i class="bi bi-calendar-event"></i>
                                <div class="empty-state-title">Записей пока нет</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($holidays as $holiday)
    <div class="modal fade" id="editHoliday{{ $holiday->id }}" tabindex="-1" aria-labelledby="editHolidayLabel{{ $holiday->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('holidays.update', $holiday) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHolidayLabel{{ $holiday->id }}">Редактировать</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Название</label>
                            <input name="name" class="form-control" required value="{{ old('name', $holiday->name) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Начало</label>
                            <input name="start_date" type="date" class="form-control" required value="{{ old('start_date', sprintf('2000-%02d-%02d', $holiday->start_month, $holiday->start_day)) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Конец</label>
                            <input name="end_date" type="date" class="form-control" required value="{{ old('end_date', sprintf('2000-%02d-%02d', $holiday->end_month, $holiday->end_day)) }}">
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
