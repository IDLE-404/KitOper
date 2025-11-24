@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/form-two.css') }}">
@endpush

@section('content')
@php
    $months = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];
    $monthDays = $days ?? range(1, 31);
@endphp

<div class="form2-shell">
    <div class="form2-head">
        <div>
            <p class="overline">Ведомость учета учебного времени преподавателей (в часах)</p>
            <h1>Форма 2 — 1 курс</h1>
            <p class="muted">Колледж информационных технологий • Учёт по месяцам и группам</p>
        </div>
        <div class="meta-grid">
            <div class="d-flex align-items-end">
                <a href="{{ route('first.schedule.index') }}" class="btn btn-outline-secondary">← Назад</a>
            </div>
            <label class="field">
                <span>Группа</span>
                <select id="groupSelect">
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected($selectedGroupId === $g->id)>{{ $g->group_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Месяц</span>
                <select id="monthSelect">
                    @foreach([9,10,11,12] as $num)
                        <option value="{{ $num }}" @selected($month === $num)>{{ $months[$num] ?? $num }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Год</span>
                <select id="yearSelect">
                    @for($y = $currentYear; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </label>
        </div>
    </div>

    <div class="table-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold">Форма 2 — {{ $selectedGroupName ?? 'группа не выбрана' }}</div>
            <span class="muted small">Период: {{ $months[$month] ?? $month }} {{ $year }}</span>
        </div>

        <table class="form2-table" id="form2Table">
            <thead>
                <tr>
                    <th class="col-num">№</th>
                    <th class="col-subject">Предмет</th>
                    <th class="col-teacher">Основной учитель</th>
                    <th class="col-start">Норматив (начало)</th>
                    @foreach($monthDays as $d)
                        <th class="col-day">{{ $d }}</th>
                    @endforeach
                    <th class="col-total">Итого</th>
                    <th class="col-left">Остаток</th>
                </tr>
            </thead>
            <tbody id="form2Body">
                {{-- Рендерится JS --}}
            </tbody>
        </table>
    </div>

    <div class="table-wrapper mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold">Замены</div>
            <span class="muted small">Показываем только выбранную группу</span>
        </div>
        <div id="replacementsBlock" class="replacements-list"></div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="primary-btn" id="saveForm2">Сохранить изменения</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const groups = @json($groups);
    const initialRows = @json($subjectRows ?? []);
    const initialReplacements = @json($replacements ?? []);
    const monthDays = @json(array_values($monthDays));
    const teachersSummary = @json($teachersSummary ?? []);
    const teachersList = @json($teachers ?? []);
    const monthsMap = @json($months);
    const meta = {
        group: @json($selectedGroupId),
        month: @json($month),
        year: @json($year),
        saveUrl: "{{ route('first.schedule.form_two.save') }}",
        csrf: document.querySelector('meta[name="csrf-token"]')?.content,
        defaultHoursPerClass: 2,
    };

    const tableBody = document.getElementById('form2Body');
    const replacementsBlock = document.getElementById('replacementsBlock');
    const groupSelect = document.getElementById('groupSelect');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const saveBtn = document.getElementById('saveForm2');

    let state = {
        rows: initialRows.map((r) => ({
            subject: r.subject ?? r.subject_name ?? '',
            teacher: r.teacher ?? r.teacher_name ?? '—',
            total_hours: Number(r.total_hours ?? 0),
            hours_per_class: Number(r.hours_per_class ?? meta.defaultHoursPerClass),
            days: (r.days ?? []).map((d) => ({
                day: d.day,
                status: d.type ?? 'normal',
                replacement_teacher: d.replacement_teacher ?? null,
                bonus_hours: d.bonus_hours ?? null,
            })),
        })),
        replacements: initialReplacements ?? [],
        teachers: teachersSummary || {},
    };

    function statusToColor(status) {
        if (status === 'sick') return 'yellow';
        if (status === 'replacement') return 'yellow';
        return 'white';
    }

    function renderTable() {
        let html = '';
        state.rows.forEach((row, idx) => {
            const usedHours = calcRowUsed(row);
            const left = Math.max((row.total_hours || 0) - usedHours, 0);
            html += `
                <tr>
                    <td class="col-num sticky">${idx + 1}</td>
                    <td class="col-subject sticky">${row.subject}</td>
                    <td class="col-teacher sticky">
                        <select class="teacher-select" data-row-index="${idx}">
                            <option value="">—</option>
                            ${teachersList.map((t) => `
                                <option value="${t.teacher_name}" ${row.teacher === t.teacher_name ? 'selected' : ''}>
                                    ${t.teacher_name}
                                </option>
                            `).join('')}
                        </select>
                    </td>
                    <td class="col-start sticky">
                        <input
                            type="number"
                            class="form-control total-hours-input"
                            data-row-index="${idx}"
                            value="${row.total_hours ?? 0}"
                            min="0"
                            step="1"
                        >
                    </td>
                    ${monthDays.map((day) => renderCell(row, day)).join('')}
                    <td class="col-total">${usedHours}</td>
                    <td class="col-left">${left}</td>
                </tr>
            `;
        });

        const totals = state.rows.reduce((acc, r) => {
            acc.total += Number(r.total_hours) || 0;
            acc.used += calcRowUsed(r);
            acc.left += Math.max((r.total_hours || 0) - calcRowUsed(r), 0);
            return acc;
        }, {total:0, used:0, left:0});

        html += `
            <tr class="total-row">
                <td colspan="3" class="text-right fw-bold">Итого</td>
                <td class="fw-bold">${totals.total}</td>
                ${monthDays.map(() => '<td></td>').join('')}
                <td class="fw-bold">${totals.used}</td>
                <td class="fw-bold">${totals.left}</td>
            </tr>
        `;
        tableBody.innerHTML = html;

        // навесим change на total_hours инпуты
        tableBody.querySelectorAll('.total-hours-input').forEach((input) => {
            input.addEventListener('change', (e) => {
                const idx = Number(input.dataset.rowIndex);
                const val = Number(input.value || 0);
                if (state.rows[idx]) {
                    state.rows[idx].total_hours = val;
                    recalcTeachers();
                    renderTable();
                    renderReplacements();
                }
            });
        });

        // основной учитель select
        tableBody.querySelectorAll('.teacher-select').forEach((sel) => {
            sel.addEventListener('change', () => {
                const idx = Number(sel.dataset.rowIndex);
                if (state.rows[idx]) {
                    state.rows[idx].teacher = sel.value || '—';
                    recalcTeachers();
                    renderTable();
                    renderReplacements();
                }
            });
        });
    }

    function renderCell(row, day) {
        const cell = row.days.find((d) => Number(d.day) === Number(day)) || {status: 'normal'};
                const colorClass = statusToColor(cell.status);
                const isReplacement = cell.status === 'replacement';
                const bonus = cell.bonus_hours ?? 2;
                const displayValue = cell.status === 'sick' ? '—' : (cell.status === 'replacement' && isReplacement ? '' : '');
                return `
            <td class="day-cell ${colorClass}" data-subject="${row.subject}" data-day="${day}">
                <div class="cell-editor">
                    <select class="cell-status">
                        <option value="normal" ${cell.status === 'normal' ? 'selected' : ''}>Обычное занятие</option>
                        <option value="sick" ${cell.status === 'sick' ? 'selected' : ''}>Учитель отсутствовал</option>
                        <option value="replacement" ${cell.status === 'replacement' ? 'selected' : ''}>Замена</option>
                    </select>
                    <select class="cell-replacement" ${isReplacement ? '' : 'style="display:none"'}>
                        <option value="">— заменяющий —</option>
                        ${teachersList.map((t) => `
                            <option value="${t.teacher_name}" ${cell.replacement_teacher === t.teacher_name ? 'selected' : ''}>${t.teacher_name}</option>
                        `).join('')}
                    </select>
                    ${isReplacement ? `<span class="red">2</span>` : `<span>${displayValue}</span>`}
                </div>
            </td>
        `;
    }

    function calcRowUsed(row) {
        return row.days.reduce((sum, d) => {
            if (d.status === 'normal') {
                return sum + (row.hours_per_class || meta.defaultHoursPerClass);
            }
            return sum;
        }, 0);
    }

    function recalcTeachers() {
        // Итоги по преподавателям пока не выводим — оставляем для расширения
        state.teachers = {};
    }

    function renderReplacements() {
        const items = [];
        state.rows.forEach((row) => {
            row.days.forEach((d) => {
                if (d.status === 'replacement' && d.replacement_teacher) {
                    items.push({
                        day: d.day,
                        subject: row.subject,
                        absent: row.teacher,
                        replacement: d.replacement_teacher,
                        hours: d.bonus_hours ?? 2,
                    });
                }
            });
        });

        if (!items.length) {
            replacementsBlock.innerHTML = '<div class="text-muted">Замены не найдены</div>';
            return;
        }

        replacementsBlock.innerHTML = items
            .sort((a, b) => a.day - b.day)
            .map((item) => `
                <div class="replacement-row">
                    <span class="pill">${item.day}</span>
                    <span class="replacement-subject">${item.subject}</span>
                    <span class="muted">${item.absent}</span>
                    <span class="arrow">→</span>
                    <span class="replacement-teacher">${item.replacement}</span>
                    <span class="hours">+${item.hours} ч.</span>
                </div>
            `).join('');
    }

    function syncStateFromDOM() {
        const rows = Array.from(tableBody.querySelectorAll('tr')).filter((tr) => tr.querySelector('.cell-editor'));
        rows.forEach((tr, idx) => {
            const subject = tr.dataset.subject || state.rows[idx]?.subject || '';
            const rowState = state.rows.find((r) => r.subject === subject) || state.rows[idx];
            const cells = tr.querySelectorAll('.day-cell');
            rowState.days = [];
            cells.forEach((cellEl) => {
                const status = cellEl.querySelector('.cell-status')?.value || 'normal';
                const replSelect = cellEl.querySelector('.cell-replacement');
                const replacementTeacher = status === 'replacement' ? (replSelect?.value || null) : null;
                rowState.days.push({
                    day: Number(cellEl.dataset.day),
                    status,
                    replacement_teacher: replacementTeacher,
                    bonus_hours: status === 'replacement' ? 2 : null,
                });
            });
        });
        recalcTeachers();
        renderTable();
        renderReplacements();
    }

    function attachHandlers() {
        tableBody.addEventListener('change', (e) => {
            if (e.target.classList.contains('cell-status')) {
                const cell = e.target.closest('.day-cell');
                const replSelect = cell.querySelector('.cell-replacement');
                if (e.target.value === 'replacement') {
                    replSelect.style.display = '';
                } else {
                    replSelect.style.display = 'none';
                }
                syncStateFromDOM();
            }
            if (e.target.classList.contains('cell-replacement')) {
                syncStateFromDOM();
            }
        });

        [groupSelect, monthSelect, yearSelect].forEach((el) => {
            el?.addEventListener('change', () => {
                const params = new URLSearchParams(window.location.search);
                if (groupSelect) params.set('group', groupSelect.value);
                if (monthSelect) params.set('month', monthSelect.value);
                if (yearSelect) params.set('year', yearSelect.value);
                window.location.search = params.toString();
            });
        });

        saveBtn?.addEventListener('click', async () => {
            // соберём текущее состояние
            syncStateFromDOM();
            const payload = {
                group_id: meta.group,
                month: meta.month,
                year: meta.year,
                rows: state.rows,
            };
            const form = new FormData();
            form.set('_token', meta.csrf || '');
            form.set('group_id', meta.group || '');
            form.set('month', meta.month || '');
            form.set('year', meta.year || '');
            form.set('data', JSON.stringify(payload));

            try {
                const res = await fetch(meta.saveUrl, {
                    method: 'POST',
                    body: form,
                });
                if (!res.ok) {
                    const msg = await res.text();
                    alert('Не удалось сохранить: ' + msg);
                } else {
                    alert('Изменения сохранены');
                }
            } catch (err) {
                alert('Ошибка сохранения');
            }
        });
    }

    // Первый рендер
    recalcTeachers();
    renderTable();
    renderReplacements();
    attachHandlers();
</script>
@endpush
