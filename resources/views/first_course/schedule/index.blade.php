@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/schedule.css') }}">
@endpush

@section('content')
<div class="container">

    <h2 class="mb-4">Расписание — 1 курс</h2>

    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('first.schedule.create') }}" class="btn btn-primary">Добавить запись</a>
        <a href="{{ route('first.schedule.week') }}" class="btn btn-outline-primary">Редактор недели</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>День</th>
                <th>Пара</th>
                <th>Группа</th>
                <th>Предмет</th>
                <th>Преподаватель</th>
                <th>Аудитория</th>
                <th>Подгруппа</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $row)
            <tr>
                <td>{{ $row->study_day }}</td>
                <td>{{ $row->lesson_number }}</td>
                <td>{{ $row->group_name ?? '-' }}</td>
                <td>{{ $row->subject_name_ru ?? $row->subject_fallback ?? '-' }}</td>
                <td>{{ $row->teacher_name ?? '-' }}</td>
                <td>{{ $row->room_id ?? '-' }}</td>
                <td>{{ $row->subgroup ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
