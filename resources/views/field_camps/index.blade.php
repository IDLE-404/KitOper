@extends('layouts.app')

@section('content')
@php
    $course = 1;
    $groupMap = $groups->pluck('group_name', 'id');
    $teacherMap = $teachers->pluck('teacher_name', 'id');
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Полевые сборы — 1 курс</h1>
        <p class="page-subtitle">Периоды сборов скрывают расписание и формируют часы в Форме 2</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('first.schedule.index', ['course' => $course]) }}" class="btn btn-secondary">К расписанию</a>
        <a href="{{ route('first.schedule.form_two', ['course' => $course]) }}" class="btn btn-secondary">Форма 2</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        {{ $errors->first() }}
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить период сборов</h2>
    <form method="POST" action="{{ route('field_camps.store') }}" id="fieldCampForm">
        @csrf
        <div class="form-row">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Курс</label>
                    <select class="field-input" name="course" required>
                        <option value="1" selected>1</option>
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Группа</label>
                    <select class="field-input" name="group_id" required>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Преподаватель</label>
                    <select class="field-input" name="teacher_id" required>
                        @if($teachers->isEmpty())
                            <option value="">Нет преподавателей по НВП</option>
                        @else
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->teacher_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Кабинет</label>
                    <input type="text" class="field-input" name="room_id" placeholder="101">
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Начало сборов</label>
                    <input type="date" class="field-input" name="start_date" required>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Окончание</label>
                    <input type="date" class="field-input" name="end_date" required>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Часов в день</label>
                    <input type="number" class="field-input" name="hours_per_day" value="6" min="1" max="10">
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end">
                <button class="btn btn-primary" type="submit" @disabled($teachers->isEmpty())>Сохранить</button>
            </div>
        </div>
    </form>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Текущие периоды</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Группа</th>
                    <th>Преподаватель</th>
                    <th>Кабинет</th>
                    <th>Период</th>
                    <th>Часов/день</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                    <tr>
                        <td>{{ $groupMap[$period->group_id] ?? '—' }}</td>
                        <td>{{ $teacherMap[$period->teacher_id] ?? '—' }}</td>
                        <td class="td-muted">{{ $period->room_id ?? '—' }}</td>
                        <td class="td-muted">{{ $period->start_date }} → {{ $period->end_date }}</td>
                        <td class="td-muted">{{ $period->hours_per_day }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('field_camps.destroy', $period->id) }}" onsubmit="return confirm('Удалить период сборов?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-compass"></i>
                                <div class="empty-state-title">Периоды не добавлены</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
