<?php

namespace App\Http\Controllers;

use App\Models\FirstCourseSchedule;
use App\Services\KazakhstanHolidayService;
use App\Services\ScheduleToFormTwoSyncService;
use App\Services\FormTwoService;
use App\Services\SemesterScheduleService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FirstCourseSchedulePageController extends Controller
{
    protected function tables(int $course): array
    {
        return CourseContext::tables($course);
    }

    /**
     * Показать расписание 1 курса.
     */
    public function index()
    {
        $course = CourseContext::normalize(request()->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $weekStartInput = request()->get('week_start');
        $weekStart = $weekStartInput ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);

        /** @var ScheduleToFormTwoSyncService $syncService */
        $syncService = app(ScheduleToFormTwoSyncService::class);
        $resolvedWeekMode = $syncService->resolveWeekMode($weekStart, $course);
        $isDenominatorWeek = $resolvedWeekMode === 'denominator';

        $days = $this->buildWeekDays($weekStart);
        $weeklyHolidays = collect($days)->pluck('holiday')->filter()->values()->all();
        $holidayWeekDates = [];
        foreach ($days as $day) {
            if (!empty($day['holiday']) && !empty($day['date'])) {
                $holidayWeekDates[$day['date']] = $day['holiday'];
            }
        }

        $subjects = DB::table($tables['subjects'])
            ->select('id', 'subject_name', 'name_ru', 'name_kz')
            ->get()
            ->mapWithKeys(function ($row) {
                $ru = $row->name_ru ?: $row->subject_name;
                $kz = $row->name_kz ?: $ru;

                return [
                    $row->id => [
                        'ru' => $ru,
                        'kz' => $kz,
                    ],
                ];
            })
            ->all();

        $subjectsForView = [];
        foreach ($subjects as $id => $entry) {
            $subjectsForView[$id] = $entry['ru'] ?? ($entry['kz'] ?? '—');
        }

        $teachers = DB::table($tables['teachers'])
            ->pluck('teacher_name', 'id');

        $groupRecords = DB::table($tables['groups'])
            ->select('id', 'group_name')
            ->get();

        $groups = [];
        $groupLocalePreference = [];
        foreach ($groupRecords as $group) {
            $groups[$group->id] = $group->group_name;
            $groupLocalePreference[$group->id] = $this->isKazakhGroup($group->group_name);
        }

        $raw = DB::table($tables['schedules'] . ' as s')
            ->whereDate('s.week_start', $weekStart->toDateString())
            ->orderBy('s.study_day')
            ->orderBy('s.lesson_number')
            ->get();

        $currentMode = $isDenominatorWeek ? 'denominator' : 'numerator';
        $roomConflicts = FirstCourseSchedule::detectRoomConflicts($raw);
        $teacherConflicts = FirstCourseSchedule::detectTeacherConflicts($raw);
        $schedule = [];

        foreach ($raw as $row) {
            $groupId = $row->group_id;
            $groupName = $groups[$groupId] ?? 'Без группы';
            $useKazakh = $groupLocalePreference[$groupId] ?? false;
            $day = $row->study_day;
            $lesson = $row->lesson_number;

            if (!isset($schedule[$groupId])) {
                $schedule[$groupId] = [
                    'name' => $groupName,
                    'use_kz' => $useKazakh,
                    'days' => [],
                ];
            }

            if (!isset($schedule[$groupId]['days'][$day][$lesson])) {
                $schedule[$groupId]['days'][$day][$lesson] = [
                    'lesson' => $lesson,
                    'sub1' => null,
                    'sub2' => null,
                    'has_denominator' => false,
                    'has_denominator_subgroup2' => false,
                ];
            }

            $subjectResolver = fn (?int $subjectId) => $this->resolveSubjectTitle($subjects, $subjectId, $useKazakh);

            $subgroupFlag = $row->subgroup === '2' ? 2 : 1;
            $num1 = $subgroupFlag === 1 ? $row->subject_id : null;
            $teacherNum1 = $subgroupFlag === 1 ? $row->teacher_id : null;
            $roomNum1 = $subgroupFlag === 1 ? $row->room_id : null;

            $num2 = $row->subject_id_2 ?: ($subgroupFlag === 2 ? $row->subject_id : null);
            $teacherNum2 = $row->teacher_id_2 ?: ($subgroupFlag === 2 ? $row->teacher_id : null);
            $roomNum2 = $row->room_id_2 ?: ($subgroupFlag === 2 ? $row->room_id : null);

            $den1 = $subgroupFlag === 1 ? ($row->subject_id_denominator ?? null) : null;
            $teacherDen1 = $subgroupFlag === 1 ? ($row->teacher_id_denominator ?? null) : null;
            $roomDen1 = $subgroupFlag === 1 ? ($row->room_id_denominator ?? null) : null;

            $den2Group2Exists = $subgroupFlag === 2 && (
                ($row->subject_id_denominator_2 ?? null)
                || ($row->teacher_id_denominator_2 ?? null)
                || ($row->room_id_denominator_2 ?? null)
            );
            $den2 = $den2Group2Exists ? ($row->subject_id_denominator_2 ?? null) : null;
            $teacherDen2 = $den2Group2Exists ? ($row->teacher_id_denominator_2 ?? null) : null;
            $roomDen2 = $den2Group2Exists ? ($row->room_id_denominator_2 ?? null) : null;

            $confNum1 = $roomConflicts[$groupId][$day][$lesson]['numerator'][1] ?? false;
            $confNum2 = $roomConflicts[$groupId][$day][$lesson]['numerator'][2] ?? false;
            $confDen1 = $roomConflicts[$groupId][$day][$lesson]['denominator'][1] ?? false;
            $confDen2 = $roomConflicts[$groupId][$day][$lesson]['denominator'][2] ?? false;

            $teacherConfNum1 = $teacherConflicts[$groupId][$day][$lesson]['numerator'][1] ?? null;
            $teacherConfNum2 = $teacherConflicts[$groupId][$day][$lesson]['numerator'][2] ?? null;
            $teacherConfDen1 = $teacherConflicts[$groupId][$day][$lesson]['denominator'][1] ?? null;
            $teacherConfDen2 = $teacherConflicts[$groupId][$day][$lesson]['denominator'][2] ?? null;

            $schedule[$groupId]['days'][$day][$lesson]['has_denominator'] = $schedule[$groupId]['days'][$day][$lesson]['has_denominator']
                || $den1 || $den2 || $teacherDen1 || $teacherDen2 || $roomDen1 || $roomDen2;
            $schedule[$groupId]['days'][$day][$lesson]['has_denominator_subgroup2'] = $schedule[$groupId]['days'][$day][$lesson]['has_denominator_subgroup2']
                || $den2Group2Exists;

            // Определяем режим: если есть знаменатель для любой подгруппы, то mode = numerator/denominator, иначе single
            $hasDenominator = $den1 || $den2 || $teacherDen1 || $teacherDen2 || $roomDen1 || $roomDen2;
            $mode = $hasDenominator ? ($isDenominatorWeek ? 'denominator' : 'numerator') : 'single';

            $sub1Data = $schedule[$groupId]['days'][$day][$lesson]['sub1'] ?? [];
            if ($subgroupFlag === 1 || $num1 || $teacherNum1 || $roomNum1 || $den1 || $teacherDen1 || $roomDen1) {
                $sub1Data = array_merge($sub1Data, [
                    'subject_num' => $num1 ? $subjectResolver($num1) : ($sub1Data['subject_num'] ?? null),
                    'subject_num_id' => $num1 ?? ($sub1Data['subject_num_id'] ?? null),
                    'teacher_num' => $teacherNum1 ? ($teachers[$teacherNum1] ?? '—') : ($sub1Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum1 ?? ($sub1Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum1 ?? ($sub1Data['room_num'] ?? null),
                    'subject_den' => $den1 ? $subjectResolver($den1) : ($sub1Data['subject_den'] ?? null),
                    'subject_den_id' => $den1 ?? ($sub1Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen1 ? ($teachers[$teacherDen1] ?? '—') : ($sub1Data['teacher_den'] ?? null),
                    'teacher_den_id' => $teacherDen1 ?? ($sub1Data['teacher_den_id'] ?? null),
                    'room_den' => $roomDen1 ?? ($sub1Data['room_den'] ?? null),
                    'conflict_num' => $confNum1 ?? ($sub1Data['conflict_num'] ?? false),
                    'conflict_den' => $confDen1 ?? ($sub1Data['conflict_den'] ?? false),
                    'teacher_conflict_num' => $teacherConfNum1 ?? ($sub1Data['teacher_conflict_num'] ?? null),
                    'teacher_conflict_den' => $teacherConfDen1 ?? ($sub1Data['teacher_conflict_den'] ?? null),
                    'absent_num' => $row->is_absent_1_num ?? false,
                    'absent_den' => $row->is_absent_1_den ?? false,
                    'replacement_flag_num' => $row->is_replacement_1_num ?? false,
                    'replacement_teacher_num' => $row->replacement_teacher_id_1_num ?? null,
                    'replacement_subject_num' => $row->replacement_subject_id_1_num ?? null,
                    'replacement_comment_num' => $row->replacement_comment_1_num ?? null,
                    'replacement_flag_den' => $row->is_replacement_1_den ?? false,
                    'replacement_teacher_den' => $row->replacement_teacher_id_1_den ?? null,
                    'replacement_subject_den' => $row->replacement_subject_id_1_den ?? null,
                    'replacement_comment_den' => $row->replacement_comment_1_den ?? null,
                ]);
            }
            $schedule[$groupId]['days'][$day][$lesson]['sub1'] = $sub1Data;

            $sub2Data = $schedule[$groupId]['days'][$day][$lesson]['sub2'] ?? [];
            if ($subgroupFlag === 2 || $num2 || $teacherNum2 || $roomNum2 || $den2 || $teacherDen2 || $roomDen2) {
                // При mode = 'single' замены всегда записываются в поля _1_num, независимо от subgroup
                $replacementSubgroup = $mode === 'single' ? 1 : 2;
                $replacementSuffix = $replacementSubgroup === 2 ? '_2' : '_1';
                
                $sub2Data = array_merge($sub2Data, [
                    'subject_num' => $num2 ? $subjectResolver($num2) : ($sub2Data['subject_num'] ?? null),
                    'subject_num_id' => $num2 ?? ($sub2Data['subject_num_id'] ?? null),
                    'teacher_num' => $teacherNum2 ? ($teachers[$teacherNum2] ?? '—') : ($sub2Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum2 ?? ($sub2Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum2 ?? ($sub2Data['room_num'] ?? null),
                    'subject_den' => $den2 ? $subjectResolver($den2) : ($sub2Data['subject_den'] ?? null),
                    'subject_den_id' => $den2 ?? ($sub2Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen2 ? ($teachers[$teacherDen2] ?? '—') : ($sub2Data['teacher_den'] ?? null),
                    'teacher_den_id' => $teacherDen2 ?? ($sub2Data['teacher_den_id'] ?? null),
                    'room_den' => $roomDen2 ?? ($sub2Data['room_den'] ?? null),
                    'conflict_num' => $confNum2 ?? ($sub2Data['conflict_num'] ?? false),
                    'conflict_den' => $confDen2 ?? ($sub2Data['conflict_den'] ?? false),
                    'teacher_conflict_num' => $teacherConfNum2 ?? ($sub2Data['teacher_conflict_num'] ?? null),
                    'teacher_conflict_den' => $teacherConfDen2 ?? ($sub2Data['teacher_conflict_den'] ?? null),
                    'absent_num' => $row->is_absent_2_num ?? false,
                    'absent_den' => $row->is_absent_2_den ?? false,
                    // Используем правильную подгруппу для чтения полей замены
                    'replacement_flag_num' => (bool) ($row->{'is_replacement' . $replacementSuffix . '_num'} ?? false),
                    'replacement_teacher_num' => $row->{'replacement_teacher_id' . $replacementSuffix . '_num'} ?? null,
                    'replacement_subject_num' => $row->{'replacement_subject_id' . $replacementSuffix . '_num'} ?? null,
                    'replacement_comment_num' => $row->{'replacement_comment' . $replacementSuffix . '_num'} ?? null,
                    'replacement_flag_den' => (bool) ($row->{'is_replacement' . $replacementSuffix . '_den'} ?? false),
                    'replacement_teacher_den' => $row->{'replacement_teacher_id' . $replacementSuffix . '_den'} ?? null,
                    'replacement_subject_den' => $row->{'replacement_subject_id' . $replacementSuffix . '_den'} ?? null,
                    'replacement_comment_den' => $row->{'replacement_comment' . $replacementSuffix . '_den'} ?? null,
                ]);
            }
            $schedule[$groupId]['days'][$day][$lesson]['sub2'] = $sub2Data;
        }

        // Проставляем активные значения по чётности недели
        foreach ($schedule as $groupId => $groupData) {
            $useKazakh = $groupData['use_kz'] ?? false;
            $subjectResolver = fn (?int $subjectId) => $this->resolveSubjectTitle($subjects, $subjectId, $useKazakh);
            foreach ($groupData['days'] as $day => $lessons) {
                foreach ($lessons as $lesson => $pair) {
                    foreach ([1, 2] as $subIndex) {
                        $key = "sub{$subIndex}";
                        $numExists = !empty($pair[$key]['subject_num_id']) || !empty($pair[$key]['teacher_num_id']) || !empty($pair[$key]['room_num']);
                        $denExists = !empty($pair[$key]['subject_den_id'])
                            || !empty($pair[$key]['teacher_den_id'])
                            || !empty($pair[$key]['room_den']);
                        $useDenominator = $isDenominatorWeek && $denExists;
                        $activeSubject = $useDenominator
                            ? ($pair[$key]['subject_den'] ?? ($pair[$key]['subject_num'] ?? null))
                            : ($pair[$key]['subject_num'] ?? null);
                        $activeTeacher = $useDenominator
                            ? ($pair[$key]['teacher_den'] ?? ($pair[$key]['teacher_num'] ?? null))
                            : ($pair[$key]['teacher_num'] ?? null);
                        $roomUsedDenominator = $useDenominator && ($pair[$key]['room_den'] ?? null);
                        $activeRoom = $roomUsedDenominator
                            ? ($pair[$key]['room_den'] ?? null)
                            : ($pair[$key]['room_num'] ?? null);
                        $activeConflict = $roomUsedDenominator
                            ? ($pair[$key]['conflict_den'] ?? false)
                            : ($pair[$key]['conflict_num'] ?? false);
                        $originalTeacherId = $useDenominator
                            ? ($pair[$key]['teacher_den_id'] ?? ($pair[$key]['teacher_num_id'] ?? null))
                            : ($pair[$key]['teacher_num_id'] ?? null);
                        $absent = $useDenominator ? ($pair[$key]['absent_den'] ?? false) : ($pair[$key]['absent_num'] ?? false);
                        $replacementFlag = $useDenominator ? ($pair[$key]['replacement_flag_den'] ?? false) : ($pair[$key]['replacement_flag_num'] ?? false);
                        $replacementTeacherId = $useDenominator ? ($pair[$key]['replacement_teacher_den'] ?? null) : ($pair[$key]['replacement_teacher_num'] ?? null);
                        $replacementSubjectId = $useDenominator ? ($pair[$key]['replacement_subject_den'] ?? null) : ($pair[$key]['replacement_subject_num'] ?? null);
                        $replacementSubjectName = $replacementSubjectId ? $subjectResolver($replacementSubjectId) : null;
                        $replacementComment = $useDenominator ? ($pair[$key]['replacement_comment_den'] ?? null) : ($pair[$key]['replacement_comment_num'] ?? null);
                        $replacementTeacherName = $replacementTeacherId ? ($teachers[$replacementTeacherId] ?? '—') : null;

                        if ($replacementFlag && $replacementSubjectName) {
                            $activeSubject = $replacementSubjectName;
                        }
                        if ($replacementFlag && $replacementTeacherName) {
                            $activeTeacher = $replacementTeacherName;
                        } elseif ($absent && !$replacementFlag) {
                            $activeTeacher = $activeTeacher ?: 'Учитель отсутствует';
                        }

                        $teacherConflictRaw = $useDenominator
                            ? ($pair[$key]['teacher_conflict_den'] ?? null)
                            : ($pair[$key]['teacher_conflict_num'] ?? null);
                        $teacherConflictGroups = [];
                        if ($teacherConflictRaw && !empty($teacherConflictRaw['groups'])) {
                            foreach ($teacherConflictRaw['groups'] as $conflictGroupId) {
                                if ((int) $conflictGroupId === (int) $groupId) {
                                    continue;
                                }
                                $teacherConflictGroups[] = $groups[$conflictGroupId] ?? ('Группа ' . $conflictGroupId);
                            }
                        }

                        $pair[$key]['active_subject'] = $activeSubject;
                        $pair[$key]['active_teacher'] = $activeTeacher;
                        $pair[$key]['active_room'] = $activeRoom;
                        $pair[$key]['active_conflict'] = $activeConflict;
                        $pair[$key]['has_den'] = $denExists;
                        $pair[$key]['has_num'] = $numExists;
                        $pair[$key]['label'] = (string) $subIndex;
                        $pair[$key]['original_teacher'] = $originalTeacherId ? ($teachers[$originalTeacherId] ?? '—') : null;
                        $pair[$key]['original_teacher_id'] = $originalTeacherId;
                        $pair[$key]['is_absent'] = $absent;
                        $pair[$key]['is_replacement'] = $replacementFlag;
                        $pair[$key]['replacement_teacher_id'] = $replacementTeacherId;
                        $pair[$key]['replacement_teacher'] = $replacementTeacherName;
                        $pair[$key]['replacement_comment'] = $replacementComment;
                        $pair[$key]['replacement_subject_id'] = $replacementSubjectId;
                        $pair[$key]['replacement_subject'] = $replacementSubjectName;
                        $pair[$key]['teacher_conflict'] = !empty($teacherConflictGroups);
                        $pair[$key]['teacher_conflict_groups'] = $teacherConflictGroups;
                    }
                    // Если в знаменателе указана пара на полную группу (sub1),
                    // подгруппы из числителя не должны подставляться.
                    if (
                        $isDenominatorWeek
                        && ($pair['sub1']['has_den'] ?? false)
                        && !($pair['sub2']['has_den'] ?? false)
                    ) {
                        $pair['sub2'] = [
                            'has_den' => false,
                            'has_num' => false,
                            'active_subject' => null,
                            'active_teacher' => null,
                            'active_room' => null,
                            'active_conflict' => false,
                            'label' => '2',
                            'is_absent' => false,
                            'is_replacement' => false,
                            'replacement_teacher_id' => null,
                            'replacement_teacher' => null,
                            'replacement_comment' => null,
                            'replacement_subject_id' => null,
                            'replacement_subject' => null,
                        ];
                    }
                    $schedule[$groupId]['days'][$day][$lesson] = $pair;
                }
            }
        }

        return view('first_course.schedule.index', [
            'schedule' => $schedule,
            'subjects' => $subjectsForView,
            'teachers' => $teachers,
            'weekMode' => $isDenominatorWeek ? 'den' : 'num',
            'weekStart' => $weekStart->toDateString(),
            'course' => $course,
            'weekDays' => $days,
            'weeklyHolidays' => $weeklyHolidays,
            'holidayWeekDates' => $holidayWeekDates ?? [],
        ]);
    }

    protected function resolveSubjectTitle(array $subjects, ?int $subjectId, bool $useKazakh): ?string
    {
        if (!$subjectId) {
            return null;
        }

        if (!isset($subjects[$subjectId])) {
            return '—';
        }

        $entry = $subjects[$subjectId];
        $ru = $entry['ru'] ?? null;
        $kz = $entry['kz'] ?? null;

        if ($useKazakh) {
            return $kz ?: ($ru ?: '—');
        }

        return $ru ?: ($kz ?: '—');
    }

    protected function isKazakhGroup(?string $groupName): bool
    {
        if (!$groupName) {
            return false;
        }

        return (bool) preg_match('/[ҚқӘәҢңӨөҰұҮүІіҺһҒғ]/u', $groupName);
    }

    /**
     * Форма создания строки расписания.
     */
    public function create()
    {
        $groups = DB::table('groups')->where('year', 1)->get();
        $subjects = DB::table('first_course_subjects')->get();
        $teachers = DB::table('frist_course_teachers')->get();

        $days = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
        ];

        return view('first_course.schedule.create', [
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'days' => $days,
        ]);
    }

    /**
     * Сохранить новую строку расписания.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_day'     => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:8',
            'group_id'      => 'required|integer',
            'subject_id'    => 'nullable|integer',
            'teacher_id'    => 'nullable|integer',
            'room_id'       => 'nullable|integer',
            'has_subgroups'     => 'sometimes|boolean',
            'subject_id_second' => 'required_if:has_subgroups,1|integer|nullable',
            'teacher_id_second' => 'nullable|integer',
            'room_id_second'    => 'nullable|integer',
            'has_denominator'   => 'sometimes|boolean',
            'subject_id_denominator' => 'nullable|integer',
            'teacher_id_denominator' => 'nullable|integer',
            'room_id_denominator'    => 'nullable|integer',
            'subject_id_second_denominator' => 'nullable|integer',
            'teacher_id_second_denominator' => 'nullable|integer',
            'room_id_second_denominator'    => 'nullable|integer',
        ]);

        $hasSubgroups = $request->boolean('has_subgroups');
        $hasDenominator = $request->boolean('has_denominator');

        $baseCommon = [
            'study_day'     => $validated['study_day'],
            'lesson_number' => $validated['lesson_number'],
            'group_id'      => $validated['group_id'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        $rows = [];

        $rows[] = array_merge($baseCommon, [
            'room_id'       => $validated['room_id'] ?? null,
            'teacher_id'    => $validated['teacher_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'subgroup'   => $hasSubgroups ? '1' : null,
            'subject_id_denominator' => $validated['subject_id_denominator'] ?? null,
            'teacher_id_denominator' => $validated['teacher_id_denominator'] ?? null,
            'room_id_denominator'    => $validated['room_id_denominator'] ?? null,
            'subject_id_denominator_2' => null,
            'teacher_id_denominator_2' => null,
            'room_id_denominator_2'    => null,
        ]);

        if ($hasSubgroups && !empty($validated['subject_id_second'])) {
            $rows[] = array_merge($baseCommon, [
                'subject_id' => $validated['subject_id_second'],
                'teacher_id' => $validated['teacher_id_second'] ?? $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id_second'] ?? $validated['room_id'] ?? null,
                'subgroup'   => '2',
                'subject_id_denominator_2' => $validated['subject_id_second_denominator'] ?? null,
                'teacher_id_denominator_2' => $validated['teacher_id_second_denominator'] ?? null,
                'room_id_denominator_2'    => $validated['room_id_second_denominator'] ?? null,
                'subject_id_denominator' => null,
                'teacher_id_denominator' => null,
                'room_id_denominator'    => null,
            ]);
        }

        $tablesCourseOne = CourseContext::tables(1);
        try {
            $teacherSlotsAll = [];
            foreach ($rows as $rowToCheck) {
                $hasDenominator = ($rowToCheck['subject_id_denominator'] ?? null)
                    || ($rowToCheck['teacher_id_denominator'] ?? null)
                    || ($rowToCheck['room_id_denominator'] ?? null)
                    || ($rowToCheck['subject_id_denominator_2'] ?? null)
                    || ($rowToCheck['teacher_id_denominator_2'] ?? null)
                    || ($rowToCheck['room_id_denominator_2'] ?? null);

                $slots = [];
                if (!empty($rowToCheck['room_id'])) {
                    $slots[] = ['room' => $rowToCheck['room_id'], 'mode' => 'numerator'];
                    if (!$hasDenominator) {
                        $slots[] = ['room' => $rowToCheck['room_id'], 'mode' => 'denominator'];
                    }
                }
                if (!empty($rowToCheck['room_id_denominator'])) {
                    $slots[] = ['room' => $rowToCheck['room_id_denominator'], 'mode' => 'denominator'];
                }
                if (!empty($rowToCheck['room_id_denominator_2'])) {
                    $slots[] = ['room' => $rowToCheck['room_id_denominator_2'], 'mode' => 'denominator'];
                }

                $teacherSlots = [];
                if (!empty($rowToCheck['teacher_id'])) {
                    $teacherSlots[] = ['id' => $rowToCheck['teacher_id'], 'mode' => 'numerator'];
                }
                if (!empty($rowToCheck['teacher_id_denominator'])) {
                    $teacherSlots[] = ['id' => $rowToCheck['teacher_id_denominator'], 'mode' => 'denominator'];
                }
                if (!empty($rowToCheck['teacher_id_denominator_2'])) {
                    $teacherSlots[] = ['id' => $rowToCheck['teacher_id_denominator_2'], 'mode' => 'denominator'];
                }
                $teacherSlotsAll = array_merge($teacherSlotsAll, $teacherSlots);

                $this->validateRoomsOrFail(
                    (int) $rowToCheck['group_id'],
                    $rowToCheck['study_day'],
                    (int) $rowToCheck['lesson_number'],
                    $slots,
                    null,
                    'first_course_schedules',
                    1,
                    $tablesCourseOne['groups'] ?? null
                );
            }

            $this->validateTeachersOrFail(
                (int) $validated['group_id'],
                $validated['study_day'],
                (int) $validated['lesson_number'],
                $teacherSlotsAll,
                null,
                'first_course_schedules',
                $tablesCourseOne['teachers'] ?? null,
                1
            );
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Недоступно в это время';
            return back()->withErrors(['room_id' => $msg])->withInput();
        }

        $nextId = (int) DB::table('first_course_schedules')->max('id') + 1;
        $rowsWithIds = [];
        foreach ($rows as $r) {
            $rowsWithIds[] = array_merge(['id' => $nextId++], $r);
        }

        DB::table('first_course_schedules')->insert($rowsWithIds);

        return redirect()
            ->route('first.schedule.index')
            ->with('success', 'Запись добавлена!');
    }

    /**
     * Визуальный редактор недельного расписания (дизайн-версия).
     */
    public function week()
    {
        $course = CourseContext::normalize(request()->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();
        $subjects = DB::table($tables['subjects'])->orderBy('name_ru')->get();
        $teachers = DB::table($tables['teachers'])->orderBy('teacher_name')->get();

        $selectedGroupId = request()->integer('group_id') ?: ($groups->first()->id ?? null);
        $weekStartInput = request()->get('week_start');
        $weekStart = $weekStartInput ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $days = $this->buildWeekDays($weekStart);

        $pairs = [1, 2, 3, 4, 5];

        $dayNames = collect($days)->mapWithKeys(fn($d) => [$d['key'] => $d['full']]);

        $existing = [];
        if ($selectedGroupId) {
            $existingRows = DB::table($tables['schedules'])
                ->where('group_id', $selectedGroupId)
                ->whereIn('study_day', $dayNames->values())
                ->whereDate('week_start', $weekStart->toDateString())
                ->get();

            foreach ($existingRows as $row) {
                $key = $dayNames->search($row->study_day) ?: null;
                if (!$key) {
                    continue;
                }
                $subKey = match ($row->subgroup) {
                    '1', 'A' => '1',
                    '2', 'B' => '2',
                    default => '',
                };
                $existing[$key][$row->lesson_number][$subKey] = $row;
            }
        }

        $holidayDaysOfWeek = collect($days)->filter(fn($day) => !empty($day['holiday']))->values()->all();

        return view('first_course.schedule.week', [
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'days' => $days,
            'pairs' => $pairs,
            'selectedGroupId' => $selectedGroupId,
            'existing' => $existing,
            'weekStart' => $weekStart->toDateString(),
            'course' => $course,
            'weeklyHolidays' => $holidayDaysOfWeek,
        ]);
    }

    /**
     * Развернуть расписание одной недели на весь семестр.
     */
    public function expandSemester(Request $request, SemesterScheduleService $semesterService)
    {
        $data = $request->validate([
            'group_id' => 'required|integer',
            'template_week_start' => 'required|date',
            'semester_start' => 'required|date',
            'semester_end' => 'required|date',
            'skip_existing' => 'sometimes|boolean',
            'sync_form_two' => 'sometimes|boolean',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        $course = CourseContext::normalize($data['course'] ?? request()->integer('course') ?? 1);
        $groupId = (int) $data['group_id'];
        $templateWeek = Carbon::parse($data['template_week_start'])->startOfWeek(Carbon::MONDAY);
        $semesterStart = Carbon::parse($data['semester_start'])->startOfWeek(Carbon::MONDAY);
        $semesterEnd = Carbon::parse($data['semester_end'])->startOfWeek(Carbon::MONDAY);

        if ($semesterEnd->lt($semesterStart)) {
            return back()->withErrors(['semester_end' => 'Дата окончания семестра не может быть раньше начала'])->withInput();
        }

        $skipExisting = $request->boolean('skip_existing');
        $syncFormTwo = $request->boolean('sync_form_two', true);

        $result = $semesterService->expandFromTemplate(
            $groupId,
            $templateWeek,
            $semesterStart,
            $semesterEnd,
            $skipExisting,
            $syncFormTwo,
            $course
        );

        if ($result['inserted_weeks'] === 0 && empty($result['skipped_weeks'])) {
            return back()->withErrors(['template_week_start' => 'Нет строк в выбранной эталонной неделе'])->withInput();
        }

        $message = sprintf(
            'Развернули %d недель (%d строк).',
            $result['inserted_weeks'],
            $result['inserted_rows']
        );
        if (!empty($result['skipped_weeks'])) {
            $message .= ' Пропущены (уже есть): ' . implode(', ', $result['skipped_weeks']);
        }

        return redirect()
            ->route('first.schedule.week', [
                'group_id' => $groupId,
                'week_start' => $templateWeek->toDateString(),
                'course' => $course,
            ])
            ->with('success', $message);
    }

    /**
     * Сохранить недельное расписание для группы.
     */
    public function weekSave(Request $request)
    {
        $dayMap = [
            'mon' => 'Понедельник',
            'tue' => 'Вторник',
            'wed' => 'Среда',
            'thu' => 'Четверг',
            'fri' => 'Пятница',
        ];

        $validated = $request->validate([
            'group_id' => 'required|integer',
            'week_start' => 'required|date',
            'schedule' => 'array',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        $course = CourseContext::normalize($validated['course'] ?? request()->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groupId = $validated['group_id'];
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $weekMode = $sync->resolveWeekMode($weekStart, $course);
        $schedule = $validated['schedule'] ?? [];

        $dayDefinitions = $this->buildWeekDays($weekStart);
        $holidayDayKeys = collect($dayDefinitions)
            ->filter(fn($day) => !empty($day['holiday']))
            ->pluck('key')
            ->all();

        $rows = [];
        $now = now();

        foreach ($schedule as $dayKey => $lessons) {
            if (!isset($dayMap[$dayKey])) {
                continue;
            }
            if (in_array($dayKey, $holidayDayKeys, true)) {
                continue;
            }
            foreach ($lessons as $lessonNumber => $row) {
                $subjectId = $row['subject_id'] ?? null;
                $teacherId = $row['teacher_id'] ?? null;
                $roomId = $row['room_id'] ?? null;
                $hasSubgroups = isset($row['has_subgroups']) && (bool)$row['has_subgroups'];
                $subjectSecond = $row['subject_id_second'] ?? null;
                $teacherSecond = $row['teacher_id_second'] ?? null;
                $roomSecond = $row['room_id_second'] ?? null;
                $subjectDenominator = $row['subject_id_denominator'] ?? null;
                $teacherDenominator = $row['teacher_id_denominator'] ?? null;
                $roomDenominator = $row['room_id_denominator'] ?? null;
                $subjectSecondDenominator = $row['subject_id_second_denominator'] ?? null;
                $teacherSecondDenominator = $row['teacher_id_second_denominator'] ?? null;
                $roomSecondDenominator = $row['room_id_second_denominator'] ?? null;
                $subjectSecondDenominator2 = $row['subject_id_second_denominator_2'] ?? null;
                $teacherSecondDenominator2 = $row['teacher_id_second_denominator_2'] ?? null;
                $roomSecondDenominator2 = $row['room_id_second_denominator_2'] ?? null;
                $subjectDenominator2 = $row['subject_id_denominator_2'] ?? null;
                $teacherDenominator2 = $row['teacher_id_denominator_2'] ?? null;
                $roomDenominator2 = $row['room_id_denominator_2'] ?? null;

                // Если вообще нет данных по строке — пропускаем
                if (
                    !$subjectId && !$subjectSecond && !$teacherId && !$teacherSecond
                    && !$roomId && !$roomSecond
                    && !$subjectDenominator && !$subjectSecondDenominator
                    && !$teacherDenominator && !$teacherSecondDenominator
                    && !$roomDenominator && !$roomSecondDenominator
                    && !$subjectSecondDenominator2 && !$teacherSecondDenominator2 && !$roomSecondDenominator2
                    && !$subjectDenominator2 && !$teacherDenominator2 && !$roomDenominator2
                ) {
                    continue;
                }

                $hasDenominator = $subjectDenominator || $teacherDenominator || $roomDenominator
                    || $subjectDenominator2 || $teacherDenominator2 || $roomDenominator2;

                $baseCommon = [
                    'week_start'   => $weekStart->toDateString(),
                    'study_day'     => $dayMap[$dayKey],
                    'lesson_number' => (int) $lessonNumber,
                    'group_id'      => $groupId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $rows[] = array_merge($baseCommon, [
                    'room_id'       => $roomId ?: null,
                    'teacher_id'    => $teacherId ?: null,
                    'subject_id' => $subjectId ?: null,
                    'subgroup'   => $hasSubgroups ? '1' : null,
                    'subject_id_denominator' => $subjectDenominator ?: null,
                    'teacher_id_denominator' => $teacherDenominator ?: null,
                    'room_id_denominator'    => $roomDenominator ?: null,
                    'subject_id_denominator_2' => null,
                    'teacher_id_denominator_2' => null,
                    'room_id_denominator_2'    => null,
                ]);

                $slots = [];
                if ($roomId) {
                    $slots[] = ['room' => $roomId, 'mode' => 'numerator'];
                    if (!$hasDenominator) {
                        $slots[] = ['room' => $roomId, 'mode' => 'denominator'];
                    }
                }
                if ($roomDenominator) {
                    $slots[] = ['room' => $roomDenominator, 'mode' => 'denominator'];
                }
                if ($roomDenominator2) {
                    $slots[] = ['room' => $roomDenominator2, 'mode' => 'denominator'];
                }
                $teacherSlots = [];
                if ($teacherId) {
                    $teacherSlots[] = ['id' => $teacherId, 'mode' => 'numerator'];
                }
                if ($teacherDenominator) {
                    $teacherSlots[] = ['id' => $teacherDenominator, 'mode' => 'denominator'];
                }

                $hasSecondNumerator = $subjectSecond || $teacherSecond || $roomSecond;
                $hasDenominatorSecond = $subjectSecondDenominator || $teacherSecondDenominator || $roomSecondDenominator
                    || $subjectSecondDenominator2 || $teacherSecondDenominator2 || $roomSecondDenominator2;

                $teacherSlotsAll = $teacherSlots;

                try {
                    $this->validateRoomsOrFail(
                        $groupId,
                        $dayMap[$dayKey],
                        (int) $lessonNumber,
                        $slots,
                        $weekStart,
                        $tables['schedules'],
                        $course,
                        $tables['groups'] ?? null
                    );

                    $slotsSecond = [];
                    $teacherSlotsSecond = [];

                    if ($hasSubgroups && ($hasSecondNumerator || $hasDenominatorSecond)) {
                        $rows[] = array_merge($baseCommon, [
                            'subject_id' => $subjectSecond ?: null,
                            'teacher_id' => $hasSecondNumerator ? ($teacherSecond ?: $teacherId ?: null) : null,
                            'room_id'    => $hasSecondNumerator ? ($roomSecond ?: $roomId ?: null) : null,
                            'subgroup'   => '2',
                            'subject_id_denominator_2' => $subjectSecondDenominator ?: $subjectSecondDenominator2 ?: null,
                            'teacher_id_denominator_2' => $teacherSecondDenominator ?: $teacherSecondDenominator2 ?: null,
                            'room_id_denominator_2'    => $roomSecondDenominator ?: $roomSecondDenominator2 ?: null,
                            'subject_id_denominator' => null,
                            'teacher_id_denominator' => null,
                            'room_id_denominator'    => null,
                        ]);

                        if ($hasSecondNumerator && ($roomSecond || $roomId)) {
                            $roomForSubgroup = $roomSecond ?: $roomId;
                            $slotsSecond[] = ['room' => $roomForSubgroup, 'mode' => 'numerator'];
                            if (!$hasDenominatorSecond) {
                                $slotsSecond[] = ['room' => $roomForSubgroup, 'mode' => 'denominator'];
                            }
                        }
                        if ($roomSecondDenominator || $roomSecondDenominator2) {
                            $slotsSecond[] = ['room' => $roomSecondDenominator ?: $roomSecondDenominator2, 'mode' => 'denominator'];
                        }
                        if ($hasSecondNumerator && ($teacherSecond || $teacherId)) {
                            $teacherSlotsSecond[] = ['id' => $teacherSecond ?: $teacherId, 'mode' => 'numerator'];
                        }
                        if ($teacherSecondDenominator || $teacherSecondDenominator2) {
                            $teacherSlotsSecond[] = ['id' => $teacherSecondDenominator ?: $teacherSecondDenominator2, 'mode' => 'denominator'];
                        }
                        if ($slotsSecond) {
                            $this->validateRoomsOrFail(
                                $groupId,
                                $dayMap[$dayKey],
                                (int) $lessonNumber,
                                $slotsSecond,
                                $weekStart,
                                $tables['schedules'],
                                $course,
                                $tables['groups'] ?? null
                            );
                        }
                        $teacherSlotsAll = array_merge($teacherSlotsAll, $teacherSlotsSecond);
                    }

                    $this->validateTeachersOrFail(
                        $groupId,
                        $dayMap[$dayKey],
                        (int) $lessonNumber,
                        $teacherSlotsAll,
                        $weekStart,
                        $tables['schedules'],
                        $tables['teachers'],
                        $course
                    );
                } catch (ValidationException $e) {
                    $msg = collect($e->errors())->flatten()->first() ?: 'Недоступно в это время';
                    return back()
                        ->withErrors(['room_id' => $msg])
                        ->withInput();
                }
            }
        }

        DB::transaction(function () use ($groupId, $rows, $weekStart, $tables) {
            DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $weekStart->toDateString())
                ->delete();
            if ($rows) {
                $nextId = (int) DB::table($tables['schedules'])->max('id') + 1;
                $rowsWithIds = [];
                foreach ($rows as $r) {
                    $rowsWithIds[] = array_merge(['id' => $nextId++], $r);
                }
                DB::table($tables['schedules'])->insert($rowsWithIds);
            }
        });

        $hasDenominatorData = collect($rows)->contains(function (array $row): bool {
            return !empty($row['subject_id_denominator'])
                || !empty($row['teacher_id_denominator'])
                || !empty($row['room_id_denominator'])
                || !empty($row['subject_id_denominator_2'])
                || !empty($row['teacher_id_denominator_2'])
                || !empty($row['room_id_denominator_2']);
        });

        if ($hasDenominatorData) {
            $nextWeekStart = $weekStart->copy()->addWeek();
            $nextWeekExists = DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $nextWeekStart->toDateString())
                ->exists();

            if (!$nextWeekExists && $rows) {
                $nextId = (int) DB::table($tables['schedules'])->max('id') + 1;
                $rowsForNextWeek = [];
                foreach ($rows as $row) {
                    $rowsForNextWeek[] = array_merge($row, [
                        'id' => $nextId++,
                        'week_start' => $nextWeekStart->toDateString(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                DB::table($tables['schedules'])->insert($rowsForNextWeek);
            }
        }

        $sync->syncWeekWithAlternation($groupId, $weekStart, $course);

        return redirect()
            ->route('first.schedule.week', ['group_id' => $groupId, 'week_start' => $weekStart->toDateString(), 'course' => $course])
            ->with('success', 'Недельное расписание сохранено.');
    }

    protected function buildWeekDays(Carbon $weekStart): array
    {
        $holidayService = app(KazakhstanHolidayService::class);
        $templates = [
            ['key' => 'mon', 'label' => 'Пн', 'full' => 'Понедельник', 'offset' => 0],
            ['key' => 'tue', 'label' => 'Вт', 'full' => 'Вторник', 'offset' => 1],
            ['key' => 'wed', 'label' => 'Ср', 'full' => 'Среда', 'offset' => 2],
            ['key' => 'thu', 'label' => 'Чт', 'full' => 'Четверг', 'offset' => 3],
            ['key' => 'fri', 'label' => 'Пт', 'full' => 'Пятница', 'offset' => 4],
        ];

        $cache = [];
        $days = [];
        foreach ($templates as $template) {
            $date = $weekStart->copy()->addDays($template['offset']);
            $monthKey = $date->format('Y-m');
            if (!isset($cache[$monthKey])) {
                $cache[$monthKey] = $holidayService->getMonthHolidays($date->year, $date->month);
            }
            $holidayMeta = $cache[$monthKey][$date->day] ?? null;

            $days[] = [
                'key' => $template['key'],
                'label' => $template['label'],
                'full' => $template['full'],
                'name' => $template['full'],
                'date' => $date->toDateString(),
                'holiday' => $holidayMeta ? [
                    'name' => $holidayMeta['name'],
                    'label' => $date->format('d.m.Y'),
                    'day' => $template['full'],
                ] : null,
            ];
        }

        return $days;
    }

    /**
     * Обновить одну пару (с подгруппами) через модалку.
     */
    public function updatePair(Request $request)
    {
        $dayMap = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
            'Суббота',
        ];

        $data = $request->validate([
            'group_id'      => 'required|integer',
            'study_day'     => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:5',
            'week_start'    => 'required|date',
            'subject_id'    => 'nullable|integer',
            'teacher_id'    => 'nullable|integer',
            'room_id'       => 'nullable|string|max:50',
            'has_sub2'      => 'sometimes|boolean',
            'subject_id_2'  => 'nullable|integer',
            'teacher_id_2'  => 'nullable|integer',
            'room_id_2'     => 'nullable|string|max:50',
            'den_subject_id'   => 'nullable|integer',
            'den_teacher_id'   => 'nullable|integer',
            'den_room_id'      => 'nullable|string|max:50',
            'den_subject_id_2' => 'nullable|integer',
            'den_teacher_id_2' => 'nullable|integer',
            'den_room_id_2'    => 'nullable|string|max:50',
            'is_absent_1' => 'sometimes|boolean',
            'is_absent_2' => 'sometimes|boolean',
            'is_replacement_1' => 'sometimes|boolean',
            'replacement_teacher_id_1' => 'nullable|integer',
            'replacement_subject_id_1' => 'nullable|integer',
            'replacement_comment_1' => 'nullable|string|max:255',
            'den_is_replacement_1' => 'sometimes|boolean',
            'replacement_teacher_id_1_den' => 'nullable|integer',
            'replacement_subject_id_1_den' => 'nullable|integer',
            'replacement_comment_1_den' => 'nullable|string|max:255',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        if (!in_array($data['study_day'], $dayMap, true)) {
            return response()->json(['message' => 'Некорректный день недели'], 422);
        }

        $course = CourseContext::normalize($data['course'] ?? request()->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groupId = $data['group_id'];
        $day = $data['study_day'];
        $lesson = $data['lesson_number'];
        $hasSub2 = $request->boolean('has_sub2');
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $weekMode = $sync->resolveWeekMode($weekStart, $course);

        $existingRows = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->where('study_day', $day)
            ->where('lesson_number', $lesson)
            ->whereDate('week_start', $weekStart->toDateString())
            ->get()
            ->keyBy(fn ($row) => $row->subgroup ?? '1');

        // Проверка занятости учителей
        $possibleTeachers = [
            $data['teacher_id'] ?? null,
            $data['teacher_id_2'] ?? null,
            $data['den_teacher_id'] ?? null,
            $data['den_teacher_id_2'] ?? null,
        ];

        $teacherIdsToCheck = array_values(array_filter($possibleTeachers));

        if ($teacherIdsToCheck) {
            $conflict = DB::table($tables['schedules'])
                ->select('group_id')
                ->where('study_day', $day)
                ->where('lesson_number', $lesson)
                ->where('group_id', '<>', $groupId)
                ->whereDate('week_start', $weekStart->toDateString())
                ->where(function ($q) use ($teacherIdsToCheck) {
                    $q->whereIn('teacher_id', $teacherIdsToCheck)
                        ->orWhereIn('teacher_id_2', $teacherIdsToCheck)
                        ->orWhereIn('teacher_id_denominator', $teacherIdsToCheck);
                })
                ->first();

            if ($conflict) {
                $groupName = $this->groupNameById($conflict->group_id ?? null, $tables['groups'] ?? null) ?? 'другой группы';
                $pairLabel = sprintf('%s, пара %d', $day, $lesson);
                return response()->json(['message' => 'Выбранный преподаватель занят на ' . $pairLabel . ' у группы ' . $groupName], 422);
            }
        }

        $hasSub2Numerator = $hasSub2 && (
            ($data['subject_id_2'] ?? null)
            || ($data['teacher_id_2'] ?? null)
            || ($data['room_id_2'] ?? null)
        );
        $hasSub2Denominator = $hasSub2 && (
            ($data['den_subject_id_2'] ?? null)
            || ($data['den_teacher_id_2'] ?? null)
            || ($data['den_room_id_2'] ?? null)
        );
        $hasSub2Data = $hasSub2Numerator || $hasSub2Denominator;
        $hasDenominatorMain = ($data['den_subject_id'] ?? null)
            || ($data['den_teacher_id'] ?? null)
            || ($data['den_room_id'] ?? null);

        $teacherSlots = [];
        if (!empty($data['teacher_id'])) {
            $teacherSlots[] = ['id' => $data['teacher_id'], 'mode' => 'numerator'];
        }
        if (!empty($data['den_teacher_id'])) {
            $teacherSlots[] = ['id' => $data['den_teacher_id'], 'mode' => 'denominator'];
        }
        if ($hasSub2Numerator && ($data['teacher_id_2'] ?? null)) {
            $teacherSlots[] = ['id' => $data['teacher_id_2'], 'mode' => 'numerator'];
        }
        if ($hasSub2Denominator && ($data['den_teacher_id_2'] ?? null)) {
            $teacherSlots[] = ['id' => $data['den_teacher_id_2'], 'mode' => 'denominator'];
        }

        $roomSlots = [];

        if (!empty($data['room_id'])) {
            $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'numerator'];
            if (!$hasDenominatorMain) {
                $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'denominator'];
            }
        }
        if (!empty($data['den_room_id'])) {
            $roomSlots[] = ['room' => $data['den_room_id'], 'mode' => 'denominator'];
        }

        if ($hasSub2Numerator) {
            if (!empty($data['room_id_2'])) {
                $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'numerator'];
                if (!$hasSub2Denominator) {
                    $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'denominator'];
                }
            }
        }

        if ($hasSub2Denominator && !empty($data['den_room_id_2'])) {
            $roomSlots[] = ['room' => $data['den_room_id_2'], 'mode' => 'denominator'];
        }

        try {
            $this->validateRoomsOrFail(
                $groupId,
                $day,
                $lesson,
                $roomSlots,
                $weekStart,
                $tables['schedules'],
                $course,
                $tables['groups'] ?? null
            );
            $this->validateTeachersOrFail(
                $groupId,
                $day,
                $lesson,
                $teacherSlots,
                $weekStart,
                $tables['schedules'],
                $tables['teachers'],
                $course
            );
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Недоступно в это время';
            return response()->json(['message' => $msg], 422);
        }

        DB::transaction(function () use (
            $groupId,
            $day,
            $lesson,
            $data,
            $hasSub2,
            $hasSub2Data,
            $hasSub2Numerator,
            $hasSub2Denominator,
            $hasDenominatorMain,
            $weekStart,
            $weekMode,
            $existingRows,
            $tables
        ) {
            DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->where('study_day', $day)
                ->where('lesson_number', $lesson)
                ->whereDate('week_start', $weekStart->toDateString())
                ->delete();

            $now = now();

            $absent1 = (bool)($data['is_absent_1'] ?? false);
            $absent2 = (bool)($data['is_absent_2'] ?? false);
            $isReplacement1 = (bool)($data['is_replacement_1'] ?? false);
            $isReplacement1DenInput = (bool)($data['den_is_replacement_1'] ?? false);

            $prev1 = $existingRows['1'] ?? null;
            $prev2 = $existingRows['2'] ?? null;

            $subjectDen1 = $hasDenominatorMain ? ($data['den_subject_id'] ?? null) : null;
            $teacherDen1 = $hasDenominatorMain ? ($data['den_teacher_id'] ?? null) : null;
            $roomDen1 = $hasDenominatorMain ? ($data['den_room_id'] ?? null) : null;

            $denAbsent1 = false;
            $denReplacement1 = false;
            $denReplacementTeacher1 = null;
            $denReplacementSubject1 = null;
            $denReplacementComment1 = null;
            if ($hasDenominatorMain) {
                $denAbsent1 = $weekMode === 'denominator'
                    ? $absent1
                    : ($prev1?->is_absent_1_den ?? false);

                if ($weekMode === 'denominator') {
                    $denReplacement1 = $isReplacement1;
                    $denReplacementTeacher1 = $data['replacement_teacher_id_1'] ?? null;
                    $denReplacementSubject1 = $data['replacement_subject_id_1'] ?? null;
                    $denReplacementComment1 = $data['replacement_comment_1'] ?? null;
                } else {
                    $denReplacement1 = ($data['den_is_replacement_1'] ?? null) !== null
                        ? $isReplacement1DenInput
                        : ($prev1?->is_replacement_1_den ?? false);
                    $denReplacementTeacher1 = $data['replacement_teacher_id_1_den'] ?? $prev1?->replacement_teacher_id_1_den ?? null;
                    $denReplacementSubject1 = $data['replacement_subject_id_1_den'] ?? $prev1?->replacement_subject_id_1_den ?? null;
                    $denReplacementComment1 = $data['replacement_comment_1_den'] ?? $prev1?->replacement_comment_1_den ?? null;
                }
            }

            $rows = [[
                'week_start'   => $weekStart->toDateString(),
                'study_day'     => $day,
                'lesson_number' => $lesson,
                'group_id'      => $groupId,
                'subject_id'    => $data['subject_id'] ?? null,
                'teacher_id'    => $data['teacher_id'] ?? null,
                'room_id'       => $data['room_id'] ?? null,
                'subgroup'      => '1',
                'subject_id_denominator' => $subjectDen1,
                'teacher_id_denominator' => $teacherDen1,
                'room_id_denominator'    => $roomDen1,
                'created_at'    => $now,
                'updated_at'    => $now,
                'is_absent_1_num' => $weekMode === 'denominator' ? ($prev1?->is_absent_1_num ?? false) : $absent1,
                'is_replacement_1_num' => $weekMode === 'denominator' ? ($prev1?->is_replacement_1_num ?? false) : $isReplacement1,
                'replacement_teacher_id_1_num' => $weekMode === 'denominator' ? ($prev1?->replacement_teacher_id_1_num ?? null) : ($data['replacement_teacher_id_1'] ?? null),
                'replacement_subject_id_1_num' => $weekMode === 'denominator' ? ($prev1?->replacement_subject_id_1_num ?? null) : ($data['replacement_subject_id_1'] ?? null),
                'replacement_comment_1_num' => $weekMode === 'denominator' ? ($prev1?->replacement_comment_1_num ?? null) : ($data['replacement_comment_1'] ?? null),
                'is_absent_1_den' => $denAbsent1,
                'is_replacement_1_den' => $denReplacement1,
                'replacement_teacher_id_1_den' => $denReplacementTeacher1,
                'replacement_subject_id_1_den' => $denReplacementSubject1,
                'replacement_comment_1_den' => $denReplacementComment1,
            ]];

            if ($hasSub2Data) {
                $subjectDen2 = $hasSub2Denominator ? ($data['den_subject_id_2'] ?? null) : null;
                $teacherDen2 = $hasSub2Denominator ? ($data['den_teacher_id_2'] ?? null) : null;
                $roomDen2 = $hasSub2Denominator ? ($data['den_room_id_2'] ?? null) : null;

                $denAbsent2 = false;
                if ($hasSub2Denominator) {
                    $denAbsent2 = $weekMode === 'denominator'
                        ? $absent2
                        : ($prev2?->is_absent_2_den ?? false);
                }

                $rows[] = [
                    'week_start'   => $weekStart->toDateString(),
                    'study_day'     => $day,
                    'lesson_number' => $lesson,
                    'group_id'      => $groupId,
                    'subject_id'    => $hasSub2Numerator ? $subjectId2 : null,
                    'teacher_id'    => $hasSub2Numerator ? $teacherId2 : null,
                    'room_id'       => $hasSub2Numerator ? $roomId2 : null,
                    'subgroup'      => '2',
                    'subject_id_denominator_2' => $subjectDen2,
                    'teacher_id_denominator_2' => $teacherDen2,
                    'room_id_denominator_2'    => $roomDen2,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'is_absent_2_num' => $weekMode === 'denominator' ? ($prev2?->is_absent_2_num ?? false) : $absent2,
                    'is_absent_2_den' => $denAbsent2,
                ];
            }

            foreach ($rows as $row) {
                DB::table($tables['schedules'])->insert($row);
            }
        });

        $sync->syncWeekWithAlternation($groupId, $weekStart, $course);

        return response()->json(['message' => 'Пара обновлена']);
    }

    /**
     * Проверяем занятость кабинетов по режимам недели.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRoomsOrFail(
        int $groupId,
        string $studyDay,
        int $lessonNumber,
        array $slots,
        ?Carbon $weekStart = null,
        ?string $table = null,
        int $currentCourse = 1,
        ?string $groupTable = null
    ): void
    {
        foreach ($slots as $slot) {
            $room = $this->normalizeRoomString($slot['room'] ?? null);
            $mode = $slot['mode'] ?? 'numerator';

            if ($room === null || $room === '') {
                continue;
            }

            $conflict = $this->roomBusyInTable(
                $room,
                $mode,
                $studyDay,
                $lessonNumber,
                $weekStart,
                $table ?: 'first_course_schedules',
                $groupId,
                $groupTable
            );
            if ($conflict) {
                $groupName = $conflict['group_name'] ?? 'другой группы';
                $pairLabel = sprintf('%s, пара %d', $conflict['study_day'], $conflict['lesson_number']);
                throw ValidationException::withMessages([
                    'room_id' => 'Кабинет занят у группы ' . $groupName . ' (' . $pairLabel . ')',
                ]);
            }

            foreach ([1, 2, 3, 4] as $course) {
                if ($course === $currentCourse) {
                    continue;
                }
                $courseTables = CourseContext::tables($course);
                $scheduleTable = $courseTables['schedules'] ?? null;
                if (!$scheduleTable || !Schema::hasTable($scheduleTable)) {
                    continue;
                }
                $conflict = $this->roomBusyInTable(
                    $room,
                    $mode,
                    $studyDay,
                    $lessonNumber,
                    $weekStart,
                    $scheduleTable,
                    null,
                    $courseTables['groups'] ?? null
                );
                if ($conflict) {
                    $groupName = $conflict['group_name'] ?? 'другой группы';
                    $pairLabel = sprintf('%s, пара %d', $conflict['study_day'], $conflict['lesson_number']);
                    throw ValidationException::withMessages([
                        'room_id' => 'Кабинет занят у группы ' . $groupName . ' (' . $pairLabel . ')',
                    ]);
                }
            }
        }
    }

    protected function normalizeRoomString($room): ?string
    {
        if ($room === null) {
            return null;
        }
        $room = trim((string) $room);
        return $room === '' ? null : $room;
    }

    protected function roomBusyInTable(
        string $room,
        string $mode,
        string $studyDay,
        int $lessonNumber,
        ?Carbon $weekStart,
        string $table,
        ?int $excludeGroupId,
        ?string $groupTable = null
    ): ?array {
        if (!Schema::hasTable($table)) {
            return null;
        }

        $rows = DB::table($table)
            ->where('study_day', $studyDay)
            ->where('lesson_number', $lessonNumber)
            ->when($excludeGroupId, fn ($q) => $q->where('group_id', '<>', $excludeGroupId))
            ->when($weekStart, fn ($q) => $q->whereDate('week_start', $weekStart->toDateString()))
            ->get();

        foreach ($rows as $row) {
            $hasDenominator = ($row->subject_id_denominator ?? null)
                || ($row->teacher_id_denominator ?? null)
                || ($row->room_id_denominator ?? null)
                || ($row->subject_id_denominator_2 ?? null)
                || ($row->teacher_id_denominator_2 ?? null)
                || ($row->room_id_denominator_2 ?? null);

            $modes = $hasDenominator ? ['numerator'] : ['numerator', 'denominator'];
            $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? '2' : '1';

            $roomNum1 = $subgroupFlag === '1' ? ($row->room_id ?? null) : null;
            $roomNum2 = ($row->room_id_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->room_id ?? null) : null);
            $roomDen1 = $subgroupFlag === '1' ? ($row->room_id_denominator ?? null) : null;
            $roomDen2 = ($row->room_id_denominator_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->room_id_denominator ?? null) : null);

            $rowSlots = [];
            foreach ($modes as $m) {
                $rowSlots[] = ['room' => $roomNum1, 'mode' => $m];
                $rowSlots[] = ['room' => $roomNum2, 'mode' => $m];
            }
            $rowSlots[] = ['room' => $roomDen1, 'mode' => 'denominator'];
            $rowSlots[] = ['room' => $roomDen2, 'mode' => 'denominator'];

            foreach ($rowSlots as $rowSlot) {
                $rowRoom = $this->normalizeRoomString($rowSlot['room'] ?? null);
                if ($rowRoom === null) {
                    continue;
                }
                if ($rowRoom === $room && $rowSlot['mode'] === $mode) {
                    $groupId = (int) ($row->group_id ?? 0);
                    return [
                        'group_id' => $groupId,
                        'group_name' => $this->groupNameById($groupId, $groupTable),
                        'mode' => $mode,
                        'study_day' => $studyDay,
                        'lesson_number' => $lessonNumber,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Проверка занятости преподавателей по режимам недели.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateTeachersOrFail(
        int $groupId,
        string $studyDay,
        int $lessonNumber,
        array $slots,
        ?Carbon $weekStart = null,
        ?string $table = null,
        ?string $teacherTable = null,
        int $currentCourse = 1
    ): void
    {
        if (empty($slots)) {
            return;
        }

        $occupiedByMode = [];
        $pairLabel = sprintf('%s, пара %d', $studyDay, $lessonNumber);
        foreach ($slots as $slot) {
            $teacherId = $slot['id'] ?? null;
            if (!$teacherId) {
                continue;
            }
            $mode = ($slot['mode'] ?? 'numerator') === 'denominator' ? 'denominator' : 'numerator';
            if (!isset($occupiedByMode[$mode])) {
                $occupiedByMode[$mode] = [];
            }
            if (in_array($teacherId, $occupiedByMode[$mode], true)) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Преподаватель уже назначен на ' . $pairLabel . ' в другой подгруппе',
                ]);
            }
            $occupiedByMode[$mode][] = $teacherId;
        }

        foreach ($slots as $slot) {
            $teacherId = $slot['id'] ?? null;
            if (!$teacherId) {
                continue;
            }
            $mode = $slot['mode'] ?? null;

            $courseTables = CourseContext::tables($currentCourse);
            $groupTable = $courseTables['groups'] ?? null;

            $conflict = DB::table($table ?? 'first_course_schedules')
                ->select('group_id')
                ->where('study_day', $studyDay)
                ->where('lesson_number', $lessonNumber)
                ->where('group_id', '<>', $groupId)
                ->when($weekStart, fn ($q) => $q->whereDate('week_start', $weekStart->toDateString()))
                ->where(function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId)
                        ->orWhere('teacher_id_2', $teacherId)
                        ->orWhere('teacher_id_denominator', $teacherId)
                        ->orWhere('teacher_id_denominator_2', $teacherId);
                })
                ->first();

            if ($conflict) {
                $groupName = $this->groupNameById($conflict->group_id ?? null, $groupTable) ?? 'другой группы';
                throw ValidationException::withMessages([
                    'teacher_id' => 'Преподаватель занят на ' . $pairLabel . ' у группы ' . $groupName,
                ]);
            }
        }

        if (!$teacherTable) {
            return;
        }

        $teacherIds = collect($slots)->pluck('id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
        $teacherNames = $this->teacherNamesByIds($teacherIds, $teacherTable);
        $normalizedNames = $this->normalizeNames($teacherNames);
        if (empty($normalizedNames)) {
            return;
        }

        foreach ([1, 2, 3, 4] as $course) {
            if ($course === $currentCourse) {
                continue;
            }
            $courseTables = CourseContext::tables($course);
            if (!Schema::hasTable($courseTables['schedules']) || !Schema::hasTable($courseTables['teachers'])) {
                continue;
            }
            $matchingTeacherIds = $this->teacherIdsByNames($courseTables['teachers'], $normalizedNames);
            if (empty($matchingTeacherIds)) {
                continue;
            }

            $conflict = DB::table($courseTables['schedules'])
                ->select('group_id')
                ->where('study_day', $studyDay)
                ->where('lesson_number', $lessonNumber)
                ->when($weekStart, fn($q) => $q->whereDate('week_start', $weekStart->toDateString()))
                ->where(function ($q) use ($matchingTeacherIds) {
                    $q->whereIn('teacher_id', $matchingTeacherIds)
                        ->orWhereIn('teacher_id_2', $matchingTeacherIds)
                        ->orWhereIn('teacher_id_denominator', $matchingTeacherIds)
                        ->orWhereIn('teacher_id_denominator_2', $matchingTeacherIds);
                })
                ->first();

            if ($conflict) {
                $groupName = $this->groupNameById($conflict->group_id ?? null, $courseTables['groups'] ?? null) ?? 'другой группы';
                throw ValidationException::withMessages([
                    'teacher_id' => 'Преподаватель занят на ' . $pairLabel . ' на другом курсе (группа ' . $groupName . ')',
                ]);
            }
        }
    }

    protected function groupNameById(?int $groupId, ?string $groupTable): ?string
    {
        if (!$groupId || !$groupTable || !Schema::hasTable($groupTable)) {
            return null;
        }

        return DB::table($groupTable)
            ->where('id', $groupId)
            ->value('group_name') ?: null;
    }

    protected function normalizeNames(array $names): array
    {
        return collect($names)
            ->filter(fn($v) => $v !== null && $v !== '')
            ->map(fn($v) => mb_strtolower(trim((string) $v)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function teacherNamesByIds(array $teacherIds, ?string $teacherTable): array
    {
        if (empty($teacherIds) || !$teacherTable || !Schema::hasTable($teacherTable)) {
            return [];
        }

        return DB::table($teacherTable)
            ->whereIn('id', $teacherIds)
            ->pluck('teacher_name')
            ->filter()
            ->values()
            ->all();
    }

    protected function teacherIdsByNames(string $teacherTable, array $normalizedNames): array
    {
        if (empty($normalizedNames) || !Schema::hasTable($teacherTable)) {
            return [];
        }

        $rows = DB::table($teacherTable)
            ->select('id', 'teacher_name')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $name = mb_strtolower(trim((string) ($row->teacher_name ?? '')));
            if ($name === '' || !in_array($name, $normalizedNames, true)) {
                continue;
            }
            $map[] = (int) $row->id;
        }

        return array_values(array_unique($map));
    }

    /**
     * Форма 2: отображение таблицы.
     */
    public function showFormTwo(Request $request, FormTwoService $formTwoService)
    {
        $groups = DB::table('first_course_group')->orderBy('group_name')->get();
        $groupId = (int) ($request->input('group_id') ?? ($groups->first()->id ?? 0));

        $month = (int) ($request->input('month') ?? now()->month);
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        $year = (int) ($request->input('year') ?? now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        $report = $groupId ? $formTwoService->buildMonthReport($groupId, $year, $month) : ['rows' => [], 'days' => []];
        $days = $report['days'] ?? range(1, Carbon::create($year, max(1, min(12, $month)), 1)->daysInMonth);
        $rows = $report['rows'] ?? [];
        $replacementRows = $report['replacement_rows'] ?? [];
        $teachers = DB::table('frist_course_teachers')->orderBy('teacher_name')->get(['id', 'teacher_name']);
        $subjects = DB::table('first_course_subjects')
            ->select('id', DB::raw('COALESCE(name_ru, subject_name) as title'))
            ->orderBy('title')
            ->get();

        return view('first_course.form_two', [
            'groups' => $groups,
            'groupId' => $groupId,
            'month' => $month,
            'year' => $year,
            'rows' => $rows,
            'days' => $days,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'replacementRows' => $replacementRows,
        ]);
    }

    /**
     * Форма 2: временное сохранение (JSON).
     */
    public function saveFormTwo(Request $request)
    {
        $payload = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'group_id' => 'required|integer',
            'data' => 'required|string',
        ]);

        $decoded = json_decode($payload['data'], true);
        if (!is_array($decoded)) {
            return back()->withErrors(['data' => 'Некорректный формат данных']);
        }

        $store = storage_path('app/form_two_state.json');
        $current = [];
        if (is_file($store)) {
            $current = json_decode(file_get_contents($store), true) ?: [];
        }

        $key = "{$payload['year']}-{$payload['month']}-{$payload['group_id']}";
        $current[$key] = $decoded;
        file_put_contents($store, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()
            ->route('first.schedule.form_two', [
                'group_id' => $payload['group_id'],
                'month' => $payload['month'],
                'year' => $payload['year'],
            ])
            ->with('success', 'Изменения сохранены (временное хранилище)');
    }
}
