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
        flex: 1 1 180px;
        min-width: 170px;
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
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Праздники и каникулы</h1>
            <p class="page-subtitle">Управление праздничными и каникулярными днями для формы 2</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index') }}" class="btn-pill ghost">К расписанию</a>
            <a href="{{ route('first.schedule.form_two') }}" class="btn-pill ghost">Форма 2</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
        <div class="panel-title">Добавить праздник/каникулы</div>
        <form method="POST" action="{{ route('holidays.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-field">
                    <label for="holidayName">Название</label>
                    <input id="holidayName" name="name" class="search-input w-100" required value="{{ old('name') }}" placeholder="Например: Наурыз">
                </div>
                <div class="form-field">
                    <label for="holidayStart">Начало</label>
                    <input id="holidayStart" name="start_date" type="date" class="search-input w-100" required value="{{ old('start_date') }}">
                </div>
                <div class="form-field">
                    <label for="holidayEnd">Конец</label>
                    <input id="holidayEnd" name="end_date" type="date" class="search-input w-100" required value="{{ old('end_date') }}">
                </div>
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список праздников</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Период</th>
                        <th class="text-end">Действия</th>
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
                            <td>{{ $start }} @if($start !== $end) — {{ $end }} @endif</td>
                            <td class="text-end">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editHoliday{{ $holiday->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('Удалить запись?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-note">Записей пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                            <label class="form-label" for="holidayStart{{ $holiday->id }}">Начало</label>
                            <input id="holidayStart{{ $holiday->id }}" name="start_date" type="date" class="form-control" required value="{{ old('start_date', sprintf('2000-%02d-%02d', $holiday->start_month, $holiday->start_day)) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="holidayEnd{{ $holiday->id }}">Конец</label>
                            <input id="holidayEnd{{ $holiday->id }}" name="end_date" type="date" class="form-control" required value="{{ old('end_date', sprintf('2000-%02d-%02d', $holiday->end_month, $holiday->end_day)) }}">
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
