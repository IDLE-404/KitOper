@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
<style>
.repl-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px; box-shadow:0 8px 20px rgba(15,23,42,0.06); }
.repl-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; background:#eef2ff; color:#4338ca; font-weight:700; }
.repl-row { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:12px; align-items:center; }
.repl-row .label { font-size:12px; color:#6b7280; }
.repl-row .value { font-weight:600; color:#0f172a; }
.repl-row .value.muted { color:#9ca3af; }
.filter-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:10px; margin-bottom:14px; }
.btn-ghost { border:1px solid #e2e8f0; background:#fff; padding:8px 12px; border-radius:8px; }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Журнал замен</h1>
            <p class="page-subtitle">Отслеживание замен преподавателей по группам</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index') }}" class="btn-ghost">← К расписанию</a>
        </div>
    </div>

    <form method="GET" class="filter-grid mb-3">
        <div>
            <label class="label">Группа</label>
            <select name="group_id" class="form-select">
                <option value="">Все</option>
                @foreach($groups as $id => $name)
                    <option value="{{ $id }}" @selected(($filters['group_id'] ?? null)==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Преподаватель</label>
            <select name="teacher_id" class="form-select">
                <option value="">Все</option>
                @foreach($teachers as $id => $name)
                    <option value="{{ $id }}" @selected(($filters['teacher_id'] ?? null)==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">День</label>
            <input type="text" name="study_day" value="{{ $filters['study_day'] ?? '' }}" class="form-control" placeholder="Понедельник">
        </div>
        <div>
            <label class="label">Режим недели</label>
            <select name="week_mode" class="form-select">
                <option value="">Все</option>
                <option value="single" @selected(($filters['week_mode'] ?? '')==='single')>Все недели</option>
                <option value="numerator" @selected(($filters['week_mode'] ?? '')==='numerator')>Числитель</option>
                <option value="denominator" @selected(($filters['week_mode'] ?? '')==='denominator')>Знаменатель</option>
            </select>
        </div>
        <div class="d-flex align-items-end">
            <button class="btn btn-primary">Фильтр</button>
        </div>
    </form>

    <div class="d-flex flex-column gap-3">
        @forelse($items as $item)
            <div class="repl-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="repl-badge">{{ $groups[$item->group_id] ?? 'Группа?' }} • {{ $item->study_day }} • Пара {{ $item->lesson_number }}</span>
                    <span class="text-muted small">{{ $item->week_mode === 'denominator' ? 'Знаменатель' : ($item->week_mode === 'numerator' ? 'Числитель' : 'Все недели') }}</span>
                </div>
                <div class="repl-row">
                    <div>
                        <div class="label">Предмет</div>
                        <div class="value">{{ $subjects[$item->subject_id] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Отсутствовал</div>
                        <div class="value">{{ $teachers[$item->absent_teacher_id] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Заменял</div>
                        <div class="value">{{ $teachers[$item->replacement_teacher_id] ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="label">Кабинет</div>
                        <div class="value">{{ $item->room_id ?? '—' }}</div>
                    </div>
                </div>
                @if($item->comment)
                    <div class="mt-2 text-muted small">Комментарий: {{ $item->comment }}</div>
                @endif
            </div>
        @empty
            <div class="text-muted">Замен не найдено.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
</div>
@endsection
