@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
<style>
    .teacher-grid {
        grid-template-columns: minmax(160px, 1fr) repeat(7, minmax(140px, 1fr));
    }
    .teacher-grid .pair-cell {
        min-height: 80px;
    }
    .teacher-occupancy-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding-bottom: 6px;
        border-bottom: 1px dashed #e5e7eb;
        margin-bottom: 6px;
    }
    .teacher-occupancy-link {
        color: inherit;
        text-decoration: none;
        display: block;
    }
    .teacher-occupancy-link:hover .cell-title {
        text-decoration: underline;
    }
    .teacher-occupancy-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Занятость преподавателей</h1>
            <p class="page-subtitle">Неделя с {{ $weekStartLabel }}</p>
        </div>
        <form method="GET" class="action-buttons">
            <input type="search" id="teacherSearch" class="search-input" placeholder="Поиск по преподавателю, предмету, группе">
            <input type="date" name="week_start" value="{{ $weekStart }}" class="search-input" style="min-width: 180px;">
            <button type="submit" class="btn-pill ghost">Показать</button>
            <a href="{{ route('first.schedule.index') }}" class="btn-pill ghost">Расписание</a>
        </form>
    </div>

    <div class="groups-compact">
        @forelse($teachers as $teacher)
            <div class="group-compact">
                <div class="group-compact__head">
                    <h2 class="group-compact__title">{{ $teacher }}</h2>
                </div>
                <div class="grid-table teacher-grid">
                    <div class="grid-row grid-head">
                        <div class="grid-cell day-col"></div>
                        @for($lesson = 1; $lesson <= 7; $lesson++)
                            <div class="grid-cell col-head">Пара {{ $lesson }}</div>
                        @endfor
                    </div>
                    @foreach($days as $day)
                        <div class="grid-row">
                            <div class="grid-cell day-col">{{ $day }}</div>
                            @for($lesson = 1; $lesson <= 7; $lesson++)
                                @php
                                    $items = $occupancy[$teacher][$day][$lesson] ?? [];
                                @endphp
                                <div class="grid-cell pair-cell {{ $items ? 'filled' : 'empty' }}">
                                    @if($items)
                                        @foreach($items as $item)
                                            <div class="teacher-occupancy-item">
                                                <a
                                                    class="teacher-occupancy-link"
                                                    href="{{ route('first.schedule.index', ['course' => $item['course'], 'group_id' => $item['group_id'], 'day' => $day, 'lesson' => $lesson, 'week_start' => $weekStart]) }}"
                                                >
                                                    <div class="cell-line">
                                                        <span class="cell-title">{{ $item['subject'] }}</span>
                                                    </div>
                                                    <div class="cell-meta">
                                                        <span class="pill"><span>👥</span>{{ $item['group'] }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="cell-line">
                                            <span class="cell-title">—</span>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="group-compact">
                <div class="group-compact__head">
                    <h2 class="group-compact__title">Нет данных для выбранной недели</h2>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('teacherSearch');
    const teacherCards = Array.from(document.querySelectorAll('.group-compact'));

    const matchesQuery = (element, query) => {
        if (!query) {
            return true;
        }
        return element.textContent.toLowerCase().includes(query);
    };

    searchInput?.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        teacherCards.forEach(card => {
            card.style.display = matchesQuery(card, query) ? '' : 'none';
        });
    });
</script>
@endpush
