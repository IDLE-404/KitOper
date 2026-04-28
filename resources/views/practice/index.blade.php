@extends('layouts.app')

@section('content')
@php
    $course = $course ?? 2;
    $groupMap = $groups->pluck('group_name', 'id');
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Практика — {{ $course }} курс</h1>
        <p class="page-subtitle">Периоды практики скрывают расписание и формируют часы в Форме 2</p>
        <div style="margin-top:8px;display:flex;align-items:center;gap:8px">
            <span class="field-label">Курс:</span>
            <select id="courseSelect" class="field-input" style="width:auto">
                @for($c = 2; $c <= 4; $c++)
                    <option value="{{ $c }}" @selected($course == $c)>{{ $c }}</option>
                @endfor
            </select>
        </div>
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
    <h2 class="section-title">Добавить период практики</h2>
    <form method="POST" action="{{ route('practice.store') }}" id="practiceForm">
        @csrf
        <div class="form-row">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Курс</label>
                    <select class="field-input" name="course" id="courseSelectForm">
                        @for($c = 2; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected($course == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Группа</label>
                    <select class="field-input" name="group_id" required>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected((string) old('group_id') === (string) $g->id)>{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Тип практики</label>
                    <select class="field-input" name="type" id="practiceType">
                        @if($course == 2)
                            <option value="educational" @selected(old('type', 'educational') === 'educational')>Учебная</option>
                        @endif
                        <option value="production" @selected(old('type', $course == 2 ? 'educational' : 'production') === 'production')>Производственная</option>
                    </select>
                </div>
            </div>
            <div class="form-field" id="roomBlock">
                <div class="field-group">
                    <label class="field-label">Кабинет (учебная)</label>
                    <input type="text" class="field-input" name="room_id" placeholder="101" value="{{ old('room_id') }}">
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Начало практики</label>
                    <input type="date" class="field-input" name="start_date" value="{{ old('start_date') }}" required>
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label">Окончание</label>
                    <input type="date" class="field-input" name="end_date" value="{{ old('end_date') }}" required>
                </div>
            </div>
            <div class="form-field-auto" style="align-self:flex-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
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
                        <td class="td-muted">{{ $period->type === 'educational' ? ($period->room_id ?? '—') : '—' }}</td>
                        <td class="td-muted">{{ $period->start_date }} → {{ $period->end_date }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('practice.destroy', $period->id) }}" onsubmit="return confirm('Удалить период практики?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-briefcase"></i>
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

@push('scripts')
<script>
    const courseSelect = document.getElementById('courseSelect');
    const courseSelectForm = document.getElementById('courseSelectForm');
    const practiceType = document.getElementById('practiceType');
    const roomBlock = document.getElementById('roomBlock');
    const roomInput = roomBlock?.querySelector('input[name="room_id"]');

    const toggleRoom = () => {
        const isEducational = practiceType && practiceType.value === 'educational';
        if (roomBlock) roomBlock.style.display = isEducational ? '' : 'none';
        if (!isEducational && roomInput) roomInput.value = '';
    };

    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        window.location.search = params.toString();
    });

    courseSelectForm?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelectForm.value);
        window.location.search = params.toString();
    });

    practiceType?.addEventListener('change', toggleRoom);
    toggleRoom();
</script>
@endpush
