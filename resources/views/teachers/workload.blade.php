@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
<style>
    .workload-shell {
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --ink: #0f172a;
        --muted: #64748b;
        --grid: #e2e8f0;
        --border: #d7dee8;
        --busy: #e0ecff;
        --busy-alt: #e6f4f0;
        --free: #f8fafc;
        --accent: #1d4ed8;
        --day-col-width: clamp(150px, 14vw, 220px);
        --lesson-col-width: clamp(72px, 6vw, 110px);
        --teacher-col-width: clamp(150px, 12vw, 220px);
        font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 18px 24px 40px;
    }

    .workload-shell .page-title {
        font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
        font-weight: 700;
        font-size: 30px;
        color: var(--ink);
        margin: 0;
    }

    .workload-shell .page-subtitle {
        color: var(--muted);
        margin: 6px 0 0;
        font-size: 14px;
    }

    .workload-header {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .workload-shell .action-buttons {
        gap: 10px;
    }

    .workload-shell .search-input {
        border-radius: 10px;
        border: 1px solid var(--border);
        box-shadow: none;
        background: #fff;
        min-width: 220px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .workload-shell .btn-pill.ghost {
        background: #eff4ff;
        color: #1e3a8a;
    }

    .workload-shell .btn-pill.ghost:hover {
        background: #e2ecff;
    }

    .workload-search {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 12px 14px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 14px;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.04);
        max-width: 420px;
    }

    .search-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
    }

    .search-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .workload-surface {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.04);
    }

    .workload-table-wrap {
        overflow: auto;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid var(--grid);
    }

    .workload-table {
        width: 100%;
        min-width: max-content;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--ink);
        font-size: 13px;
    }

    .workload-table th,
    .workload-table td {
        border-right: 1px solid var(--grid);
        border-bottom: 1px solid var(--grid);
        padding: 10px 10px;
        vertical-align: top;
        background: #fff;
    }

    .workload-table thead th {
        position: sticky;
        top: 0;
        background: #f1f5f9;
        z-index: 3;
        text-align: center;
        font-weight: 600;
        color: #0f172a;
        box-shadow: inset 0 -1px 0 var(--grid);
    }

    .workload-table thead th.teacher-head {
        min-width: var(--teacher-col-width);
        max-width: var(--teacher-col-width);
    }

    .workload-table .sticky-left {
        position: sticky;
        left: 0;
        z-index: 4;
        background: #f8fafc;
    }

    .workload-table .sticky-left.lesson-col {
        left: var(--day-col-width);
        z-index: 4;
        background: #f8fafc;
    }

    .workload-table thead .sticky-left {
        z-index: 5;
    }

    .day-col {
        width: var(--day-col-width);
        min-width: var(--day-col-width);
        max-width: var(--day-col-width);
        text-align: left;
        font-weight: 600;
        color: #0f172a;
    }

    .lesson-col {
        width: var(--lesson-col-width);
        min-width: var(--lesson-col-width);
        max-width: var(--lesson-col-width);
        text-align: center;
        font-weight: 600;
        color: #334155;
    }

    .day-name {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
    }

    .lesson-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 6px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e8edf7;
        color: #1e293b;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.2px;
    }

    .teacher-name {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        text-align: center;
    }

    .teacher-initials {
        display: block;
        font-size: 11px;
        color: var(--muted);
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .workload-cell {
        min-width: var(--teacher-col-width);
        max-width: var(--teacher-col-width);
        background: #fff;
    }

    .workload-cell.filled {
        background: linear-gradient(180deg, var(--busy), #f5f7ff);
    }

    .workload-cell.free {
        background: var(--free);
        color: #94a3b8;
        text-align: center;
        font-weight: 500;
    }

    .workload-cell.cell--split {
        background: linear-gradient(135deg, rgba(224, 236, 255, 0.95) 0 49%, rgba(230, 244, 240, 0.95) 50 100%);
    }

    .cell-stack {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .cell-block {
        display: block;
        text-decoration: none;
        color: inherit;
        padding: 8px 9px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        border: 1px solid #d6e0ee;
        border-left: 3px solid #7aa2e3;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .cell-block.subgroup-2 {
        border-left-color: #5aa38f;
    }

    .cell-block:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 16px rgba(15, 23, 42, 0.08);
    }

    .cell-subject {
        font-weight: 600;
        font-size: 13px;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .cell-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
        color: var(--muted);
    }

    .cell-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eef2f7;
        color: #334155;
        font-weight: 500;
    }

    .cell-pill.subgroup {
        background: #e7f3ef;
        color: #2f6658;
        font-weight: 600;
    }

    .cell-free {
        font-size: 12px;
        letter-spacing: 0.3px;
    }

    .workload-table tr.is-hover th,
    .workload-table tr.is-hover td {
        background: #f1f5fb;
    }

    .workload-table .is-col-hover {
        box-shadow: inset 0 0 0 999px rgba(120, 150, 204, 0.08);
    }

    .workload-table .is-hidden {
        display: none;
    }
</style>
@endpush

@section('content')
@php
    $initialsFor = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($part, 0, 1)) . '.';
        }
        return $initials;
    };
@endphp

<div class="workload-shell">
    <div class="workload-header">
        <div>
            <h1 class="page-title">Занятость преподавателей</h1>
            <p class="page-subtitle">Неделя с {{ $weekStartLabel }}</p>
        </div>
        <form method="GET" class="action-buttons">
            <input type="date" name="week_start" value="{{ $weekStart }}" class="search-input" style="min-width: 180px;">
            <button type="submit" class="btn-pill ghost">Показать</button>
            <a href="{{ route('first.schedule.index') }}" class="btn-pill ghost">Расписание</a>
        </form>
    </div>

    <div class="workload-search">
        <label class="search-label" for="teacherSearch">Поиск преподавателя по фамилии</label>
        <div class="search-row">
            <input type="search" id="teacherSearch" class="search-input" placeholder="Например: Иванов">
        </div>
    </div>

    @if(empty($teachers))
        <div class="workload-surface">
            <div class="workload-table-wrap">
                <div class="cell-free" style="padding: 18px; text-align: center;">Нет данных для выбранной недели</div>
            </div>
        </div>
    @else
        <div class="workload-surface">
            <div class="workload-table-wrap">
                <table class="workload-table" id="workloadTable">
                    <thead>
                        <tr>
                            <th class="sticky-left day-col">День</th>
                            <th class="sticky-left lesson-col">Пара</th>
                            @foreach($teachers as $tIndex => $teacher)
                                @php $initials = $initialsFor($teacher); @endphp
                                <th class="teacher-head" data-col="{{ $tIndex }}">
                                    <span class="teacher-name">{{ $teacher }}</span>
                                    <span class="teacher-initials">{{ $initials }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            @for($lesson = 1; $lesson <= 7; $lesson++)
                                <tr data-day="{{ $day }}" data-lesson="{{ $lesson }}">
                                    @if($lesson === 1)
                                        <th class="sticky-left day-col" rowspan="7">
                                            <span class="day-name">{{ $day }}</span>
                                        </th>
                                    @endif
                                    <th class="sticky-left lesson-col">
                                        <span class="lesson-chip">Пара {{ $lesson }}</span>
                                    </th>
                                    @foreach($teachers as $tIndex => $teacher)
                                        @php
                                            $items = $occupancy[$teacher][$day][$lesson] ?? [];
                                            $subgroups = collect($items)->pluck('subgroup')->filter()->unique()->values();
                                            $isSplit = $subgroups->count() > 1;
                                        @endphp
                                        <td class="workload-cell {{ $items ? 'filled' : 'free' }} {{ $isSplit ? 'cell--split' : '' }}" data-col="{{ $tIndex }}">
                                            @if($items)
                                                <div class="cell-stack">
                                                    @foreach($items as $item)
                                                        <a
                                                            class="cell-block {{ !empty($item['subgroup']) && (int) $item['subgroup'] === 2 ? 'subgroup-2' : '' }}"
                                                            href="{{ route('first.schedule.index', ['course' => $item['course'], 'group_id' => $item['group_id'], 'day' => $day, 'lesson' => $lesson, 'week_start' => $weekStart]) }}"
                                                        >
                                                            <div class="cell-subject">{{ $item['subject'] }}</div>
                                                            <div class="cell-meta">
                                                                <span class="cell-pill">Гр. {{ $item['group'] }}</span>
                                                                @if(!empty($item['subgroup']))
                                                                    <span class="cell-pill subgroup">Подгр. {{ $item['subgroup'] }}</span>
                                                                @endif
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="cell-free">Свободно</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('teacherSearch');
    const table = document.getElementById('workloadTable');
    const headerCells = table ? Array.from(table.querySelectorAll('th[data-col]')) : [];

    const setColumnVisibility = (columnIndex, isVisible) => {
        if (!table) {
            return;
        }
        table.querySelectorAll(`[data-col="${columnIndex}"]`).forEach(cell => {
            cell.classList.toggle('is-hidden', !isVisible);
        });
    };

    const matchesQuery = (text, query) => {
        if (!query) {
            return true;
        }
        return text.toLowerCase().includes(query);
    };

    const filterColumns = (query) => {
        if (!table) {
            return;
        }
        headerCells.forEach(header => {
            const columnIndex = header.dataset.col;
            const headerText = header.textContent || '';
            const isVisible = matchesQuery(headerText, query);
            setColumnVisibility(columnIndex, isVisible);
        });
    };

    searchInput?.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        filterColumns(query);
    });

    if (table) {
        const clearHover = () => {
            table.querySelectorAll('tr.is-hover').forEach(row => row.classList.remove('is-hover'));
            table.querySelectorAll('.is-col-hover').forEach(cell => cell.classList.remove('is-col-hover'));
        };

        table.querySelectorAll('[data-col]').forEach(cell => {
            cell.addEventListener('mouseenter', () => {
                clearHover();
                const col = cell.dataset.col;
                cell.closest('tr')?.classList.add('is-hover');
                table.querySelectorAll(`[data-col="${col}"]`).forEach(colCell => colCell.classList.add('is-col-hover'));
            });
        });

        table.addEventListener('mouseleave', clearHover);
    }
</script>
@endpush
