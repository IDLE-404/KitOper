@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
@endpush

@section('content')
@php
    $days = ['Понедельник','Вторник','Среда','Четверг','Пятница'];
    $itemsByGroup = $schedule ?? [];
@endphp

<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Расписание — 1 курс</h1>
            <p class="page-subtitle">Компактный обзор по всем группам</p>
        </div>
        <div class="action-buttons">
            <input type="search" id="groupSearch" class="search-input" placeholder="Поиск по группе или предмету">
            <a href="{{ route('first.schedule.week') }}" class="btn-pill primary">Редактор недели</a>
            <a href="{{ route('first.schedule.form_two') }}" class="btn-pill ghost">Форма 2</a>
        </div>
    </div>

    <div class="groups-compact">
        @foreach($itemsByGroup as $groupId => $groupData)
        @php $groupItems = $groupData['days'] ?? []; @endphp
        <div class="group-compact">
            <div class="group-compact__head">
                <h2 class="group-compact__title">Группа: {{ $groupData['name'] ?? 'Без названия' }}</h2>
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
                            @php $pair = $groupItems[$day][$i] ?? ['sub1'=>[], 'sub2'=>[]]; @endphp
                            @php
                                $filled = !empty($pair['sub1']['subject']);
                            @endphp
                            <div class="grid-cell pair-cell {{ $filled ? 'filled' : 'empty' }}">
                                <a href="#"
                                   class="cell-edit"
                                   title="Редактировать"
                                   data-group="{{ $groupId }}"
                                   data-day="{{ $day }}"
                                   data-lesson="{{ $i }}"
                                   data-subject1="{{ $pair['sub1']['subject_id'] ?? '' }}"
                                   data-teacher1="{{ $pair['sub1']['teacher_id'] ?? '' }}"
                                   data-room1="{{ $pair['sub1']['room'] ?? '' }}"
                                   data-sub1="{{ $pair['sub1']['label'] ?? '' }}"
                                   data-has-sub2="{{ !empty($pair['sub2']) ? '1' : '0' }}"
                                   data-subject2="{{ $pair['sub2']['subject_id'] ?? '' }}"
                                   data-teacher2="{{ $pair['sub2']['teacher_id'] ?? '' }}"
                                   data-room2="{{ $pair['sub2']['room'] ?? '' }}"
                                   data-sub2="{{ $pair['sub2']['label'] ?? '' }}"
                                   data-subject1-title="{{ $pair['sub1']['subject'] ?? '' }}"
                                   data-subject2-title="{{ $pair['sub2']['subject'] ?? '' }}"
                                >✏️</a>
                                <div class="cell-line">
                                    <span class="cell-title">{{ $pair['sub1']['subject'] ?? '—' }}</span>
                                </div>
                                <div class="cell-meta">
                                    <span class="pill"><span>👤</span>{{ $pair['sub1']['teacher'] ?? '—' }}</span>
                                    <span class="pill"><span>🏫</span>{{ $pair['sub1']['room'] ?? '—' }}</span>
                                    <span class="pill"><span>🔸</span>{{ $pair['sub1']['label'] ?? '—' }}</span>
                                </div>
                                @if(!empty($pair['sub2']))
                                <div class="cell-line subpair-line">
                                    <span class="label-sub">2 подгруппа</span>
                                    <span class="cell-title sub2">{{ $pair['sub2']['subject'] ?? '—' }}</span>
                                </div>
                                <div class="cell-meta subpair">
                                    <span class="pill"><span>👤</span>{{ $pair['sub2']['teacher'] ?? '—' }}</span>
                                    <span class="pill"><span>🏫</span>{{ $pair['sub2']['room'] ?? '—' }}</span>
                                    <span class="pill"><span>🔸</span>{{ $pair['sub2']['label'] ?? '—' }}</span>
                                </div>
                                @endif
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const subjects = @json($subjects ?? []);
        const teachers = @json($teachers ?? []);

        const modal = document.getElementById('pairModal');
    const overlay = document.getElementById('modalOverlay');
    const form = document.getElementById('pairForm');

    const subject1 = document.getElementById('modalSubject1');
    const teacher1 = document.getElementById('modalTeacher1');
    const room1 = document.getElementById('modalRoom1');

    const toggleSub2 = document.getElementById('modalHasSub2');
    const subject2 = document.getElementById('modalSubject2');
    const teacher2 = document.getElementById('modalTeacher2');
    const room2 = document.getElementById('modalRoom2');
    const sub2Block = document.getElementById('subgroup2Block');

    const hiddenGroup = document.getElementById('modalGroupId');
    const hiddenDay = document.getElementById('modalDay');
    const hiddenLesson = document.getElementById('modalLesson');

    const openModal = (data) => {
        hiddenGroup.value = data.group;
        hiddenDay.value = data.day;
        hiddenLesson.value = data.lesson;

        subject1.value = data.subject1 || '';
        teacher1.value = data.teacher1 || '';
        room1.value = data.room1 || '';

        subject2.value = data.subject2 || '';
        teacher2.value = data.teacher2 || '';
        room2.value = data.room2 || '';

        toggleSub2.checked = data.hasSub2 === '1';
        sub2Block.classList.toggle('d-none', !toggleSub2.checked);

        overlay.classList.add('show');
        modal.classList.add('show');
    };

    const closeModal = () => {
        overlay.classList.remove('show');
        modal.classList.remove('show');
    };

    toggleSub2.addEventListener('change', () => {
        sub2Block.classList.toggle('d-none', !toggleSub2.checked);
        if (!toggleSub2.checked) {
            subject2.value = '';
            teacher2.value = '';
            room2.value = '';
        }
    });

    document.querySelectorAll('.cell-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(btn.dataset);
        });
    });

    document.getElementById('modalClose').addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    const searchInput = document.getElementById('groupSearch');
    if (searchInput) {
        const groups = document.querySelectorAll('.group-compact');
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            groups.forEach(group => {
                const text = group.innerText.toLowerCase();
                group.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Поиск в селектах модалки
    document.querySelectorAll('.search-field').forEach(input => {
        const targetId = input.dataset.target;
        const select = document.getElementById(targetId);
        if (!select) return;
        const options = Array.from(select.options);

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase();
            select.innerHTML = '';
            options
                .filter(opt => opt.text.toLowerCase().includes(term))
                .forEach(opt => select.appendChild(opt.cloneNode(true)));
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        try {
            const res = await fetch("{{ route('first.schedule.pair.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                alert(err.message || 'Ошибка сохранения');
                return;
            }
            closeModal();
            window.location.reload();
        } catch (error) {
            alert('Ошибка сети');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;
    z-index: 90;
}
.modal-overlay.show { opacity: 1; pointer-events: all; }
.modal-card {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.96);
    background: #fff;
    border-radius: 12px;
    width: min(720px, 95%);
    padding: 18px 20px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.15s ease, opacity 0.15s ease;
    z-index: 91;
}
.modal-card.show {
    opacity: 1;
    pointer-events: all;
    transform: translate(-50%, -50%) scale(1);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.modal-title { margin: 0; font-size: 18px; font-weight: 700; }
.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #475569;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px;
}
.form-label { font-weight: 600; color: #334155; margin-bottom: 6px; display: block; }
.form-control, .form-select {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 10px;
    font-size: 14px;
}
.form-check { display: flex; align-items: center; gap: 8px; margin: 10px 0; }
.btn-primary {
    background: linear-gradient(135deg, #4f7cff, #7a6bff);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 10px 16px;
    border-radius: 10px;
}
.d-none { display: none !important; }
</style>
@endpush

@push('scripts')
<div id="modalOverlay" class="modal-overlay"></div>
<div id="pairModal" class="modal-card">
    <div class="modal-header">
        <h3 class="modal-title">Редактировать пару</h3>
        <button type="button" class="modal-close" id="modalClose">✕</button>
    </div>
    <form id="pairForm">
        <input type="hidden" name="group_id" id="modalGroupId">
        <input type="hidden" name="study_day" id="modalDay">
        <input type="hidden" name="lesson_number" id="modalLesson">

        <div class="form-grid">
            <div>
                <label class="form-label">Предмет (подгруппа 1)</label>
                <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject1">
                <select class="form-select" name="subject_id" id="modalSubject1">
                    <option value="">—</option>
                    @foreach($subjects as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Преподаватель</label>
                <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher1">
                <select class="form-select" name="teacher_id" id="modalTeacher1">
                    <option value="">—</option>
                    @foreach($teachers as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Кабинет</label>
                <input type="text" class="form-control" name="room_id" id="modalRoom1" placeholder="101">
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="modalHasSub2" name="has_sub2" value="1">
            <label class="form-check-label" for="modalHasSub2">Добавить подгруппу 2</label>
        </div>

        <div id="subgroup2Block" class="d-none">
            <div class="form-grid">
                <div>
                    <label class="form-label">Предмет (подгруппа 2)</label>
                    <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject2">
                    <select class="form-select" name="subject_id_2" id="modalSubject2">
                        <option value="">—</option>
                        @foreach($subjects as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Преподаватель</label>
                    <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher2">
                    <select class="form-select" name="teacher_id_2" id="modalTeacher2">
                        <option value="">—</option>
                        @foreach($teachers as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Кабинет</label>
                    <input type="text" class="form-control" name="room_id_2" id="modalRoom2" placeholder="102">
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
</div>
@endpush
