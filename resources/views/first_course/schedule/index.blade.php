@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule-modern.css') }}">
<style>
    .holiday-banner {
        margin: 1rem 0;
        padding: 0.75rem 1rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.9rem;
    }
    .holiday-banner__title {
        font-weight: 600;
        color: #92400e;
        margin-right: 0.5rem;
    }
    .holiday-banner__list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .holiday-pill {
        padding: 0.15rem 0.6rem;
        border-radius: 999px;
        background: #fff7d6;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .holiday-row {
        background-color: #fefce8;
    }
    .grid-cell.day-col.holiday-day {
        background-color: #fffbeb;
    }
    .holiday-note {
        font-size: 0.75rem;
        color: #92400e;
    }
    .holiday-cell {
        background-color: #fff9c2;
    }
    .holiday-lock {
        font-size: 0.75rem;
        color: #7c2d12;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
        background: #fef3c7;
        margin-bottom: 0.35rem;
    }
</style>
@endpush

@section('content')
@php
    $weekDays = $weekDays ?? [];
    if (empty($weekDays)) {
        $weekDayNames = ['Понедельник','Вторник','Среда','Четверг','Пятница'];
        foreach ($weekDayNames as $name) {
            $weekDays[] = [
                'name' => $name,
                'date' => null,
                'label' => null,
                'holiday' => null,
            ];
        }
    }
    $holidayWeekDates = $holidayWeekDates ?? [];
    $weeklyHolidays = $weeklyHolidays ?? [];
    $dayDetails = [];
    foreach ($weekDays as $dayInfo) {
        $dayDetails[$dayInfo['name']] = $dayInfo;
    }
    $days = array_keys($dayDetails);
    $itemsByGroup = $schedule ?? [];
    $firstGroupId = count($itemsByGroup) ? array_key_first($itemsByGroup) : null;
    $expandLinkParams = ['course' => $course ?? 1];
    if ($firstGroupId) {
        $expandLinkParams['group_id'] = $firstGroupId;
    }
@endphp

<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Расписание — {{ $course ?? 1 }} курс</h1>
            <p class="page-subtitle">Компактный обзор по всем группам</p>
            <div class="mt-2 d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Курс:</label>
                <select id="courseSelect" class="search-input" style="width:auto;">
                    @for($c = 1; $c <= 4; $c++)
                        <option value="{{ $c }}" @selected(($course ?? 1) == $c)>{{ $c }}</option>
                    @endfor
                </select>
            </div>
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
        <a href="{{ route('first.schedule.week', ['course' => $course ?? 1]) }}" class="btn-pill primary">Редактор недели</a>
        <a href="{{ route('first.schedule.week', $expandLinkParams) }}#semesterExpandSection" class="btn-pill ghost">Развернуть семестр</a>
        <a href="{{ route('first.schedule.form_two', ['course' => $course ?? 1]) }}" class="btn-pill ghost">Форма 2</a>
    </div>
</div>
    @if(!empty($weeklyHolidays))
        <div class="holiday-banner">
            <div class="holiday-banner__title">Праздники недели:</div>
                    <div class="holiday-banner__list">
                        @foreach($weeklyHolidays as $holiday)
                            <span class="holiday-pill" title="Праздник — {{ data_get($holiday, 'name', '') }}">
                                {{ data_get($holiday, 'label', '') }} ({{ data_get($holiday, 'day', 'день') }}) — {{ data_get($holiday, 'name', 'праздник') }}
                            </span>
                        @endforeach
                    </div>
        </div>
    @endif

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
                    @php
                        $dayInfo = $dayDetails[$day] ?? [];
                        $holidayMeta = $dayInfo['holiday'] ?? null;
                    @endphp
                    <div class="grid-row{{ $holidayMeta ? ' holiday-row' : '' }}">
                        <div class="grid-cell day-col{{ $holidayMeta ? ' holiday-day' : '' }}">
                            {{ $day }}
                            @if($holidayMeta)
                                <div class="holiday-note">{{ $holidayMeta['name'] }}</div>
                            @endif
                        </div>
                        @for($i = 1; $i <= 5; $i++)
                            @php
                                $pair = $groupItems[$day][$i] ?? ['sub1'=>[], 'sub2'=>[], 'has_denominator' => false];
                                $hasLesson = ($pair['sub1']['has_den'] ?? false) || ($pair['sub1']['has_num'] ?? false) || ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                $hasConflict = ($pair['sub1']['active_conflict'] ?? false) || ($pair['sub2']['active_conflict'] ?? false);
                                $hasSubgroupsAny = ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                $hasSubgroupsCurrentWeek = ($pair['sub2']['has_den'] ?? false) || ($pair['sub2']['has_num'] ?? false);
                                $pairStatus = '';
                                if (($pair['sub1']['is_replacement'] ?? false) || ($pair['sub2']['is_replacement'] ?? false)) {
                                    $pairStatus = 'pair-replacement';
                                } elseif (($pair['sub1']['is_absent'] ?? false) || ($pair['sub2']['is_absent'] ?? false)) {
                                    $pairStatus = 'pair-sick';
                                }
                            @endphp
                            <div class="grid-cell pair-cell {{ $hasLesson ? 'filled' : 'empty' }} {{ $hasConflict ? 'conflict' : '' }} {{ $pairStatus }}{{ $holidayMeta ? ' holiday-cell' : '' }}">
                                @if(!$holidayMeta)
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
                                        data-has-sub2="{{ $hasSubgroupsAny ? '1' : '0' }}"
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
                                        data-replacement2="0"
                                        data-replacement-teacher-1="{{ $pair['sub1']['replacement_teacher_id'] ?? '' }}"
                                        data-replacement-subject-1="{{ $pair['sub1']['replacement_subject_id'] ?? '' }}"
                                        data-replacement-comment-1="{{ $pair['sub1']['replacement_comment'] ?? '' }}"
                                        data-replacement-den-1="{{ ($pair['sub1']['replacement_flag_den'] ?? false) ? '1' : '0' }}"
                                        data-replacement-teacher-den-1="{{ $pair['sub1']['replacement_teacher_den'] ?? '' }}"
                                        data-replacement-subject-den-1="{{ $pair['sub1']['replacement_subject_den'] ?? '' }}"
                                        data-replacement-comment-den-1="{{ $pair['sub1']['replacement_comment_den'] ?? '' }}"
                                        data-teacher-conflict1="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                        data-teacher-conflict1-groups="{{ ($pair['sub1']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub1']['teacher_conflict_groups'] ?? []) : '' }}"
                                        data-teacher-conflict2="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? '1' : '0' }}"
                                        data-teacher-conflict2-groups="{{ ($pair['sub2']['teacher_conflict'] ?? false) ? implode(', ', $pair['sub2']['teacher_conflict_groups'] ?? []) : '' }}"
                                    >✏️</a>
                                @else
                                    <div class="holiday-lock" title="Праздник — {{ $holidayMeta['name'] }} ({{ $holidayMeta['label'] }})">
                                        🎉 {{ $holidayMeta['label'] }} ({{ $holidayMeta['day'] ?? '' }})
                                    </div>
                                @endif
                                @if ($hasLesson)
                                    @php $main = $pair['sub1'] ?? []; @endphp
                                    <div class="cell-line main-line sub-line">
                                        <span class="pill badge-sub">1</span>
                                        @if($main['is_absent'] ?? false)
                                            <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                        @elseif($main['is_replacement'] ?? false)
                                            <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                        @endif
                                        <span class="cell-title emphasis">{{ $main['active_subject'] ?? '' }}</span>
                                        @if(($main['replacement_subject'] ?? null) && ($main['is_replacement'] ?? false) && ($main['replacement_subject'] !== ($main['active_subject'] ?? null)))
                                            <span class="text-danger ms-1">→ {{ $main['replacement_subject'] }}</span>
                                        @endif
                                    </div>
                                    <div class="cell-meta">
                                        @if (!empty($main['active_teacher']))
                                            <span class="pill">
                                                <span>👤</span>{{ $main['active_teacher'] }}
                                        @if(
                                            ($main['replacement_teacher'] ?? null)
                                            && ($main['is_replacement'] ?? false)
                                            && ($main['replacement_teacher'] !== ($main['active_teacher'] ?? null))
                                        )
                                            <span class="text-warning ms-1">→ {{ $main['replacement_teacher'] }}</span>
                                        @endif
                                            </span>
                                        @endif
                                        @if (!empty($main['active_room']))
                                            <span class="pill room-pill {{ ($main['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($main['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                <span>🏫</span>{{ $main['active_room'] }}
                                            </span>
                                        @endif
                                        @if (!empty($main['label']))
                                            <span class="pill"><span>🔸</span>{{ $main['label'] }}</span>
                                        @endif
                                    </div>
                                    @if($main['active_conflict'] ?? false)
                                        <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                    @endif
                                    @php $sub2 = $pair['sub2'] ?? []; @endphp
                                    @if($hasSubgroupsCurrentWeek)
                                        <div class="cell-line subpair-line">
                                            <span class="pill badge-sub soft">2</span>
                                            @if($sub2['is_absent'] ?? false)
                                                <span class="status-chip tiny status-sick" title="Болезнь">Б</span>
                                            @elseif($sub2['is_replacement'] ?? false)
                                                <span class="status-chip tiny status-replacement" title="Замена">2</span>
                                            @endif
                                            <span class="cell-title sub2 emphasis">{{ $sub2['active_subject'] ?? '' }}</span>
                                            @if(($sub2['replacement_subject'] ?? null) && ($sub2['is_replacement'] ?? false) && ($sub2['replacement_subject'] !== ($sub2['active_subject'] ?? null)))
                                                <span class="text-danger ms-1">→ {{ $sub2['replacement_subject'] }}</span>
                                            @endif
                                        </div>
                                        <div class="cell-meta subpair">
                                            @if (!empty($sub2['active_teacher']))
                                                <span class="pill">
                                                    <span>👤</span>{{ $sub2['active_teacher'] }}
                                            @if(
                                                ($sub2['replacement_teacher'] ?? null)
                                                && ($sub2['is_replacement'] ?? false)
                                                && ($sub2['replacement_teacher'] !== ($sub2['active_teacher'] ?? null))
                                            )
                                                <span class="text-warning ms-1">→ {{ $sub2['replacement_teacher'] }}</span>
                                            @endif
                                                </span>
                                            @endif
                                            @if (!empty($sub2['active_room']))
                                                <span class="pill room-pill {{ ($sub2['active_conflict'] ?? false) ? 'pill-conflict' : '' }}" title="{{ ($sub2['active_conflict'] ?? false) ? 'Конфликт: кабинет уже занят' : '' }}">
                                                    <span>🏫</span>{{ $sub2['active_room'] }}
                                                </span>
                                            @endif
                                            @if (!empty($sub2['label']))
                                                <span class="pill"><span>🔸</span>{{ $sub2['label'] }}</span>
                                            @endif
                                        </div>
                                        @if($sub2['active_conflict'] ?? false)
                                            <div class="conflict-hint">Конфликт: кабинет уже занят</div>
                                        @endif
                                    @endif
                                    @if($pair['has_denominator'])
                                        <div class="den-separator" title="Разделение числитель/знаменатель"></div>
                                    @endif
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
    const replacementToggle1 = document.getElementById('modalReplacementToggle1');
    const replacementBlock1 = document.getElementById('replacementBlock1');
    const replacementDen1Hidden = document.getElementById('modalReplacement1DenHidden');
    const replacementTeacher1Den = document.getElementById('modalReplacementTeacher1Den');
    const replacementSubject1Den = document.getElementById('modalReplacementSubject1Den');
    const replacementComment1Den = document.getElementById('modalReplacementComment1Den');
    const replacementToggle1Den = document.getElementById('modalReplacementToggle1Den');
    const replacementBlock1Den = document.getElementById('replacementBlock1Den');
    const teacherConflictAlert1 = document.getElementById('teacherConflictAlert1');
    const teacherConflictAlert2 = document.getElementById('teacherConflictAlert2');

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

    const syncReplacementFlag1Den = (resetFields = false) => {
        const enabled = replacementToggle1Den.checked;
        replacementBlock1Den.classList.toggle('d-none', !enabled);
        replacementDen1Hidden.value = enabled ? '1' : '0';
        if (!enabled && resetFields) {
            replacementTeacher1Den.value = '';
            replacementSubject1Den.value = '';
            replacementComment1Den.value = '';
        }
    };

    const setTeacherConflictAlert = (el, flag, groupsText, day, lesson) => {
        if (!el) return;
        const active = flag === '1' && groupsText;
        el.classList.toggle('d-none', !active);
        el.textContent = active
            ? `Преподаватель занят у групп: ${groupsText} (${day}, пара ${lesson})`
            : '';
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
        replacementTeacher1.value = data.replacementTeacher1 || '';
        replacementSubject1.value = data.replacementSubject1 || '';
        replacementComment1.value = data.replacementComment1 || '';
        replacementDen1Hidden.value = data.replacementDen1 === '1' ? '1' : '0';
        replacementTeacher1Den.value = data.replacementTeacher1Den || '';
        replacementSubject1Den.value = data.replacementSubject1Den || '';
        replacementComment1Den.value = data.replacementComment1Den || '';
        const hasReplacement1 = data.replacement1 === '1'
            || data.replacementTeacher1
            || data.replacementSubject1
            || (data.replacementComment1 || '').trim();
        replacementToggle1.checked = !!hasReplacement1;
        const hasReplacement1Den = data.replacementDen1 === '1'
            || data.replacementTeacher1Den
            || data.replacementSubject1Den
            || (data.replacementComment1Den || '').trim();
        replacementToggle1Den.checked = !!hasReplacement1Den;
        syncReplacementFlag1();
        syncReplacementFlag1Den();
        setTeacherConflictAlert(
            teacherConflictAlert1,
            data.teacherConflict1 || '0',
            data.teacherConflict1Groups || '',
            data.day,
            data.lesson
        );
        setTeacherConflictAlert(
            teacherConflictAlert2,
            data.teacherConflict2 || '0',
            data.teacherConflict2Groups || '',
            data.day,
            data.lesson
        );

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
            setTeacherConflictAlert(teacherConflictAlert2, '0', '', '', '');
        }
    });

    [replacementTeacher1, replacementSubject1].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag1());
    });
    replacementComment1.addEventListener('input', () => syncReplacementFlag1());
    replacementToggle1.addEventListener('change', () => syncReplacementFlag1(true));
    [replacementTeacher1Den, replacementSubject1Den].forEach((el) => {
        el.addEventListener('change', () => syncReplacementFlag1Den());
    });
    replacementComment1Den.addEventListener('input', () => syncReplacementFlag1Den());
    replacementToggle1Den.addEventListener('change', () => syncReplacementFlag1Den(true));

    hasDenToggle.addEventListener('change', () => {
        denBlock.classList.toggle('d-none', !hasDenToggle.checked);
        if (!hasDenToggle.checked) {
            subject1Den.value = '';
            teacher1Den.value = '';
            room1Den.value = '';
            subject2Den.value = '';
            teacher2Den.value = '';
            room2Den.value = '';
            replacementToggle1Den.checked = false;
            syncReplacementFlag1Den(true);
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
    const courseSelect = document.getElementById('courseSelect');
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

    if (courseSelect) {
        courseSelect.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('course', courseSelect.value);
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
.alert-conflict {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
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
        <input type="hidden" name="course" value="{{ $course ?? 1 }}">
        <input type="hidden" name="den_is_replacement_1" id="modalReplacement1DenHidden" value="0">

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
                    <div class="alert-conflict d-none" id="teacherConflictAlert1"></div>
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
                    <div class="alert-conflict d-none" id="teacherConflictAlert2"></div>
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
                    <input type="hidden" name="is_absent_2" id="modalAbsent2Hidden" value="0">
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
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalReplacementToggle1Den">
                            <label class="form-check-label" for="modalReplacementToggle1Den">Замена (знаменатель, подгр. 1)</label>
                        </div>
                        <div id="replacementBlock1Den" class="replacement-card flex-grow-1 d-none">
                            <label class="form-label">Заменяющий (подгр. 1, знаменатель)</label>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск заменяющего преподавателя" data-target="modalReplacementTeacher1Den">
                            <select class="form-select mb-2" name="replacement_teacher_id_1_den" id="modalReplacementTeacher1Den">
                                <option value="">— преподаватель</option>
                                @foreach($teachers as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="search" class="form-control mb-2 search-field" placeholder="Поиск предмета замены" data-target="modalReplacementSubject1Den">
                            <select class="form-select mb-2" name="replacement_subject_id_1_den" id="modalReplacementSubject1Den">
                                <option value="">— предмет</option>
                                @foreach($subjects as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="replacement_comment_1_den" id="modalReplacementComment1Den" placeholder="Комментарий">
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
                    <div class="mt-2">
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
