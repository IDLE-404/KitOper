@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
@endpush

@section('content')
@php
    $days = ['Понедельник','Вторник','Среда','Четверг','Пятница'];
    $itemsByGroup = collect($schedule ?? [])->all();
@endphp

<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Расписание — 1 курс</h1>
            <p class="page-subtitle">Компактный обзор по всем группам</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.create') }}" class="btn-pill primary">Добавить запись</a>
            <a href="{{ route('first.schedule.week') }}" class="btn-pill ghost">Редактор недели</a>
        </div>
    </div>

    <div class="groups-compact">
        @foreach($itemsByGroup as $groupName => $groupItems)
        <div class="group-compact">
            <div class="group-compact__head">
                <h2 class="group-compact__title">Группа: {{ $groupName }}</h2>
                <a href="{{ route('first.schedule.week') }}" class="link-edit">Редактировать</a>
            </div>
            <div class="grid-table">
                <div class="grid-row grid-head">
                    <div class="grid-cell day-col"></div>
                    @for($i = 1; $i <= 5; $i++)
                        <div class="grid-cell col-head">Пара {{ $i }}</div>
                    @endfor
                </div>
                @foreach($days as $day)
                    <div class="grid-row">
                        <div class="grid-cell day-col">{{ $day }}</div>
                        @for($i = 1; $i <= 5; $i++)
                            @php $pair = $groupItems[$day][$i] ?? null; @endphp
                            <div class="grid-cell pair-cell">
                                <a href="{{ route('first.schedule.week') }}" class="cell-edit" title="Редактировать">✏️</a>
                                <div class="cell-line">
                                    <span class="badge-num">{{ $i }}</span>
                                    <span class="cell-title">{{ $pair['sub1']['subject'] ?? '—' }}</span>
                                </div>
                                <div class="cell-meta">
                                    <span>Преподаватель: {{ $pair['sub1']['teacher'] ?? '—' }}</span>
                                    <span>Кабинет: {{ $pair['sub1']['room'] ?? '—' }}</span>
                                    <span>Подгруппа: {{ $pair['sub1']['label'] ?? '—' }}</span>
                                    @if(!empty($pair['sub2']))
                                        <span class="text-strong">2 подгруппа</span>
                                        <span>{{ $pair['sub2']['subject'] ?? '—' }}</span>
                                        <span>Преподаватель: {{ $pair['sub2']['teacher'] ?? '—' }}</span>
                                        <span>Кабинет: {{ $pair['sub2']['room'] ?? '—' }}</span>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
