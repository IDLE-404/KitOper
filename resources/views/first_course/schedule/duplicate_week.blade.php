@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/schedule/main.css') }}">
<style>
    .dup-shell {
        max-width: 980px;
        margin: 0 auto;
    }
    .dup-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        padding: 20px;
    }
    .dup-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .dup-field label {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .dup-help {
        margin-top: 14px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f7f7f8;
        color: #6b7280;
        font-size: 13px;
    }
    .dup-actions {
        margin-top: 18px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="dup-shell">
    <div class="header-row mb-3">
        <div>
            <h1 class="page-title">Дубликат недели</h1>
            <p class="page-subtitle mb-0">Копирует расписание группы на весь выбранный период.</p>
        </div>
        <div class="action-buttons">
            <a class="btn-pill ghost" href="{{ route('first.schedule.week', ['course' => $course]) }}">Редактор недели</a>
            <a class="btn-pill ghost" href="{{ route('first.schedule.index', ['course' => $course]) }}">К расписанию</a>
        </div>
    </div>

    <div class="dup-card">
        <form method="POST" action="{{ route('first.schedule.week.duplicate.store') }}">
            @csrf
            <div class="dup-grid">
                <div class="dup-field">
                    <label for="courseSelect">Курс</label>
                    <select class="search-input" id="courseSelect" name="course">
                        @for($c = 1; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected(($course ?? 1) === $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="dup-field">
                    <label for="groupSelect">Группа</label>
                    <select class="search-input" id="groupSelect" name="group_id" required>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected((int) old('group_id', $selectedGroupId ?? 0) === (int) $group->id)>
                                {{ $group->group_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="dup-field">
                    <label for="templateWeekStart">Неделя-шаблон</label>
                    <input class="search-input" id="templateWeekStart" type="date" name="template_week_start" value="{{ old('template_week_start', $templateWeekStart ?? '') }}" required>
                </div>
                <div class="dup-field">
                    <label for="periodStart">Начало периода</label>
                    <input class="search-input" id="periodStart" type="date" name="period_start" value="{{ old('period_start', $periodStart ?? '') }}" required>
                </div>
                <div class="dup-field">
                    <label for="periodEnd">Окончание периода</label>
                    <input class="search-input" id="periodEnd" type="date" name="period_end" value="{{ old('period_end', $periodEnd ?? '') }}" required>
                </div>
            </div>

            <div class="dup-help">
                Вы выбираете неделю-шаблон отдельно, затем указываете период, куда это расписание нужно растянуть.
            </div>

            <div class="mt-3 d-flex flex-wrap gap-3">
                <label class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="skip_existing" value="1" @checked(old('skip_existing', $skipExisting ?? false))>
                    <span class="form-check-label">Пропускать недели, где уже есть расписание</span>
                </label>
                <label class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="sync_form_two" value="1" @checked(old('sync_form_two', $syncFormTwo ?? true))>
                    <span class="form-check-label">Синхронизировать Форму 2</span>
                </label>
            </div>

            <div class="dup-actions">
                <button class="btn-pill primary" type="submit">Сделать дубликат</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const courseSelect = document.getElementById('courseSelect');
    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        window.location.search = params.toString();
    });
</script>
@endpush
