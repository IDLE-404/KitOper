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
    $practiceDates = $practiceDates ?? [];
    $practiceDateSet = [];
    foreach ($practiceDates as $date) {
        $practiceDateSet[$date] = true;
    }
    $subgroupTwoRows = $subgroupTwoRows ?? [];
    $subgroupTwoDayTotals = $subgroupTwoDayTotals ?? [];
    $subgroupTwoColumnTotals = $subgroupTwoColumnTotals ?? ['normative' => 0, 'used' => 0, 'bonus' => 0, 'left' => 0];
    $hasSubgroups = $hasSubgroups ?? false;
    $subjects = $subjects ?? collect();
    $dayTotals = $dayTotals ?? [];
    $columnTotals = $columnTotals ?? ['normative' => 0, 'used' => 0, 'bonus' => 0, 'left' => 0];
    $holidayDays = $holidayDays ?? [];
    $activeSemester = $activeSemester ?? 1;
    $ghostMode      = $ghostMode ?? false;
    $ghostSemester  = $ghostSemester ?? $activeSemester;
    $ghostCells     = $ghostCells ?? [];
    $ghostConflicts = $ghostConflicts ?? [];
    $ghostPrevAccum = $ghostPrevAccum ?? [];

    // Месяцы каждого семестра
    $semesterMonths = [
        1 => [9, 10, 11, 12, 1],
        2 => [2, 3, 4, 5, 6],
    ];

    // Вспомогательная функция: есть ли ghost-пара для строки в день $d
    $hasGhost = function(array $row, int $d, int $subgroup = 1) use ($ghostCells): bool {
        if (empty($ghostCells)) return false;
        $sid = $row['subject_id'] ?? null;
        $tid = $row['teacher_id'] ?? null;
        $key = "{$sid}|{$tid}|{$subgroup}";
        return isset($ghostCells[$key][$d]);
    };
    $ghostLessons = function(array $row, int $d, int $subgroup = 1) use ($ghostCells): string {
        $sid = $row['subject_id'] ?? null;
        $tid = $row['teacher_id'] ?? null;
        $key = "{$sid}|{$tid}|{$subgroup}";
        $lessons = $ghostCells[$key][$d] ?? [];
        return $lessons ? 'Пара ' . implode(', ', $lessons) : '';
    };

    // Вычитаем ghost-часы из прошлых месяцев, чтобы остаток переходил корректно
    if ($ghostMode && !empty($ghostPrevAccum)) {
        foreach ($rows as &$row) {
            $hpc  = (float)($row['hours_per_class'] ?? 2);
            $key  = "{$row['subject_id']}|{$row['teacher_id']}|1";
            $prev = ($ghostPrevAccum[$key] ?? 0) * $hpc;
            if ($prev <= 0) continue;
            $row['total_hours']      = max(0, ($row['total_hours'] ?? 0) - $prev);
            $row['hours_left_start'] = $row['total_hours'];
            $row['hours_left']       = max(0, ($row['hours_left'] ?? 0) - $prev);
            $columnTotals['used']    = ($columnTotals['used'] ?? 0) + $prev;
            $columnTotals['left']    = ($columnTotals['left'] ?? 0) - $prev;
        }
        unset($row);
        foreach ($subgroupTwoRows as &$row) {
            $hpc  = (float)($row['hours_per_class'] ?? 2);
            $key  = "{$row['subject_id']}|{$row['teacher_id']}|2";
            $prev = ($ghostPrevAccum[$key] ?? 0) * $hpc;
            if ($prev <= 0) continue;
            $row['total_hours']      = max(0, ($row['total_hours'] ?? 0) - $prev);
            $row['hours_left_start'] = $row['total_hours'];
            $row['hours_left']       = max(0, ($row['hours_left'] ?? 0) - $prev);
            $subgroupTwoColumnTotals['used'] = ($subgroupTwoColumnTotals['used'] ?? 0) + $prev;
            $subgroupTwoColumnTotals['left'] = ($subgroupTwoColumnTotals['left'] ?? 0) - $prev;
        }
        unset($row);
    }

    // Пересчёт итогов с учётом ghost-часов текущего месяца (прогноз семестра)
    if ($ghostMode && !empty($ghostCells)) {
        // Основные строки (подгруппа 1)
        foreach ($rows as &$row) {
            $sid = $row['subject_id'] ?? null;
            $tid = $row['teacher_id'] ?? null;
            $hpc = (float)($row['hours_per_class'] ?? 2);
            $key = "{$sid}|{$tid}|1";
            if (!isset($ghostCells[$key])) continue;
            foreach ($ghostCells[$key] as $d => $lessons) {
                $cell = $row['days'][$d] ?? [];
                if (($cell['status'] ?? 'empty') !== 'empty') continue;
                if (isset($weekendDays[$d]) || isset($holidayDays[$d])) continue;
                $addHours = count($lessons) * $hpc;
                $row['used_hours_total'] = ($row['used_hours_total'] ?? 0) + $addHours;
                $row['hours_left']       = ($row['hours_left'] ?? 0) - $addHours;
                $dayTotals[$d]           = ($dayTotals[$d] ?? 0) + $addHours;
                $columnTotals['used']    = ($columnTotals['used'] ?? 0) + $addHours;
                $columnTotals['left']    = ($columnTotals['left'] ?? 0) - $addHours;
            }
        }
        unset($row);

        // Строки подгруппы 2
        foreach ($subgroupTwoRows as &$row) {
            $sid = $row['subject_id'] ?? null;
            $tid = $row['teacher_id'] ?? null;
            $hpc = (float)($row['hours_per_class'] ?? 2);
            $key = "{$sid}|{$tid}|2";
            if (!isset($ghostCells[$key])) continue;
            foreach ($ghostCells[$key] as $d => $lessons) {
                $cell = $row['days'][$d] ?? [];
                if (($cell['status'] ?? 'empty') !== 'empty') continue;
                if (isset($weekendDays[$d]) || isset($holidayDays[$d])) continue;
                $addHours = count($lessons) * $hpc;
                $row['used_hours_total']       = ($row['used_hours_total'] ?? 0) + $addHours;
                $row['hours_left']             = ($row['hours_left'] ?? 0) - $addHours;
                $subgroupTwoDayTotals[$d]      = ($subgroupTwoDayTotals[$d] ?? 0) + $addHours;
                $subgroupTwoColumnTotals['used'] = ($subgroupTwoColumnTotals['used'] ?? 0) + $addHours;
                $subgroupTwoColumnTotals['left'] = ($subgroupTwoColumnTotals['left'] ?? 0) - $addHours;
            }
        }
        unset($row);
    }

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

<div class="form-two-container">
    <div class="page-header mb-3">
        <div>
            <h1 class="page-title">Форма 2 — {{ $course ?? 1 }} курс</h1>
            <p class="page-subtitle">Отчёт по фактическим занятиям за месяц</p>
        </div>
        <a href="{{ route('first.schedule.index', ['course' => $course ?? 1]) }}" class="btn btn-secondary">← Расписание</a>
    </div>

    <div class="surface surface-p mb-3">
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
                <div class="col-md-2">
                    <label class="form-label text-muted small mb-1">Семестр</label>
                    <div class="btn-group w-100" id="semesterBtnsFilter">
                        <button type="button"
                                class="btn btn-sm {{ $activeSemester === 1 ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                data-semester="1">1</button>
                        <button type="button"
                                class="btn btn-sm {{ $activeSemester === 2 ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                data-semester="2">2</button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small mb-1">Месяц</label>
                    <select class="form-select" id="monthSelect">
                        @foreach($months as $num => $label)
                            @if(in_array($num, $semesterMonths[$activeSemester]))
                                <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small mb-1">Год</label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-secondary btn-sm" id="yearPrevBtn" type="button">Предыдущий</button>
                        <span class="fw-semibold" id="yearLabel">{{ $year }}</span>
                        <button class="btn btn-secondary btn-sm" id="yearNextBtn" type="button">Следующий</button>
                    </div>
                    <input type="hidden" id="yearInput" value="{{ $year }}">
                </div>
                <div class="col-12 text-end ok-row">
                    <button class="btn btn-primary btn-sm" id="reloadBtn">OK</button>
                </div>
            </div>
    </div>

    @php
        $semester1Year = $month >= 9 ? $year : ($year - 1);
        $semester2Year = $month >= 9 ? ($year + 1) : $year;
    @endphp
    <div class="surface surface-p mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('first.schedule.form_two.export', ['group_id' => $groupId, 'month' => $month, 'year' => $year, 'course' => $course]) }}"
               class="btn btn-secondary btn-sm">
                📊 Экспорт в Excel
            </a>
            <a href="{{ route('first.schedule.form_two.export_semester', ['group_id' => $groupId, 'semester' => 1, 'year' => $semester1Year, 'course' => $course]) }}"
               class="btn btn-secondary btn-sm">
                📘 Экспорт 1 семестр
            </a>
            <a href="{{ route('first.schedule.form_two.export_semester', ['group_id' => $groupId, 'semester' => 2, 'year' => $semester2Year, 'course' => $course]) }}"
               class="btn btn-secondary btn-sm">
                📗 Экспорт 2 семестр
            </a>
            <div class="vr mx-1"></div>
            <div class="form-check form-switch correction-switch mb-0">
                <input class="form-check-input" type="checkbox" id="manualToggle">
                <label class="form-check-label" for="manualToggle">Режим коррекции</label>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="ghostToggle" @checked($ghostMode)>
                <label class="form-check-label" for="ghostToggle" style="white-space:nowrap">
                    <span class="ghost-toggle-icon">👻</span> Просмотр семестра
                </label>
            </div>
            <button class="btn btn-secondary btn-sm d-none" id="addSubjectBtn" type="button">Добавить предмет</button>
            <button class="btn btn-primary btn-sm d-none js-correction-save" id="saveBtn" type="button">Сохранить коррекцию</button>
        </div>
    </div>

    <div class="surface surface-p mb-3">
        <div class="d-flex flex-wrap gap-3 align-items-center legend-row">
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
            <div class="legend-item">
                <span class="status-chip status-practice-legend">Практика</span>
                <span class="text-muted ms-2 small">День практики</span>
            </div>
            <div class="ms-auto text-muted small">
                Статусы поступают из расписания. Ручная коррекция — только в исключительных случаях.
            </div>
        </div>
    </div>

    <div class="surface surface-p">
        <div class="mb-2">
            <div class="fw-semibold">Группа: {{ optional($groups->firstWhere('id', $groupId))->group_name ?? '—' }}</div>
            <div class="text-muted small">{{ $months[$month] ?? $month }} {{ $year }}</div>
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
                            <th class="text-muted col-used" style="white-space:nowrap">Использовано</th>
                            <th class="text-muted col-bonus" style="white-space:nowrap">Бонус</th>
                            <th class="text-muted col-left" style="white-space:nowrap">Остаток</th>
                        </tr>
                    </thead>
                    <tbody id="formBody">
                        @forelse($rows as $idx => $row)
                            <tr data-row="{{ $idx }}">
                                <td class="col-index">{{ $idx + 1 }}</td>
                                <td class="col-subject">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="fw-semibold row-subject-name">{{ $row['subject_name'] ?? '—' }}</div>
                                        <button type="button" class="btn-close row-delete-btn manual-edit d-none" aria-label="Удалить строку"></button>
                                    </div>
                                    <div class="manual-edit d-none mt-2">
                                        <select class="form-select form-select-sm row-subject">
                                            <option value="">— выберите предмет</option>
                                            @foreach($subjects as $s)
                                                <option value="{{ $s->id }}" @selected(($row['subject_id'] ?? null) == $s->id)>{{ $s->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                    <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                </td>
                                <td class="col-teacher">
                                    <div class="row-teacher-name">{{ $row['teacher_name'] ?? '—' }}</div>
                                    <div class="manual-edit d-none mt-2">
                                        <select class="form-select form-select-sm row-teacher">
                                            <option value="">— выберите преподавателя</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}" @selected(($row['teacher_id'] ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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
                                        $cellDate = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                                        $isPractice = isset($practiceDateSet[$cellDate]);
                                        // Ghost: по расписанию здесь должна быть пара, но записи нет
                                        $isGhost = $ghostMode && $status === 'empty' && !$isPractice
                                            && !isset($weekendDays[$d]) && !isset($holidayDays[$d])
                                            && $hasGhost($row, $d, 1);
                                        $ghostHint   = $isGhost ? $ghostLessons($row, $d, 1) : '';
                                        $ghostValue  = $isGhost ? ($row['hours_per_class'] ?? 2) : '';
                                        $titleParts  = array_filter([$tooltip, $holidayNote, $isGhost ? 'Прогноз: ' . $ghostHint : null]);
                                        $defaultTitle = $isPractice ? 'Практика' : ($isGhost ? 'Прогноз: ' . $ghostHint : 'Нет записи');
                                        $cellTitle   = $titleParts ? implode(' | ', $titleParts) : $defaultTitle;
                                    @endphp
                                    <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }} {{ $isPractice ? 'practice' : '' }} {{ $isGhost ? 'ghost-cell' : '' }}">
                                        <div class="status-chip status-{{ $status }} {{ $isPractice ? 'status-practice' : '' }} {{ $isGhost ? 'status-ghost' : '' }}" title="{{ $cellTitle }}">
                                            <span class="chip-value">{{ $isPractice ? '' : ($isGhost ? $ghostValue : $value) }}</span>
                                        </div>
                                        <div class="manual-status d-none mt-1">
                                            <select class="form-select form-select-sm cell-status" data-day="{{ $d }}" @disabled(isset($holidayDays[$d]))>
                                                <option value="empty" @selected($status === 'empty')>—</option>
                                                <option value="normal" @selected($status === 'normal')>Норма</option>
                                                <option value="replaced" @selected($status === 'replaced')>Замена (замещённая)</option>
                                                <option value="replacement" @selected($status === 'replacement')>Замена (замещающая)</option>
                                            </select>
                                            <select class="form-select form-select-sm cell-repl js-teacher-select mt-1"
                                                    data-day="{{ $d }}"
                                                    data-selected="{{ $cell['replacement_teacher_id'] ?? '' }}"
                                                    @disabled(isset($holidayDays[$d]))>
                                                <option value="">— заменяющий</option>
                                            </select>
                                            <select class="form-select form-select-sm cell-repl-subject js-subject-select mt-1"
                                                    data-day="{{ $d }}"
                                                    data-selected="{{ $cell['replacement_subject_id'] ?? '' }}"
                                                    @disabled(isset($holidayDays[$d]))>
                                                <option value="">— замещающий предмет</option>
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


    <div class="surface surface-p mt-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                <div>
                    <div class="fw-semibold">Только замены преподавателей</div>
                    <div class="text-muted small">повторяет форму 2, но показывает только фактические подмены</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-secondary btn-sm js-correction-toggle" type="button">Режим коррекции</button>
                    <button class="btn btn-primary btn-sm d-none js-correction-save" type="button">Сохранить коррекцию</button>
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
                            <th class="text-muted col-used" style="white-space:nowrap">Использовано</th>
                            <th class="text-muted col-bonus" style="white-space:nowrap">Бонус</th>
                            <th class="text-muted col-left" style="white-space:nowrap">Остаток</th>
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
                                        $cellDate = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                                        $isPractice = isset($practiceDateSet[$cellDate]);
                                        $titleParts = array_filter([$tooltip, $holidayNote]);
                                        $defaultTitle = $status === 'empty' ? '—' : 'Нет записи';
                                        $cellTitle = $titleParts ? implode(' | ', $titleParts) : $defaultTitle;
                                    @endphp
                                    <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }} {{ $isPractice ? 'practice' : '' }}">
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

    @if($hasSubgroups)
        <div class="surface surface-p mt-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                    <div>
                        <div class="fw-semibold">Подвоение (подгруппа 2)</div>
                        <div class="text-muted small">только записи со второй подгруппой</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-secondary btn-sm d-none" id="addSubgroupTwoSubjectBtn" type="button">Добавить предмет</button>
                        <button class="btn btn-secondary btn-sm js-correction-toggle" type="button">Режим коррекции</button>
                        <button class="btn btn-primary btn-sm d-none js-correction-save" type="button">Сохранить коррекцию</button>
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
                                <th class="text-muted col-used" style="white-space:nowrap">Использовано</th>
                                <th class="text-muted col-bonus" style="white-space:nowrap">Бонус</th>
                                <th class="text-muted col-left" style="white-space:nowrap">Остаток</th>
                            </tr>
                        </thead>
                        <tbody id="subgroupTwoBody">
                            @forelse($subgroupTwoRows as $idx => $row)
                                <tr data-row="{{ $idx }}">
                                    <td class="col-index">{{ $idx + 1 }}</td>
                                    <td class="col-subject">
                                        <div class="fw-semibold row-subject-name">{{ $row['subject_name'] ?? '—' }}</div>
                                        <div class="manual-edit d-none mt-2">
                                            <select class="form-select form-select-sm row-subject">
                                                <option value="">— выберите предмет</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected(($row['subject_id'] ?? null) == $s->id)>{{ $s->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                        <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                    </td>
                                    <td class="col-teacher">
                                        <div class="row-teacher-name">{{ $row['teacher_name'] ?? '—' }}</div>
                                        <div class="manual-edit d-none mt-2">
                                            <select class="form-select form-select-sm row-teacher">
                                                <option value="">— выберите преподавателя</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected(($row['teacher_id'] ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
                                            $cellDate = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                                            $isPractice = isset($practiceDateSet[$cellDate]);
                                            $isGhost = $ghostMode && $status === 'empty' && !$isPractice
                                                && !isset($weekendDays[$d]) && !isset($holidayDays[$d])
                                                && $hasGhost($row, $d, 2);
                                            $ghostHint   = $isGhost ? $ghostLessons($row, $d, 2) : '';
                                            $ghostValue  = $isGhost ? ($row['hours_per_class'] ?? 2) : '';
                                            $titleParts  = array_filter([$tooltip, $holidayNote, $isGhost ? 'Прогноз: ' . $ghostHint : null]);
                                            $defaultTitle = $isPractice ? 'Практика' : ($isGhost ? 'Прогноз: ' . $ghostHint : 'Нет записи');
                                            $cellTitle   = $titleParts ? implode(' | ', $titleParts) : $defaultTitle;
                                        @endphp
                                        <td class="text-center day-cell col-day {{ isset($weekendDays[$d]) ? 'weekend' : '' }} {{ isset($holidayDays[$d]) ? 'holiday' : '' }} {{ $isPractice ? 'practice' : '' }} {{ $isGhost ? 'ghost-cell' : '' }}">
                                            <div class="status-chip status-{{ $status }} {{ $isPractice ? 'status-practice' : '' }} {{ $isGhost ? 'status-ghost' : '' }}" title="{{ $cellTitle }}">
                                                <span class="chip-value">{{ $isPractice ? '' : ($isGhost ? $ghostValue : $value) }}</span>
                                            </div>
                                            <div class="manual-status d-none mt-1">
                                                <select class="form-select form-select-sm cell-status" data-day="{{ $d }}" @disabled(isset($holidayDays[$d]))>
                                                    <option value="empty" @selected($status === 'empty')>—</option>
                                                    <option value="normal" @selected($status === 'normal')>Норма</option>
                                                    <option value="replaced" @selected($status === 'replaced')>Замена (замещённая)</option>
                                                    <option value="replacement" @selected($status === 'replacement')>Замена (замещающая)</option>
                                                </select>
                                                <select class="form-select form-select-sm cell-repl js-teacher-select mt-1"
                                                        data-day="{{ $d }}"
                                                        data-selected="{{ $cell['replacement_teacher_id'] ?? '' }}"
                                                        @disabled(isset($holidayDays[$d]))>
                                                    <option value="">— заменяющий</option>
                                                </select>
                                                <select class="form-select form-select-sm cell-repl-subject js-subject-select mt-1"
                                                        data-day="{{ $d }}"
                                                        data-selected="{{ $cell['replacement_subject_id'] ?? '' }}"
                                                        @disabled(isset($holidayDays[$d]))>
                                                    <option value="">— замещающий предмет</option>
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
    @endif

</div>

{{-- Correction popover --}}
<div id="corrPopover" role="dialog" aria-label="Редактировать ячейку">
    <div class="corr-pop-header">
        <span class="corr-pop-day-label">—</span>
        <button class="corr-pop-close" type="button" aria-label="Закрыть">&times;</button>
    </div>
    <div class="corr-pop-body">
        <div class="corr-pop-status-grid">
            <button class="corr-status-btn" type="button" data-status="empty">
                Нет занятия<small>пары не было</small>
            </button>
            <button class="corr-status-btn" type="button" data-status="normal">
                Пара прошла<small>засчитать часы</small>
            </button>
            <button class="corr-status-btn" type="button" data-status="replaced">
                Другой провёл<small>другой преп вместо меня</small>
            </button>
            <button class="corr-status-btn" type="button" data-status="replacement">
                Я провёл чужую<small>бонусные часы</small>
            </button>
        </div>
        <div id="corrPopTeacherWrap" class="d-none">
            <label class="corr-pop-label">Заменяющий преподаватель</label>
            <select id="corrPopTeacher" class="corr-pop-select">
                <option value="">— не выбран</option>
            </select>
        </div>
        <div id="corrPopSubjectWrap" class="d-none">
            <label class="corr-pop-label">Замещающий предмет</label>
            <select id="corrPopSubject" class="corr-pop-select">
                <option value="">— не выбран</option>
            </select>
        </div>
        <div id="corrPopHolidayNote" class="d-none text-warning small mb-1"></div>
    </div>
    <div class="corr-pop-footer">
        <button class="corr-pop-apply" id="corrPopApply" type="button">Применить</button>
        <button class="corr-pop-cancel" id="corrPopCancel" type="button">Отмена</button>
    </div>
    <div class="corr-pop-schedule-note">
        <i class="bi bi-info-circle"></i> Только Форма 2 — расписание не меняется
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
    .form-two-container .surface {
        margin-bottom: 16px;
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
        width: auto !important;
    }
    .form-two-table .col-index {
        width: 40px;
    }
    .form-two-table .col-subject {
        width: 220px;
    }
    .form-two-table .col-teacher {
        width: 170px;
    }
    .form-two-table .col-norm {
        width: 70px;
        white-space: nowrap;
    }
    .form-two-table .col-day {
        width: 46px;
    }
    .form-two-table .col-used,
    .form-two-table .col-bonus,
    .form-two-table .col-left {
        width: 110px;
        white-space: nowrap;
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
    .day-cell.practice {
        background-color: #fef9c3 !important;
    }
    .day-cell.practice .status-chip {
        border-color: #facc15;
        background: #fef08a;
        color: #854d0e;
    }
    .day-cell.practice .chip-value {
        color: transparent;
        user-select: none;
    }
    .totals-row {
        background: #f1f5f9;
        font-weight: 600;
    }
    .totals-cell {
        border-top: 2px solid #e2e8f0;
    }
    .column-totals-row {
        background: #f1f5f9;
        font-weight: 600;
    }
    .column-totals-row .totals-cell {
        border-top: 2px solid #e2e8f0;
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
        background: rgba(127, 86, 217, 0.08);
        color: #6941c6;
        border-color: rgba(127, 86, 217, 0.25);
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
    .status-chip.status-practice-legend {
        width: auto;
        min-width: 50px;
        border-color: #facc15;
        background: #fef08a;
        color: #854d0e;
    }
    .status-chip.status-practice {
        display: inline-flex;
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
    .manual-edit .form-select {
        padding-top: 3px;
        padding-bottom: 3px;
        font-size: 12px;
    }
    .correction-switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border: 1px solid var(--c-border, #e5e7eb);
        border-radius: 999px;
        background: #f7f7f8;
    }
    .correction-switch .form-check-input {
        width: 2.4rem;
        height: 1.2rem;
        margin: 0;
        cursor: pointer;
    }
    .correction-switch .form-check-input:checked {
        background-color: #7f56d9;
        border-color: #7f56d9;
    }
    .correction-switch .form-check-label {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        cursor: pointer;
        user-select: none;
    }
    .row-new-added {
        animation: rowFlash 1.1s ease;
    }
    @keyframes rowFlash {
        0% { background: #dcfce7; }
        100% { background: transparent; }
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

    /* Ghost / Просмотр семестра */
    td.ghost-cell {
        background: #e9ecef !important;
    }
    td.ghost-cell:hover {
        background: #dee2e6 !important;
    }
    .status-chip.status-ghost {
        background: rgba(127, 86, 217, 0.06);
        color: #7f56d9;
        border: 2px dashed rgba(127, 86, 217, 0.4);
        font-weight: 700;
    }

    /* Correction mode — popover */
    .day-cell.correction-active { cursor: pointer; position: relative; }
    .day-cell.correction-active:hover .status-chip:not(.status-practice) {
        outline: 2px solid #7f56d9;
        outline-offset: 2px;
    }
    .day-cell.cell-dirty .status-chip {
        outline: 2px solid #f59e0b !important;
        outline-offset: 2px;
    }
    .day-cell.cell-dirty::after {
        content: '';
        position: absolute;
        top: 3px; right: 3px;
        width: 5px; height: 5px;
        border-radius: 50%;
        background: #f59e0b;
        pointer-events: none;
    }
    #corrPopover {
        position: fixed;
        z-index: 9999;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0,0,0,.18);
        width: 280px;
        max-height: calc(100vh - 24px);
        overflow-y: auto;
        display: none;
        font-size: 13px;
    }
    #corrPopover.is-open { display: block; }
    .corr-pop-header {
        padding: 10px 14px 8px;
        font-weight: 700;
        font-size: 12px;
        color: #6941c6;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .corr-pop-day-label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .corr-pop-close {
        background: none; border: none; cursor: pointer;
        color: #9ca3af; font-size: 18px; padding: 0; line-height: 1; flex-shrink: 0;
    }
    .corr-pop-close:hover { color: #374151; }
    .corr-pop-body { padding: 10px 14px 6px; }
    .corr-pop-status-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        margin-bottom: 10px;
    }
    .corr-status-btn {
        padding: 7px 6px;
        border: 1.5px solid #e5e7eb;
        border-radius: 6px;
        background: #f9fafb;
        cursor: pointer;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        transition: border-color .1s, background .1s, color .1s;
        color: #374151;
        line-height: 1.2;
    }
    .corr-status-btn small {
        display: block;
        font-size: 10px;
        font-weight: 400;
        opacity: .7;
        margin-top: 2px;
    }
    .corr-status-btn:hover { border-color: #7f56d9; color: #7f56d9; background: #faf5ff; }
    .corr-status-btn.is-active { border-color: #7f56d9; background: #7f56d9; color: #fff; }
    .corr-status-btn.is-active small { opacity: .85; }
    .corr-pop-schedule-note {
        font-size: 10px;
        color: #9ca3af;
        text-align: center;
        padding: 0 14px 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .corr-pop-label {
        font-size: 11px; font-weight: 600; color: #6b7280;
        margin-bottom: 3px; display: block;
    }
    .corr-pop-select {
        width: 100%; font-size: 12px; padding: 4px 6px;
        border: 1px solid #e5e7eb; border-radius: 6px;
        background: #fff; margin-bottom: 6px;
    }
    .corr-pop-footer {
        padding: 8px 14px 10px;
        display: flex; gap: 6px;
        border-top: 1px solid #f3f4f6;
    }
    .corr-pop-apply {
        flex: 1; background: #7f56d9; color: #fff;
        border: none; border-radius: 6px; padding: 6px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: background .1s;
    }
    .corr-pop-apply:hover:not(:disabled) { background: #6941c6; }
    .corr-pop-apply:disabled { opacity: .5; cursor: not-allowed; }
    .corr-pop-cancel {
        flex: 1; background: #f3f4f6; color: #374151;
        border: none; border-radius: 6px; padding: 6px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        transition: background .1s;
    }
    .corr-pop-cancel:hover { background: #e5e7eb; }
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
    const addSubjectBtn = document.getElementById('addSubjectBtn');
    const addSubgroupTwoSubjectBtn = document.getElementById('addSubgroupTwoSubjectBtn');

    const formBody = document.getElementById('formBody');
    const replacementTableBody = document.getElementById('replacementTableBody');
    const subgroupTwoBody = document.getElementById('subgroupTwoBody');
    const manualToggle = document.getElementById('manualToggle');
    const semester2Year = Number("{{ $semester2Year ?? ($year ?? now()->year) }}");
    const days = @json($days ?? []);
    const weekendDays = @json($weekendDays ?? []);
    const holidayDays = @json($holidayDays ?? []);
    const teachersData = @json(($teachers ?? collect())->map(fn ($t) => ['id' => (int) $t->id, 'name' => (string) ($t->teacher_name ?? '')])->values());
    const subjectsData = @json(($subjects ?? collect())->map(fn ($s) => ['id' => (int) $s->id, 'title' => (string) ($s->title ?? '')])->values());
    const teacherOptionsHtml = teachersData.map((t) => `<option value="${t.id}">${String(t.name ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;')}</option>`).join('');
    const subjectOptionsHtml = subjectsData.map((s) => `<option value="${s.id}">${String(s.title ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;')}</option>`).join('');
    let dynamicOptionsHydrated = false;

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
        params.set('semester', String(currentActiveSemester));
        if (ghostToggle?.checked) {
            params.set('ghost', '1');
        }
        window.location.search = params.toString();
    });

    // Кнопки "1 семестр / 2 семестр"
    const semesterMonths = { 1: [9, 10, 11, 12, 1], 2: [2, 3, 4, 5, 6] };

    document.querySelectorAll('[data-semester]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sem = Number(btn.dataset.semester);
            const params = new URLSearchParams(window.location.search);
            params.set('semester', String(sem));
            // Первый месяц выбранного семестра
            params.set('month', String(semesterMonths[sem][0]));
            // Год: семестр 1 → текущий год страницы; семестр 2 → год января семестра 1
            const pageYear = Number(yearInput?.value ?? {{ $year }});
            if (sem === 2 && pageYear) {
                // Если сейчас сентябрь-декабрь, фев-июнь будет в следующем году
                const curMonth = Number(monthSelect?.value ?? {{ $month }});
                params.set('year', String(curMonth >= 9 ? pageYear + 1 : pageYear));
            } else {
                params.set('year', String(pageYear));
            }
            if (ghostToggle?.checked) {
                params.set('ghost', '1');
            }
            window.location.search = params.toString();
        });
    });

    const hydrateDynamicManualOptions = () => {
        if (dynamicOptionsHydrated) {
            return;
        }

        document.querySelectorAll('.js-teacher-select').forEach((select) => {
            if (select.dataset.optionsReady === '1') {
                return;
            }
            const selected = String(select.dataset.selected ?? select.value ?? '');
            select.insertAdjacentHTML('beforeend', teacherOptionsHtml);
            if (selected !== '') {
                select.value = selected;
            }
            select.dataset.optionsReady = '1';
        });

        document.querySelectorAll('.js-subject-select').forEach((select) => {
            if (select.dataset.optionsReady === '1') {
                return;
            }
            const selected = String(select.dataset.selected ?? select.value ?? '');
            select.insertAdjacentHTML('beforeend', subjectOptionsHtml);
            if (selected !== '') {
                select.value = selected;
            }
            select.dataset.optionsReady = '1';
        });

        dynamicOptionsHydrated = true;
    };

    manualToggle?.addEventListener('change', () => {
        const enabled = manualToggle.checked;
        if (enabled) {
            hydrateDynamicManualOptions();
        }
        document.querySelectorAll('.manual-norm').forEach(el => el.classList.toggle('d-none', !enabled));
        document.querySelectorAll('.manual-edit').forEach(el => el.classList.toggle('d-none', !enabled));
        document.querySelectorAll('.day-cell').forEach(el => el.classList.toggle('correction-active', enabled));
        saveBtn?.classList.toggle('d-none', !enabled);
        addSubjectBtn?.classList.toggle('d-none', !enabled);
        addSubgroupTwoSubjectBtn?.classList.toggle('d-none', !enabled);
        document.querySelectorAll('.js-correction-save').forEach(btn => btn.classList.toggle('d-none', !enabled));
        if (!enabled) corrPopoverClose();
    });

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const reindexRows = () => {
        formBody?.querySelectorAll('tr[data-row]').forEach((tr, idx) => {
            tr.dataset.row = String(idx);
            const indexCell = tr.querySelector('.col-index');
            if (indexCell) {
                indexCell.textContent = String(idx + 1);
            }
        });
    };

    const reindexSubgroupTwoRows = () => {
        subgroupTwoBody?.querySelectorAll('tr[data-row]').forEach((tr, idx) => {
            tr.dataset.row = String(idx);
            const indexCell = tr.querySelector('.col-index');
            if (indexCell) {
                indexCell.textContent = String(idx + 1);
            }
        });
    };

    addSubjectBtn?.addEventListener('click', () => {
        if (!formBody) {
            return;
        }

        const nextIndex = formBody.querySelectorAll('tr[data-row]').length;
        const subjectOptions = subjectsData.map((s) => `<option value="${s.id}">${escapeHtml(s.title)}</option>`).join('');
        const teacherOptions = teachersData.map((t) => `<option value="${t.id}">${escapeHtml(t.name)}</option>`).join('');
        const dayCells = days.map((day) => {
            const isWeekend = Boolean(weekendDays[String(day)]);
            const holidayMeta = holidayDays[String(day)] || null;
            const isHoliday = Boolean(holidayMeta);
            const dayClasses = ['text-center', 'day-cell', 'col-day'];
            if (isWeekend) dayClasses.push('weekend');
            if (isHoliday) dayClasses.push('holiday');
            const disabledAttr = isHoliday ? 'disabled' : '';
            const holidayNote = isHoliday ? `<div class="text-warning small mt-1">Редактирование отключено — ${escapeHtml(holidayMeta.name || '')}</div>` : '';
            return `
                <td class="${dayClasses.join(' ')}">
                    <div class="status-chip status-empty d-none"><span class="chip-value">•</span></div>
                    <div class="manual-status mt-1">
                        <select class="form-select form-select-sm cell-status" data-day="${day}" ${disabledAttr}>
                            <option value="empty" selected>—</option>
                            <option value="normal">Норма</option>
                            <option value="replaced">Замена (замещённая)</option>
                            <option value="replacement">Замена (замещающая)</option>
                        </select>
                        <select class="form-select form-select-sm cell-repl mt-1" data-day="${day}" ${disabledAttr}>
                            <option value="">— заменяющий</option>
                            ${teacherOptions}
                        </select>
                        <select class="form-select form-select-sm cell-repl-subject mt-1" data-day="${day}" ${disabledAttr}>
                            <option value="">— замещающий предмет</option>
                            ${subjectOptions}
                        </select>
                        ${holidayNote}
                    </div>
                </td>
            `;
        }).join('');

        const tr = document.createElement('tr');
        tr.dataset.row = String(nextIndex);
        tr.innerHTML = `
            <td class="col-index">${nextIndex + 1}</td>
            <td class="col-subject">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="fw-semibold row-subject-name">Новый предмет</div>
                    <button type="button" class="btn-close row-delete-btn manual-edit" aria-label="Удалить строку"></button>
                </div>
                <input type="hidden" class="row-hours-per-class" value="2">
                <input type="hidden" class="row-total-hours" value="0">
                <div class="manual-edit mt-2">
                    <select class="form-select form-select-sm row-subject" required>
                        <option value="">— выберите предмет</option>
                        ${subjectOptions}
                    </select>
                </div>
            </td>
            <td class="col-teacher">
                <div class="row-teacher-name">—</div>
                <div class="manual-edit mt-2">
                    <select class="form-select form-select-sm row-teacher">
                        <option value="">— выберите преподавателя</option>
                        ${teacherOptions}
                    </select>
                </div>
            </td>
            <td class="col-norm">
                <div class="small text-muted">0</div>
                <div class="manual-norm mt-2">
                    <label class="form-label text-muted small mb-1">Всего часов</label>
                    <input type="number" class="form-control form-control-sm row-total-hours-input" min="0" step="1" value="0">
                </div>
            </td>
            ${dayCells}
            <td class="fw-semibold used-cell col-used">0</td>
            <td class="fw-semibold text-primary col-bonus">0</td>
            <td class="fw-semibold text-success col-left">0</td>
        `;
        formBody.appendChild(tr);
        tr.classList.add('row-new-added');
        tr.scrollIntoView({ behavior: 'smooth', block: 'end' });
        reindexRows();
    });

    addSubgroupTwoSubjectBtn?.addEventListener('click', () => {
        if (!subgroupTwoBody) {
            return;
        }

        const nextIndex = subgroupTwoBody.querySelectorAll('tr[data-row]').length;
        const subjectOptions = subjectsData.map((s) => `<option value="${s.id}">${escapeHtml(s.title)}</option>`).join('');
        const teacherOptions = teachersData.map((t) => `<option value="${t.id}">${escapeHtml(t.name)}</option>`).join('');
        const dayCells = days.map((day) => {
            const isWeekend = Boolean(weekendDays[String(day)]);
            const holidayMeta = holidayDays[String(day)] || null;
            const isHoliday = Boolean(holidayMeta);
            const dayClasses = ['text-center', 'day-cell', 'col-day'];
            if (isWeekend) dayClasses.push('weekend');
            if (isHoliday) dayClasses.push('holiday');
            const disabledAttr = isHoliday ? 'disabled' : '';
            const holidayNote = isHoliday ? `<div class="text-warning small mt-1">Редактирование отключено — ${escapeHtml(holidayMeta.name || '')}</div>` : '';
            return `
                <td class="${dayClasses.join(' ')}">
                    <div class="status-chip status-empty d-none"><span class="chip-value">•</span></div>
                    <div class="manual-status mt-1">
                        <select class="form-select form-select-sm cell-status" data-day="${day}" ${disabledAttr}>
                            <option value="empty" selected>—</option>
                            <option value="normal">Норма</option>
                            <option value="replaced">Замена (замещённая)</option>
                            <option value="replacement">Замена (замещающая)</option>
                        </select>
                        <select class="form-select form-select-sm cell-repl mt-1" data-day="${day}" ${disabledAttr}>
                            <option value="">— заменяющий</option>
                            ${teacherOptions}
                        </select>
                        <select class="form-select form-select-sm cell-repl-subject mt-1" data-day="${day}" ${disabledAttr}>
                            <option value="">— замещающий предмет</option>
                            ${subjectOptions}
                        </select>
                        ${holidayNote}
                    </div>
                </td>
            `;
        }).join('');

        const tr = document.createElement('tr');
        tr.dataset.row = String(nextIndex);
        tr.innerHTML = `
            <td class="col-index">${nextIndex + 1}</td>
            <td class="col-subject">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="fw-semibold row-subject-name">Новый предмет</div>
                    <button type="button" class="btn-close row-delete-btn manual-edit" aria-label="Удалить строку"></button>
                </div>
                <input type="hidden" class="row-hours-per-class" value="2">
                <input type="hidden" class="row-total-hours" value="0">
                <div class="manual-edit mt-2">
                    <select class="form-select form-select-sm row-subject" required>
                        <option value="">— выберите предмет</option>
                        ${subjectOptions}
                    </select>
                </div>
            </td>
            <td class="col-teacher">
                <div class="row-teacher-name">—</div>
                <div class="manual-edit mt-2">
                    <select class="form-select form-select-sm row-teacher">
                        <option value="">— выберите преподавателя</option>
                        ${teacherOptions}
                    </select>
                </div>
            </td>
            <td class="col-norm">
                <div class="small text-muted">0</div>
                <div class="manual-norm mt-2">
                    <label class="form-label text-muted small mb-1">Всего часов</label>
                    <input type="number" class="form-control form-control-sm row-total-hours-input" min="0" step="1" value="0">
                </div>
            </td>
            ${dayCells}
            <td class="fw-semibold used-cell col-used">0</td>
            <td class="fw-semibold text-primary col-bonus">0</td>
            <td class="fw-semibold text-success col-left">0</td>
        `;
        subgroupTwoBody.appendChild(tr);
        tr.classList.add('row-new-added');
        tr.scrollIntoView({ behavior: 'smooth', block: 'end' });
        reindexSubgroupTwoRows();
    });

    formBody?.addEventListener('click', (event) => {
        const btn = event.target.closest('.row-delete-btn');
        if (!btn) {
            return;
        }
        const tr = btn.closest('tr[data-row]');
        if (!tr) {
            return;
        }
        tr.remove();
        reindexRows();
    });

    formBody?.addEventListener('change', (event) => {
        const subjectSelect = event.target.closest('.row-subject');
        if (subjectSelect) {
            const tr = subjectSelect.closest('tr[data-row]');
            const label = subjectSelect.options[subjectSelect.selectedIndex]?.textContent?.trim() || 'Новый предмет';
            tr?.querySelector('.row-subject-name')?.replaceChildren(document.createTextNode(label));
        }

        const teacherSelect = event.target.closest('.row-teacher');
        if (teacherSelect) {
            const tr = teacherSelect.closest('tr[data-row]');
            const label = teacherSelect.options[teacherSelect.selectedIndex]?.textContent?.trim() || '—';
            tr?.querySelector('.row-teacher-name')?.replaceChildren(document.createTextNode(label));
        }
    });

    subgroupTwoBody?.addEventListener('click', (event) => {
        const btn = event.target.closest('.row-delete-btn');
        if (!btn) {
            return;
        }
        const tr = btn.closest('tr[data-row]');
        if (!tr) {
            return;
        }
        tr.remove();
        reindexSubgroupTwoRows();
    });

    subgroupTwoBody?.addEventListener('change', (event) => {
        const subjectSelect = event.target.closest('.row-subject');
        if (subjectSelect) {
            const tr = subjectSelect.closest('tr[data-row]');
            const label = subjectSelect.options[subjectSelect.selectedIndex]?.textContent?.trim() || 'Новый предмет';
            tr?.querySelector('.row-subject-name')?.replaceChildren(document.createTextNode(label));
        }

        const teacherSelect = event.target.closest('.row-teacher');
        if (teacherSelect) {
            const tr = teacherSelect.closest('tr[data-row]');
            const label = teacherSelect.options[teacherSelect.selectedIndex]?.textContent?.trim() || '—';
            tr?.querySelector('.row-teacher-name')?.replaceChildren(document.createTextNode(label));
        }
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
        const subgroupTwoRows = [];
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
            subgroupTwoRows.push({
                subject_id: subjectId,
                teacher_id: teacherId,
                total_hours: totalHours,
                hours_per_class: hoursPerClass,
                days,
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
            subgroup_two_rows: subgroupTwoRows,
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

    // === Correction Popover ===
    const corrPop = document.getElementById('corrPopover');
    const corrPopStatusBtns = corrPop ? Array.from(corrPop.querySelectorAll('.corr-status-btn')) : [];
    const corrPopTeacherWrap = document.getElementById('corrPopTeacherWrap');
    const corrPopTeacherSel = document.getElementById('corrPopTeacher');
    const corrPopSubjectWrap = document.getElementById('corrPopSubjectWrap');
    const corrPopSubjectSel = document.getElementById('corrPopSubject');
    const corrPopApplyBtn = document.getElementById('corrPopApply');
    const corrPopCancelBtn = document.getElementById('corrPopCancel');
    const corrPopHolidayNote = document.getElementById('corrPopHolidayNote');
    let corrActiveTd = null;
    let corrPopTeacherReady = false;
    let corrPopSubjectReady = false;

    function corrPopoverClose() {
        corrPop?.classList.remove('is-open');
        corrActiveTd = null;
    }

    function corrPopReposition() {
        if (!corrActiveTd || !corrPop?.classList.contains('is-open')) return;
        const rect = corrActiveTd.getBoundingClientRect();
        const popW = 280;
        const popH = corrPop.scrollHeight;
        let top = rect.bottom + 6;
        let left = parseFloat(corrPop.style.left) || rect.left;
        if (top + popH > window.innerHeight - 12) top = rect.top - popH - 6;
        if (top < 8) top = 8;
        if (left + popW > window.innerWidth - 12) left = window.innerWidth - popW - 12;
        if (left < 8) left = 8;
        corrPop.style.top = top + 'px';
        corrPop.style.left = left + 'px';
    }

    function corrPopUpdateFields(status) {
        const needTeacher = status === 'replaced' || status === 'replacement';
        const needSubject = status === 'replacement';
        corrPopTeacherWrap?.classList.toggle('d-none', !needTeacher);
        corrPopSubjectWrap?.classList.toggle('d-none', !needSubject);
        setTimeout(corrPopReposition, 10);
    }

    function corrPopoverOpen(td) {
        if (!corrPop || !td) return;

        const statusSel = td.querySelector('.cell-status');
        const teacherSel = td.querySelector('.cell-repl');
        const subjectSel = td.querySelector('.cell-repl-subject');
        const isDisabled = statusSel?.disabled || false;

        const day = statusSel?.dataset.day || '?';
        const tr = td.closest('tr[data-row]');
        const subjectName = tr?.querySelector('.row-subject-name')?.textContent?.trim() || '';
        const dayLabel = corrPop.querySelector('.corr-pop-day-label');
        if (dayLabel) dayLabel.textContent = `День ${day}${subjectName ? ' · ' + subjectName : ''}`;

        if (corrPopHolidayNote) {
            const holidayMeta = holidayDays[String(day)] || null;
            if (holidayMeta || isDisabled) {
                corrPopHolidayNote.textContent = holidayMeta ? `Праздник: ${holidayMeta.name} — редактирование недоступно` : 'Редактирование недоступно';
                corrPopHolidayNote.classList.remove('d-none');
            } else {
                corrPopHolidayNote.classList.add('d-none');
            }
        }

        if (!corrPopTeacherReady) {
            corrPopTeacherSel?.insertAdjacentHTML('beforeend', teacherOptionsHtml);
            corrPopTeacherReady = true;
        }
        if (!corrPopSubjectReady) {
            corrPopSubjectSel?.insertAdjacentHTML('beforeend', subjectOptionsHtml);
            corrPopSubjectReady = true;
        }

        const curStatus = statusSel?.value || 'empty';
        corrPopStatusBtns.forEach(btn => btn.classList.toggle('is-active', btn.dataset.status === curStatus));
        if (corrPopTeacherSel) corrPopTeacherSel.value = teacherSel?.value || '';
        if (corrPopSubjectSel) corrPopSubjectSel.value = subjectSel?.value || '';
        corrPopUpdateFields(curStatus);

        if (corrPopApplyBtn) corrPopApplyBtn.disabled = isDisabled;

        corrActiveTd = td;

        // Position near the cell, flip if near viewport edge
        const rect = td.getBoundingClientRect();
        const popW = 264;
        corrPop.style.visibility = 'hidden';
        corrPop.classList.add('is-open');
        const popH = corrPop.offsetHeight;
        corrPop.classList.remove('is-open');
        corrPop.style.visibility = '';

        let top = rect.bottom + 6;
        let left = rect.left;
        if (top + popH > window.innerHeight - 12) top = rect.top - popH - 6;
        if (top < 8) top = 8;
        if (left + popW > window.innerWidth - 12) left = window.innerWidth - popW - 12;
        if (left < 8) left = 8;
        corrPop.style.top = top + 'px';
        corrPop.style.left = left + 'px';
        corrPop.classList.add('is-open');
    }

    corrPopStatusBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            corrPopStatusBtns.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            corrPopUpdateFields(btn.dataset.status);
        });
    });

    corrPopApplyBtn?.addEventListener('click', () => {
        if (!corrActiveTd) return;
        const statusSel = corrActiveTd.querySelector('.cell-status');
        const teacherSel = corrActiveTd.querySelector('.cell-repl');
        const subjectSel = corrActiveTd.querySelector('.cell-repl-subject');
        const activeBtn = corrPop?.querySelector('.corr-status-btn.is-active');
        const newStatus = activeBtn?.dataset.status || 'empty';

        if (statusSel) statusSel.value = newStatus;
        if (teacherSel) teacherSel.value = corrPopTeacherSel?.value || '';
        if (subjectSel) subjectSel.value = corrPopSubjectSel?.value || '';

        const chip = corrActiveTd.querySelector('.status-chip');
        if (chip) {
            const hoursPerClass = corrActiveTd.closest('tr[data-row]')?.querySelector('.row-hours-per-class')?.value || '2';
            const classMap = { empty: 'status-empty', normal: 'status-normal', replaced: 'status-replaced', replacement: 'status-replacement' };
            const valMap = { empty: '•', normal: hoursPerClass, replaced: '■', replacement: hoursPerClass };
            chip.className = 'status-chip ' + (classMap[newStatus] || 'status-empty');
            const valEl = chip.querySelector('.chip-value');
            if (valEl) valEl.textContent = valMap[newStatus] ?? '•';
        }
        corrActiveTd.classList.add('cell-dirty');
        corrPopoverClose();
    });

    corrPopCancelBtn?.addEventListener('click', corrPopoverClose);
    corrPop?.querySelector('.corr-pop-close')?.addEventListener('click', corrPopoverClose);

    document.addEventListener('mousedown', (e) => {
        if (corrPop?.classList.contains('is-open') && !corrPop.contains(e.target) && !e.target.closest('.day-cell')) {
            corrPopoverClose();
        }
    });

    [formBody, subgroupTwoBody].forEach(body => {
        body?.addEventListener('click', (e) => {
            if (!manualToggle?.checked) return;
            const td = e.target.closest('.day-cell.correction-active');
            if (!td) return;
            e.stopPropagation();
            if (corrActiveTd === td && corrPop?.classList.contains('is-open')) {
                corrPopoverClose();
                return;
            }
            corrPopoverOpen(td);
        });
    });
    // === End Correction Popover ===

    manualToggle?.dispatchEvent(new Event('change'));

    // ===== Ghost toggle =====
    const ghostToggle = document.getElementById('ghostToggle');
    const currentActiveSemester = {{ $activeSemester }};

    if (ghostToggle) {
        ghostToggle.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            if (ghostToggle.checked) {
                params.set('ghost', '1');
            } else {
                params.delete('ghost');
            }
            window.location.search = params.toString();
        });
    }
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/tours/form-two.js') }}"></script>
@endpush
