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
            <div class="mt-2">
                <span class="pill {{ ($weekMode ?? 'num') === 'den' ? 'primary' : 'soft' }}">
                    Сейчас показывается: {{ ($weekMode ?? 'num') === 'den' ? 'неделя B (знаменатель)' : 'неделя A (числитель)' }} (неделя от {{ $weekStart ?? '—' }})
                </span>
            </div>
        </div>
        <div class="action-buttons">
            <input type="search" id="groupSearch" class="search-input" placeholder="Поиск по группе или предмету">
            <input type="date" id="weekStartInput" class="search-input" value="{{ $weekStart ?? '' }}" style="width:auto;">
            <button type="button" class="btn-pill ghost" id="weekStartApply">Показать неделю</button>
            <button type="button" class="btn-pill ghost" id="weekModeToggle" data-week="{{ $weekMode ?? 'num' }}">
                Переключить неделю
            </button>
            <a href="{{ route('first.schedule.week', ['group_id' => $schedule ? array_key_first($schedule) : null, 'week_start' => $weekStart ?? null]) }}#semester-expand" class="btn-pill ghost">Развернуть семестр</a>
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
                            @php $pair = $groupItems[$day][$i] ?? ['sub1'=>[], 'sub2'=>[], 'has_denominator' => false]; @endphp
                            @php
                                $filled = ($pair['sub1']['has_den'] ?? false) || ($pair['sub1']['has_num'] ?? false) || ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                $hasConflict = ($pair['sub1']['active_conflict'] ?? false) || ($pair['sub2']['active_conflict'] ?? false);
                                $hasSubgroups = ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                $pairStatus = '';
                                if (($pair['sub1']['is_replacement'] ?? false) || ($pair['sub2']['is_replacement'] ?? false)) {
                                    $pairStatus = 'pair-replacement';
                                } elseif (($pair['sub1']['is_absent'] ?? false) || ($pair['sub2']['is_absent'] ?? false)) {
                                    $pairStatus = 'pair-sick';
                                }
                            @endphp
                            <div class="grid-cell pair-cell {{ $filled ? 'filled' : 'empty' }} {{ $hasConflict ? 'conflict' : '' }} {{ $pairStatus }}">
                                <a href="#"
                                   class="cell-edit"
                                   title="Редактировать"
                                   data-group="{{ $groupId }}"
                                   data-day="{{ $day }}"
                                   data-lesson="{{ $i }}"
                                    data-subject1="{{ $pair['sub1']['subject_num_id'] ?? '' }}"
                                    data-teacher1="{{ $pair['sub1']['teacher_num_id'] ?? '' }}"
                                    data-room1="{{ $pair['sub1']['room_num'] ?? '' }}"
                                    data-den-subject1="{{ $pair['sub1']['subject_den_id'] ?? '' }}"
                                    data-den-teacher1="{{ $pair['sub1']['teacher_den_id'] ?? '' }}"
                                    data-den-room1="{{ $pair['sub1']['room_den'] ?? '' }}"
                                    data-sub1="1"
                                    data-has-sub2="{{ $hasSubgroups ? '1' : '0' }}"
                                    data-subject2="{{ $pair['sub2']['subject_num_id'] ?? '' }}"
                                    data-teacher2="{{ $pair['sub2']['teacher_num_id'] ?? '' }}"
                                    data-room2="{{ $pair['sub2']['room_num'] ?? '' }}"
                                    data-den-subject2="{{ $pair['sub2']['subject_den_id'] ?? '' }}"
                                    data-den-teacher2="{{ $pair['sub2']['teacher_den_id'] ?? '' }}"
                                    data-den-room2="{{ $pair['sub2']['room_den'] ?? '' }}"
                                    data-sub2="2"
                                    data-subject1-title="{{ $pair['sub1']['subject_num'] ?? '' }}"
                                    data-subject2-title="{{ $pair['sub2']['subject_num'] ?? '' }}"
                                    data-week-mode="{{ ($weekMode ?? 'num') === 'den' ? 'denominator' : 'numerator' }}"
                                    data-has-denominator="{{ $pair['has_denominator'] ? '1' : '0' }}"
                                    data-week-start="{{ $weekStart ?? '' }}"
                                    data-absent1="{{ ($pair['sub1']['is_absent'] ?? false) ? '1' : '0' }}"
                                    data-absent2="{{ ($pair['sub2']['is_absent'] ?? false) ? '1' : '0' }}"
                                    data-replacement1="{{ ($pair['sub1']['is_replacement'] ?? false) ? '1' : '0' }}"
                                    data-replacement2="{{ ($pair['sub2']['is_replacement'] ?? false) ? '1' : '0' }}"
                                    data-replacement-teacher-1="{{ $pair['sub1']['replacement_teacher_id'] ?? '' }}"
                                    data-replacement-teacher-2="{{ $pair['sub2']['replacement_teacher_id'] ?? '' }}"
                                    data-replacement-subject-1="{{ $pair['sub1']['replacement_subject_id'] ?? '' }}"
                                    data-replacement-subject-2="{{ $pair['sub2']['replacement_subject_id'] ?? '' }}"
                                    data-replacement-comment-1="{{ $pair['sub1']['replacement_comment'] ?? '' }}"
                                    data-replacement-comment-2="{{ $pair['sub2']['replacement_comment'] ?? '' }}"
                                >✏️</a>
                                @php $main = $pair['sub1'] ?? []; @endphp
                                <div class="cell-line main-line sub-line">
                                    <span class="pill badge-sub">1</span>
                                    @if($main['is_absent'] ?? false)
                                        <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                    @elseif($main['is_replacement'] ?? false)
                                        <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                    @endif
                                    <span class="cell-title emphasis">{{ $main['active_subject'] ?? '—' }}</span>
                                    @if(($main['replacement_subject'] ?? null) && ($main['is_replacement'] ?? false) && ($main['replacement_subject'] !== ($main['active_subject'] ?? null)))
                                        <span class="text-danger ms-1">→ {{ $main['replacement_subject'] }}</span>
                                    @endif
                                </div>
                                <div class="cell-meta">
                                    <span class="pill">
                                        <span>👤</span>{{ $main['active_teacher'] ?? '—' }}
                                        @if(($main['replacement_teacher'] ?? null) && ($main['is_replacement'] ?? false))
                                            <span class="text-danger ms-1">→ {{ $main['replacement_teacher'] }}</span>
                                        @endif
                                    </span>
                                    <span class="pill room-pill {{ ($main['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($main['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                        <span>🏫</span>{{ $main['active_room'] ?? '—' }}
                                    </span>
                                    <span class="pill"><span>🔸</span>{{ $main['label'] ?? '—' }}</span>
                                </div>
                                @if($main['active_conflict'] ?? false)
                                    <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                @endif
                                @php $sub2 = $pair['sub2'] ?? []; @endphp
                                @if($hasSubgroups)
                                    <div class="cell-line subpair-line">
                                        <span class="pill badge-sub soft">2</span>
                                        @if($sub2['is_absent'] ?? false)
                                            <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                        @elseif($sub2['is_replacement'] ?? false)
                                            <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                        @endif
                                        <span class="cell-title sub2 emphasis">{{ $sub2['active_subject'] ?? '—' }}</span>
                                        @if(($sub2['replacement_subject'] ?? null) && ($sub2['is_replacement'] ?? false) && ($sub2['replacement_subject'] !== ($sub2['active_subject'] ?? null)))
                                            <span class="text-danger ms-1">→ {{ $sub2['replacement_subject'] }}</span>
                                        @endif
                                    </div>
                                    <div class="cell-meta subpair">
                                        <span class="pill">
                                            <span>👤</span>{{ $sub2['active_teacher'] ?? '—' }}
                                            @if(($sub2['replacement_teacher'] ?? null) && ($sub2['is_replacement'] ?? false))
                                                <span class="text-danger ms-1">→ {{ $sub2['replacement_teacher'] }}</span>
                                            @endif
                                        </span>
                                        <span class="pill room-pill {{ ($sub2['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($sub2['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                            <span>🏫</span>{{ $sub2['active_room'] ?? '—' }}
                                        </span>
                                        <span class="pill"><span>🔸</span>{{ $sub2['label'] ?? '—' }}</span>
                                    </div>
                                    @if($sub2['active_conflict'] ?? false)
                                        <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                    @endif
                                @endif
                                @if($pair['has_denominator'])
                                    <div class="den-separator" title="Разделение числитель/знаменатель"></div>
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
    const weekToggle = document.getElementById('weekModeToggle');
    const weekStartPicker = document.getElementById('weekStartInput');
    const weekStartApply = document.getElementById('weekStartApply');

    const subject1 = document.getElementById('modalSubject1');
    const teacher1 = document.getElementById('modalTeacher1');
    const room1 = document.getElementById('modalRoom1');
    const subject1Den = document.getElementById('modalSubject1Den');
    const teacher1Den = document.getElementById('modalTeacher1Den');
    const room1Den = document.getElementById('modalRoom1Den');
    const weekModeInput = document.getElementById('modalWeekMode');
    const weekStartHidden = document.getElementById('modalWeekStart');

    const toggleSub2 = document.getElementById('modalHasSub2');
    const subject2 = document.getElementById('modalSubject2');
    const teacher2 = document.getElementById('modalTeacher2');
    const room2 = document.getElementById('modalRoom2');
    const subject2Den = document.getElementById('modalSubject2Den');
    const teacher2Den = document.getElementById('modalTeacher2Den');
    const room2Den = document.getElementById('modalRoom2Den');
    const sub2CardNum = document.getElementById('subgroup2CardNum');
    const sub2CardDen = document.getElementById('subgroup2CardDen');
    const hasDenToggle = document.getElementById('modalHasDen');
    const denBlock = document.getElementById('denominatorBlock');
    const absent1Hidden = document.getElementById('modalAbsent1Hidden');
    const replacement1Hidden = document.getElementById('modalReplacement1Hidden');
    const replacementTeacher1 = document.getElementById('modalReplacementTeacher1');
    const replacementSubject1 = document.getElementById('modalReplacementSubject1');
    const replacementComment1 = document.getElementById('modalReplacementComment1');
    const absent2Hidden = document.getElementById('modalAbsent2Hidden');
    const replacement2Hidden = document.getElementById('modalReplacement2Hidden');
    const replacementTeacher2 = document.getElementById('modalReplacementTeacher2');
    const replacementSubject2 = document.getElementById('modalReplacementSubject2');
    const replacementComment2 = document.getElementById('modalReplacementComment2');
    const replacementToggle1 = document.getElementById('modalReplacementToggle1');
    const replacementToggle2 = document.getElementById('modalReplacementToggle2');
    const replacementBlock1 = document.getElementById('replacementBlock1');
    const replacementBlock2 = document.getElementById('replacementBlock2');

    const hiddenGroup = document.getElementById('modalGroupId');
    const hiddenDay = document.getElementById('modalDay');
    const hiddenLesson = document.getElementById('modalLesson');

    const syncReplacementFlag1 = (resetFields = false) => {
        const enabled = replacementToggle1.checked;
        replacementBlock1.classList.toggle('d-none', !enabled);
        replacement1Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher1.value = '';
            replacementSubject1.value = '';
            replacementComment1.value = '';
        }
    };

    const syncReplacementFlag2 = (resetFields = false) => {
        const enabled = replacementToggle2.checked;
        replacementBlock2.classList.toggle('d-none', !enabled);
        replacement2Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher2.value = '';
            replacementSubject2.value = '';
            replacementComment2.value = '';
        }
    };

    const openModal = (data) => {
        hiddenGroup.value = data.group;
        hiddenDay.value = data.day;
        hiddenLesson.value = data.lesson;
        if (data.weekMode) {
            weekModeInput.value = data.weekMode;
        }
        weekStartHidden.value = data.weekStart || (document.getElementById('weekStartInput')?.value || '');

        subject1.value = data.subject1 || '';
        teacher1.value = data.teacher1 || '';
        room1.value = data.room1 || '';
        subject1Den.value = data.denSubject1 || '';
        teacher1Den.value = data.denTeacher1 || '';
        room1Den.value = data.denRoom1 || '';

        subject2.value = data.subject2 || '';
        teacher2.value = data.teacher2 || '';
        room2.value = data.room2 || '';
        subject2Den.value = data.denSubject2 || '';
        teacher2Den.value = data.denTeacher2 || '';
        room2Den.value = data.denRoom2 || '';

        toggleSub2.checked = data.hasSub2 === '1';
        sub2CardNum.classList.toggle('d-none', !toggleSub2.checked);
        sub2CardDen.classList.toggle('d-none', !toggleSub2.checked);

        const hasDen = data.hasDenominator === '1' || data.denSubject1 || data.denSubject2 || data.denTeacher1 || data.denTeacher2 || data.denRoom1 || data.denRoom2;
        hasDenToggle.checked = !!hasDen;
        denBlock.classList.toggle('d-none', !hasDenToggle.checked);

        absent1Hidden.value = data.absent1 === '1' ? '1' : '0';
        absent2Hidden.value = data.absent2 === '1' ? '1' : '0';
        replacement1Hidden.value = data.replacement1 === '1' ? '1' : '0';
        replacement2Hidden.value = data.replacement2 === '1' ? '1' : '0';
        replacementTeacher1.value = data.replacementTeacher1 || '';
        replacementSubject1.value = data.replacementSubject1 || '';
        replacementComment1.value = data.replacementComment1 || '';
        replacementTeacher2.value = data.replacementTeacher2 || '';
        replacementSubject2.value = data.replacementSubject2 || '';
        replacementComment2.value = data.replacementComment2 || '';
        const hasReplacement1 = data.replacement1 === '1'
            || data.replacementTeacher1
            || data.replacementSubject1
            || (data.replacementComment1 || '').trim();
        replacementToggle1.checked = !!hasReplacement1;
        const hasReplacement2 = toggleSub2.checked && (data.replacement2 === '1'
            || data.replacementTeacher2
            || data.replacementSubject2
            || (data.replacementComment2 || '').trim());
        replacementToggle2.checked = !!hasReplacement2;
        syncReplacementFlag1();
        syncReplacementFlag2();

        overlay.classList.add('show');
        modal.classList.add('show');
    };

    const closeModal = () => {
        overlay.classList.remove('show');
        modal.classList.remove('show');
    };

    toggleSub2.addEventListener('change', () => {
        sub2CardNum.classList.toggle('d-none', !toggleSub2.checked);
        sub2CardDen.classList.toggle('d-none', !toggleSub2.checked);
        if (!toggleSub2.checked) {
            subject2.value = '';
            teacher2.value = '';
            room2.value = '';
            subject2Den.value = '';
            teacher2Den.value = '';
            room2Den.value = '';
            absent2Hidden.value = '0';
            replacementToggle2.checked = false;
            syncReplacementFlag2(true);
        }
    });

    [replacementTeacher1, replacementSubject1].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag1());
    });
    replacementComment1.addEventListener('input', () => syncReplacementFlag1());
    [replacementTeacher2, replacementSubject2].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag2());
    });
    replacementComment2.addEventListener('input', () => syncReplacementFlag2());
    replacementToggle1.addEventListener('change', () => syncReplacementFlag1(true));
    replacementToggle2.addEventListener('change', () => syncReplacementFlag2(true));

    hasDenToggle.addEventListener('change', () => {
        denBlock.classList.toggle('d-none', !hasDenToggle.checked);
        if (!hasDenToggle.checked) {
            subject1Den.value = '';
            teacher1Den.value = '';
            room1Den.value = '';
            subject2Den.value = '';
            teacher2Den.value = '';
            room2Den.value = '';
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

    if (weekStartApply && weekStartPicker) {
        weekStartApply.addEventListener('click', () => {
            const params = new URLSearchParams(window.location.search);
            if (weekStartPicker.value) {
                params.set('week_start', weekStartPicker.value);
            } else {
                params.delete('week_start');
            }
            window.location.search = params.toString();
        });
    }

    // Переключение недели (числитель/знаменатель) через URL-параметр week_mode
    if (weekToggle) {
        weekToggle.addEventListener('click', () => {
            const current = weekToggle.dataset.week === 'den' || weekToggle.dataset.week === 'denominator' ? 'den' : 'num';
            const next = current === 'den' ? 'num' : 'den';
            const params = new URLSearchParams(window.location.search);
            params.set('week_mode', next);
            if (weekStartPicker && weekStartPicker.value) {
                params.set('week_start', weekStartPicker.value);
            }
            window.location.search = params.toString();
        });
    }
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
    max-height: 90vh;
    overflow-y: auto;
}
.modal-card.show {
    opacity: 1;
    pointer-events: all;
    transform: translate(-50%, -50%) scale(1);
}
.subgroup-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 14px;
}
.modal-topline {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 12px;
}
.section-block {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    margin-top: 10px;
}
.section-head h5 {
    margin: 0 0 8px;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.subcard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 10px;
}
.subcard {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 10px 8px;
    box-shadow: 0 6px 12px rgba(15,23,42,0.06);
}
.subcard-head {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
.subcard-grid-inner {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 8px;
}
.replacement-card {
    background: #f8fafc;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    padding: 10px;
}
.form-grid.compact {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
.main-line { align-items: center; gap: 8px; }
.cell-title.emphasis { font-weight: 700; font-size: 16px; }
.fraction-block { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; display: flex; flex-direction: column; gap: 6px; }
.fraction-block.subpair { margin-top: 6px; }
.fraction-line { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; padding: 6px 8px; border-radius: 8px; background: #f8fafc; }
.fraction-line.active { background: #eef2ff; border: 1px solid #d0d7ff; }
.fraction-text { font-weight: 600; }
.pill.tiny { font-size: 12px; padding: 4px 7px; }
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
        <input type="hidden" name="week_mode" id="modalWeekMode" value="{{ ($weekMode ?? 'num') === 'den' ? 'denominator' : 'numerator' }}">
        <input type="hidden" name="week_start" id="modalWeekStart" value="{{ $weekStart ?? '' }}">

        <div class="modal-topline">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="modalHasSub2" name="has_sub2" value="1">
                <label class="form-check-label" for="modalHasSub2">Включить подгруппу 2</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="modalHasDen" name="has_denominator" value="1">
                <label class="form-check-label" for="modalHasDen">Включить знаменатель</label>
            </div>
        </div>

        <div class="section-block">
            <div class="section-head">
                <h5>Числитель (текущая неделя)</h5>
            </div>
            <div class="subcard-grid">
                <div class="subcard">
                    <div class="subcard-head">Подгруппа 1</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
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
                    <div class="mt-2">
                        <input type="hidden" name="is_absent_1" id="modalAbsent1Hidden" value="0">
                        <input type="hidden" name="is_replacement_1" id="modalReplacement1Hidden" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle1">
                            <label class="form-check-label" for="modalReplacementToggle1">Включить замену</label>
                        </div>
                        <div id="replacementBlock1" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 1)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher1">
                            <select class="form-select mb-2" name="replacement_teacher_id_1" id="modalReplacementTeacher1">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject1">
                            <select class="form-select mb-2" name="replacement_subject_id_1" id="modalReplacementSubject1">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_1" id="modalReplacementComment1" placeholder="Комментарий">
                        </div>
                    </div>
                </div>
                <div class="subcard sub2-card d-none" id="subgroup2CardNum">
                    <div class="subcard-head">Подгруппа 2</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
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
                    <div class="mt-2">
                        <input type="hidden" name="is_absent_2" id="modalAbsent2Hidden" value="0">
                        <input type="hidden" name="is_replacement_2" id="modalReplacement2Hidden" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle2">
                            <label class="form-check-label" for="modalReplacementToggle2">Включить замену</label>
                        </div>
                        <div id="replacementBlock2" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 2)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher2">
                            <select class="form-select mb-2" name="replacement_teacher_id_2" id="modalReplacementTeacher2">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject2">
                            <select class="form-select mb-2" name="replacement_subject_id_2" id="modalReplacementSubject2">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_2" id="modalReplacementComment2" placeholder="Комментарий">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-block d-none" id="denominatorBlock">
            <div class="section-head">
                <h5>Знаменатель (следующая неделя)</h5>
            </div>
            <div class="subcard-grid">
                <div class="subcard">
                    <div class="subcard-head">Подгруппа 1</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject1Den">
                            <select class="form-select" name="den_subject_id" id="modalSubject1Den">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher1Den">
                            <select class="form-select" name="den_teacher_id" id="modalTeacher1Den">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <input type="text" class="form-control" name="den_room_id" id="modalRoom1Den" placeholder="101">
                        </div>
                    </div>
                </div>
                <div class="subcard sub2-card d-none" id="subgroup2CardDen">
                    <div class="subcard-head">Подгруппа 2</div>
                    <div class="subcard-grid-inner">
                        <div>
                            <label class="form-label">Предмет</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета" data-target="modalSubject2Den">
                            <select class="form-select" name="den_subject_id_2" id="modalSubject2Den">
                                <option value="">—</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Преподаватель</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск преподавателя" data-target="modalTeacher2Den">
                            <select class="form-select" name="den_teacher_id_2" id="modalTeacher2Den">
                                <option value="">—</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Кабинет</label>
                            <input type="text" class="form-control" name="den_room_id_2" id="modalRoom2Den" placeholder="102">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
</div>
@endpush
