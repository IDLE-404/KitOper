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
        flex: 1 1 220px;
        min-width: 200px;
    }
    .form-field .search-input {
        width: 100%;
        min-width: 0;
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
@php
    $course = 1;
    $groupMap = $groups->pluck('group_name', 'id');
    $teacherMap = $teachers->pluck('teacher_name', 'id');
@endphp

<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Полевые сборы — 1 курс</h1>
            <p class="page-subtitle">Периоды сборов скрывают расписание и формируют часы в Форме 2</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index', ['course' => $course]) }}" class="btn-pill ghost">К расписанию</a>
            <a href="{{ route('first.schedule.form_two', ['course' => $course]) }}" class="btn-pill ghost">Форма 2</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            {{ $errors->first() }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="panel-card mb-3">
        <div class="panel-title">Добавить период сборов</div>
        <form method="POST" action="{{ route('field_camps.store') }}" id="fieldCampForm">
            @csrf
            <div class="form-row">
                <div class="form-field">
                    <label>Курс</label>
                    <select class="search-input" name="course" required>
                        <option value="1" selected>1</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Группа</label>
                    <select class="search-input" name="group_id" required>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Преподаватель</label>
                    <select class="search-input" name="teacher_id" required>
                        @if($teachers->isEmpty())
                            <option value="">Нет преподавателей по НВП</option>
                        @else
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-field">
                    <label>Кабинет</label>
                    <input type="text" class="search-input" name="room_id" placeholder="101">
                </div>
                <div class="form-field">
                    <label>Начало сборов</label>
                    <input type="date" class="search-input" name="start_date" required>
                </div>
                <div class="form-field">
                    <label>Окончание</label>
                    <input type="date" class="search-input" name="end_date" required>
                </div>
                <div class="form-field">
                    <label>Часов в день</label>
                    <input type="number" class="search-input" name="hours_per_day" value="6" min="1" max="10">
                </div>
                <div class="form-field form-field--actions">
                    <button class="btn-pill primary" type="submit" @disabled($teachers->isEmpty())>Сохранить</button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-card">
        <div class="panel-title">Текущие периоды</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Преподаватель</th>
                        <th>Кабинет</th>
                        <th>Период</th>
                        <th>Часы/день</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        <tr>
                            <td>{{ $groupMap[$period->group_id] ?? '—' }}</td>
                            <td>{{ $teacherMap[$period->teacher_id] ?? '—' }}</td>
                            <td>{{ $period->room_id ?? '—' }}</td>
                            <td>{{ $period->start_date }} → {{ $period->end_date }}</td>
                            <td>{{ $period->hours_per_day }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('field_camps.destroy', $period->id) }}" onsubmit="return confirm('Удалить период сборов?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-pill ghost">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-note">Периоды не добавлены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
