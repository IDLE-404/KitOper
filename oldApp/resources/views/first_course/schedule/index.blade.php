@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/schedule/main.css') }}">
<style>
    .holiday-banner {
        margin: 1rem 0;
        padding: 0.75rem 1rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.9rem;
    }
    .holiday-banner__title {
        font-weight: 600;
        color: #92400e;
        margin-right: 0.5rem;
    }
    .holiday-banner__list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .holiday-pill {
        padding: 0.15rem 0.6rem;
        border-radius: 999px;
        background: #fff7d6;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .holiday-row {
        background-color: #fefce8;
    }
    .grid-cell.day-col.holiday-day {
        background-color: #fffbeb;
    }
    .holiday-note {
        font-size: 0.75rem;
        color: #92400e;
    }
    .holiday-cell {
        background-color: #fff9c2;
    }
    .pair-cell.highlighted {
        border: 2px solid #7f56d9;
        box-shadow: 0 12px 22px rgba(127, 86, 217, 0.2);
        background: rgba(127, 86, 217, 0.06);
    }
    .pair-cell.filled {
        cursor: pointer;
    }
    .holiday-lock {
        font-size: 0.75rem;
        color: #7c2d12;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
        background: #fef3c7;
        margin-bottom: 0.35rem;
    }
    .schedule-shell.day-grid-mode {
        max-width: 100%;
        width: 100%;
    }
    .schedule-shell {
        max-width: none !important;
        width: 100% !important;
    }
    .schedule-shell.compact {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .group-compact {
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: 100%;
    }
    .group-compact__head {
        padding-left: 12px;
        padding-right: 12px;
    }
    .day-grid-wrap,
    .grid-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 1100px) {
        .grid-table {
            min-width: 100%;
        }
    }
    @media (max-width: 768px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .header-actions__secondary {
            margin-left: 0;
            flex-wrap: wrap;
            gap: 6px;
        }
        .btn-pill {
            font-size: 12px;
            padding: 6px 12px;
        }
        .header-controls {
            gap: 6px;
        }
        .schedule-wrap, .grid-outer {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .page-title {
            font-size: 1.2rem !important;
        }
    }
    @media (max-width: 480px) {
        .header-top {
            flex-direction: column;
            gap: 8px;
        }
        .header-actions__secondary .btn-pill:not(#scheduleHealthBtn):not(#replacementsJournalBtn) {
            display: none;
        }
        .header-actions__secondary::after {
            content: 'Все инструменты — в меню «Дополнительно»';
            font-size: 11px;
            color: #94a3b8;
            width: 100%;
        }
    }
    .day-grid-table {
        min-width: 0 !important;
        width: 100% !important;
    }
    .grid-table {
        width: 100% !important;
        min-width: 100% !important;
        grid-template-columns: 100px repeat(7, minmax(0, 1fr)) !important;
    }
    .schedule-main {
        flex: 1 1 auto;
        min-width: 0;
    }
    .header-block {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 16px;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }
    .header-context {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .header-context .page-title {
        margin-bottom: 2px;
    }
    .header-subline {
        font-size: 0.85rem;
        color: #64748b;
    }
    .header-controls {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .control-group {
        display: inline-flex;
        align-items: baseline;
        gap: 8px;
    }
    .control-label {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .header-search {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .header-search .search-input {
        height: 36px;
        min-width: 220px;
    }
    .header-search .btn-pill {
        height: 36px;
    }
    .header-search .btn-primary {
        background: #7f56d9;
        color: #fff;
        border: none;
    }
    .nav-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 8px 10px;
        border-radius: 12px;
        background: #f7f7f8;
        border: 1px solid #e5e7eb;
    }
    .nav-left,
    .nav-right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .segmented {
        display: inline-flex;
        background: #e2e8f0;
        border-radius: 999px;
        padding: 2px;
        gap: 2px;
    }
    .segmented .btn-pill {
        padding: 6px 12px;
    }
    .segmented .btn-pill.primary {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .header-actions__primary,
    .header-actions__secondary {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .header-actions__secondary {
        margin-left: auto;
    }
    .tools-dropdown {
        position: relative;
    }
    .tools-dropdown > summary {
        list-style: none;
        cursor: pointer;
    }
    .tools-dropdown > summary::-webkit-details-marker {
        display: none;
    }
    .tools-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
        padding: 6px;
        min-width: 220px;
        display: grid;
        gap: 4px;
        z-index: 20;
    }
    .tools-menu a {
        text-decoration: none;
        color: #0f172a;
        padding: 8px 10px;
        border-radius: 8px;
    }
    .tools-menu a:hover {
        background: #f1f5f9;
    }
    .pair-practice {
        background: #eef2ff;
        border: 1px dashed #94a3b8;
    }
    .practice-label {
        font-weight: 600;
        text-align: center;
        margin-top: 0.3rem;
    }
    .practice-meta {
        font-size: 0.75rem;
        text-align: center;
        margin-top: 0.15rem;
    }
    .pair-cell.pair-sick,
    .pair-cell.pair-absence {
        background: #ffe4e6;
        border: 1px solid #fca5a5;
        box-shadow: 0 10px 20px rgba(248, 113, 113, 0.18);
    }
    .pair-cell.pair-replacement {
        background: #fff7cc;
        border: 1px solid #facc15;
    }
    .absence-note {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        color: #b91c1c;
        margin-left: 8px;
        padding: 2px 6px;
        background: #fee2e2;
        border-radius: 999px;
        white-space: nowrap;
    }
    .day-grid-wrap {
        width: 100%;
        overflow-x: hidden;
        border: 1px solid #d5dbe6;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    }
    .day-grid-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .day-grid-table th,
    .day-grid-table td {
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        vertical-align: top;
    }
    .day-grid-table td.day-grid-cell {
        height: 170px;
    }
    .day-grid-head {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        text-align: center;
        font-weight: 700;
        font-size: 15px;
        letter-spacing: 0.2px;
        color: #0f172a;
        position: sticky;
        top: 0;
        z-index: 6;
    }
    .day-grid-corner {
        width: 54px;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 7;
    }
    .day-grid-num {
        text-align: center;
        font-weight: 700;
        background: #f8fafc;
        color: #0f172a;
        width: 54px;
        position: sticky;
        left: 0;
        z-index: 5;
    }
    .day-grid-table th.day-grid-head:not(.day-grid-corner),
    .day-grid-table td.day-grid-cell {
        width: calc((100% - 54px) / var(--day-grid-cols));
    }
    .day-grid-row:nth-child(even) .day-grid-cell {
        background: #fcfdff;
    }
    .day-grid-cell {
        padding: 0;
        background: #fff;
        overflow: hidden;
    }
    .day-grid-cell .pair-cell {
        border: 0;
        border-radius: 0;
        box-shadow: none;
        min-height: 0;
        height: 100%;
        overflow: hidden;
        padding: 8px 10px;
        gap: 6px;
        justify-content: flex-start;
        transition: box-shadow 0.12s ease, transform 0.12s ease, background 0.12s ease;
    }
    .day-grid-cell .pair-cell.filled {
        margin: 6px;
        height: calc(100% - 12px);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        cursor: pointer;
    }
    .day-grid-cell:hover {
        background: #f8fafc;
    }
    .day-grid-cell:hover .pair-cell.filled {
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        transform: translateY(-1px);
    }
    .day-grid-cell .pair-cell.empty {
        background: #f9fafb;
    }
    .grid-table .grid-cell.pair-cell {
        min-height: 170px;
        height: 170px;
        overflow: hidden;
        padding: 8px 10px;
        gap: 6px;
        justify-content: flex-start;
    }
    .grid-table .pair-cell .cell-line {
        font-size: 13px;
        gap: 6px;
    }
    .grid-table .pair-cell .cell-title {
        font-size: 14px;
    }
    .grid-table .pair-cell .cell-meta {
        font-size: 12px;
        gap: 6px;
    }
    .grid-table .pair-cell .den-separator {
        margin-top: 4px;
        padding-top: 3px;
    }
    .table-empty {
        padding: 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        background: #f8fafc;
        text-align: center;
    }
    .table-alert {
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: 0.85rem;
        margin-bottom: 12px;
    }
    .table-skeleton {
        display: grid;
        gap: 8px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }
    .table-skeleton__row {
        height: 56px;
        border-radius: 10px;
        background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
        background-size: 200% 100%;
        animation: skeleton 1.2s ease-in-out infinite;
    }
    @keyframes skeleton {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
@php
    $weekDays = $weekDays ?? [];
    if (empty($weekDays)) {
        $weekDayNames = ['Понедельник','Вторник','Среда','Четверг','Пятница'];
        foreach ($weekDayNames as $name) {
            $weekDays[] = [
                'name' => $name,
                'date' => null,
                'label' => null,
                'holiday' => null,
            ];
        }
    }
    $holidayWeekDates = $holidayWeekDates ?? [];
    $weeklyHolidays = $weeklyHolidays ?? [];
    $practiceMap = $practiceMap ?? [];
    $dayDetails = [];
    foreach ($weekDays as $dayInfo) {
        $dayDetails[$dayInfo['name']] = $dayInfo;
    }
    $days = array_keys($dayDetails);
    $dayFilter = $dayFilter ?? null;
    $isDayView = $isDayView ?? false;
    $dayKey = $dayKey ?? null;
    $dayKeyOrder = ['mon', 'tue', 'wed', 'thu', 'fri'];
    $dayOptions = $weekDays;
    if ($isDayView) {
        $dayOptions = array_values(array_filter($weekDays, function ($dayInfo) use ($dayKeyOrder) {
            $key = $dayInfo['key'] ?? null;
            return $key && in_array($key, $dayKeyOrder, true);
        }));
        if (empty($dayOptions)) {
            $dayOptions = $weekDays;
        }
        $allowedKeys = array_column($dayOptions, 'key');
        if ($dayKey && !in_array($dayKey, $allowedKeys, true)) {
            $dayKey = $dayOptions[0]['key'] ?? $dayKey;
        }
    }
    $daysToShow = $dayFilter ? [$dayFilter] : $days;
    $itemsByGroup = $schedule ?? [];
    $firstGroupId = count($itemsByGroup) ? array_key_first($itemsByGroup) : null;
    $expandLinkParams = ['course' => $course ?? 1];
    $requestedWeekStart = $requestedWeekStart ?? ($weekStart ?? null);
    $isFallbackWeek = $isFallbackWeek ?? false;
    $fallbackWeekStart = $fallbackWeekStart ?? null;
    $fallbackMode = $fallbackMode ?? null;
    $isLoading = request()->boolean('loading');
    if ($firstGroupId) {
        $expandLinkParams['group_id'] = $firstGroupId;
    }
@endphp

<div class="schedule-layout">
    <div class="schedule-main">
<div class="schedule-shell compact{{ $isDayView ? ' day-grid-mode' : '' }}">
    @php
        $dayDisplay = $dayFilter ? ($dayDetails[$dayFilter]['name'] ?? $dayFilter) : null;
        $weekModeLabel = ($weekMode ?? 'num') === 'den' ? 'неделя B (знаменатель)' : 'неделя A (числитель)';
        $weekStartLabel = $weekStart ?? '—';
    @endphp
    <div class="header-block">
        <div class="header-top">
            <div class="header-context">
                <h1 class="page-title">Расписание — {{ $course ?? 1 }} курс</h1>
                @if($isDayView)
                    <div class="header-subline">Только {{ $dayDisplay ?? 'день недели' }}</div>
                @else
                    <div class="header-subline">Обзор по всем группам</div>
                @endif
                <div class="header-subline">
                    Сейчас показывается: {{ $weekModeLabel }} • старт {{ $weekStartLabel }}
                </div>
                @if($isFallbackWeek)
                    <div class="header-subline">
                        Для выбранной недели знаменателя нет расписания — использован шаблон за {{ $fallbackWeekStart ?? ($weekStart ?? '—') }}.
                    </div>
                @endif
            </div>
            <div class="header-search">
                <input type="search" id="groupSearch" class="search-input" placeholder="Поиск по группе или предмету">
                <input type="date" id="weekStartInput" class="search-input" value="{{ $requestedWeekStart ?? '' }}" style="width:auto;">
                <button type="button" class="btn-pill primary btn-primary" id="weekStartApply">Показать неделю</button>
            </div>
        </div>
        <div class="header-controls">
            <div class="control-group">
                <label class="control-label">Курс</label>
                <select id="courseSelect" class="search-input" style="width:auto;">
                    @for($c = 1; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
            @if($isDayView)
                <div class="control-group">
                    <label class="control-label">День</label>
                    <select id="daySelect" class="search-input" style="width:auto;">
                        @foreach($dayOptions as $dayInfo)
                            <option value="{{ $dayInfo['key'] ?? $dayInfo['name'] }}" @selected(($dayKey ?? '') === ($dayInfo['key'] ?? ''))>
                                {{ $dayInfo['name'] ?? 'День' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="nav-bar">
            <div class="nav-left">
                <span class="control-label">Режим</span>
                <div class="segmented">
                    @if($isDayView)
                        <a href="{{ route('first.schedule.index', ['course' => $course ?? 1, 'week_start' => $requestedWeekStart ?? null]) }}" class="btn-pill ghost">Неделя</a>
                        <span class="btn-pill primary">День</span>
                    @else
                        <span class="btn-pill primary">Неделя</span>
                        @php $defaultDayKey = $dayOptions[0]['key'] ?? ($weekDays[0]['key'] ?? 'mon'); @endphp
                        <a href="{{ route('first.schedule.day', ['course' => $course ?? 1, 'week_start' => $requestedWeekStart ?? null, 'day' => $dayKey ?? $defaultDayKey]) }}" class="btn-pill ghost">День</a>
                    @endif
                </div>
            </div>
            <div class="nav-right">
                @if($isDayView)
                    <button type="button" class="btn-pill ghost" id="dayPrev">Предыдущий</button>
                    <button type="button" class="btn-pill ghost" id="dayToday">Сегодня</button>
                    <button type="button" class="btn-pill ghost" id="dayNext">Следующий</button>
                @else
                    <button type="button" class="btn-pill ghost" id="weekPrev">Предыдущая неделя</button>
                    <button type="button" class="btn-pill ghost" id="weekNext">Следующая неделя</button>
                @endif
            </div>
        </div>
        <div class="header-actions">
            <div class="header-actions__primary">
                @if($isDayView)
                    <button type="button" class="btn-pill primary" id="autoAssignRoomsDayBtn">Подставить кабинеты на день</button>
                    <button type="button" class="btn-pill ghost" id="clearRoomsDayBtn">Очистить кабинеты на день</button>
                @endif
            </div>
            <div class="header-actions__secondary">
                <button type="button" class="btn-pill ghost" id="scheduleHealthBtn"
                    data-health-url="{{ route('first.schedule.health') }}"
                    data-holiday-url="{{ route('first.schedule.holiday_compensation') }}"
                    style="position:relative">
                    Анализ недели
                    <span id="healthBadge" style="display:none;position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 3px"></span>
                </button>
                <button type="button" class="btn-pill ghost" id="replacementsJournalBtn"
                    data-url="{{ route('first.schedule.replacements_summary') }}"
                    style="position:relative">
                    <i class="bi bi-arrow-left-right"></i> Замены
                    <span id="replacementsBadge" style="display:none;position:absolute;top:-5px;right:-5px;background:#7f56d9;color:#fff;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 3px"></span>
                </button>
                @if(!empty($weeklyHolidays))
                    <button type="button" class="btn-pill ghost" id="holidayCompBtn"
                        data-holiday-url="{{ route('first.schedule.holiday_compensation') }}"
                        style="border-color:#f59e0b;color:#92400e">
                        Перенос праздничных пар
                    </button>
                @endif
                <a href="{{ route('first.schedule.week', ['course' => $course ?? 1]) }}" class="btn-pill ghost">Редактор недели</a>
                <a href="{{ route('first.schedule.week', $expandLinkParams) }}#semesterExpandSection" class="btn-pill ghost">Развернуть семестр</a>
                @php
                    $practiceCourse = max(2, (int) ($course ?? 2));
                @endphp
                <details class="tools-dropdown">
                    <summary class="btn-pill ghost">Дополнительно ▾</summary>
                    <div class="tools-menu">
                        <a href="{{ route('practice.index', ['course' => $practiceCourse]) }}">Практика</a>
                        <a href="{{ route('holidays.index') }}">Праздники</a>
                        <a href="{{ route('teachers.workload', ['week_start' => $weekStart]) }}">Занятость преподавателей</a>
                        <a href="{{ route('subjects.index', ['course' => $course ?? 1]) }}">Предметы</a>
                        <a href="{{ route('teacher_absences.index') }}">Отсутствия</a>
                    </div>
                </details>
            </div>
        </div>
    </div>
    @if(!empty($weeklyHolidays))
        <div class="holiday-banner">
            <div class="holiday-banner__title">Праздники недели:</div>
                    <div class="holiday-banner__list">
                        @foreach($weeklyHolidays as $holiday)
                            <span class="holiday-pill" title="Праздник — {{ data_get($holiday, 'name', '') }}">
                                {{ data_get($holiday, 'label', '') }} ({{ data_get($holiday, 'day', 'день') }}) — {{ data_get($holiday, 'name', 'праздник') }}
                            </span>
                        @endforeach
                    </div>
        </div>
    @endif

    @if(session('error'))
        <div class="table-alert">{{ session('error') }}</div>
    @endif
    @if($isLoading)
        <div class="table-skeleton" aria-hidden="true">
            <div class="table-skeleton__row"></div>
            <div class="table-skeleton__row"></div>
            <div class="table-skeleton__row"></div>
            <div class="table-skeleton__row"></div>
        </div>
    @elseif(empty($itemsByGroup))
        <div class="table-empty">Нет пар в этот день/неделю.</div>
    @elseif($isDayView)
        @php
            $dayToShow = $daysToShow[0] ?? null;
            $dayInfo = $dayToShow ? ($dayDetails[$dayToShow] ?? []) : [];
            $holidayMeta = $dayInfo['holiday'] ?? null;
        @endphp
        @php
            $gridCols = max(1, count($itemsByGroup));
            $gridMinWidth = 54 + ($gridCols * 220);
        @endphp
        <div class="day-grid-wrap">
            <table class="day-grid-table" style="--day-grid-cols: {{ $gridCols }}; min-width: {{ $gridMinWidth }}px;">
                <thead>
                    <tr>
                        <th class="day-grid-head day-grid-corner">№</th>
                        @foreach($itemsByGroup as $groupId => $groupData)
                            <th class="day-grid-head">
                                {{ $groupData['name'] ?? 'Без названия' }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= 7; $i++)
                        <tr class="day-grid-row{{ $holidayMeta ? ' holiday-row' : '' }}">
                            <td class="day-grid-num">{{ $i }}</td>
                            @foreach($itemsByGroup as $groupId => $groupData)
                                @php
                                    $groupItems = $groupData['days'] ?? [];
                                    $pair = ($dayToShow && isset($groupItems[$dayToShow][$i]))
                                        ? $groupItems[$dayToShow][$i]
                                        : ['sub1'=>[], 'sub2'=>[], 'has_denominator' => false];
                                    $practiceInfo = $practiceMap[$groupId][$dayInfo['date'] ?? ''] ?? null;
                                    $hasPractice = !empty($practiceInfo);
                                    $main = $pair['sub1'] ?? [];
                                    $sub2 = $pair['sub2'] ?? [];
                                    $mainHasActive = !empty($main['active_subject'])
                                        || !empty($main['active_teacher'])
                                        || !empty($main['active_room'])
                                        || ($main['is_absent'] ?? false)
                                        || ($main['is_replacement'] ?? false);
                                    $sub2HasActive = !empty($sub2['active_subject'])
                                        || !empty($sub2['active_teacher'])
                                        || !empty($sub2['active_room'])
                                        || ($sub2['is_absent'] ?? false)
                                        || ($sub2['is_replacement'] ?? false);
                                    $hasLesson = $hasPractice ? true : ($mainHasActive || $sub2HasActive);
                                    $cellFilled = $hasPractice || ($hasLesson && !$holidayMeta);
                                    $renderLesson = $hasLesson && !$hasPractice && !$holidayMeta;
                                    $hasConflict = ($pair['sub1']['active_conflict'] ?? false) || ($pair['sub2']['active_conflict'] ?? false);
                                    $hasSubgroupsAny = ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                    $hasSubgroupsCurrentWeek = $sub2HasActive;
                                    $pairStatus = '';
                                    if ($hasPractice) {
                                        $pairStatus = 'pair-practice';
                                    } elseif (($pair['sub1']['is_replacement'] ?? false) || ($pair['sub2']['is_replacement'] ?? false)) {
                                        $pairStatus = 'pair-replacement';
                                    } elseif (($pair['sub1']['is_absent'] ?? false) || ($pair['sub2']['is_absent'] ?? false)) {
                                        $pairStatus = 'pair-sick';
                                    }
                                    if (!empty($main['absence_type']) || !empty($sub2['absence_type'])) {
                                        $pairStatus = 'pair-absence';
                                    }
                                @endphp
                                <td class="day-grid-cell">
                                    <div class="pair-cell {{ $cellFilled ? 'filled' : 'empty' }} {{ $hasConflict ? 'conflict' : '' }} {{ $pairStatus }}{{ $holidayMeta ? ' holiday-cell' : '' }}"
                                         data-group="{{ $groupId }}"
                                         data-day="{{ $dayToShow ?? '' }}"
                                         data-day-key="{{ $dayToShow ? ($dayDetails[$dayToShow]['key'] ?? '') : '' }}"
                                         data-lesson="{{ $i }}"
                                         title="Открыть детали пары">
                                        @if($hasPractice)
                                            @php
                                                $practiceType = $practiceInfo['type'] ?? '';
                                                $practiceTitle = $practiceType === 'field_camp' ? 'Полевые сборы' : 'На практике';
                                                $showRoom = !empty($practiceInfo['room_id']) && in_array($practiceType, ['educational', 'field_camp'], true);
                                            @endphp
                                            <div class="practice-label" title="{{ $practiceTitle }}">
                                                {{ $practiceTitle }}
                                            </div>
                                            @if($showRoom)
                                                <div class="practice-meta text-muted">
                                                    Каб. {{ $practiceInfo['room_id'] }}
                                                </div>
                                            @endif
                                        @elseif(!$holidayMeta)
                                            <a href="#"
                                               class="cell-edit"
                                               title="Редактировать"
                                               data-group="{{ $groupId }}"
                                               data-day="{{ $dayToShow ?? '' }}"
                                               data-lesson="{{ $i }}"
                                                data-subject1="{{ $pair['sub1']['subject_num_id'] ?? '' }}"
                                                data-teacher1="{{ $pair['sub1']['teacher_num_id'] ?? '' }}"
                                                data-room1="{{ $pair['sub1']['room_num'] ?? '' }}"
                                                data-den-subject1="{{ $pair['sub1']['subject_den_id'] ?? '' }}"
                                                data-den-teacher1="{{ $pair['sub1']['teacher_den_id'] ?? '' }}"
                                                data-den-room1="{{ $pair['sub1']['room_den'] ?? '' }}"
                                                data-sub1="1"
                                                data-has-sub2="{{ $hasSubgroupsAny ? '1' : '0' }}"
                                                data-subject2="{{ $pair['sub2']['subject_num_id'] ?? '' }}"
                                                data-teacher2="{{ $pair['sub2']['teacher_num_id'] ?? '' }}"
                                                data-room2="{{ $pair['sub2']['room_num'] ?? '' }}"
                                                data-den-subject2="{{ $pair['sub2']['subject_den_id'] ?? '' }}"
                                                data-den-teacher2="{{ $pair['sub2']['teacher_den_id'] ?? '' }}"
                                                data-den-room2="{{ $pair['sub2']['room_den'] ?? '' }}"
                                                data-sub2="2"
                                                data-subject1-title="{{ $pair['sub1']['subject_num'] ?? '' }}"
                                                data-subject2-title="{{ $pair['sub2']['subject_num'] ?? '' }}"
                                                data-has-denominator="{{ $pair['has_denominator'] ? '1' : '0' }}"
                                                data-week-start="{{ $weekStart ?? '' }}"
                                                data-absent1="{{ ($pair['sub1']['is_absent'] ?? false) ? '1' : '0' }}"
                                                data-absent2="{{ ($pair['sub2']['is_absent'] ?? false) ? '1' : '0' }}"
                                                data-absence-type1="{{ $pair['sub1']['absence_type'] ?? '' }}"
                                                data-absence-type2="{{ $pair['sub2']['absence_type'] ?? '' }}"
                                                data-replacement1="{{ ($pair['sub1']['is_replacement'] ?? false) ? '1' : '0' }}"
                                                data-replacement2="0"
                                                data-replacement-teacher-1="{{ $pair['sub1']['replacement_teacher_id'] ?? '' }}"
                                                data-replacement-subject-1="{{ $pair['sub1']['replacement_subject_id'] ?? '' }}"
                                                data-replacement-comment-1="{{ $pair['sub1']['replacement_comment'] ?? '' }}"
                                                data-replacement2="{{ ($pair['sub2']['is_replacement'] ?? false) ? '1' : '0' }}"
                                                data-replacement-teacher-2="{{ $pair['sub2']['replacement_teacher_id'] ?? '' }}"
                                                data-replacement-subject-2="{{ $pair['sub2']['replacement_subject_id'] ?? '' }}"
                                                data-replacement-comment-2="{{ $pair['sub2']['replacement_comment'] ?? '' }}"
                                                data-replacement-den-1="{{ ($pair['sub1']['replacement_flag_den'] ?? false) ? '1' : '0' }}"
                                                data-replacement-teacher-den-1="{{ $pair['sub1']['replacement_teacher_den'] ?? '' }}"
                                                data-replacement-subject-den-1="{{ $pair['sub1']['replacement_subject_den'] ?? '' }}"
                                                data-replacement-comment-den-1="{{ $pair['sub1']['replacement_comment_den'] ?? '' }}"
                                                data-replacement-den-2="{{ ($pair['sub2']['replacement_flag_den'] ?? false) ? '1' : '0' }}"
                                                data-replacement-teacher-den-2="{{ $pair['sub2']['replacement_teacher_den'] ?? '' }}"
                                                data-replacement-subject-den-2="{{ $pair['sub2']['replacement_subject_den'] ?? '' }}"
                                                data-replacement-comment-den-2="{{ $pair['sub2']['replacement_comment_den'] ?? '' }}"
                                                data-teacher-conflict1="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                                data-teacher-conflict1-groups="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub1']['teacher_conflict_groups'] ?? []) : '' }}"
                                                data-teacher-conflict2="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                                data-teacher-conflict2-groups="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub2']['teacher_conflict_groups'] ?? []) : '' }}"
                                            >✏️</a>
                                        @else
                                            <div class="holiday-lock" title="Праздник — {{ $holidayMeta['name'] }} ({{ $holidayMeta['label'] }})">
                                                🎉 {{ $holidayMeta['label'] }} ({{ $holidayMeta['day'] ?? '' }})
                                            </div>
                                        @endif
                                        @if ($renderLesson)
                                            @if($mainHasActive)
                                                <div class="cell-line main-line sub-line">
                                                    <span class="pill badge-sub">1</span>
                                                    @if($main['is_absent'] ?? false)
                                                        <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                                    @elseif($main['is_replacement'] ?? false)
                                                        <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                                    @endif
                                                    <span class="cell-title emphasis">{{ $main['active_subject'] ?? '' }}</span>
                                                    @if(($main['replacement_subject'] ?? null) && ($main['is_replacement'] ?? false) && ($main['replacement_subject'] !== ($main['active_subject'] ?? null)))
                                                        <span class="text-danger ms-1">→ {{ $main['replacement_subject'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="cell-meta">
                                                    @if (!empty($main['active_teacher']))
                                                        <span class="pill">
                                                            <span>👤</span>{{ $main['active_teacher'] }}
                                                    @if(
                                                        ($main['replacement_teacher'] ?? null)
                                                        && ($main['is_replacement'] ?? false)
                                                        && ($main['replacement_teacher'] !== ($main['active_teacher'] ?? null))
                                                    )
                                                        <span class="text-warning ms-1">→ {{ $main['replacement_teacher'] }}</span>
                                                    @endif
                                                        </span>
                                                        @if(!empty($main['absence_type']))
                                                            <span class="absence-note">
                                                                {{ $absenceLabels[$main['absence_type']] ?? $main['absence_type'] }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                    @if (!empty($main['active_room']))
                                                        <span class="pill room-pill {{ ($main['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($main['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                            <span>🏫</span>{{ $main['active_room'] }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($main['label']))
                                                        <span class="pill"><span>🔸</span>{{ $main['label'] }}</span>
                                                    @endif
                                                </div>
                                                @if($main['active_conflict'] ?? false)
                                                    <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                                @endif
                                            @endif
                                            @if($hasSubgroupsCurrentWeek)
                                                <div class="cell-line subpair-line">
                                                    <span class="pill badge-sub soft">2</span>
                                                    @if($sub2['is_absent'] ?? false)
                                                        <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                                    @elseif($sub2['is_replacement'] ?? false)
                                                        <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                                    @endif
                                                    <span class="cell-title sub2 emphasis">{{ $sub2['active_subject'] ?? '' }}</span>
                                                    @if(($sub2['replacement_subject'] ?? null) && ($sub2['is_replacement'] ?? false) && ($sub2['replacement_subject'] !== ($sub2['active_subject'] ?? null)))
                                                        <span class="text-danger ms-1">→ {{ $sub2['replacement_subject'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="cell-meta subpair">
                                                    @if (!empty($sub2['active_teacher']))
                                                        <span class="pill">
                                                            <span>👤</span>{{ $sub2['active_teacher'] }}
                                                    @if(
                                                        ($sub2['replacement_teacher'] ?? null)
                                                        && ($sub2['is_replacement'] ?? false)
                                                        && ($sub2['replacement_teacher'] !== ($sub2['active_teacher'] ?? null))
                                                    )
                                                        <span class="text-warning ms-1">→ {{ $sub2['replacement_teacher'] }}</span>
                                                    @endif
                                                        </span>
                                                        @if(!empty($sub2['absence_type']))
                                                            <span class="absence-note">
                                                                {{ $absenceLabels[$sub2['absence_type']] ?? $sub2['absence_type'] }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                    @if (!empty($sub2['active_room']))
                                                        <span class="pill room-pill {{ ($sub2['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($sub2['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                            <span>🏫</span>{{ $sub2['active_room'] }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($sub2['label']))
                                                        <span class="pill"><span>🔸</span>{{ $sub2['label'] }}</span>
                                                    @endif
                                                </div>
                                                @if($sub2['active_conflict'] ?? false)
                                                    <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                                @endif
                                            @endif
                                            @if($pair['has_denominator'])
                                                <div class="den-separator" title="Разделение числитель/знаменатель"></div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    @else
        <div class="groups-compact">
            @foreach($itemsByGroup as $groupId => $groupData)
                @php $groupItems = $groupData['days'] ?? []; @endphp
            <div class="group-compact" id="group-{{ $groupId }}">
                <div class="group-compact__head">
                    <h2 class="group-compact__title">Группа: {{ $groupData['name'] ?? 'Без названия' }}</h2>
                    <a href="{{ route('first.schedule.week') }}" class="link-edit">Редактировать</a>
                </div>
                <div class="grid-table-wrap">
                <div class="grid-table">
                    <div class="grid-row grid-head">
                        <div class="grid-cell day-col"></div>
                        @for($i = 1; $i <= 7; $i++)
                            <div class="grid-cell col-head">Пара {{ $i }}</div>
                        @endfor
                    </div>
                    @foreach($daysToShow as $day)
                        @php
                            $dayInfo = $dayDetails[$day] ?? [];
                            $holidayMeta = $dayInfo['holiday'] ?? null;
                        @endphp
                        <div class="grid-row{{ $holidayMeta ? ' holiday-row' : '' }}">
                            <div class="grid-cell day-col{{ $holidayMeta ? ' holiday-day' : '' }}">
                                {{ $day }}
                                @if($holidayMeta)
                                    <div class="holiday-note">{{ $holidayMeta['name'] }}</div>
                                @endif
                            </div>
                            @for($i = 1; $i <= 7; $i++)
                                @php
                                    $pair = $groupItems[$day][$i] ?? ['sub1'=>[], 'sub2'=>[], 'has_denominator' => false];
                                    $practiceInfo = $practiceMap[$groupId][$dayInfo['date'] ?? ''] ?? null;
                                    $hasPractice = !empty($practiceInfo);
                                    $main = $pair['sub1'] ?? [];
                                    $sub2 = $pair['sub2'] ?? [];
                                    $mainHasActive = !empty($main['active_subject'])
                                        || !empty($main['active_teacher'])
                                        || !empty($main['active_room'])
                                        || ($main['is_absent'] ?? false)
                                        || ($main['is_replacement'] ?? false);
                                    $sub2HasActive = !empty($sub2['active_subject'])
                                        || !empty($sub2['active_teacher'])
                                        || !empty($sub2['active_room'])
                                        || ($sub2['is_absent'] ?? false)
                                        || ($sub2['is_replacement'] ?? false);
                                    $hasLesson = $hasPractice ? true : ($mainHasActive || $sub2HasActive);
                                    $cellFilled = $hasPractice || ($hasLesson && !$holidayMeta);
                                    $renderLesson = $hasLesson && !$hasPractice && !$holidayMeta;
                                    $hasConflict = ($pair['sub1']['active_conflict'] ?? false) || ($pair['sub2']['active_conflict'] ?? false);
                                    $hasSubgroupsAny = ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                    $hasSubgroupsCurrentWeek = $sub2HasActive;
                                    $pairStatus = '';
                                    if ($hasPractice) {
                                        $pairStatus = 'pair-practice';
                                    } elseif (($pair['sub1']['is_replacement'] ?? false) || ($pair['sub2']['is_replacement'] ?? false)) {
                                        $pairStatus = 'pair-replacement';
                                    } elseif (($pair['sub1']['is_absent'] ?? false) || ($pair['sub2']['is_absent'] ?? false)) {
                                        $pairStatus = 'pair-sick';
                                    }
                                    if (!empty($main['absence_type']) || !empty($sub2['absence_type'])) {
                                        $pairStatus = 'pair-absence';
                                    }
                                @endphp
                                <div class="grid-cell pair-cell {{ $cellFilled ? 'filled' : 'empty' }} {{ $hasConflict ? 'conflict' : '' }} {{ $pairStatus }}{{ $holidayMeta ? ' holiday-cell' : '' }}"
                                     data-group="{{ $groupId }}"
                                     data-day="{{ $day }}"
                                     data-day-key="{{ $dayDetails[$day]['key'] ?? '' }}"
                                     data-lesson="{{ $i }}"
                                     title="Открыть детали пары">
                                    @if($hasPractice)
                                        @php
                                            $practiceType = $practiceInfo['type'] ?? '';
                                            $practiceTitle = $practiceType === 'field_camp' ? 'Полевые сборы' : 'На практике';
                                            $showRoom = !empty($practiceInfo['room_id']) && in_array($practiceType, ['educational', 'field_camp'], true);
                                        @endphp
                                        <div class="practice-label" title="{{ $practiceTitle }}">
                                            {{ $practiceTitle }}
                                        </div>
                                        @if($showRoom)
                                            <div class="practice-meta text-muted">
                                                Каб. {{ $practiceInfo['room_id'] }}
                                            </div>
                                        @endif
                                    @elseif(!$holidayMeta)
                                        <a href="#"
                                           class="cell-edit"
                                           title="Редактировать"
                                           data-group="{{ $groupId }}"
                                           data-day="{{ $day }}"
                                           data-lesson="{{ $i }}"
                                            data-subject1="{{ $pair['sub1']['subject_num_id'] ?? '' }}"
                                            data-teacher1="{{ $pair['sub1']['teacher_num_id'] ?? '' }}"
                                            data-room1="{{ $pair['sub1']['room_num'] ?? '' }}"
                                            data-den-subject1="{{ $pair['sub1']['subject_den_id'] ?? '' }}"
                                            data-den-teacher1="{{ $pair['sub1']['teacher_den_id'] ?? '' }}"
                                            data-den-room1="{{ $pair['sub1']['room_den'] ?? '' }}"
                                            data-sub1="1"
                                            data-has-sub2="{{ $hasSubgroupsAny ? '1' : '0' }}"
                                            data-subject2="{{ $pair['sub2']['subject_num_id'] ?? '' }}"
                                            data-teacher2="{{ $pair['sub2']['teacher_num_id'] ?? '' }}"
                                            data-room2="{{ $pair['sub2']['room_num'] ?? '' }}"
                                            data-den-subject2="{{ $pair['sub2']['subject_den_id'] ?? '' }}"
                                            data-den-teacher2="{{ $pair['sub2']['teacher_den_id'] ?? '' }}"
                                            data-den-room2="{{ $pair['sub2']['room_den'] ?? '' }}"
                                            data-sub2="2"
                                            data-subject1-title="{{ $pair['sub1']['subject_num'] ?? '' }}"
                                            data-subject2-title="{{ $pair['sub2']['subject_num'] ?? '' }}"
                                            data-has-denominator="{{ $pair['has_denominator'] ? '1' : '0' }}"
                                            data-week-start="{{ $weekStart ?? '' }}"
                                            data-absent1="{{ ($pair['sub1']['is_absent'] ?? false) ? '1' : '0' }}"
                                            data-absent2="{{ ($pair['sub2']['is_absent'] ?? false) ? '1' : '0' }}"
                                            data-absence-type1="{{ $pair['sub1']['absence_type'] ?? '' }}"
                                            data-absence-type2="{{ $pair['sub2']['absence_type'] ?? '' }}"
                                            data-replacement1="{{ ($pair['sub1']['is_replacement'] ?? false) ? '1' : '0' }}"
                                            data-replacement2="0"
                                            data-replacement-teacher-1="{{ $pair['sub1']['replacement_teacher_id'] ?? '' }}"
                                            data-replacement-subject-1="{{ $pair['sub1']['replacement_subject_id'] ?? '' }}"
                                            data-replacement-comment-1="{{ $pair['sub1']['replacement_comment'] ?? '' }}"
                                            data-replacement2="{{ ($pair['sub2']['is_replacement'] ?? false) ? '1' : '0' }}"
                                            data-replacement-teacher-2="{{ $pair['sub2']['replacement_teacher_id'] ?? '' }}"
                                            data-replacement-subject-2="{{ $pair['sub2']['replacement_subject_id'] ?? '' }}"
                                            data-replacement-comment-2="{{ $pair['sub2']['replacement_comment'] ?? '' }}"
                                            data-replacement-den-1="{{ ($pair['sub1']['replacement_flag_den'] ?? false) ? '1' : '0' }}"
                                            data-replacement-teacher-den-1="{{ $pair['sub1']['replacement_teacher_den'] ?? '' }}"
                                            data-replacement-subject-den-1="{{ $pair['sub1']['replacement_subject_den'] ?? '' }}"
                                            data-replacement-comment-den-1="{{ $pair['sub1']['replacement_comment_den'] ?? '' }}"
                                            data-replacement-den-2="{{ ($pair['sub2']['replacement_flag_den'] ?? false) ? '1' : '0' }}"
                                            data-replacement-teacher-den-2="{{ $pair['sub2']['replacement_teacher_den'] ?? '' }}"
                                            data-replacement-subject-den-2="{{ $pair['sub2']['replacement_subject_den'] ?? '' }}"
                                            data-replacement-comment-den-2="{{ $pair['sub2']['replacement_comment_den'] ?? '' }}"
                                            data-teacher-conflict1="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                            data-teacher-conflict1-groups="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub1']['teacher_conflict_groups'] ?? []) : '' }}"
                                            data-teacher-conflict2="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                            data-teacher-conflict2-groups="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub2']['teacher_conflict_groups'] ?? []) : '' }}"
                                        >✏️</a>
                                    @else
                                        <div class="holiday-lock" title="Праздник — {{ $holidayMeta['name'] }} ({{ $holidayMeta['label'] }})">
                                            🎉 {{ $holidayMeta['label'] }} ({{ $holidayMeta['day'] ?? '' }})
                                        </div>
                                    @endif
                                    @if ($renderLesson)
                                        @if($mainHasActive)
                                            <div class="cell-line main-line sub-line">
                                                <span class="pill badge-sub">1</span>
                                                @if($main['is_absent'] ?? false)
                                                    <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                                @elseif($main['is_replacement'] ?? false)
                                                    <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                                @endif
                                                <span class="cell-title emphasis">{{ $main['active_subject'] ?? '' }}</span>
                                                @if(($main['replacement_subject'] ?? null) && ($main['is_replacement'] ?? false) && ($main['replacement_subject'] !== ($main['active_subject'] ?? null)))
                                                    <span class="text-danger ms-1">→ {{ $main['replacement_subject'] }}</span>
                                                @endif
                                            </div>
                                            <div class="cell-meta">
                                                @if (!empty($main['active_teacher']))
                                                    <span class="pill">
                                                        <span>👤</span>{{ $main['active_teacher'] }}
                                                @if(
                                                    ($main['replacement_teacher'] ?? null)
                                                    && ($main['is_replacement'] ?? false)
                                                    && ($main['replacement_teacher'] !== ($main['active_teacher'] ?? null))
                                                )
                                                    <span class="text-warning ms-1">→ {{ $main['replacement_teacher'] }}</span>
                                                @endif
                                                    </span>
                                                    @if(!empty($main['absence_type']))
                                                        <span class="absence-note">
                                                            {{ $absenceLabels[$main['absence_type']] ?? $main['absence_type'] }}
                                                        </span>
                                                    @endif
                                                @endif
                                                @if (!empty($main['active_room']))
                                                    <span class="pill room-pill {{ ($main['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($main['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                        <span>🏫</span>{{ $main['active_room'] }}
                                                    </span>
                                                @endif
                                                @if (!empty($main['label']))
                                                    <span class="pill"><span>🔸</span>{{ $main['label'] }}</span>
                                                @endif
                                            </div>
                                            @if($main['active_conflict'] ?? false)
                                                <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                            @endif
                                        @endif
                                        @if($hasSubgroupsCurrentWeek)
                                            <div class="cell-line subpair-line">
                                                <span class="pill badge-sub soft">2</span>
                                                @if($sub2['is_absent'] ?? false)
                                                    <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                                @elseif($sub2['is_replacement'] ?? false)
                                                    <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                                @endif
                                                <span class="cell-title sub2 emphasis">{{ $sub2['active_subject'] ?? '' }}</span>
                                                @if(($sub2['replacement_subject'] ?? null) && ($sub2['is_replacement'] ?? false) && ($sub2['replacement_subject'] !== ($sub2['active_subject'] ?? null)))
                                                    <span class="text-danger ms-1">→ {{ $sub2['replacement_subject'] }}</span>
                                                @endif
                                            </div>
                                            <div class="cell-meta subpair">
                                                @if (!empty($sub2['active_teacher']))
                                                    <span class="pill">
                                                        <span>👤</span>{{ $sub2['active_teacher'] }}
                                                @if(
                                                    ($sub2['replacement_teacher'] ?? null)
                                                    && ($sub2['is_replacement'] ?? false)
                                                    && ($sub2['replacement_teacher'] !== ($sub2['active_teacher'] ?? null))
                                                )
                                                    <span class="text-warning ms-1">→ {{ $sub2['replacement_teacher'] }}</span>
                                                @endif
                                                    </span>
                                                    @if(!empty($sub2['absence_type']))
                                                        <span class="absence-note">
                                                            {{ $absenceLabels[$sub2['absence_type']] ?? $sub2['absence_type'] }}
                                                        </span>
                                                    @endif
                                                @endif
                                                @if (!empty($sub2['active_room']))
                                                    <span class="pill room-pill {{ ($sub2['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($sub2['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                        <span>🏫</span>{{ $sub2['active_room'] }}
                                                    </span>
                                                @endif
                                                @if (!empty($sub2['label']))
                                                    <span class="pill"><span>🔸</span>{{ $sub2['label'] }}</span>
                                                @endif
                                            </div>
                                            @if($sub2['active_conflict'] ?? false)
                                                <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                            @endif
                                        @endif
                                        @if($pair['has_denominator'])
                                            <div class="den-separator" title="Разделение числитель/знаменатель"></div>
                                        @endif
                                    @endif
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const targetGroupId = params.get('group_id');
        const targetDay = params.get('day');
        const targetLesson = params.get('lesson');
        const weekDays = @json($weekDays ?? []);
        const dayKeyMap = {};
        weekDays.forEach((dayInfo) => {
            if (dayInfo && dayInfo.key) {
                dayKeyMap[dayInfo.key] = dayInfo.name;
            }
        });

        if (targetGroupId && targetDay && targetLesson) {
            const targetDayName = dayKeyMap[targetDay] || targetDay;
            const groupCard = document.getElementById(`group-${targetGroupId}`);
            if (groupCard) {
                const targetCell = groupCard.querySelector(
                    `.pair-cell[data-group="${targetGroupId}"][data-day="${targetDayName}"][data-lesson="${targetLesson}"]`
                ) || groupCard.querySelector(
                    `.pair-cell[data-group="${targetGroupId}"][data-day-key="${targetDay}"][data-lesson="${targetLesson}"]`
                );
                if (targetCell) {
                    targetCell.classList.add('highlighted');
                    targetCell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    groupCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                const targetCell = document.querySelector(
                    `.pair-cell[data-group="${targetGroupId}"][data-day="${targetDayName}"][data-lesson="${targetLesson}"]`
                ) || document.querySelector(
                    `.pair-cell[data-group="${targetGroupId}"][data-day-key="${targetDay}"][data-lesson="${targetLesson}"]`
                );
                if (targetCell) {
                    targetCell.classList.add('highlighted');
                    targetCell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        const subjectsRu = @json($subjects ?? []);
        const subjectsKz = @json($subjectsKz ?? []);
        const subjectGroupTypes = @json($subjectGroupTypes ?? []);
        const groupLocalePreference = @json($groupLocalePreference ?? []);
        const teachers = @json($teachers ?? []);
        const teacherSubjectMap = @json($teacherSubjectMap ?? []);
        const freeTeachersUrl = @json(route('first.schedule.free_teachers'));
        const freeRoomsUrl = @json(route('first.schedule.free_rooms'));
        const autoAssignRoomsDayUrl = @json(route('first.schedule.auto_assign_rooms_day'));
        const clearRoomsDayUrl = @json(route('first.schedule.clear_rooms_day'));
        const initialDayKey = @json($dayKey ?? null);
        const roomsList = @json(($rooms ?? collect())->values()->all());
        const absenceLabels = @json($absenceLabels ?? []);
        const dayKeyByName = {};
        weekDays.forEach((dayInfo) => {
            if (dayInfo && dayInfo.name && dayInfo.key) {
                dayKeyByName[dayInfo.name] = dayInfo.key;
            }
        });

    const modal = document.getElementById('pairModal');
    const overlay = document.getElementById('modalOverlay');
    const form = document.getElementById('pairForm');
    const weekStartPicker = document.getElementById('weekStartInput');
    const weekStartApply = document.getElementById('weekStartApply');

    const subject1 = document.getElementById('modalSubject1');
    const teacher1 = document.getElementById('modalTeacher1');
    const room1 = document.getElementById('modalRoom1');
    const subject1Den = document.getElementById('modalSubject1Den');
    const teacher1Den = document.getElementById('modalTeacher1Den');
    const room1Den = document.getElementById('modalRoom1Den');
    const weekStartHidden = document.getElementById('modalWeekStart');

    const toggleSub2 = document.getElementById('modalHasSub2');
    const subject2 = document.getElementById('modalSubject2');
    const teacher2 = document.getElementById('modalTeacher2');
    const room2 = document.getElementById('modalRoom2');
    const subject2Den = document.getElementById('modalSubject2Den');
    const teacher2Den = document.getElementById('modalTeacher2Den');
    const room2Den = document.getElementById('modalRoom2Den');
    const sub2CardNum = document.getElementById('subgroup2CardNum');
    const sub2CardDen = document.getElementById('subgroup2CardDen');
    const numeratorBlock = document.getElementById('numeratorBlock');
    const hasDenToggle = document.getElementById('modalHasDen');
    const denBlock = document.getElementById('denominatorBlock');
    const absent1Hidden = document.getElementById('modalAbsent1Hidden');
    const replacement1Hidden = document.getElementById('modalReplacement1Hidden');
    const replacementTeacher1 = document.getElementById('modalReplacementTeacher1');
    const replacementSubject1 = document.getElementById('modalReplacementSubject1');
    const replacementComment1 = document.getElementById('modalReplacementComment1');
    const absent2Hidden = document.getElementById('modalAbsent2Hidden');
    const replacement2Hidden = document.getElementById('modalReplacement2Hidden');
    const replacementTeacher2 = document.getElementById('modalReplacementTeacher2');
    const replacementSubject2 = document.getElementById('modalReplacementSubject2');
    const replacementComment2 = document.getElementById('modalReplacementComment2');
    const replacementToggle1 = document.getElementById('modalReplacementToggle1');
    const replacementBlock1 = document.getElementById('replacementBlock1');
    const replacementDen1Hidden = document.getElementById('modalReplacement1DenHidden');
    const replacementDen2Hidden = document.getElementById('modalReplacement2DenHidden');
    const replacementTeacher1Den = document.getElementById('modalReplacementTeacher1Den');
    const replacementSubject1Den = document.getElementById('modalReplacementSubject1Den');
    const replacementComment1Den = document.getElementById('modalReplacementComment1Den');
    const replacementToggle1Den = document.getElementById('modalReplacementToggle1Den');
    const replacementBlock1Den = document.getElementById('replacementBlock1Den');
    const replacementToggle2 = document.getElementById('modalReplacementToggle2');
    const replacementBlock2 = document.getElementById('replacementBlock2');
    const replacementTeacher2Den = document.getElementById('modalReplacementTeacher2Den');
    const replacementSubject2Den = document.getElementById('modalReplacementSubject2Den');
    const replacementComment2Den = document.getElementById('modalReplacementComment2Den');
    const replacementToggle2Den = document.getElementById('modalReplacementToggle2Den');
    const replacementBlock2Den = document.getElementById('replacementBlock2Den');
    const teacherConflictAlert1 = document.getElementById('teacherConflictAlert1');
    const teacherConflictAlert2 = document.getElementById('teacherConflictAlert2');

    const hiddenGroup = document.getElementById('modalGroupId');
    const hiddenDay = document.getElementById('modalDay');
    const hiddenLesson = document.getElementById('modalLesson');

    let currentSubjectMode = 'ru';
    const subjectSelects = [
        subject1,
        subject2,
        subject1Den,
        subject2Den,
        replacementSubject1,
        replacementSubject2,
        replacementSubject1Den,
        replacementSubject2Den,
    ].filter(Boolean);

    const teacherSelects = [
        teacher1,
        teacher2,
        teacher1Den,
        teacher2Den,
        replacementTeacher1,
        replacementTeacher2,
        replacementTeacher1Den,
        replacementTeacher2Den,
    ].filter(Boolean);
    const roomSelects = [
        room1,
        room2,
        room1Den,
        room2Den,
    ].filter(Boolean);

    const teacherOptionsMap = new Map();
    teacherSelects.forEach((select) => {
        if (!select) return;
        select.dataset.role = 'teacher';
        teacherOptionsMap.set(select, Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        })));
    });
    const roomOptionsMap = new Map();
    roomSelects.forEach((select) => {
        if (!select) return;
        select.dataset.role = 'room';
        roomOptionsMap.set(select, Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        })));
    });

    const rebuildTeacherOptions = (selectEl, allowedValues = null, term = '') => {
        const options = teacherOptionsMap.get(selectEl) || Array.from(selectEl.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        }));
        const allowedSet = allowedValues && allowedValues.length ? new Set(allowedValues.map(String)) : null;
        const previousValue = selectEl.value;
        const search = term.toLowerCase();
        selectEl.innerHTML = '';

        let hasSelection = false;
        options.forEach(opt => {
            if (allowedSet && opt.value && !allowedSet.has(String(opt.value))) {
                return;
            }
            if (search && !opt.text.toLowerCase().includes(search)) {
                return;
            }
            const node = document.createElement('option');
            node.value = opt.value;
            node.text = opt.text;
            if (opt.value === previousValue) {
                node.selected = true;
                hasSelection = true;
            }
            selectEl.appendChild(node);
        });

        if (!hasSelection && selectEl.options.length) {
            selectEl.selectedIndex = 0;
        }

        if (allowedSet) {
            selectEl.dataset.allowedValues = JSON.stringify(Array.from(allowedSet));
        } else {
            delete selectEl.dataset.allowedValues;
        }
    };

    const getAllowedTeachers = (subjectId) => {
        if (!subjectId) {
            return null;
        }
        const allowed = teacherSubjectMap[subjectId];
        if (!Array.isArray(allowed) || allowed.length === 0) {
            return null;
        }
        return allowed.map(String);
    };

    const getFreeTeachersForSelect = (select) => {
        if (!select || !select.dataset.freeTeachers) {
            return null;
        }
        try {
            const parsed = JSON.parse(select.dataset.freeTeachers);
            return Array.isArray(parsed) ? parsed.map(String) : null;
        } catch (e) {
            return null;
        }
    };

    const rebuildRoomOptions = (selectEl, allowedValues = null) => {
        const options = roomOptionsMap.get(selectEl) || Array.from(selectEl.options).map(opt => ({
            value: opt.value,
            text: opt.text,
        }));
        const allowedSet = allowedValues && allowedValues.length ? new Set(allowedValues.map(String)) : null;
        const previousValue = selectEl.value;
        selectEl.innerHTML = '';

        let hasSelection = false;
        options.forEach(opt => {
            if (allowedSet && opt.value && !allowedSet.has(String(opt.value))) {
                return;
            }
            const node = document.createElement('option');
            node.value = opt.value;
            node.text = opt.text;
            if (opt.value === previousValue) {
                node.selected = true;
                hasSelection = true;
            }
            selectEl.appendChild(node);
        });

        if (!hasSelection && selectEl.options.length) {
            selectEl.selectedIndex = 0;
        }
    };

    const teacherLinks = [
        { subject: subject1, teacher: teacher1 },
        { subject: subject1Den, teacher: teacher1Den },
        { subject: subject2, teacher: teacher2 },
        { subject: subject2Den, teacher: teacher2Den },
        { subject: replacementSubject1, teacher: replacementTeacher1, fallbackSubject: subject1 },
        { subject: replacementSubject2, teacher: replacementTeacher2, fallbackSubject: subject2 },
        { subject: replacementSubject1Den, teacher: replacementTeacher1Den, fallbackSubject: subject1Den },
        { subject: replacementSubject2Den, teacher: replacementTeacher2Den, fallbackSubject: subject2Den },
    ].filter(link => link.subject && link.teacher);

    const applyTeacherFilter = (link) => {
        const subjectId = link.subject.value || link.fallbackSubject?.value;
        let allowed = getAllowedTeachers(subjectId);
        const freeTeachers = getFreeTeachersForSelect(link.teacher);
        if (freeTeachers) {
            if (allowed) {
                const freeSet = new Set(freeTeachers.map(String));
                allowed = allowed.filter((id) => freeSet.has(String(id)));
            } else {
                allowed = freeTeachers;
            }
        }
        const searchInput = document.querySelector(`.search-field[data-target="${link.teacher.id}"]`);
        if (searchInput) {
            searchInput.value = '';
        }
        rebuildTeacherOptions(link.teacher, allowed, '');
    };

    const applyAllTeacherFilters = () => {
        teacherLinks.forEach(applyTeacherFilter);
    };

    const freeTeacherCache = new Map();
    const buildFreeTeacherKey = (dayKey, lesson, mode, groupId) => `${dayKey}|${lesson}|${mode}|${groupId || ''}`;

    const fetchFreeTeachersForSlot = async (dayKey, lesson, mode, groupId) => {
        if (!dayKey || !lesson) {
            return null;
        }
        const key = buildFreeTeacherKey(dayKey, lesson, mode, groupId);
        if (freeTeacherCache.has(key)) {
            return freeTeacherCache.get(key);
        }
        const params = new URLSearchParams();
        params.set('week_start', weekStartPicker?.value || weekStartHidden?.value || '');
        params.set('day_key', dayKey);
        params.set('lesson_number', lesson);
        params.set('mode', mode || 'numerator');
        if (courseSelect?.value) {
            params.set('course', courseSelect.value);
        }
        if (groupId) {
            params.set('group_id', groupId);
        }

        try {
            const response = await fetch(`${freeTeachersUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                return null;
            }
            const payload = await response.json();
            freeTeacherCache.set(key, payload);
            return payload;
        } catch (error) {
            return null;
        }
    };

    const setTeacherFreeList = (select, freeTeachers, absentMap) => {
        if (!select) return;
        if (Array.isArray(freeTeachers)) {
            const currentValue = select.value ? String(select.value) : '';
            const normalized = freeTeachers.map(String);
            if (currentValue && !normalized.includes(currentValue)) {
                normalized.push(currentValue);
            }
            select.dataset.freeTeachers = JSON.stringify(normalized);
        } else {
            delete select.dataset.freeTeachers;
        }
        if (absentMap && typeof absentMap === 'object') {
            select.dataset.absentTeachers = JSON.stringify(absentMap);
        } else {
            delete select.dataset.absentTeachers;
        }
    };

    const updateTeacherAbsenceState = (select) => {
        if (!select) return;
        let absentMap = null;
        if (select.dataset.absentTeachers) {
            try {
                absentMap = JSON.parse(select.dataset.absentTeachers);
            } catch (e) {
                absentMap = null;
            }
        }
        const selected = select.value;
        const type = selected && absentMap ? absentMap[selected] : null;
        select.classList.toggle('teacher-absent', !!type);
    };

    const refreshModalFreeTeachers = async () => {
        const dayName = hiddenDay?.value || '';
        const dayKey = dayKeyByName[dayName] || dayName;
        const lesson = hiddenLesson?.value || '';
        const groupId = hiddenGroup?.value || '';
        if (!dayKey || !lesson) {
            return;
        }
        const payloadNum = await fetchFreeTeachersForSlot(dayKey, lesson, 'numerator', groupId);
        const payloadDen = await fetchFreeTeachersForSlot(dayKey, lesson, 'denominator', groupId);

        teacherSelects.forEach((select) => {
            const mode = select.dataset.mode || 'numerator';
            const payload = mode === 'denominator' ? payloadDen : payloadNum;
            if (!payload) {
                setTeacherFreeList(select, null, null);
                return;
            }
            setTeacherFreeList(select, payload.free || null, payload.absent || null);
        });

        applyAllTeacherFilters();
        teacherSelects.forEach(updateTeacherAbsenceState);
        await refreshModalFreeRooms();
    };

    const fetchFreeRoomsForSlot = async (dayKey, lesson, mode, groupId, teacherId) => {
        if (!dayKey || !lesson) {
            return null;
        }
        const params = new URLSearchParams();
        params.set('week_start', weekStartPicker?.value || weekStartHidden?.value || '');
        params.set('day_key', dayKey);
        params.set('lesson_number', lesson);
        params.set('mode', mode || 'numerator');
        if (courseSelect?.value) {
            params.set('course', courseSelect.value);
        }
        if (groupId) {
            params.set('group_id', groupId);
        }
        if (teacherId) {
            params.set('teacher_id', teacherId);
        }
        try {
            const response = await fetch(`${freeRoomsUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                return null;
            }
            return await response.json();
        } catch (error) {
            return null;
        }
    };

    const refreshModalFreeRooms = async () => {
        const dayName = hiddenDay?.value || '';
        const dayKey = dayKeyByName[dayName] || dayName;
        const lesson = hiddenLesson?.value || '';
        const groupId = hiddenGroup?.value || '';
        if (!dayKey || !lesson) {
            return;
        }

        for (const select of roomSelects) {
            const mode = select.dataset.mode || 'numerator';
            const teacherTarget = select.dataset.teacherTarget || '';
            const teacherSelect = teacherTarget ? document.querySelector(teacherTarget) : null;
            const teacherId = teacherSelect?.value || '';
            const payload = await fetchFreeRoomsForSlot(dayKey, lesson, mode, groupId, teacherId);

            if (!payload || !Array.isArray(payload.rooms)) {
                const fallback = roomsList.map((r) => String(r.code));
                rebuildRoomOptions(select, fallback);
                continue;
            }

            const freeCodes = payload.rooms
                .map((room) => String(room.code ?? '').trim())
                .filter((code) => code !== '');
            const currentValue = select.value ? String(select.value) : '';
            if (currentValue && !freeCodes.includes(currentValue)) {
                freeCodes.push(currentValue);
            }
            rebuildRoomOptions(select, freeCodes);
        }
    };

    const suggestRoomForSlot = async ({ roomInput, teacherSelect, mode, force = false }) => {
        if (!roomInput || !teacherSelect) {
            return;
        }
        if (!force && roomInput.value) {
            return;
        }
        const dayName = hiddenDay?.value || '';
        const dayKey = dayKeyByName[dayName] || dayName;
        const lesson = hiddenLesson?.value || '';
        const groupId = hiddenGroup?.value || '';
        const resolvedMode = mode || teacherSelect.dataset.mode || 'numerator';
        const teacherId = teacherSelect.value || '';
        if (!teacherId) {
            return;
        }
        const payload = await fetchFreeRoomsForSlot(dayKey, lesson, resolvedMode, groupId, teacherId);
        const suggested = payload?.suggested;
        if (suggested) {
            roomInput.value = suggested;
            roomInput.dispatchEvent(new Event('input'));
        }
    };

    const suggestRoomForTeacher = async (teacherSelect) => {
        const roomTarget = teacherSelect.dataset.roomTarget;
        if (!roomTarget) return;
        const roomInput = document.querySelector(roomTarget);
        await suggestRoomForSlot({ roomInput, teacherSelect, mode: teacherSelect.dataset.mode || 'numerator', force: false });
    };

    teacherLinks.forEach(link => {
        link.subject.addEventListener('change', () => applyTeacherFilter(link));
        if (link.fallbackSubject) {
            link.fallbackSubject.addEventListener('change', () => applyTeacherFilter(link));
        }
    });

    teacherSelects.forEach((select) => {
        select.addEventListener('change', () => {
            updateTeacherAbsenceState(select);
            suggestRoomForTeacher(select);
            refreshModalFreeRooms();
        });
    });

    document.querySelectorAll('.js-suggest-room').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const roomInput = document.querySelector(btn.dataset.roomTarget || '');
            const teacherSelect = document.querySelector(btn.dataset.teacherTarget || '');
            const mode = btn.dataset.mode || teacherSelect?.dataset.mode || 'numerator';
            await suggestRoomForSlot({ roomInput, teacherSelect, mode, force: true });
        });
    });

    const applySubjectFilter = (groupId) => {
        const useKazakh = groupLocalePreference[String(groupId)] === true;
        const mode = useKazakh ? 'kz' : 'ru';
        const titles = useKazakh ? subjectsKz : subjectsRu;
        currentSubjectMode = mode;

        subjectSelects.forEach((select) => {
            Array.from(select.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const groupType = subjectGroupTypes[option.value] || 'both';
                const allowed = groupType === 'both' || groupType === mode;
                const keepSelected = option.value === select.value;

                option.textContent = titles[option.value] || option.textContent;
                option.hidden = !(allowed || keepSelected);
                option.disabled = !(allowed || keepSelected);
            });
        });
    };

    const syncReplacementFlag1 = (resetFields = false) => {
        const enabled = replacementToggle1.checked;
        replacementBlock1.classList.toggle('d-none', !enabled);
        replacement1Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher1.value = '';
            replacementSubject1.value = '';
            replacementComment1.value = '';
        }
    };

    const syncReplacementFlag1Den = (resetFields = false) => {
        const enabled = replacementToggle1Den.checked;
        replacementBlock1Den.classList.toggle('d-none', !enabled);
        replacementDen1Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher1Den.value = '';
            replacementSubject1Den.value = '';
            replacementComment1Den.value = '';
        }
    };

    const syncReplacementFlag2 = (resetFields = false) => {
        const enabled = replacementToggle2.checked;
        replacementBlock2.classList.toggle('d-none', !enabled);
        replacement2Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher2.value = '';
            replacementSubject2.value = '';
            replacementComment2.value = '';
        }
    };

    const syncReplacementFlag2Den = (resetFields = false) => {
        const enabled = replacementToggle2Den.checked;
        replacementBlock2Den.classList.toggle('d-none', !enabled);
        replacementDen2Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher2Den.value = '';
            replacementSubject2Den.value = '';
            replacementComment2Den.value = '';
        }
    };

    const setTeacherConflictAlert = (el, flag, groupsText, day, lesson) => {
        if (!el) return;
        const active = flag === '1' && groupsText;
        el.classList.toggle('d-none', !active);
        el.textContent = active
            ? `Преподаватель занят у групп: ${groupsText} (${day}, пара ${lesson})`
            : '';
    };

    const weekMode = "{{ $weekMode ?? 'num' }}";
    const allowDenEdit = true;

    const setBlockEnabled = (block, enabled) => {
        if (!block) return;
        block.classList.toggle('d-none', !enabled);
        block.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = !enabled;
        });
    };

    const syncDenominatorVisibility = () => {
        hasDenToggle.disabled = false;
        denBlock.classList.toggle('d-none', !hasDenToggle.checked);
        denBlock.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = !hasDenToggle.checked;
        });
    };

    const openModal = (data) => {
        hiddenGroup.value = data.group;
        hiddenDay.value = data.day;
        hiddenLesson.value = data.lesson;
        weekStartHidden.value = data.weekStart || (document.getElementById('weekStartInput')?.value || '');

        subject1.value = data.subject1 || '';
        teacher1.value = data.teacher1 || '';
        room1.value = data.room1 || '';
        subject1Den.value = data.denSubject1 || '';
        teacher1Den.value = data.denTeacher1 || '';
        room1Den.value = data.denRoom1 || '';

        subject2.value = data.subject2 || '';
        teacher2.value = data.teacher2 || '';
        room2.value = data.room2 || '';
        subject2Den.value = data.denSubject2 || '';
        teacher2Den.value = data.denTeacher2 || '';
        room2Den.value = data.denRoom2 || '';

        applySubjectFilter(data.group);
        applyAllTeacherFilters();

        toggleSub2.checked = data.hasSub2 === '1';
        sub2CardNum.classList.toggle('d-none', !toggleSub2.checked);
        sub2CardDen.classList.toggle('d-none', !toggleSub2.checked);

        const hasDen = data.hasDenominator === '1' || data.denSubject1 || data.denSubject2 || data.denTeacher1 || data.denTeacher2 || data.denRoom1 || data.denRoom2;
        hasDenToggle.checked = !!hasDen;
        syncDenominatorVisibility();

        absent1Hidden.value = data.absent1 === '1' ? '1' : '0';
        absent2Hidden.value = data.absent2 === '1' ? '1' : '0';

        // Absence type hints
        const subjectReplaceTypes = ['dayoff'];
        function applyAbsenceHint(hintEl, toggleEl, absenceType, isAbsent) {
            if (!hintEl) return;
            hintEl.className = 'absence-hint d-none mb-2';
            hintEl.innerHTML = '';
            if (!isAbsent || !absenceType) return;
            const isSubjectReplace = subjectReplaceTypes.includes(absenceType);
            const typeLabels = @json(\App\Support\TeacherAbsenceTypes::TYPES);
            const typeLabel = typeLabels[absenceType] || absenceType;
            if (isSubjectReplace) {
                hintEl.classList.add('hint-subject');
                hintEl.innerHTML = `<i class="bi bi-arrow-left-right"></i><span><strong>${typeLabel}</strong> — выберите другого преподавателя и другой предмет для этой пары</span>`;
            } else {
                hintEl.classList.add('hint-teacher');
                hintEl.innerHTML = `<i class="bi bi-person-fill-x"></i><span><strong>${typeLabel}</strong> — выберите заменяющего преподавателя (предмет остаётся)</span>`;
            }
            hintEl.classList.remove('d-none');
            if (toggleEl && !toggleEl.checked) {
                toggleEl.checked = true;
                toggleEl.dispatchEvent(new Event('change'));
            }
        }
        applyAbsenceHint(
            document.getElementById('absenceHint1'),
            replacementToggle1,
            data.absenceType1 || '',
            data.absent1 === '1'
        );
        replacement1Hidden.value = data.replacement1 === '1' ? '1' : '0';
        replacementTeacher1.value = data.replacementTeacher1 || '';
        replacementSubject1.value = data.replacementSubject1 || '';
        replacementComment1.value = data.replacementComment1 || '';
        replacement2Hidden.value = data.replacement2 === '1' ? '1' : '0';
        replacementTeacher2.value = data.replacementTeacher2 || '';
        replacementSubject2.value = data.replacementSubject2 || '';
        replacementComment2.value = data.replacementComment2 || '';
        replacementDen1Hidden.value = data.replacementDen1 === '1' ? '1' : '0';
        replacementTeacher1Den.value = data.replacementTeacher1Den || '';
        replacementSubject1Den.value = data.replacementSubject1Den || '';
        replacementComment1Den.value = data.replacementComment1Den || '';
        replacementDen2Hidden.value = data.replacementDen2 === '1' ? '1' : '0';
        replacementTeacher2Den.value = data.replacementTeacher2Den || '';
        replacementSubject2Den.value = data.replacementSubject2Den || '';
        replacementComment2Den.value = data.replacementComment2Den || '';
        const hasReplacement1 = data.replacement1 === '1'
            || data.replacementTeacher1
            || data.replacementSubject1
            || (data.replacementComment1 || '').trim();
        replacementToggle1.checked = !!hasReplacement1;
        const hasReplacement2 = data.replacement2 === '1'
            || data.replacementTeacher2
            || data.replacementSubject2
            || (data.replacementComment2 || '').trim();
        replacementToggle2.checked = !!hasReplacement2;
        const hasReplacement1Den = data.replacementDen1 === '1'
            || data.replacementTeacher1Den
            || data.replacementSubject1Den
            || (data.replacementComment1Den || '').trim();
        replacementToggle1Den.checked = !!hasReplacement1Den;
        const hasReplacement2Den = data.replacementDen2 === '1'
            || data.replacementTeacher2Den
            || data.replacementSubject2Den
            || (data.replacementComment2Den || '').trim();
        replacementToggle2Den.checked = !!hasReplacement2Den;
        syncReplacementFlag1();
        syncReplacementFlag1Den();
        syncReplacementFlag2();
        syncReplacementFlag2Den();
        setTeacherConflictAlert(
            teacherConflictAlert1,
            data.teacherConflict1 || '0',
            data.teacherConflict1Groups || '',
            data.day,
            data.lesson
        );
        setTeacherConflictAlert(
            teacherConflictAlert2,
            data.teacherConflict2 || '0',
            data.teacherConflict2Groups || '',
            data.day,
            data.lesson
        );
        refreshModalFreeTeachers();
        refreshAvailability();

        overlay.classList.add('show');
        modal.classList.add('show');
    };

    const closeModal = () => {
        overlay.classList.remove('show');
        modal.classList.remove('show');
    };

    const availabilityUrl = @json(route('first.schedule.availability'));
    const availabilityNotes = new Map();
    document.querySelectorAll('.availability-note[data-status-for]').forEach(note => {
        availabilityNotes.set(note.dataset.statusFor, note);
    });

    const setAvailabilityNote = (targetId, status, message) => {
        const note = availabilityNotes.get(targetId);
        if (!note) {
            return;
        }
        note.textContent = message || '';
        note.classList.remove('is-free', 'is-busy');
        if (status === 'free') {
            note.classList.add('is-free');
        } else if (status === 'busy') {
            note.classList.add('is-busy');
        }
    };

    const debounce = (fn, wait = 350) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), wait);
        };
    };

    const buildAvailabilityParams = (field) => {
        const weekStart = weekStartHidden?.value;
        const day = hiddenDay?.value;
        const lesson = hiddenLesson?.value;
        const groupId = hiddenGroup?.value;
        const type = field.dataset.type;
        const mode = field.dataset.mode;
        if (!weekStart || !day || !lesson || !type || !mode) {
            return null;
        }
        const params = new URLSearchParams();
        params.set('week_start', weekStart);
        params.set('day_key', day);
        params.set('lesson_number', lesson);
        params.set('mode', mode);
        params.set('type', type);
        const courseValue = form.querySelector('input[name="course"]')?.value;
        if (courseValue) {
            params.set('course', courseValue);
        }
        if (groupId) {
            params.set('group_id', groupId);
        }
        if (type === 'teacher') {
            params.set('teacher_id', field.value || '');
        } else if (type === 'room') {
            params.set('room', field.value || '');
        }
        return params;
    };

    const requestAvailability = async (field) => {
        if (field.disabled) {
            return;
        }
        const params = buildAvailabilityParams(field);
        if (!params) {
            return;
        }
        const targetId = field.id;
        if (!targetId) {
            return;
        }
        if (!field.value) {
            setAvailabilityNote(targetId, '', '');
            return;
        }
        try {
            const response = await fetch(`${availabilityUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) {
                setAvailabilityNote(targetId, 'busy', 'Нет данных');
                return;
            }
            const payload = await response.json();
            setAvailabilityNote(targetId, payload.status, payload.message);
        } catch (error) {
            setAvailabilityNote(targetId, 'busy', 'Нет данных');
        }
    };

    const debouncedRoomCheck = debounce(requestAvailability, 400);

    const bindAvailabilityListeners = () => {
        modal.querySelectorAll('.availability-check').forEach(field => {
            if (field.dataset.type === 'room') {
                field.addEventListener('input', () => debouncedRoomCheck(field));
                field.addEventListener('blur', () => requestAvailability(field));
            } else {
                field.addEventListener('change', () => requestAvailability(field));
            }
        });
    };

    const refreshAvailability = () => {
        modal.querySelectorAll('.availability-check').forEach(field => {
            requestAvailability(field);
        });
    };

    bindAvailabilityListeners();

    toggleSub2.addEventListener('change', () => {
        sub2CardNum.classList.toggle('d-none', !toggleSub2.checked);
        sub2CardDen.classList.toggle('d-none', !toggleSub2.checked);
        if (!toggleSub2.checked) {
            if (allowDenEdit) {
                subject2Den.value = '';
                teacher2Den.value = '';
                room2Den.value = '';
            } else {
                subject2.value = '';
                teacher2.value = '';
                room2.value = '';
                absent2Hidden.value = '0';
                replacementToggle2.checked = false;
                syncReplacementFlag2(true);
                setTeacherConflictAlert(teacherConflictAlert2, '0', '', '', '');
            }
            replacementToggle2Den.checked = false;
            syncReplacementFlag2Den(true);
        }
    });

    [replacementTeacher1, replacementSubject1].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag1());
    });
    replacementComment1.addEventListener('input', () => syncReplacementFlag1());
    replacementToggle1.addEventListener('change', () => syncReplacementFlag1(true));
    [replacementTeacher2, replacementSubject2].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag2());
    });
    replacementComment2.addEventListener('input', () => syncReplacementFlag2());
    replacementToggle2.addEventListener('change', () => syncReplacementFlag2(true));
    [replacementTeacher1Den, replacementSubject1Den].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag1Den());
    });
    replacementComment1Den.addEventListener('input', () => syncReplacementFlag1Den());
    replacementToggle1Den.addEventListener('change', () => syncReplacementFlag1Den(true));
    [replacementTeacher2Den, replacementSubject2Den].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag2Den());
    });
    replacementComment2Den.addEventListener('input', () => syncReplacementFlag2Den());
    replacementToggle2Den.addEventListener('change', () => syncReplacementFlag2Den(true));

    hasDenToggle.addEventListener('change', () => {
        if (!allowDenEdit) return;
        denBlock.classList.toggle('d-none', !hasDenToggle.checked);
        denBlock.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = !hasDenToggle.checked;
        });
        if (!hasDenToggle.checked) {
            subject1Den.value = '';
            teacher1Den.value = '';
            room1Den.value = '';
            subject2Den.value = '';
            teacher2Den.value = '';
            room2Den.value = '';
            replacementToggle1Den.checked = false;
            syncReplacementFlag1Den(true);
            replacementToggle2Den.checked = false;
            syncReplacementFlag2Den(true);
        }
    });

    document.querySelectorAll('.cell-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(btn.dataset);
        });
    });

    document.querySelectorAll('.pair-cell').forEach(cell => {
        cell.addEventListener('click', (e) => {
            if (e.target.closest('.cell-edit')) {
                return;
            }
            const editBtn = cell.querySelector('.cell-edit');
            if (editBtn) {
                editBtn.click();
            }
        });
    });

    document.getElementById('modalClose').addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    const searchInput = document.getElementById('groupSearch');
    const courseSelect = document.getElementById('courseSelect');
    if (searchInput) {
        const groups = document.querySelectorAll('.group-compact');
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            groups.forEach(group => {
                const text = group.innerText.toLowerCase();
                group.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Поиск в селектах модалки
    document.querySelectorAll('.search-field').forEach(input => {
        const targetId = input.dataset.target;
        const select = document.getElementById(targetId);
        if (!select) return;

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            if (select.dataset.role === 'teacher') {
                let allowedValues = null;
                if (select.dataset.allowedValues) {
                    try {
                        allowedValues = JSON.parse(select.dataset.allowedValues);
                    } catch (err) {
                        allowedValues = null;
                    }
                }
                rebuildTeacherOptions(select, allowedValues, term);
                return;
            }
            Array.from(select.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }
                const groupType = subjectGroupTypes[option.value] || 'both';
                const allowed = groupType === 'both' || groupType === currentSubjectMode;
                const match = option.text.toLowerCase().includes(term);
                const keepSelected = option.value === select.value;
                option.hidden = !(allowed && match) && !keepSelected;
                option.disabled = !(allowed && match) && !keepSelected;
            });
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        try {
            const res = await fetch("{{ route('first.schedule.pair.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                alert(err.message || 'Ошибка сохранения');
                return;
            }
            closeModal();
            window.location.reload();
        } catch (error) {
            alert('Ошибка сети');
        }
    });

    const deleteBtn = document.getElementById('modalDelete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!confirm('Удалить эту пару из расписания?')) return;
            const formData = new FormData();
            formData.set('group_id', hiddenGroup.value || '');
            formData.set('study_day', hiddenDay.value || '');
            formData.set('lesson_number', hiddenLesson.value || '');
            formData.set('week_start', weekStartHidden.value || '');
            formData.set('course', form.querySelector('input[name="course"]')?.value || '');
            try {
                const res = await fetch("{{ route('first.schedule.pair.delete') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Ошибка удаления');
                    return;
                }
                closeModal();
                window.location.reload();
            } catch (error) {
                alert('Ошибка сети');
            }
        });
    }

    const weekNext = document.getElementById('weekNext');
    const weekPrev = document.getElementById('weekPrev');
    const dayPrev = document.getElementById('dayPrev');
    const dayNext = document.getElementById('dayNext');
    const dayToday = document.getElementById('dayToday');

    const formatDate = (date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const getWeekStart = (date) => {
        const copy = new Date(date.getTime());
        const day = copy.getDay();
        const diff = (day + 6) % 7;
        copy.setDate(copy.getDate() - diff);
        return copy;
    };
    const applyWeekStart = (value) => {
        const params = new URLSearchParams(window.location.search);
        if (daySelect) {
            params.set('day', daySelect.value);
        }
        if (value) {
            params.set('week_start', value);
        } else {
            params.delete('week_start');
        }
        window.location.search = params.toString();
    };

    if (weekStartApply && weekStartPicker) {
        weekStartApply.addEventListener('click', () => {
            applyWeekStart(weekStartPicker.value);
        });
    }

    if (weekNext && weekStartPicker) {
        weekNext.addEventListener('click', () => {
            let baseDate = new Date();
            if (weekStartPicker.value) {
                const [year, month, day] = weekStartPicker.value.split('-').map(Number);
                baseDate = new Date(year, (month || 1) - 1, day || 1);
            }
            if (Number.isNaN(baseDate.getTime())) return;
            baseDate.setDate(baseDate.getDate() + 7);
            const isoDate = formatDate(baseDate);
            weekStartPicker.value = isoDate;
            applyWeekStart(isoDate);
        });
    }

    if (weekPrev && weekStartPicker) {
        weekPrev.addEventListener('click', () => {
            let baseDate = new Date();
            if (weekStartPicker.value) {
                const [year, month, day] = weekStartPicker.value.split('-').map(Number);
                baseDate = new Date(year, (month || 1) - 1, day || 1);
            }
            if (Number.isNaN(baseDate.getTime())) return;
            baseDate.setDate(baseDate.getDate() - 7);
            const isoDate = formatDate(baseDate);
            weekStartPicker.value = isoDate;
            applyWeekStart(isoDate);
        });
    }

    if (courseSelect) {
        courseSelect.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('course', courseSelect.value);
            if (daySelect) {
                params.set('day', daySelect.value);
            }
            window.location.search = params.toString();
        });
    }

    const daySelect = document.getElementById('daySelect');
    if (daySelect) {
        daySelect.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('day', daySelect.value);
            window.location.search = params.toString();
        });
    }

    const applyDayAndWeek = (dayKey, weekStart) => {
        const params = new URLSearchParams(window.location.search);
        if (dayKey) {
            params.set('day', dayKey);
        }
        if (weekStart) {
            params.set('week_start', weekStart);
        }
        window.location.search = params.toString();
    };

    if (daySelect && dayPrev && dayNext) {
        const dayOrder = Array.from(daySelect.options).map(opt => opt.value).filter(Boolean);
        const resolveWeekStart = () => {
            if (weekStartPicker?.value) {
                const [year, month, day] = weekStartPicker.value.split('-').map(Number);
                const parsed = new Date(year, (month || 1) - 1, day || 1);
                if (!Number.isNaN(parsed.getTime())) {
                    return parsed;
                }
            }
            return getWeekStart(new Date());
        };

        dayPrev.addEventListener('click', () => {
            const currentKey = daySelect.value || dayOrder[0];
            let idx = dayOrder.indexOf(currentKey);
            if (idx === -1) idx = 0;
            if (idx === 0) {
                const weekStart = resolveWeekStart();
                weekStart.setDate(weekStart.getDate() - 7);
                applyDayAndWeek(dayOrder[dayOrder.length - 1], formatDate(weekStart));
            } else {
                applyDayAndWeek(dayOrder[idx - 1], weekStartPicker?.value || '');
            }
        });

        dayNext.addEventListener('click', () => {
            const currentKey = daySelect.value || dayOrder[0];
            let idx = dayOrder.indexOf(currentKey);
            if (idx === -1) idx = 0;
            if (idx === dayOrder.length - 1) {
                const weekStart = resolveWeekStart();
                weekStart.setDate(weekStart.getDate() + 7);
                applyDayAndWeek(dayOrder[0], formatDate(weekStart));
            } else {
                applyDayAndWeek(dayOrder[idx + 1], weekStartPicker?.value || '');
            }
        });
    }

    if (daySelect && dayToday) {
        dayToday.addEventListener('click', () => {
            const today = new Date();
            const weekStart = getWeekStart(today);
            const dayKeyMap = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const todayKey = dayKeyMap[today.getDay()];
            const dayOrder = Array.from(daySelect.options).map(opt => opt.value).filter(Boolean);
            const targetKey = dayOrder.includes(todayKey) ? todayKey : dayOrder[0];
            applyDayAndWeek(targetKey, formatDate(weekStart));
        });
    }

    const autoAssignRoomsDayBtn = document.getElementById('autoAssignRoomsDayBtn');
    autoAssignRoomsDayBtn?.addEventListener('click', async () => {
        const dayKey = daySelect?.value || initialDayKey;
        const weekStart = weekStartPicker?.value || weekStartHidden?.value || '';
        const course = courseSelect?.value || '1';

        if (!dayKey || !weekStart) {
            alert('Не удалось определить день или начало недели');
            return;
        }

        if (!confirm('Подставить свободные кабинеты для всех групп на выбранный день?')) {
            return;
        }

        autoAssignRoomsDayBtn.disabled = true;
        const originalText = autoAssignRoomsDayBtn.textContent;
        autoAssignRoomsDayBtn.textContent = 'Подстановка...';

        try {
            const res = await fetch(autoAssignRoomsDayUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    week_start: weekStart,
                    day_key: dayKey,
                    course: Number(course),
                }),
            });

            const payload = await res.json().catch(() => ({}));
            if (!res.ok) {
                alert(payload.message || 'Не удалось подставить кабинеты');
                return;
            }

            const updated = Number(payload.updated || 0);
            const skipped = Number(payload.skipped || 0);
            alert(`Готово. Обновлено: ${updated}. Пропущено: ${skipped}.`);
            window.location.reload();
        } catch (e) {
            alert('Ошибка сети при подстановке кабинетов');
        } finally {
            autoAssignRoomsDayBtn.disabled = false;
            autoAssignRoomsDayBtn.textContent = originalText;
        }
    });

    const clearRoomsDayBtn = document.getElementById('clearRoomsDayBtn');
    clearRoomsDayBtn?.addEventListener('click', async () => {
        const dayKey = daySelect?.value || initialDayKey;
        const weekStart = weekStartPicker?.value || weekStartHidden?.value || '';
        const course = courseSelect?.value || '1';

        if (!dayKey || !weekStart) {
            alert('Не удалось определить день или начало недели');
            return;
        }

        if (!confirm('Очистить кабинеты для всех групп на выбранный день?')) {
            return;
        }

        clearRoomsDayBtn.disabled = true;
        const originalText = clearRoomsDayBtn.textContent;
        clearRoomsDayBtn.textContent = 'Очистка...';

        try {
            const res = await fetch(clearRoomsDayUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    week_start: weekStart,
                    day_key: dayKey,
                    course: Number(course),
                }),
            });

            const payload = await res.json().catch(() => ({}));
            if (!res.ok) {
                alert(payload.message || 'Не удалось очистить кабинеты');
                return;
            }

            const updated = Number(payload.updated || 0);
            alert(`Готово. Очищено записей: ${updated}.`);
            window.location.reload();
        } catch (e) {
            alert('Ошибка сети при очистке кабинетов');
        } finally {
            clearRoomsDayBtn.disabled = false;
            clearRoomsDayBtn.textContent = originalText;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
@keyframes toastIn {
    from { opacity:0; transform:translateX(-50%) translateY(12px); }
    to   { opacity:1; transform:translateX(-50%) translateY(0); }
}
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;
    z-index: 90;
}
.modal-overlay.show { opacity: 1; pointer-events: all; }
.modal-card {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.96);
    background: #fff;
    border-radius: 12px;
    width: min(720px, 95%);
    padding: 18px 20px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.15s ease, opacity 0.15s ease;
    z-index: 91;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-card.show {
    opacity: 1;
    pointer-events: all;
    transform: translate(-50%, -50%) scale(1);
}
.teacher-absent {
    border-color: #f87171 !important;
    box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.2);
}
.subgroup-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 14px;
}
.modal-topline {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 12px;
}
.section-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    margin-top: 10px;
}
.section-head h5 {
    margin: 0 0 8px;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.subcard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 10px;
}
.subcard {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 10px 8px;
    box-shadow: 0 6px 12px rgba(15,23,42,0.06);
}
.subcard-head {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
.subcard-grid-inner {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 8px;
}
.replacement-card {
    background: #f8fafc;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    padding: 10px;
}
.absence-hint {
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 7px;
    line-height: 1.4;
}
.absence-hint.hint-subject {
    background: #fefce8;
    border: 1px solid #fde68a;
    color: #92400e;
}
.absence-hint.hint-teacher {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
}
.absence-hint i { flex-shrink: 0; margin-top: 1px; }
.alert-conflict {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}
.availability-note {
    margin-top: 6px;
    font-size: 12px;
    color: #64748b;
}
.availability-note.is-free {
    color: #15803d;
}
.availability-note.is-busy {
    color: #b91c1c;
}
.form-grid.compact {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.modal-title { margin: 0; font-size: 18px; font-weight: 700; }
.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #475569;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px;
}
.form-label { font-weight: 600; color: #334155; margin-bottom: 6px; display: block; }
.form-control, .form-select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 10px;
    font-size: 14px;
}
.form-check { display: flex; align-items: center; gap: 8px; margin: 10px 0; }
.btn-primary {
    background: linear-gradient(135deg, #7f56d9, #6941c6);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 10px 16px;
    border-radius: 10px;
}
.d-none { display: none !important; }
.main-line { align-items: center; gap: 8px; }
.cell-title.emphasis { font-weight: 700; font-size: 16px; }
.fraction-block { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; display: flex; flex-direction: column; gap: 6px; }
.fraction-block.subpair { margin-top: 6px; }
.fraction-line { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; padding: 6px 8px; border-radius: 8px; background: #f8fafc; }
.fraction-line.active { background: #eef2ff; border: 1px solid #d0d7ff; }
.fraction-text { font-weight: 600; }
.pill.tiny { font-size: 12px; padding: 4px 7px; }
</style>
@endpush

@push('scripts')
<div id="modalOverlay" class="modal-overlay"></div>
<div id="pairModal" class="modal-card">
    <div class="modal-header">
        <h3 class="modal-title">Редактировать пару</h3>
        <button type="button" class="modal-close" id="modalClose">✕</button>
    </div>
    <form id="pairForm">
        <input type="hidden" name="group_id" id="modalGroupId">
        <input type="hidden" name="study_day" id="modalDay">
        <input type="hidden" name="lesson_number" id="modalLesson">
        <input type="hidden" name="week_start" id="modalWeekStart" value="{{ $weekStart ?? '' }}">
        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
        <input type="hidden" name="den_is_replacement_1" id="modalReplacement1DenHidden" value="0">
        <input type="hidden" name="den_is_replacement_2" id="modalReplacement2DenHidden" value="0">

        <div class="modal-topline">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="modalHasSub2" name="has_sub2" value="1">
                <label class="form-check-label" for="modalHasSub2">Включить подгруппу 2</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="modalHasDen" name="has_denominator" value="1">
                <label class="form-check-label" for="modalHasDen">Включить знаменатель</label>
            </div>
        </div>

        <div class="section-block" id="numeratorBlock">
            <div class="section-head">
                <h5>Числитель (текущая неделя)</h5>
            </div>
            <div class="subcard-grid">
                <div class="subcard">
                    <div class="subcard-head">Подгруппа 1</div>
                    <div class="alert-conflict d-none" id="teacherConflictAlert1"></div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject1">
                            <select class="form-select" name="subject_id" id="modalSubject1">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher1">
                            <select class="form-select availability-check" name="teacher_id" id="modalTeacher1" data-type="teacher" data-mode="numerator" data-room-target="#modalRoom1">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalTeacher1"></div>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <select class="form-select availability-check" name="room_id" id="modalRoom1" data-type="room" data-mode="numerator" data-teacher-target="#modalTeacher1">
                                <option value="">—</option>
                                @foreach(($rooms ?? collect()) as $room)
                                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm mt-2 js-suggest-room"
                                data-teacher-target="#modalTeacher1"
                                data-room-target="#modalRoom1"
                                data-mode="numerator"
                            >
                                Подставить свободный
                            </button>
                            <div class="availability-note" data-status-for="modalRoom1"></div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <input type="hidden" name="is_absent_1" id="modalAbsent1Hidden" value="0">
                        <input type="hidden" name="is_replacement_1" id="modalReplacement1Hidden" value="0">
                        <div id="absenceHint1" class="absence-hint d-none mb-2"></div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle1">
                            <label class="form-check-label" for="modalReplacementToggle1">Включить замену</label>
                        </div>
                        <div id="replacementBlock1" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 1)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher1">
                            <select class="form-select mb-2 availability-check" name="replacement_teacher_id_1" id="modalReplacementTeacher1" data-type="teacher" data-mode="numerator">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalReplacementTeacher1"></div>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject1">
                            <select class="form-select mb-2" name="replacement_subject_id_1" id="modalReplacementSubject1">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_1" id="modalReplacementComment1" placeholder="Комментарий">
                        </div>
                    </div>
                </div>
                <div class="subcard sub2-card d-none" id="subgroup2CardNum">
                    <div class="subcard-head">Подгруппа 2</div>
                    <div class="alert-conflict d-none" id="teacherConflictAlert2"></div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject2">
                            <select class="form-select" name="subject_id_2" id="modalSubject2">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher2">
                            <select class="form-select availability-check" name="teacher_id_2" id="modalTeacher2" data-type="teacher" data-mode="numerator" data-room-target="#modalRoom2">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalTeacher2"></div>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <select class="form-select availability-check" name="room_id_2" id="modalRoom2" data-type="room" data-mode="numerator" data-teacher-target="#modalTeacher2">
                                <option value="">—</option>
                                @foreach(($rooms ?? collect()) as $room)
                                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm mt-2 js-suggest-room"
                                data-teacher-target="#modalTeacher2"
                                data-room-target="#modalRoom2"
                                data-mode="numerator"
                            >
                                Подставить свободный
                            </button>
                            <div class="availability-note" data-status-for="modalRoom2"></div>
                        </div>
                    </div>
                    <input type="hidden" name="is_absent_2" id="modalAbsent2Hidden" value="0">
                    <input type="hidden" name="is_replacement_2" id="modalReplacement2Hidden" value="0">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="modalReplacementToggle2">
                        <label class="form-check-label" for="modalReplacementToggle2">Включить замену</label>
                    </div>
                    <div id="replacementBlock2" class="replacement-card flex-grow-1 d-none">
                        <label class="form-label">Заменяющий (подгр. 2)</label>
                        <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher2">
                        <select class="form-select mb-2 availability-check" name="replacement_teacher_id_2" id="modalReplacementTeacher2" data-type="teacher" data-mode="numerator">
                            <option value="">— преподаватель</option>
                            @foreach($teachers as $id => $title)
                                <option value="{{ $id }}">{{ $title }}</option>
                            @endforeach
                        </select>
                        <div class="availability-note" data-status-for="modalReplacementTeacher2"></div>
                        <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject2">
                        <select class="form-select mb-2" name="replacement_subject_id_2" id="modalReplacementSubject2">
                            <option value="">— предмет</option>
                            @foreach($subjects as $id => $title)
                                <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control" name="replacement_comment_2" id="modalReplacementComment2" placeholder="Комментарий">
                    </div>
                </div>
            </div>
        </div>

        <div class="section-block d-none" id="denominatorBlock">
            <div class="section-head">
                <h5>{{ ($weekMode ?? 'num') === 'den' ? 'Знаменатель (текущая неделя)' : 'Знаменатель (следующая неделя)' }}</h5>
            </div>
            <div class="subcard-grid">
                <div class="subcard">
                    <div class="subcard-head">Подгруппа 1</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject1Den">
                            <select class="form-select" name="den_subject_id" id="modalSubject1Den">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher1Den">
                            <select class="form-select availability-check" name="den_teacher_id" id="modalTeacher1Den" data-type="teacher" data-mode="denominator" data-room-target="#modalRoom1Den">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalTeacher1Den"></div>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <select class="form-select availability-check" name="den_room_id" id="modalRoom1Den" data-type="room" data-mode="denominator" data-teacher-target="#modalTeacher1Den">
                                <option value="">—</option>
                                @foreach(($rooms ?? collect()) as $room)
                                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm mt-2 js-suggest-room"
                                data-teacher-target="#modalTeacher1Den"
                                data-room-target="#modalRoom1Den"
                                data-mode="denominator"
                            >
                                Подставить свободный
                            </button>
                            <div class="availability-note" data-status-for="modalRoom1Den"></div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle1Den">
                            <label class="form-check-label" for="modalReplacementToggle1Den">Замена (знаменатель, подгр. 1)</label>
                        </div>
                        <div id="replacementBlock1Den" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 1, знаменатель)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher1Den">
                            <select class="form-select mb-2 availability-check" name="replacement_teacher_id_1_den" id="modalReplacementTeacher1Den" data-type="teacher" data-mode="denominator">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalReplacementTeacher1Den"></div>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject1Den">
                            <select class="form-select mb-2" name="replacement_subject_id_1_den" id="modalReplacementSubject1Den">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_1_den" id="modalReplacementComment1Den" placeholder="Комментарий">
                        </div>
                    </div>
                </div>
                <div class="subcard sub2-card d-none" id="subgroup2CardDen">
                    <div class="subcard-head">Подгруппа 2</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject2Den">
                            <select class="form-select" name="den_subject_id_2" id="modalSubject2Den">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher2Den">
                            <select class="form-select availability-check" name="den_teacher_id_2" id="modalTeacher2Den" data-type="teacher" data-mode="denominator" data-room-target="#modalRoom2Den">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalTeacher2Den"></div>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <select class="form-select availability-check" name="den_room_id_2" id="modalRoom2Den" data-type="room" data-mode="denominator" data-teacher-target="#modalTeacher2Den">
                                <option value="">—</option>
                                @foreach(($rooms ?? collect()) as $room)
                                    <option value="{{ $room->code }}">{{ $room->code }}</option>
                                @endforeach
                            </select>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm mt-2 js-suggest-room"
                                data-teacher-target="#modalTeacher2Den"
                                data-room-target="#modalRoom2Den"
                                data-mode="denominator"
                            >
                                Подставить свободный
                            </button>
                            <div class="availability-note" data-status-for="modalRoom2Den"></div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle2Den">
                            <label class="form-check-label" for="modalReplacementToggle2Den">Замена (знаменатель, подгр. 2)</label>
                        </div>
                        <div id="replacementBlock2Den" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 2, знаменатель)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher2Den">
                            <select class="form-select mb-2 availability-check" name="replacement_teacher_id_2_den" id="modalReplacementTeacher2Den" data-type="teacher" data-mode="denominator">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <div class="availability-note" data-status-for="modalReplacementTeacher2Den"></div>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject2Den">
                            <select class="form-select mb-2" name="replacement_subject_id_2_den" id="modalReplacementSubject2Den">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}" data-group="{{ $subjectGroupTypes[$id] ?? 'both' }}" data-title-ru="{{ $title }}" data-title-kz="{{ $subjectsKz[$id] ?? $title }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_2_den" id="modalReplacementComment2Den" placeholder="Комментарий">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-outline-danger" type="button" id="modalDelete">Удалить пару</button>
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
</div>

{{-- ─── Модал: Журнал замен ────────────────────────────────────────────────── --}}
<div id="replacementsModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);overflow-y:auto">
    <div style="max-width:700px;margin:48px auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <div>
                <h3 style="margin:0;font-size:1.1rem;font-weight:700"><i class="bi bi-arrow-left-right"></i> Замены на неделю</h3>
                <div id="replacementsSubtitle" style="font-size:12px;color:#64748b;margin-top:2px"></div>
            </div>
            <button id="replacementsModalClose" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <div id="replacementsModalBody">
            <div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>
        </div>
    </div>
</div>

{{-- ─── Модал: Анализ расписания ──────────────────────────────────────────── --}}
<div id="scheduleHealthModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);overflow-y:auto">
    <div style="max-width:640px;margin:48px auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="margin:0;font-size:1.1rem;font-weight:700">Анализ расписания</h3>
            <button id="healthModalClose" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <div id="healthModalBody">
            <div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>
        </div>
    </div>
</div>

{{-- ─── Модал: Перенос праздничных пар ───────────────────────────────────── --}}
<div id="holidayCompModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);overflow-y:auto">
    <div style="max-width:680px;margin:48px auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 8px 40px rgba(0,0,0,.18)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="margin:0;font-size:1.1rem;font-weight:700">Перенос праздничных пар</h3>
            <button id="compModalClose" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <div id="compModalBody">
            <div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const weekStart  = document.getElementById('weekStartInput')?.value ?? '';
    const courseEl   = document.getElementById('courseSelect');

    function getCourse() { return courseEl?.value ?? '1'; }

    // ─── Health modal ───────────────────────────────────────────────────────
    const healthBtn   = document.getElementById('scheduleHealthBtn');
    const healthModal = document.getElementById('scheduleHealthModal');
    const healthBody  = document.getElementById('healthModalBody');
    const healthClose = document.getElementById('healthModalClose');

    const SEVERITY_COLOR = { danger: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const SEVERITY_ICON  = { danger: '🔴', warning: '🟡', info: '🔵' };
    const TYPE_LABEL = {
        window:           'Окно в расписании',
        overload:         'Перегрузка группы',
        uniformity:       'Неравномерность нагрузки',
        teacher_overload: 'Перегрузка преподавателя',
        teacher_uniformity: 'Неравномерность (преп.)',
    };

    function renderHealth(data) {
        if (data.ok) {
            healthBody.innerHTML = '<div style="text-align:center;padding:32px;color:#22c55e;font-size:1.1rem;font-weight:600">✅ Расписание в норме — нарушений нет</div>';
            return;
        }
        const byType = {};
        data.warnings.forEach(w => {
            const key = w.type;
            if (!byType[key]) byType[key] = [];
            byType[key].push(w);
        });

        let html = '';
        // Summary chips
        html += '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">';
        if (data.counts.danger)  html += `<span style="padding:4px 12px;border-radius:999px;background:#fee2e2;color:#991b1b;font-size:12px;font-weight:600">🔴 Критичных: ${data.counts.danger}</span>`;
        if (data.counts.warning) html += `<span style="padding:4px 12px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:12px;font-weight:600">🟡 Предупреждений: ${data.counts.warning}</span>`;
        if (data.counts.info)    html += `<span style="padding:4px 12px;border-radius:999px;background:rgba(127,86,217,0.1);color:#6941c6;font-size:12px;font-weight:600">Замечаний: ${data.counts.info}</span>`;
        html += '</div>';

        Object.entries(byType).forEach(([type, items]) => {
            const sev = items[0].severity;
            const color = SEVERITY_COLOR[sev] ?? '#64748b';
            html += `<div style="margin-bottom:14px">
                <div style="font-size:12px;font-weight:700;color:${color};text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px">
                    ${SEVERITY_ICON[sev] ?? ''} ${TYPE_LABEL[type] ?? type} (${items.length})
                </div>
                <div style="display:flex;flex-direction:column;gap:4px">`;
            items.forEach(w => {
                html += `<div style="padding:8px 12px;background:#f8fafc;border-left:3px solid ${color};border-radius:4px;font-size:13px">${w.message}</div>`;
            });
            html += '</div></div>';
        });

        healthBody.innerHTML = html;
    }

    function openHealthModal() {
        healthModal.style.display = 'block';
        healthBody.innerHTML = '<div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>';

        const url = new URL(healthBtn.dataset.healthUrl, location.href);
        url.searchParams.set('week_start', weekStart);
        url.searchParams.set('course', getCourse());

        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                renderHealth(data);
                // Update badge
                const badge = document.getElementById('healthBadge');
                const total = (data.counts?.danger ?? 0) + (data.counts?.warning ?? 0);
                if (total > 0) {
                    badge.textContent = total;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {
                healthBody.innerHTML = '<div style="color:#ef4444;padding:16px">Ошибка загрузки данных</div>';
            });
    }

    healthBtn?.addEventListener('click', openHealthModal);
    healthClose?.addEventListener('click', () => { healthModal.style.display = 'none'; });
    healthModal?.addEventListener('click', e => { if (e.target === healthModal) healthModal.style.display = 'none'; });

    // ── Журнал замен ────────────────────────────────────────────────────────
    const replBtn   = document.getElementById('replacementsJournalBtn');
    const replModal = document.getElementById('replacementsModal');
    const replBody  = document.getElementById('replacementsModalBody');
    const replClose = document.getElementById('replacementsModalClose');
    const replSub   = document.getElementById('replacementsSubtitle');
    const replBadge = document.getElementById('replacementsBadge');

    const DAY_ICONS = { 'Понедельник':'Пн','Вторник':'Вт','Среда':'Ср','Четверг':'Чт','Пятница':'Пт','Суббота':'Сб' };

    function renderReplacements(data) {
        if (!replBody) return;
        if (data.count === 0) {
            replBody.innerHTML = '<div style="text-align:center;padding:32px;color:#22c55e;font-size:1rem;font-weight:600">✅ Замен на эту неделю нет</div>';
            return;
        }
        if (replSub) replSub.textContent = `Всего ${data.count} замен`;
        if (replBadge) { replBadge.textContent = data.count; replBadge.style.display = 'block'; }

        let currentDay = null;
        let html = '<div style="display:flex;flex-direction:column;gap:6px">';
        data.items.forEach(item => {
            if (item.day !== currentDay) {
                currentDay = item.day;
                html += `<div style="font-size:11px;font-weight:700;color:#7f56d9;text-transform:uppercase;letter-spacing:.5px;margin-top:10px;margin-bottom:4px">${item.day}</div>`;
            }
            html += `<div style="display:grid;grid-template-columns:40px 1fr 1fr 1fr;gap:8px;align-items:center;padding:10px 14px;background:#f8fafc;border-radius:8px;font-size:13px">
                <div style="text-align:center;background:#ede9fe;color:#6941c6;border-radius:6px;padding:4px;font-weight:700;font-size:12px">${item.pair} пара</div>
                <div><div style="font-size:11px;color:#64748b">Группа</div><div style="font-weight:600">${item.group}</div></div>
                <div><div style="font-size:11px;color:#64748b">Отсутствует</div><div style="color:#ef4444">${item.absent_teacher}</div></div>
                <div><div style="font-size:11px;color:#64748b">Заменяет</div><div style="color:#15803d;font-weight:600">${item.repl_teacher}</div></div>
            </div>`;
        });
        html += '</div>';
        replBody.innerHTML = html;
    }

    function openReplacementsModal() {
        if (!replModal || !replBtn) return;
        replModal.style.display = 'block';
        if (replBody) replBody.innerHTML = '<div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>';

        const url = new URL(replBtn.dataset.url, location.href);
        url.searchParams.set('week_start', weekStart);
        url.searchParams.set('course', getCourse());
        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => renderReplacements(data))
            .catch(() => { if (replBody) replBody.innerHTML = '<div style="color:#ef4444;padding:16px">Ошибка загрузки</div>'; });
    }

    replBtn?.addEventListener('click', openReplacementsModal);
    replClose?.addEventListener('click', () => { if (replModal) replModal.style.display = 'none'; });
    replModal?.addEventListener('click', e => { if (e.target === replModal) replModal.style.display = 'none'; });

    // Загружаем бейдж замен при открытии страницы
    if (replBtn && weekStart) {
        const url = new URL(replBtn.dataset.url, location.href);
        url.searchParams.set('week_start', weekStart);
        url.searchParams.set('course', getCourse());
        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.count > 0 && replBadge) {
                    replBadge.textContent = data.count;
                    replBadge.style.display = 'block';
                }
            })
            .catch(() => {});
    }

    // Автоматически подгружаем бейдж при загрузке страницы + тост
    if (healthBtn && weekStart) {
        const url = new URL(healthBtn.dataset.healthUrl, location.href);
        url.searchParams.set('week_start', weekStart);
        url.searchParams.set('course', getCourse());
        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('healthBadge');
                const danger  = data.counts?.danger  ?? 0;
                const warning = data.counts?.warning ?? 0;
                const total   = danger + warning;
                if (total > 0 && badge) {
                    badge.textContent = total;
                    badge.style.display = 'block';
                    // Показываем тост только при критичных
                    if (danger > 0) showConflictToast(danger, warning);
                }
            })
            .catch(() => {});
    }

    function showConflictToast(danger, warning) {
        const existing = document.getElementById('conflictToast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.id = 'conflictToast';
        toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;background:#1e293b;color:#fff;border-radius:12px;padding:12px 20px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:12px;box-shadow:0 8px 24px rgba(0,0,0,0.25);max-width:420px;animation:toastIn .25s ease';
        toast.innerHTML = `
            <span style="font-size:18px">🔴</span>
            <div>
                <div style="font-weight:700;margin-bottom:2px">Конфликты в расписании на эту неделю</div>
                <div style="opacity:.75;font-size:12px">${danger} критичных${warning ? `, ${warning} предупреждений` : ''} — нажмите «Анализ недели» для подробностей</div>
            </div>
            <button onclick="this.closest('#conflictToast').remove()" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;padding:0;margin-left:4px">×</button>`;
        document.body.appendChild(toast);
        setTimeout(() => toast?.remove(), 8000);
    }

    // ─── Holiday compensation modal ─────────────────────────────────────────
    const compBtn   = document.getElementById('holidayCompBtn');
    const compModal = document.getElementById('holidayCompModal');
    const compBody  = document.getElementById('compModalBody');
    const compClose = document.getElementById('compModalClose');

    function renderComp(data) {
        if (!data.has_holidays || !data.groups?.length) {
            compBody.innerHTML = '<div style="text-align:center;padding:32px;color:#22c55e;font-size:1.05rem">На этой неделе нет праздников с парами, которые нужно переносить.</div>';
            return;
        }

        const holNames = Object.values(data.holidays).join(', ');
        let html = `<div style="padding:10px 14px;background:#fef3c7;border-radius:8px;margin-bottom:16px;font-size:13px;color:#92400e">
            <strong>Праздники:</strong> ${holNames}<br>
            <span style="font-size:12px">Ниже указаны пары, которые выпали на праздник, и свободные слоты для переноса.</span>
        </div>`;

        data.groups.forEach(group => {
            html += `<div style="margin-bottom:18px">
                <div style="font-weight:700;font-size:14px;margin-bottom:8px;padding:4px 0;border-bottom:1px solid #e2e8f0">
                    ${group.group_name} — ${group.count} пар(ы)
                </div>`;

            group.pairs.forEach(pair => {
                const slots = pair.free_this_week.length ? pair.free_this_week : pair.free_next_week;
                const isNextWeek = pair.free_this_week.length === 0 && pair.free_next_week.length > 0;
                const noSlots = slots.length === 0;

                html += `<div style="padding:10px 12px;background:#f8fafc;border-radius:8px;margin-bottom:6px;border-left:3px solid #f59e0b">
                    <div style="font-size:13px;margin-bottom:6px">
                        <strong>${pair.holiday_day}</strong> (${pair.holiday_name}) — пара ${pair.lesson_number}:
                        ${pair.subject}${pair.teacher ? ' / ' + pair.teacher : ''}
                    </div>`;

                if (noSlots) {
                    html += `<div style="color:#ef4444;font-size:12px">⚠ Нет свободных слотов ни на этой, ни на следующей неделе</div>`;
                } else {
                    html += `<div style="font-size:12px;color:#64748b;margin-bottom:4px">${isNextWeek ? '📅 Следующая неделя:' : '📅 Эта неделя:'}</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">`;
                    slots.forEach(s => {
                        html += `<span style="padding:3px 10px;border-radius:6px;background:rgba(127,86,217,0.08);color:#6941c6;font-size:12px;font-weight:600">${s.day}, пара ${s.lesson} (${s.date})</span>`;
                    });
                    html += `</div>`;
                }
                html += `</div>`;
            });

            html += `</div>`;
        });

        compBody.innerHTML = html;
    }

    function openCompModal() {
        compModal.style.display = 'block';
        compBody.innerHTML = '<div style="text-align:center;padding:24px;color:#64748b">Загрузка...</div>';

        const baseUrl = compBtn?.dataset.holidayUrl ?? healthBtn?.dataset.holidayUrl;
        if (!baseUrl) return;
        const url = new URL(baseUrl, location.href);
        url.searchParams.set('week_start', weekStart);
        url.searchParams.set('course', getCourse());

        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(renderComp)
            .catch(() => {
                compBody.innerHTML = '<div style="color:#ef4444;padding:16px">Ошибка загрузки данных</div>';
            });
    }

    compBtn?.addEventListener('click', openCompModal);
    compClose?.addEventListener('click', () => { compModal.style.display = 'none'; });
    compModal?.addEventListener('click', e => { if (e.target === compModal) compModal.style.display = 'none'; });
})();
</script>
@endpush
@endpush

@push('scripts')
<script>
(function() {
    var s = document.createElement('script');
    s.src = window.location.pathname.indexOf('/schedule/day') !== -1
        ? '{{ asset("js/tours/schedule-day.js") }}'
        : '{{ asset("js/tours/schedule-index.js") }}';
    document.head.appendChild(s);
})();
</script>
@endpush
