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
    $course = $course ?? 2;
    $groupMap = $groups->pluck('group_name', 'id');
@endphp

<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Практика — {{ $course }} курс</h1>
            <p class="page-subtitle">Периоды практики скрывают расписание и формируют часы в Форме 2</p>
            <div class="mt-2 d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Курс:</label>
                <select id="courseSelect" class="search-input" style="width:auto;">
                    @for($c = 2; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected($course == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
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
        <div class="panel-title">Добавить период практики</div>
        <form method="POST" action="{{ route('practice.store') }}" id="practiceForm">
            @csrf
            <div class="form-row">
                <div class="form-field">
                    <label>Курс</label>
                    <select class="search-input" name="course" id="courseSelectForm">
                        @for($c = 2; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected($course == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-field">
                    <label>Группа</label>
                    <select class="search-input" name="group_id" required>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected((string) old('group_id') === (string) $g->id)>{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Тип практики</label>
                    <select class="search-input" name="type" id="practiceType">
                        @if($course == 2)
                            <option value="educational" @selected(old('type', 'educational') === 'educational')>Учебная</option>
                        @endif
                        <option value="production" @selected(old('type', $course == 2 ? 'educational' : 'production') === 'production')>Производственная</option>
                    </select>
                </div>
                <div class="form-field" id="roomBlock">
                    <label>Кабинет (учебная)</label>
                    <input type="text" class="search-input" name="room_id" placeholder="101" value="{{ old('room_id') }}">
                </div>
                <div class="form-field">
                    <label>Начало практики</label>
                    <input type="date" class="search-input" name="start_date" value="{{ old('start_date') }}" required>
                </div>
                <div class="form-field">
                    <label>Окончание</label>
                    <input type="date" class="search-input" name="end_date" value="{{ old('end_date') }}" required>
                </div>
                <div class="form-field form-field--actions">
                    <button class="btn-pill primary" type="submit">Сохранить</button>
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
                        <th>Тип</th>
                        <th>Кабинет</th>
                        <th>Период</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        <tr>
                            <td>{{ $groupMap[$period->group_id] ?? '—' }}</td>
                            <td>{{ $period->type === 'educational' ? 'Учебная' : 'Производственная' }}</td>
                            <td>{{ $period->type === 'educational' ? ($period->room_id ?? '—') : '—' }}</td>
                            <td>{{ $period->start_date }} → {{ $period->end_date }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('practice.destroy', $period->id) }}" onsubmit="return confirm('Удалить период практики?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-pill ghost">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-note">Периоды не добавлены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const courseSelect = document.getElementById('courseSelect');
    const courseSelectForm = document.getElementById('courseSelectForm');
    const practiceType = document.getElementById('practiceType');
    const roomBlock = document.getElementById('roomBlock');
    const roomInput = roomBlock?.querySelector('input[name="room_id"]');

    const toggleRoom = () => {
        const isEducational = practiceType && practiceType.value === 'educational';
        roomBlock?.classList.toggle('d-none', !isEducational);
        if (!isEducational && roomInput) {
            roomInput.value = '';
        }
    };

    if (courseSelect) {
        courseSelect.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('course', courseSelect.value);
            window.location.search = params.toString();
        });
    }

    if (courseSelectForm) {
        courseSelectForm.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('course', courseSelectForm.value);
            window.location.search = params.toString();
        });
    }

    if (practiceType) {
        practiceType.addEventListener('change', toggleRoom);
    }

    toggleRoom();
</script>
@endsection
