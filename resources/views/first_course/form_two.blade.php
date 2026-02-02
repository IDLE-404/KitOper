@extends('layouts.app')

@section('content')
@php
    $months = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];
    $daysCount = count($days ?? []);
    $replacementRows = $replacementRows ?? [];
    $replacementTableRows = $replacementTableRows ?? [];
    $replacementDayTotals = $replacementDayTotals ?? [];
    $replacementColumnTotals = $replacementColumnTotals ?? ['normative' => 0, 'used' => 0, 'bonus' => 0, 'left' => 0];
    $subgroupTwoRows = $subgroupTwoRows ?? [];
    $subgroupTwoDayTotals = $subgroupTwoDayTotals ?? [];
    $subgroupTwoColumnTotals = $subgroupTwoColumnTotals ?? ['normative' => 0, 'used' => 0, 'bonus' => 0, 'left' => 0];
    $hasSubgroups = $hasSubgroups ?? false;
    $subjects = $subjects ?? collect();
    $dayTotals = $dayTotals ?? [];
    $columnTotals = $columnTotals ?? ['normative' => 0, 'used' => 0, 'bonus' => 0, 'left' => 0];
    $holidayDays = $holidayDays ?? [];
    
    // Определяем выходные дни (суббота и воскресенье)
    $weekendDays = [];
    if (!empty($days) && isset($month) && isset($year)) {
        foreach ($days as $day) {
            $date = \Carbon\Carbon::create($year, $month, $day);
            $dayOfWeek = $date->dayOfWeek; // 0 = воскресенье, 6 = суббота
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                $weekendDays[$day] = true;
            }
        }
    }
@endphp

<div class="container-fluid form-two-container py-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Форма 2 — 1 курс</h1>
            <div class="text-muted">Отчёт по фактическим занятиям за месяц</div>
        </div>
        <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-outline-secondary">← Расписание</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Курс</label>
                    <select class="form-select" id="courseSelect">
                        @for($c = 1; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Группа</label>
                    <select class="form-select" id="groupSelect">
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected($groupId === $g->id)>{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Месяц</label>
                    <select class="form-select" id="monthSelect">
                        @foreach($months as $num => $label)
                            <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small mb-1">Год</label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="yearPrevBtn" type="button">Предыдущий</button>
                        <span class="fw-semibold" id="yearLabel">{{ $year }}</span>
                        <button class="btn btn-outline-secondary btn-sm" id="yearNextBtn" type="button">Следующий</button>
                    </div>
                    <input type="hidden" id="yearInput" value="{{ $year }}">
                </div>
                <div class="col-12 text-end ok-row">
                    <button class="btn btn-primary btn-sm" id="reloadBtn">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center legend-row">
            <div class="legend-item">
                <span class="status-chip status-normal">2</span>
                <span class="text-muted ms-2 small">Пара проведена</span>
            </div>
            <div class="legend-item">
                <span class="status-chip status-replaced">■</span>
                <span class="text-muted ms-2 small">Пара заменена другим предметом</span>
            </div>
            <div class="legend-item">
                <span class="status-chip status-replacement">2</span>
                <span class="text-muted ms-2 small">Замена (бонус часов)</span>
            </div>
            <div class="legend-item">
                <span class="status-chip status-empty">•</span>
                <span class="text-muted ms-2 small">Нет занятия</span>
            </div>
            <div class="legend-item">
                <span class="status-chip holiday-chip">П</span>
                <span class="text-muted ms-2 small">Национальный праздник</span>
            </div>
            <div class="ms-auto text-muted small">
                Статусы поступают из расписания. Ручная коррекция — только в исключительных случаях.
            </div>
        </div>
    </div>

    @php
        $semester1Year = $month >= 9 ? $year : ($year - 1);
        $semester2Year = $month >= 9 ? ($year + 1) : $year;
    @endphp
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                <div>
                    <div class="fw-semibold">Группа: {{ optional($groups->firstWhere('id', $groupId))->group_name ?? '—' }}</div>
                    <div class="text-muted small">{{ $months[$month] ?? $month }} {{ $year }}</div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('first.schedule.form_two.export', ['group_id' => $groupId, 'month' => $month, 'year' => $year, 'course' => $course]) }}"
                       class="btn btn-outline-primary btn-sm">
                        📊 Экспорт в Excel
                    </a>
                    <a href="{{ route('first.schedule.form_two.export_semester', ['group_id' => $groupId, 'semester' => 1, 'year' => $semester1Year, 'course' => $course]) }}"
                       class="btn btn-outline-primary btn-sm">
                        📘 Экспорт 1 семестр
                    </a>
                    <a href="{{ route('first.schedule.form_two.export_semester', ['group_id' => $groupId, 'semester' => 2, 'year' => $semester2Year, 'course' => $course]) }}"
                       class="btn btn-outline-primary btn-sm">
                        📗 Экспорт 2 семестр
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" id="semester2Btn">Ко 2 семестру</button>
                    <input class="form-check-input d-none" type="checkbox" id="manualToggle">
                    <button class="btn btn-outline-secondary btn-sm js-correction-toggle" type="button">Режим коррекции</button>
                    <button class="btn btn-success btn-sm d-none js-correction-save" id="saveBtn" type="button">Сохранить коррекцию</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle form-two-table">
                    <thead>
                        <tr>
                            <th class="text-muted col-index">#</th>
                            <th class="text-muted col-subject">Предмет</th>
                            <th class="text-muted col-teacher">Преподаватель</th>
                            <th class="text-muted col-norm">Часы</th>
                            @foreach($days as $d)
                                <th class="text-center text-muted day-head col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">{{ $d }}</th>
                            @endforeach
                            <th class="text-muted col-used">Использовано</th>
                            <th class="text-muted col-bonus">Бонус</th>
                            <th class="text-muted col-left">Остаток</th>
                        </tr>
                    </thead>
                    <tbody id="formBody">
                        @forelse($rows as $idx => $row)
                            <tr data-row="{{ $idx }}">
                                <td class="col-index">{{ $idx + 1 }}</td>
                                <td class="col-subject">
                                    <div class="fw-semibold">{{ $row['subject_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-subject" value="{{ $row['subject_id'] }}">
                                    <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                    <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                </td>
                                <td class="col-teacher">
                                    <div>{{ $row['teacher_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-teacher" value="{{ $row['teacher_id'] }}">
                                </td>
                                <td class="col-norm">
                                    <div class="small text-muted">{{ $row['hours_left_start'] ?? $row['total_hours'] ?? 0 }}</div>
                                    <div class="manual-norm d-none mt-2">
                                        <label class="form-label text-muted small mb-1">Всего часов</label>
                                        <input type="number"
                                               class="form-control form-control-sm row-total-hours-input"
                                               min="0"
                                               step="1"
                                               value="{{ $row['total_hours'] ?? 0 }}">
                                    </div>
                                </td>
                                @foreach($days as $d)
                                    @php
                                        $cell = $row['days'][$d] ?? [];
                                        $status = $cell['status'] ?? 'empty';
                                        $value = '';
                                        // Сводим старый статус sick к replaced, чтобы “болел” отображался как замена.
                                        if ($status === 'sick') {
                                            $status = 'replaced';
                                        }

                                        if ($status === 'normal') {
                                            $value = $cell['used_hours'] ?? $row['hours_per_class'] ?? '2';
                                        } elseif ($status === 'replacement') {
                                            $value = $cell['bonus_hours'] ?? $row['hours_per_class'] ?? '2';
                                        } elseif ($status === 'replaced') {
                                            $value = '■';
                                        } elseif ($status === 'sick') {
                                            $value = 'Б';
                                        } else {
                                            $value = '•';
                                        }
                                        $tooltip = collect($cell['details'] ?? [])->map(function ($detail) {
                                            $parts = [];
                                            if (!empty($detail['lesson_number'])) {
                                                $parts[] = 'Пара ' . $detail['lesson_number'];
                                            }
                                            if (!empty($detail['subgroup'])) {
                                                $parts[] = 'подгр. ' . $detail['subgroup'];
                                            }
                                            if (!empty($detail['mode'])) {
                                                $parts[] = 'режим: ' . $detail['mode'];
                                            }
                                            if (!empty($detail['status'])) {
                                                $parts[] = 'статус: ' . $detail['status'];
                                            }
                                            if (!empty($detail['replacement_teacher_name'])) {
                                                $parts[] = 'замена: ' . $detail['replacement_teacher_name'];
                                            }
                                            if (!empty($detail['replacement_subject_name'])) {
                                                $parts[] = 'предмет: ' . $detail['replacement_subject_name'];
                                            }
                                            return implode(', ', $parts);
                                        })->filter()->implode(' | ');
                                        $holidayMeta = $holidayDays[$d] ?? null;
                                        $holidayNote = $holidayMeta ? ('Праздник: ' . $holidayMeta['name']) : null;
                                        $titleParts = array_filter([$tooltip, $holidayNote]);
                                        $cellTitle = $titleParts ? implode(' | ', $titleParts) : 'Нет записи';
                                    @endphp
                                    <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">
                                        <div class="status-chip status-{{ $status }}" title="{{ $cellTitle }}">
                                            <span class="chip-value">{{ $value }}</span>
                                        </div>
                                        <div class="manual-status d-none mt-1">
                                            <select class="form-select form-select-sm cell-status" data-day="{{ $d }}" @disabled(isset($holidayDays[$d]))>
                                                <option value="empty" @selected($status === 'empty')>—</option>
                                                <option value="normal" @selected($status === 'normal')>Норма</option>
                                                <option value="replaced" @selected($status === 'replaced')>Замена (замещённая)</option>
                                                <option value="replacement" @selected($status === 'replacement')>Замена (замещающая)</option>
                                            </select>
                                            <select class="form-select form-select-sm cell-repl mt-1" data-day="{{ $d }}" @disabled(isset($holidayDays[$d]))>
                                                <option value="">— заменяющий</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected(($cell['replacement_teacher_id'] ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                            <select class="form-select form-select-sm cell-repl-subject mt-1" data-day="{{ $d }}" @disabled(isset($holidayDays[$d]))>
                                                <option value="">— замещающий предмет</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected(($cell['replacement_subject_id'] ?? null) == $s->id)>{{ $s->title }}</option>
                                                @endforeach
                                            </select>
                                            @if(isset($holidayDays[$d]))
                                                <div class="text-warning small mt-1">Редактирование отключено — {{ $holidayDays[$d]['name'] }}</div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                <td class="fw-semibold used-cell col-used">{{ $row['used_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-primary col-bonus">{{ $row['bonus_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-success col-left">{{ $row['hours_left'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 7 + $daysCount }}" class="text-center text-muted">Данных нет</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="column-totals-row">
                            <td colspan="3" class="text-end text-muted small">Итого:</td>
                            <td class="text-center totals-cell text-primary col-norm">{{ $columnTotals['normative'] ?? 0 }}</td>
                            @foreach($days as $d)
                                <td class="text-center totals-cell text-primary col-day">{{ $dayTotals[$d] ?? 0 }}</td>
                            @endforeach
                            <td class="fw-semibold col-used">{{ $columnTotals['used'] ?? 0 }}</td>
                            <td class="fw-semibold text-primary col-bonus">{{ $columnTotals['bonus'] ?? 0 }}</td>
                            <td class="fw-semibold text-success col-left">{{ $columnTotals['left'] ?? 0 }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                <div>
                    <div class="fw-semibold">Только замены преподавателей</div>
                    <div class="text-muted small">повторяет форму 2, но показывает только фактические подмены</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm js-correction-toggle" type="button">Режим коррекции</button>
                    <button class="btn btn-success btn-sm d-none js-correction-save" type="button">Сохранить коррекцию</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle form-two-table replacement-table">
                    <thead>
                        <tr>
                            <th class="text-muted col-index">#</th>
                            <th class="text-muted col-subject">Предмет</th>
                            <th class="text-muted col-teacher">Преподаватель</th>
                            <th class="text-muted col-norm">Часы</th>
                            @foreach($days as $d)
                                <th class="text-center text-muted day-head col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">{{ $d }}</th>
                            @endforeach
                            <th class="text-muted col-used">Использовано</th>
                            <th class="text-muted col-bonus">Бонус</th>
                            <th class="text-muted col-left">Остаток</th>
                        </tr>
                    </thead>
                    <tbody id="replacementTableBody">
                        @forelse($replacementTableRows as $idx => $row)
                            <tr data-row="{{ $idx }}">
                                <td class="col-index">{{ $idx + 1 }}</td>
                                <td class="col-subject">
                                    <div class="fw-semibold">{{ $row['subject_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-subject" value="{{ $row['subject_id'] }}">
                                    <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                    <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                </td>
                                <td class="col-teacher">
                                    <div>{{ $row['teacher_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-teacher" value="{{ $row['teacher_id'] }}">
                                </td>
                                <td class="col-norm">
                                    <div class="small text-muted">{{ $row['hours_left_start'] ?? $row['total_hours'] ?? 0 }}</div>
                                    <div class="manual-norm d-none mt-2">
                                        <label class="form-label text-muted small mb-1">Всего часов</label>
                                        <input type="number"
                                               class="form-control form-control-sm row-total-hours-input"
                                               min="0"
                                               step="1"
                                               value="{{ $row['total_hours'] ?? 0 }}">
                                    </div>
                                </td>
                                @foreach($days as $d)
                                    @php
                                        $cell = $row['days'][$d] ?? [];
                                        $status = $cell['status'] ?? 'empty';
                                        $value = '';
                                        if ($status === 'normal') {
                                            $value = $cell['used_hours'] ?? $row['hours_per_class'] ?? '2';
                                        } elseif ($status === 'replacement') {
                                            $value = $cell['bonus_hours'] ?? $row['hours_per_class'] ?? '2';
                                        } elseif ($status === 'replaced') {
                                            $value = '■';
                                        } elseif ($status === 'sick') {
                                            $value = 'Б';
                                        }
                                        $tooltip = collect($cell['details'] ?? [])->map(function ($detail) {
                                            $parts = [];
                                            if (!empty($detail['lesson_number'])) {
                                                $parts[] = 'Пара ' . $detail['lesson_number'];
                                            }
                                            if (!empty($detail['subgroup'])) {
                                                $parts[] = 'подгр. ' . $detail['subgroup'];
                                            }
                                            if (!empty($detail['mode'])) {
                                                $parts[] = 'режим: ' . $detail['mode'];
                                            }
                                            if (!empty($detail['status'])) {
                                                $parts[] = 'статус: ' . $detail['status'];
                                            }
                                            if (!empty($detail['replacement_teacher_name'])) {
                                                $parts[] = 'замена: ' . $detail['replacement_teacher_name'];
                                            }
                                            if (!empty($detail['replacement_subject_name'])) {
                                                $parts[] = 'предмет: ' . $detail['replacement_subject_name'];
                                            }
                                            return implode(', ', $parts);
                                        })->filter()->implode(' | ');
                                        $holidayMeta = $holidayDays[$d] ?? null;
                                        $holidayNote = $holidayMeta ? ('Праздник: ' . $holidayMeta['name']) : null;
                                        $titleParts = array_filter([$tooltip, $holidayNote]);
                                        $defaultTitle = $status === 'empty' ? '—' : 'Нет записи';
                                        $cellTitle = $titleParts ? implode(' | ', $titleParts) : $defaultTitle;
                                    @endphp
                                    <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">
                                        <div class="status-chip status-{{ $status }}" title="{{ $cellTitle }}">
                                            <span class="chip-value">{{ $status === 'empty' ? '' : $value }}</span>
                                        </div>
                                    </td>
                                @endforeach
                                <td class="fw-semibold used-cell col-used">{{ $row['used_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-primary col-bonus">{{ $row['bonus_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-success col-left">{{ $row['hours_left'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 7 + $daysCount }}" class="text-center text-muted">Данных нет</td></tr>
                        @endforelse
                    </tbody>
                <tfoot>
                    <tr class="column-totals-row">
                        <td colspan="3" class="text-end text-muted small">Итого:</td>
                        <td class="text-center totals-cell text-primary col-norm">{{ $replacementColumnTotals['normative'] ?? 0 }}</td>
                        @foreach($days as $d)
                            <td class="text-center totals-cell text-primary col-day">{{ $replacementDayTotals[$d] ?? 0 }}</td>
                        @endforeach
                        <td class="fw-semibold col-used">{{ $replacementColumnTotals['used'] ?? 0 }}</td>
                        <td class="fw-semibold text-primary col-bonus">{{ $replacementColumnTotals['bonus'] ?? 0 }}</td>
                        <td class="fw-semibold text-success col-left">{{ $replacementColumnTotals['left'] ?? 0 }}</td>
                    </tr>
                </tfoot>
                </table>
            </div>
        </div>
    </div>

    @if($hasSubgroups)
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                    <div>
                        <div class="fw-semibold">Подвоение (подгруппа 2)</div>
                        <div class="text-muted small">только записи со второй подгруппой</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm js-correction-toggle" type="button">Режим коррекции</button>
                        <button class="btn btn-success btn-sm d-none js-correction-save" type="button">Сохранить коррекцию</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle form-two-table">
                        <thead>
                            <tr>
                                <th class="text-muted col-index">#</th>
                                <th class="text-muted col-subject">Предмет</th>
                                <th class="text-muted col-teacher">Преподаватель</th>
                                <th class="text-muted col-norm">Часы</th>
                                @foreach($days as $d)
                                    <th class="text-center text-muted day-head col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">{{ $d }}</th>
                                @endforeach
                                <th class="text-muted col-used">Использовано</th>
                                <th class="text-muted col-bonus">Бонус</th>
                                <th class="text-muted col-left">Остаток</th>
                            </tr>
                        </thead>
                        <tbody id="subgroupTwoBody">
                            @forelse($subgroupTwoRows as $idx => $row)
                                <tr data-row="{{ $idx }}">
                                    <td class="col-index">{{ $idx + 1 }}</td>
                                    <td class="col-subject">
                                        <div class="fw-semibold">{{ $row['subject_name'] ?? '—' }}</div>
                                        <input type="hidden" class="row-subject" value="{{ $row['subject_id'] }}">
                                        <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                        <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                    </td>
                                    <td class="col-teacher">
                                        <div>{{ $row['teacher_name'] ?? '—' }}</div>
                                        <input type="hidden" class="row-teacher" value="{{ $row['teacher_id'] }}">
                                    </td>
                                    <td class="col-norm">
                                        <div class="small text-muted">{{ $row['hours_left_start'] ?? $row['total_hours'] ?? 0 }}</div>
                                        <div class="manual-norm d-none mt-2">
                                            <label class="form-label text-muted small mb-1">Всего часов</label>
                                            <input type="number"
                                                   class="form-control form-control-sm row-total-hours-input"
                                                   min="0"
                                                   step="1"
                                                   value="{{ $row['total_hours'] ?? 0 }}">
                                        </div>
                                    </td>
                                    @foreach($days as $d)
                                        @php
                                            $cell = $row['days'][$d] ?? [];
                                            $status = $cell['status'] ?? 'empty';
                                            $value = '';
                                            if ($status === 'sick') {
                                                $status = 'replaced';
                                            }
                                            if ($status === 'normal') {
                                                $value = $cell['used_hours'] ?? $row['hours_per_class'] ?? '2';
                                            } elseif ($status === 'replacement') {
                                                $value = $cell['bonus_hours'] ?? $row['hours_per_class'] ?? '2';
                                            } elseif ($status === 'replaced') {
                                                $value = '■';
                                            } else {
                                                $value = '•';
                                            }
                                            $tooltip = collect($cell['details'] ?? [])->map(function ($detail) {
                                                $parts = [];
                                                if (!empty($detail['lesson_number'])) {
                                                    $parts[] = 'Пара ' . $detail['lesson_number'];
                                                }
                                                if (!empty($detail['subgroup'])) {
                                                    $parts[] = 'подгр. ' . $detail['subgroup'];
                                                }
                                                if (!empty($detail['mode'])) {
                                                    $parts[] = 'режим: ' . $detail['mode'];
                                                }
                                                if (!empty($detail['status'])) {
                                                    $parts[] = 'статус: ' . $detail['status'];
                                                }
                                                if (!empty($detail['replacement_teacher_name'])) {
                                                    $parts[] = 'замена: ' . $detail['replacement_teacher_name'];
                                                }
                                                if (!empty($detail['replacement_subject_name'])) {
                                                    $parts[] = 'предмет: ' . $detail['replacement_subject_name'];
                                                }
                                                return implode(', ', $parts);
                                            })->filter()->implode(' | ');
                                            $holidayMeta = $holidayDays[$d] ?? null;
                                            $holidayNote = $holidayMeta ? ('Праздник: ' . $holidayMeta['name']) : null;
                                            $titleParts = array_filter([$tooltip, $holidayNote]);
                                            $cellTitle = $titleParts ? implode(' | ', $titleParts) : 'Нет записи';
                                        @endphp
                                        <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }}">
                                            <div class="status-chip status-{{ $status }}" title="{{ $cellTitle }}">
                                                <span class="chip-value">{{ $value }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="fw-semibold used-cell col-used">{{ $row['used_hours_total'] ?? 0 }}</td>
                                    <td class="fw-semibold text-primary col-bonus">{{ $row['bonus_hours_total'] ?? 0 }}</td>
                                    <td class="fw-semibold text-success col-left">{{ $row['hours_left'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ 7 + $daysCount }}" class="text-center text-muted">Данных нет</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="column-totals-row">
                                <td colspan="3" class="text-end text-muted small">Итого:</td>
                                <td class="text-center totals-cell text-primary col-norm">{{ $subgroupTwoColumnTotals['normative'] ?? 0 }}</td>
                                @foreach($days as $d)
                                    <td class="text-center totals-cell text-primary col-day">{{ $subgroupTwoDayTotals[$d] ?? 0 }}</td>
                                @endforeach
                                <td class="fw-semibold col-used">{{ $subgroupTwoColumnTotals['used'] ?? 0 }}</td>
                                <td class="fw-semibold text-primary col-bonus">{{ $subgroupTwoColumnTotals['bonus'] ?? 0 }}</td>
                                <td class="fw-semibold text-success col-left">{{ $subgroupTwoColumnTotals['left'] ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
</div>

@endsection

@push('styles')
<style>
    .form-two-container {
        width: 100%;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
    .form-two-container .card {
        border-radius: 12px;
    }
    .ok-row {
        margin-top: 0.5rem;
    }
    #reloadBtn {
        padding: 8px 16px;
    }
    .legend-row .status-chip {
        min-width: 32px;
    }
    .form-two-table .day-head {
        min-width: 54px;
    }
    .form-two-table {
        table-layout: fixed;
    }
    .form-two-table .col-index {
        width: 40px;
    }
    .form-two-table .col-subject {
        width: 280px;
    }
    .form-two-table .col-teacher {
        width: 220px;
    }
    .form-two-table .col-norm {
        width: 190px;
    }
    .form-two-table .col-day {
        width: 54px;
    }
    .form-two-table .col-used,
    .form-two-table .col-bonus,
    .form-two-table .col-left {
        width: 90px;
    }
    .form-two-table .day-head.weekend {
        background-color: #d1fae5 !important;
    }
    .form-two-table .day-head.holiday {
        background-color: #fff7d6 !important;
    }
    .day-cell {
        min-width: 70px;
        vertical-align: middle;
    }
    .day-cell.weekend {
        background-color: #d1fae5 !important;
    }
    .day-cell.holiday {
        background-color: #fff7d6 !important;
    }
    .day-cell.holiday .status-chip {
        border-color: #fcd34d;
        background: #fff8d5;
    }
    .totals-row {
        background: #e0f2fe;
        font-weight: 600;
    }
    .totals-cell {
        border-top: 2px solid #bae6fd;
    }
    .column-totals-row {
        background: #dcfce7;
        font-weight: 600;
    }
    .column-totals-row .totals-cell {
        border-top: 2px solid #86efac;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 6px 8px;
        font-weight: 700;
        font-size: 13px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        width: 50px;
        height: 36px;
    }
    .status-chip.status-normal {
        background: #e7f5ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .status-chip.status-replaced {
        background: #fff7d6;
        color: #b45309;
        border-color: #facc15;
    }
    .status-chip.status-replacement {
        background: #ffeaea;
        color: #c1121f;
        border-color: #f87171;
    }
    .status-chip.status-empty {
        background: #f8fafc;
        color: #94a3b8;
    }
    .status-chip.holiday-chip {
        background: #fff8d5;
        color: #92400e;
        border-color: #fcd34d;
    }
    .chip-value {
        display: inline-block;
        min-width: 14px;
        text-align: center;
    }
    .manual-status .form-select {
        padding-top: 3px;
        padding-bottom: 3px;
        font-size: 12px;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .replacement-detail {
        font-size: 12px;
        color: #475569;
    }
    .replacement-table th,
    .replacement-table td {
        font-size: 13px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .replacement-chip {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .replacement-empty-cell {
        width: 50px;
        height: 36px;
    }
</style>
@endpush

@push('scripts')
<script>
    const groupSelect = document.getElementById('groupSelect');
    const monthSelect = document.getElementById('monthSelect');
    const yearInput = document.getElementById('yearInput');
    const yearLabel = document.getElementById('yearLabel');
    const yearPrevBtn = document.getElementById('yearPrevBtn');
    const yearNextBtn = document.getElementById('yearNextBtn');
    const courseSelect = document.getElementById('courseSelect');
    const reloadBtn = document.getElementById('reloadBtn');
    const saveBtn = document.getElementById('saveBtn');
    const semester2Btn = document.getElementById('semester2Btn');
    const formBody = document.getElementById('formBody');
    const replacementTableBody = document.getElementById('replacementTableBody');
    const subgroupTwoBody = document.getElementById('subgroupTwoBody');
    const manualToggle = document.getElementById('manualToggle');
    const semester2Year = Number("{{ $semester2Year ?? ($year ?? now()->year) }}");

    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        params.delete('group_id');
        window.location.search = params.toString();
    });

    const updateYearLabel = () => {
        if (yearLabel && yearInput) {
            yearLabel.textContent = yearInput.value;
        }
    };

    updateYearLabel();

    yearPrevBtn?.addEventListener('click', () => {
        yearInput.value = Number(yearInput.value) - 1;
        updateYearLabel();
    });

    yearNextBtn?.addEventListener('click', () => {
        yearInput.value = Number(yearInput.value) + 1;
        updateYearLabel();
    });

    reloadBtn?.addEventListener('click', () => {
        const params = new URLSearchParams();
        params.set('group_id', groupSelect.value);
        params.set('month', monthSelect.value);
        params.set('year', yearInput.value);
        params.set('course', courseSelect ? courseSelect.value : '{{ $course ?? 1 }}');
        window.location.search = params.toString();
    });

    semester2Btn?.addEventListener('click', () => {
        if (!monthSelect || !yearInput) {
            return;
        }
        monthSelect.value = '2';
        yearInput.value = String(semester2Year || yearInput.value);
        updateYearLabel();
        reloadBtn?.click();
    });

    manualToggle?.addEventListener('change', () => {
        const enabled = manualToggle.checked;
        document.querySelectorAll('.manual-norm').forEach(el => el.classList.toggle('d-none', !enabled));
        saveBtn?.classList.toggle('d-none', !enabled);
        document.querySelectorAll('.js-correction-save').forEach(btn => btn.classList.toggle('d-none', !enabled));
    });

    saveBtn?.addEventListener('click', async () => {
        if (!manualToggle.checked) {
            return;
        }

        const rows = [];
        formBody.querySelectorAll('tr[data-row]').forEach((tr) => {
            const subjectId = Number(tr.querySelector('.row-subject')?.value);
            if (!subjectId) return;
            const teacherId = Number(tr.querySelector('.row-teacher')?.value) || null;
            const totalHoursInput = tr.querySelector('.row-total-hours-input');
            const totalHoursHidden = tr.querySelector('.row-total-hours');
            const totalHours = totalHoursInput
                ? Number(totalHoursInput.value || 0)
                : Number(totalHoursHidden?.value || 0);
            const hoursPerClass = Number(tr.querySelector('.row-hours-per-class')?.value) || 2;
            const days = {};
            tr.querySelectorAll('.cell-status').forEach((sel) => {
                const day = sel.dataset.day;
                const status = sel.value;
                const replSel = tr.querySelector(`.cell-repl[data-day="${day}"]`);
                const replSubjectSel = tr.querySelector(`.cell-repl-subject[data-day="${day}"]`);
                const replacement_teacher_id = replSel && replSel.value ? Number(replSel.value) : null;
                const replacement_subject_id = replSubjectSel && replSubjectSel.value ? Number(replSubjectSel.value) : null;
                days[day] = { status, replacement_teacher_id, replacement_subject_id };
            });
            rows.push({
                subject_id: subjectId,
                teacher_id: teacherId,
                total_hours: totalHours,
                hours_per_class: hoursPerClass,
                days,
            });
        });

        const replacementNormatives = [];
        replacementTableBody?.querySelectorAll('tr[data-row]').forEach((tr) => {
            const subjectId = Number(tr.querySelector('.row-subject')?.value);
            if (!subjectId) return;
            const teacherId = Number(tr.querySelector('.row-teacher')?.value) || null;
            const totalHoursInput = tr.querySelector('.row-total-hours-input');
            const totalHoursHidden = tr.querySelector('.row-total-hours');
            const totalHours = totalHoursInput
                ? Number(totalHoursInput.value || 0)
                : Number(totalHoursHidden?.value || 0);
            const hoursPerClass = Number(tr.querySelector('.row-hours-per-class')?.value) || 2;
            replacementNormatives.push({
                subject_id: subjectId,
                teacher_id: teacherId,
                total_hours: totalHours,
                hours_per_class: hoursPerClass,
            });
        });

        const subgroupTwoNormatives = [];
        subgroupTwoBody?.querySelectorAll('tr[data-row]').forEach((tr) => {
            const subjectId = Number(tr.querySelector('.row-subject')?.value);
            if (!subjectId) return;
            const teacherId = Number(tr.querySelector('.row-teacher')?.value) || null;
            const totalHoursInput = tr.querySelector('.row-total-hours-input');
            const totalHoursHidden = tr.querySelector('.row-total-hours');
            const totalHours = totalHoursInput
                ? Number(totalHoursInput.value || 0)
                : Number(totalHoursHidden?.value || 0);
            const hoursPerClass = Number(tr.querySelector('.row-hours-per-class')?.value) || 2;
            subgroupTwoNormatives.push({
                subject_id: subjectId,
                teacher_id: teacherId,
                total_hours: totalHours,
                hours_per_class: hoursPerClass,
            });
        });

        const payload = {
            group_id: Number(groupSelect.value),
            month: Number(monthSelect.value),
            year: Number(yearInput.value),
            course: courseSelect ? Number(courseSelect.value) : 1,
            allow_manual: true,
            rows,
            replacement_normatives: replacementNormatives,
            subgroup_two_normatives: subgroupTwoNormatives,
        };

        try {
            const res = await fetch("{{ route('first.schedule.form_two.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });
            const body = await res.json().catch(() => ({}));
            if (!res.ok) {
                alert(body.message || 'Ошибка сохранения');
                return;
            }
            alert('Коррекция сохранена');
            window.location.reload();
        } catch (e) {
            alert('Ошибка сети');
        }
    });

    document.querySelectorAll('.js-correction-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!manualToggle) return;
            manualToggle.checked = !manualToggle.checked;
            manualToggle.dispatchEvent(new Event('change'));
        });
    });

    document.querySelectorAll('.js-correction-save').forEach((btn) => {
        btn.addEventListener('click', () => {
            saveBtn?.click();
        });
    });
</script>
@endpush
