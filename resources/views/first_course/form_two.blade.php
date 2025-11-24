@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/form-two.css') }}">
@endpush

@section('content')
@php
    $days = range(1, 31);
    $rows = [
        [
            'subject' => 'Русский язык',
            'teacher' => 'Ахменова А.Е.',
            'start' => 62,
            'hours' => [2 => '2g', 12 => '2g', 14 => '2g', 19 => '2g', 26 => '2y', 28 => '2g'],
            'total' => 14,
            'left' => 48,
        ],
        [
            'subject' => 'Иностранный язык',
            'teacher' => 'Бралина М.Д.',
            'start' => 44,
            'hours' => [5 => '2g', 15 => '2y', 20 => '2g', 22 => '2g', 28 => '2g'],
            'total' => 10,
            'left' => 34,
        ],
        [
            'subject' => 'Физическая культура',
            'teacher' => 'Окенова Р.Н.',
            'start' => 60,
            'hours' => [3 => '2g', 8 => '2g', 12 => '2y', 19 => '2g', 22 => '2g', 25 => '2g', 28 => '2g'],
            'total' => 14,
            'left' => 46,
        ],
    ];
@endphp

<div class="form2-shell">
    <div class="form2-head">
        <div>
            <p class="overline">Ведомость учета учебного времени преподавателей (в часах)</p>
            <h1>Форма 2 — 1 курс</h1>
            <p class="muted">Колледж информационных технологий • Учёт по месяцам и группам</p>
        </div>
        <div class="meta-grid">
            <label class="field">
                <span>Группа</span>
                <select>
                    @foreach($groups as $g)
                        <option>{{ $g->group_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Месяц</span>
                <input type="text" value="Сентябрь">
            </label>
            <label class="field">
                <span>Год</span>
                <input type="text" value="2025">
            </label>
            <label class="field">
                <span>Курс</span>
                <input type="text" value="1">
            </label>
            <label class="field">
                <span>Недель</span>
                <input type="text" value="20">
            </label>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="form2-table">
            <thead>
                <tr>
                    <th class="col-num">№</th>
                    <th class="col-subject">Пән / предмет</th>
                    <th class="col-teacher">Оқытушының аты-жөні / Ф.И.О. преподавателя</th>
                    <th class="col-start">Норматив часов (начало)</th>
                    @foreach($days as $d)
                        <th class="col-day">{{ $d }}</th>
                    @endforeach
                    <th class="col-total">Итого часов</th>
                    <th class="col-left">Остаток часов</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $idx => $row)
                <tr>
                    <td class="col-num">{{ $idx + 1 }}</td>
                    <td class="col-subject editable">{{ $row['subject'] }}</td>
                    <td class="col-teacher editable">{{ $row['teacher'] }}</td>
                    <td class="col-start editable">{{ $row['start'] }}</td>
                    @foreach($days as $d)
                        @php $val = $row['hours'][$d] ?? ''; @endphp
                        <td class="day-cell {{ str_contains($val,'g') ? 'green' : (str_contains($val,'y') ? 'yellow' : '') }}">
                            <span class="{{ str_contains($val,'r') ? 'red' : '' }}">{{ $val ? preg_replace('/[a-z]/','',$val) : '' }}</span>
                        </td>
                    @endforeach
                    <td class="col-total editable">{{ $row['total'] }}</td>
                    <td class="col-left editable">{{ $row['left'] }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right fw-bold">Итого</td>
                    <td class="fw-bold">{{ collect($rows)->sum('start') }}</td>
                    @foreach($days as $d)
                        <td class="fw-bold">{{ '' }}</td>
                    @endforeach
                    <td class="fw-bold">{{ collect($rows)->sum('total') }}</td>
                    <td class="fw-bold">{{ collect($rows)->sum('left') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
