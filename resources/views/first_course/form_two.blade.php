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
    $subjects = $subjects ?? collect();
    
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
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Курс</label>
                    <select class="form-select" id="courseSelect">
                        @for($c = 1; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
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
                    <input type="number" class="form-control" id="yearInput" value="{{ $year }}">
                </div>
                <div class="col-md-1 text-end">
                    <button class="btn btn-primary w-100" id="reloadBtn">OK</button>
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
            <div class="ms-auto text-muted small">
                Статусы поступают из расписания. Ручная коррекция — только в исключительных случаях.
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                <div>
                    <div class="fw-semibold">Группа: {{ optional($groups->firstWhere('id', $groupId))->group_name ?? '—' }}</div>
                    <div class="text-muted small">{{ $months[$month] ?? $month }} {{ $year }}</div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="manualToggle">
                        <label class="form-check-label small text-muted" for="manualToggle">Режим коррекции</label>
                    </div>
                    <button class="btn btn-success d-none" id="saveBtn">Сохранить коррекцию</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle form-two-table">
                    <thead>
                        <tr>
                            <th class="text-muted">#</th>
                            <th class="text-muted">Предмет</th>
                            <th class="text-muted">Преподаватель</th>
                            <th class="text-muted">Норматив</th>
                            @foreach($days as $d)
                                <th class="text-center text-muted day-head {{ isset($weekendDays[$d]) ? 'weekend' : '' }}">{{ $d }}</th>
                            @endforeach
                            <th class="text-muted">Использовано</th>
                            <th class="text-muted">Бонус</th>
                            <th class="text-muted">Остаток</th>
                        </tr>
                    </thead>
                    <tbody id="formBody">
                        @forelse($rows as $idx => $row)
                            <tr data-row="{{ $idx }}">
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $row['subject_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-subject" value="{{ $row['subject_id'] }}">
                                    <input type="hidden" class="row-hours-per-class" value="{{ $row['hours_per_class'] ?? 2 }}">
                                    <input type="hidden" class="row-total-hours" value="{{ $row['total_hours'] ?? 0 }}">
                                </td>
                                <td>
                                    <div>{{ $row['teacher_name'] ?? '—' }}</div>
                                    <input type="hidden" class="row-teacher" value="{{ $row['teacher_id'] }}">
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        Остаток на начало:
                                        <strong>{{ $row['hours_left_start'] ?? $row['total_hours'] ?? 0 }}</strong>
                                    </div>
                                    <div class="small text-muted">По паре: {{ $row['hours_per_class'] ?? 2 }}</div>
                                    <div class="manual-input d-none mt-2">
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
                                    @endphp
                                    <td class="text-center day-cell {{ isset($weekendDays[$d]) ? 'weekend' : '' }}">
                                        <div class="status-chip status-{{ $status }}" title="{{ $tooltip ?: 'Нет записи' }}">
                                            <span class="chip-value">{{ $value }}</span>
                                        </div>
                                        <div class="manual-input d-none mt-1">
                                            <select class="form-select form-select-sm cell-status" data-day="{{ $d }}">
                                                <option value="empty" @selected($status === 'empty')>—</option>
                                                <option value="normal" @selected($status === 'normal')>Норма</option>
                                                <option value="replaced" @selected($status === 'replaced')>Замена (замещённая)</option>
                                                <option value="replacement" @selected($status === 'replacement')>Замена (замещающая)</option>
                                            </select>
                                            <select class="form-select form-select-sm cell-repl mt-1" data-day="{{ $d }}">
                                                <option value="">— заменяющий</option>
                                                @foreach($teachers as $t)
                                                    <option value="{{ $t->id }}" @selected(($cell['replacement_teacher_id'] ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                                @endforeach
                                            </select>
                                            <select class="form-select form-select-sm cell-repl-subject mt-1" data-day="{{ $d }}">
                                                <option value="">— замещающий предмет</option>
                                                @foreach($subjects as $s)
                                                    <option value="{{ $s->id }}" @selected(($cell['replacement_subject_id'] ?? null) == $s->id)>{{ $s->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                @endforeach
                                <td class="fw-semibold used-cell">{{ $row['used_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-primary">{{ $row['bonus_hours_total'] ?? 0 }}</td>
                                <td class="fw-semibold text-success">{{ $row['hours_left'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 7 + $daysCount }}" class="text-center text-muted">Данных нет</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

@endsection

@push('styles')
<style>
    .form-two-container {
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }
    .form-two-container .card {
        border-radius: 12px;
    }
    .legend-row .status-chip {
        min-width: 32px;
    }
    .form-two-table .day-head {
        min-width: 54px;
    }
    .form-two-table .day-head.weekend {
        background-color: #d1fae5 !important;
    }
    .day-cell {
        min-width: 70px;
        vertical-align: middle;
    }
    .day-cell.weekend {
        background-color: #d1fae5 !important;
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
    .chip-value {
        display: inline-block;
        min-width: 14px;
        text-align: center;
    }
    .manual-input .form-select {
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
</style>
@endpush

@push('scripts')
<script>
    const groupSelect = document.getElementById('groupSelect');
    const monthSelect = document.getElementById('monthSelect');
    const yearInput = document.getElementById('yearInput');
    const courseSelect = document.getElementById('courseSelect');
    const reloadBtn = document.getElementById('reloadBtn');
    const saveBtn = document.getElementById('saveBtn');
    const formBody = document.getElementById('formBody');
    const manualToggle = document.getElementById('manualToggle');

    courseSelect?.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('course', courseSelect.value);
        params.delete('group_id');
        window.location.search = params.toString();
    });

    reloadBtn?.addEventListener('click', () => {
        const params = new URLSearchParams();
        params.set('group_id', groupSelect.value);
        params.set('month', monthSelect.value);
        params.set('year', yearInput.value);
        params.set('course', courseSelect ? courseSelect.value : '{{ $course ?? 1 }}');
        window.location.search = params.toString();
    });

    manualToggle?.addEventListener('change', () => {
        const enabled = manualToggle.checked;
        document.querySelectorAll('.manual-input').forEach(el => el.classList.toggle('d-none', !enabled));
        saveBtn?.classList.toggle('d-none', !enabled);
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

        const payload = {
            group_id: Number(groupSelect.value),
            month: Number(monthSelect.value),
            year: Number(yearInput.value),
            course: courseSelect ? Number(courseSelect.value) : 1,
            allow_manual: true,
            rows,
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
</script>
@endpush
