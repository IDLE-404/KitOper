@extends('layouts.app')

@section('content')
@php
    $months = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];
@endphp

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Форма 2 — 1 курс</h1>
            <div class="text-muted">Учёт часов по месяцам и группам</div>
        </div>
        <div>
            <a href="{{ route('first.schedule.index') }}" class="btn btn-outline-secondary">← Назад</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Группа</label>
                    <select class="form-select" id="groupSelect">
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" @selected($groupId === $g->id)>{{ $g->group_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Месяц</label>
                    <select class="form-select" id="monthSelect">
                        @foreach($months as $num => $label)
                            <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Год</label>
                    <input type="number" class="form-control" id="yearInput" value="{{ $year }}">
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-primary" id="reloadBtn">Показать</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Группа: {{ optional($groups->firstWhere('id', $groupId))->group_name ?? '—' }}</div>
                <div class="text-muted">{{ $months[$month] ?? $month }} {{ $year }}</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Предмет</th>
                            <th>Преподаватель</th>
                            <th>Норматив</th>
                            @foreach($days as $d)
                                <th class="text-center" style="min-width:80px;">{{ $d }}</th>
                            @endforeach
                            <th>Списано</th>
                            <th>Остаток</th>
                        </tr>
                    </thead>
                    <tbody id="formBody">
                        @forelse($records as $idx => $row)
                            @php
                                $used = $row['used_hours_total'] ?? 0;
                                $left = max(0, ($row['total_hours'] ?? 0) - $used);
                            @endphp
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
                                <td style="min-width:110px;">
                                    <div class="small text-muted">Всего</div>
                                    <div class="fw-semibold">{{ $row['total_hours'] ?? 0 }}</div>
                                    <div class="small text-muted">По паре: {{ $row['hours_per_class'] ?? 2 }}</div>
                                </td>
                                @foreach($days as $d)
                                    @php $cell = $row['days'][$d] ?? []; @endphp
                                    <td class="text-center">
                                        <select class="form-select form-select-sm cell-status" data-day="{{ $d }}" style="min-width:90px;">
                                            <option value="normal" @selected(($cell['status'] ?? 'normal') === 'normal')>Норма</option>
                                            <option value="sick" @selected(($cell['status'] ?? '') === 'sick')>Болел</option>
                                            <option value="replacement" @selected(($cell['status'] ?? '') === 'replacement')>Замена</option>
                                        </select>
                                        <select class="form-select form-select-sm cell-repl mt-1" data-day="{{ $d }}">
                                            <option value="">— заменяющий</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}" @selected(($cell['replacement_teacher_id'] ?? null) == $t->id)>{{ $t->teacher_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                                <td class="fw-semibold used-cell">{{ $used }}</td>
                                <td class="fw-semibold text-success left-cell">{{ $left }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 6 + count($days) }}" class="text-center text-muted">Данных нет</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <button class="btn btn-success" id="saveBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const groupSelect = document.getElementById('groupSelect');
    const monthSelect = document.getElementById('monthSelect');
    const yearInput = document.getElementById('yearInput');
    const reloadBtn = document.getElementById('reloadBtn');
    const saveBtn = document.getElementById('saveBtn');
    const formBody = document.getElementById('formBody');

    reloadBtn.addEventListener('click', () => {
        const params = new URLSearchParams();
        params.set('group_id', groupSelect.value);
        params.set('month', monthSelect.value);
        params.set('year', yearInput.value);
        window.location.search = params.toString();
    });

    saveBtn.addEventListener('click', async () => {
        const rows = [];
        formBody.querySelectorAll('tr[data-row]').forEach((tr) => {
            const subjectId = Number(tr.querySelector('.row-subject').value);
            const teacherId = Number(tr.querySelector('.row-teacher').value) || null;
            const totalHours = Number(tr.querySelector('.row-total-hours').value) || 0;
            const hoursPerClass = Number(tr.querySelector('.row-hours-per-class').value) || 2;
            const days = {};
            tr.querySelectorAll('.cell-status').forEach((sel) => {
                const day = sel.dataset.day;
                const status = sel.value;
                const replSel = tr.querySelector(`.cell-repl[data-day="${day}"]`);
                const replacement_teacher_id = replSel && replSel.value ? Number(replSel.value) : null;
                days[day] = { status, replacement_teacher_id };
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
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                alert(err.message || 'Ошибка сохранения');
                return;
            }
            alert('Сохранено');
            window.location.reload();
        } catch (e) {
            alert('Ошибка сети');
        }
    });
</script>
@endpush
