<?php

namespace App\Http\Controllers;

use App\Models\FirstCourseSchedule;
use App\Services\KazakhstanHolidayService;
use App\Services\ScheduleToFormTwoSyncService;
use App\Services\FormTwoService;
use App\Services\SemesterScheduleService;
use App\Services\PracticeService;
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
        if (!$weekStartInput) {
            $today = Carbon::now();
            $holidayService = app(KazakhstanHolidayService::class);
            $holidayDays = $holidayService->getMonthHolidays($today->year, $today->month);
            $todayHoliday = $holidayDays[$today->day] ?? null;
            if ($this->isVacationHoliday($todayHoliday) && (int) $today->month === 1) {
                $weekStartInput = Carbon::create($today->year, 2, 2)->toDateString();
            }
        }
        $requestedWeekStart = $weekStartInput
            ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekStart = $requestedWeekStart->copy();

        /** @var ScheduleToFormTwoSyncService $syncService */
        $syncService = app(ScheduleToFormTwoSyncService::class);
        $resolvedWeekMode = $syncService->resolveWeekMode($weekStart, $course);
        $isDenominatorWeek = $resolvedWeekMode === 'denominator';

        $isFallbackWeek = false;
        $fallbackWeekStart = null;
        $fallbackMode = null;

        $subjectHasGroupType = Schema::hasColumn($tables['subjects'], 'group_type');
        $subjectsQuery = DB::table($tables['subjects'])
            ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title');
        if ($subjectHasGroupType) {
            $subjectsQuery->addSelect('group_type');
        }
        $subjects = $subjectsQuery
            ->get()
            ->mapWithKeys(function ($row) use ($subjectHasGroupType) {
                $ru = $row->name_ru ?: $row->subject_name;
                $kz = $row->name_kz ?: $ru;

                return [
                    $row->id => [
                        'ru' => $ru,
                        'kz' => $kz,
                        'module' => $row->module_title,
                        'group_type' => $subjectHasGroupType ? ($row->group_type ?? 'both') : 'both',
                    ],
                ];
            })
            ->all();

        $includeModule = $course !== 1;
        $subjectsForView = [];
        $subjectsForViewKz = [];
        $subjectGroupTypes = [];
        foreach ($subjects as $id => $entry) {
            $subjectsForView[$id] = $this->formatSubjectTitle($entry, false, $includeModule);
            $subjectsForViewKz[$id] = $this->formatSubjectTitle($entry, true, $includeModule);
            $subjectGroupTypes[$id] = $entry['group_type'] ?? 'both';
        }

        $teachers = DB::table($tables['teachers'])
            ->pluck('teacher_name', 'id');
        $teacherDisplay = DB::table($tables['teachers'])
            ->select('id', DB::raw('COALESCE(initials, teacher_name) as display_name'))
            ->pluck('display_name', 'id');

        $groupsQuery = DB::table($tables['groups'])->select('id', 'group_name');
        if (Schema::hasColumn($tables['groups'], 'group_type')) {
            $groupsQuery->addSelect('group_type');
        }
        $groupRecords = $groupsQuery->get();

        $groups = [];
        $groupLocalePreference = [];
        foreach ($groupRecords as $group) {
            $groups[$group->id] = $group->group_name;
            $groupLocalePreference[$group->id] = $this->resolveGroupIsKazakh($group);
        }

        $raw = DB::table($tables['schedules'] . ' as s')
            ->whereDate('s.week_start', $weekStart->toDateString())
            ->orderBy('s.study_day')
            ->orderBy('s.lesson_number')
            ->get();
        if ($raw->isEmpty() && $isDenominatorWeek) {
            $fallbackWeek = $weekStart->copy()->subWeek();
            $fallbackRaw = DB::table($tables['schedules'] . ' as s')
                ->whereDate('s.week_start', $fallbackWeek->toDateString())
                ->orderBy('s.study_day')
                ->orderBy('s.lesson_number')
                ->get();
            if ($fallbackRaw->isNotEmpty()) {
                $raw = $fallbackRaw;
                $fallbackWeekStart = $fallbackWeek->toDateString();
                $fallbackMode = 'denominator';
                $isFallbackWeek = true;
            }
        }

        $days = $this->buildWeekDays($weekStart);
        $weeklyHolidays = collect($days)->pluck('holiday')->filter()->values()->all();
        $holidayWeekDates = [];
        foreach ($days as $day) {
            if (!empty($day['holiday']) && !empty($day['date'])) {
                $holidayWeekDates[$day['date']] = $day['holiday'];
            }
        }

        $practiceMap = [];
        if ($course >= 2) {
            /** @var PracticeService $practiceService */
            $practiceService = app(PracticeService::class);
            $groupIds = $groupRecords->pluck('id')->all();
            $weekEnd = $weekStart->copy()->addDays(6);
            $periods = $practiceService->periodsForRange($course, $groupIds, $weekStart, $weekEnd);

            foreach ($periods as $period) {
                $rangeStart = Carbon::parse($period->start_date)->max($weekStart);
                $rangeEnd = Carbon::parse($period->end_date)->min($weekEnd);
                for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                    $dateKey = $cursor->toDateString();
                    if (!empty($holidayWeekDates[$dateKey])) {
                        continue;
                    }
                    $practiceMap[$period->group_id][$dateKey] = [
                        'type' => $period->type,
                        'teacher_id' => $period->teacher_id,
                        'room_id' => $period->room_id,
                        'hours_per_day' => $period->hours_per_day,
                    ];
                }
            }
        }

        $currentMode = $isDenominatorWeek ? 'denominator' : 'numerator';
        $roomConflicts = FirstCourseSchedule::detectRoomConflicts($raw);
        $teacherConflicts = FirstCourseSchedule::detectTeacherConflicts($raw);
        $teacherConflictsCross = $this->detectTeacherConflictsAcrossCourses($raw, $weekStart, $course, $teachers->all());
        $teacherConflicts = $this->mergeTeacherConflicts($teacherConflicts, $teacherConflictsCross);
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

        $subjectResolver = fn (?int $subjectId) => $this->resolveSubjectTitle($subjects, $subjectId, $useKazakh, $includeModule);

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
                    'teacher_num' => $teacherNum1 ? ($teacherDisplay[$teacherNum1] ?? '—') : ($sub1Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum1 ?? ($sub1Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum1 ?? ($sub1Data['room_num'] ?? null),
                    'subject_den' => $den1 ? $subjectResolver($den1) : ($sub1Data['subject_den'] ?? null),
                    'subject_den_id' => $den1 ?? ($sub1Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen1 ? ($teacherDisplay[$teacherDen1] ?? '—') : ($sub1Data['teacher_den'] ?? null),
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
                $replacementSuffix = '_2';

                $sub2Data = array_merge($sub2Data, [
                    'subject_num' => $num2 ? $subjectResolver($num2) : ($sub2Data['subject_num'] ?? null),
                    'subject_num_id' => $num2 ?? ($sub2Data['subject_num_id'] ?? null),
                    'teacher_num' => $teacherNum2 ? ($teacherDisplay[$teacherNum2] ?? '—') : ($sub2Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum2 ?? ($sub2Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum2 ?? ($sub2Data['room_num'] ?? null),
                    'subject_den' => $den2 ? $subjectResolver($den2) : ($sub2Data['subject_den'] ?? null),
                    'subject_den_id' => $den2 ?? ($sub2Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen2 ? ($teacherDisplay[$teacherDen2] ?? '—') : ($sub2Data['teacher_den'] ?? null),
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
            $subjectResolver = fn (?int $subjectId) => $this->resolveSubjectTitle($subjects, $subjectId, $useKazakh, $includeModule);
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
                        $replacementTeacherName = $replacementTeacherId ? ($teacherDisplay[$replacementTeacherId] ?? '—') : null;

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
                        if ($teacherConflictRaw && !empty($teacherConflictRaw['groups_named'])) {
                            $teacherConflictGroups = $teacherConflictRaw['groups_named'];
                        } elseif ($teacherConflictRaw && !empty($teacherConflictRaw['groups'])) {
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
                        $pair[$key]['original_teacher'] = $originalTeacherId ? ($teacherDisplay[$originalTeacherId] ?? '—') : null;
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

        // teacherConflicts is merged above and already applied in the per-pair loop.

        return view('first_course.schedule.index', [
            'schedule' => $schedule,
            'subjects' => $subjectsForView,
            'subjectsKz' => $subjectsForViewKz,
            'subjectGroupTypes' => $subjectGroupTypes,
            'teachers' => $teachers,
            'teacherDisplay' => $teacherDisplay,
            'weekMode' => $isDenominatorWeek ? 'den' : 'num',
            'weekStart' => $weekStart->toDateString(),
            'requestedWeekStart' => $requestedWeekStart->toDateString(),
            'isFallbackWeek' => $isFallbackWeek,
            'fallbackWeekStart' => $fallbackWeekStart,
            'fallbackMode' => $fallbackMode,
            'course' => $course,
            'weekDays' => $days,
            'weeklyHolidays' => $weeklyHolidays,
            'holidayWeekDates' => $holidayWeekDates ?? [],
            'practiceMap' => $practiceMap,
            'groupLocalePreference' => $groupLocalePreference,
        ]);
    }

    protected function resolveSubjectTitle(array $subjects, ?int $subjectId, bool $useKazakh, bool $includeModule): ?string
    {
        if (!$subjectId) {
            return null;
        }

        if (!isset($subjects[$subjectId])) {
            return '—';
        }

        return $this->formatSubjectTitle($subjects[$subjectId], $useKazakh, $includeModule);
    }

    protected function formatSubjectTitle(array $entry, bool $useKazakh, bool $includeModule): string
    {
        $ru = $entry['ru'] ?? null;
        $kz = $entry['kz'] ?? null;
        $name = $useKazakh ? ($kz ?: $ru) : ($ru ?: $kz);
        $module = trim((string) ($entry['module'] ?? ''));

        if ($includeModule && $module !== '') {
            return trim($module . ' ' . ($name ?: ''));
        }

        return $name ?: '—';
    }

    protected function isKazakhGroup(?string $groupName): bool
    {
        if (!$groupName) {
            return false;
        }

        return (bool) preg_match('/[ҚқӘәҢңӨөҰұҮүІіҺһҒғ]/u', $groupName);
    }

    protected function resolveGroupIsKazakh(object $group): bool
    {
        $groupType = $group->group_type ?? null;
        if ($groupType === 'ru') {
            return false;
        }
        if ($groupType === 'kz') {
            return true;
        }

        return $this->isKazakhGroup($group->group_name ?? null);
    }

    /**
     * Форма создания строки расписания.
     */
    public function create()
    {
        $groupsQuery = DB::table('groups')
            ->select('id', 'group_name', 'group_number', 'subgroup')
            ->where('year', 1);
        if (Schema::hasColumn('groups', 'group_type')) {
            $groupsQuery->addSelect('group_type');
        }
        $groups = $groupsQuery->get();
        $subjectsQuery = DB::table('first_course_subjects')
            ->select('id', 'subject_name', 'name_ru', 'name_kz');
        if (Schema::hasColumn('first_course_subjects', 'group_type')) {
            $subjectsQuery->addSelect('group_type')
                ->where('group_type', '<>', 'hidden');
        }
        $subjects = $subjectsQuery->get();
        $teachers = DB::table(CourseContext::tables(1)['teachers'])->get();
        $groupLocalePreference = [];
        foreach ($groups as $group) {
            $groupLocalePreference[$group->id] = $this->resolveGroupIsKazakh($group);
        }

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
            'groupLocalePreference' => $groupLocalePreference,
        ]);
    }

    /**
     * Сохранить новую строку расписания.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_day'     => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:7',
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
                $rowSubgroup = $rowToCheck['subgroup'] ?? '1';
                if (!empty($rowToCheck['teacher_id'])) {
                    $teacherSlots[] = [
                        'id' => $rowToCheck['teacher_id'],
                        'mode' => 'numerator',
                        'subgroup' => $rowSubgroup ?: '1',
                        'subject_id' => $rowToCheck['subject_id'] ?? null,
                    ];
                }
                $denSubject = ($rowSubgroup === '2')
                    ? ($rowToCheck['subject_id_denominator_2'] ?? null)
                    : ($rowToCheck['subject_id_denominator'] ?? null);
                if (!empty($rowToCheck['teacher_id_denominator'])) {
                    $teacherSlots[] = [
                        'id' => $rowToCheck['teacher_id_denominator'],
                        'mode' => 'denominator',
                        'subgroup' => $rowSubgroup ?: '1',
                        'subject_id' => $denSubject,
                    ];
                }
                if (!empty($rowToCheck['teacher_id_denominator_2'])) {
                    $teacherSlots[] = [
                        'id' => $rowToCheck['teacher_id_denominator_2'],
                        'mode' => 'denominator',
                        'subgroup' => $rowSubgroup ?: '1',
                        'subject_id' => $rowToCheck['subject_id_denominator_2'] ?? null,
                    ];
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
                $tablesCourseOne['subjects'] ?? null,
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

        $groupsQuery = DB::table($tables['groups'])
            ->select('id', 'group_name')
            ->orderBy('group_name');
        if (Schema::hasColumn($tables['groups'], 'group_type')) {
            $groupsQuery->addSelect('group_type');
        }
        $groups = $groupsQuery->get();
        $includeModule = $course !== 1;
        $selectedGroupId = request()->integer('group_id') ?: ($groups->first()->id ?? null);
        $selectedGroup = $selectedGroupId
            ? $groups->firstWhere('id', $selectedGroupId)
            : null;
        $useKazakh = $selectedGroup ? $this->resolveGroupIsKazakh($selectedGroup) : false;

        $subjectsQuery = DB::table($tables['subjects'])->orderBy('name_ru');
        if (Schema::hasColumn($tables['subjects'], 'group_type')) {
            $subjectsQuery->whereIn('group_type', [$useKazakh ? 'kz' : 'ru', 'both']);
        }

        $subjects = $subjectsQuery
            ->get()
            ->map(function ($row) use ($includeModule, $useKazakh) {
                $name = $useKazakh
                    ? ($row->name_kz ?: ($row->name_ru ?: $row->subject_name))
                    : ($row->name_ru ?: ($row->name_kz ?: $row->subject_name));
                $module = trim((string) ($row->module_title ?? ''));
                $row->title = ($includeModule && $module !== '') ? trim($module . ' ' . $name) : $name;
                return $row;
            });
        $teachers = DB::table($tables['teachers'])->orderBy('teacher_name')->get();
        $teacherSubjectMap = [];
        $teacherSubjectTable = $tables['teacher_subjects'] ?? null;
        if ($teacherSubjectTable && Schema::hasTable($teacherSubjectTable)) {
            $pairs = DB::table($teacherSubjectTable)
                ->select('subject_id', 'teacher_id')
                ->get();
            foreach ($pairs as $pair) {
                $subjectId = (int) $pair->subject_id;
                $teacherId = (int) $pair->teacher_id;
                $teacherSubjectMap[$subjectId][] = $teacherId;
            }
            foreach ($teacherSubjectMap as $subjectId => $teacherIds) {
                $teacherSubjectMap[$subjectId] = array_values(array_unique($teacherIds));
            }
        }

        $weekStartInput = request()->get('week_start');
        if (!$weekStartInput) {
            $today = Carbon::now();
            $holidayService = app(KazakhstanHolidayService::class);
            $holidayDays = $holidayService->getMonthHolidays($today->year, $today->month);
            $todayHoliday = $holidayDays[$today->day] ?? null;
            if ($this->isVacationHoliday($todayHoliday) && (int) $today->month === 1) {
                $weekStartInput = Carbon::create($today->year, 2, 2)->toDateString();
            }
        }
        $weekStart = $weekStartInput
            ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $days = $this->buildWeekDays($weekStart);

        $pairs = [1, 2, 3, 4, 5, 6, 7];

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

                $hasSub2Data = ($row->subject_id_2 ?? null)
                    || ($row->teacher_id_2 ?? null)
                    || ($row->room_id_2 ?? null)
                    || ($row->subject_id_denominator_2 ?? null)
                    || ($row->teacher_id_denominator_2 ?? null)
                    || ($row->room_id_denominator_2 ?? null);
                if ($hasSub2Data && !isset($existing[$key][$row->lesson_number]['2'])) {
                    $rowB = clone $row;
                    $rowB->subgroup = '2';
                    $rowB->subject_id = $row->subject_id_2 ?? null;
                    $rowB->teacher_id = $row->teacher_id_2 ?? null;
                    $rowB->room_id = $row->room_id_2 ?? null;
                    $rowB->subject_id_denominator = $row->subject_id_denominator_2 ?? null;
                    $rowB->teacher_id_denominator = $row->teacher_id_denominator_2 ?? null;
                    $rowB->room_id_denominator = $row->room_id_denominator_2 ?? null;
                    $existing[$key][$row->lesson_number]['2'] = $rowB;
                }
            }
        }

        $holidayDaysOfWeek = collect($days)->filter(fn($day) => !empty($day['holiday']))->values()->all();

        return view('first_course.schedule.week', [
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'teacherSubjectMap' => $teacherSubjectMap,
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
     * Проверка занятости преподавателей и кабинетов в реальном времени.
     */
    public function availability(Request $request)
    {
        $data = $request->validate([
            'week_start' => 'required|date',
            'day_key' => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:7',
            'mode' => 'nullable|in:numerator,denominator',
            'type' => 'required|in:teacher,room',
            'teacher_id' => 'nullable|integer',
            'room' => 'nullable|string',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        $dayMap = [
            'mon' => 'Понедельник',
            'tue' => 'Вторник',
            'wed' => 'Среда',
            'thu' => 'Четверг',
            'fri' => 'Пятница',
        ];

        $course = CourseContext::normalize($data['course'] ?? 1);
        $tables = CourseContext::tables($course);
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $dayKey = $data['day_key'] ?? '';
        $studyDay = $dayMap[$dayKey] ?? $dayKey;
        $lessonNumber = (int) $data['lesson_number'];
        $mode = ($data['mode'] ?? 'numerator') === 'denominator' ? 'denominator' : 'numerator';

        if ($data['type'] === 'room') {
            $room = $this->normalizeRoomString($data['room'] ?? null);
            if (!$room) {
                return response()->json(['status' => '', 'message' => '']);
            }

            $conflict = $this->roomBusyInTable(
                $room,
                $mode,
                $studyDay,
                $lessonNumber,
                $weekStart,
                $tables['schedules'],
                null,
                $tables['groups'] ?? null
            );

            if (!$conflict) {
                foreach ([1, 2, 3, 4] as $courseCheck) {
                    if ($courseCheck === $course) {
                        continue;
                    }
                    $courseTables = CourseContext::tables($courseCheck);
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
                        $label = $this->formatCourseGroupLabel($courseCheck, $groupName);
                        return response()->json([
                            'status' => 'busy',
                            'message' => 'Кабинет занят у ' . $label,
                        ]);
                    }
                }
            }

            if ($conflict) {
                $groupName = $conflict['group_name'] ?? 'другой группы';
                return response()->json([
                    'status' => 'busy',
                    'message' => 'Кабинет занят у группы ' . $groupName,
                ]);
            }

            return response()->json(['status' => 'free', 'message' => 'Свободно']);
        }

        $teacherId = (int) ($data['teacher_id'] ?? 0);
        if (!$teacherId) {
            return response()->json(['status' => '', 'message' => '']);
        }

        $conflict = $this->teacherBusyInTable(
            $teacherId,
            $mode,
            $studyDay,
            $lessonNumber,
            $weekStart,
            $tables['schedules'],
            $tables['groups'] ?? null
        );

        if ($conflict) {
            $subjectTitles = $this->subjectTitlesByIds([$conflict['subject_id'] ?? null], $tables['subjects'] ?? null);
            $subjectTitle = $this->subjectTitleFromMap($conflict['subject_id'] ?? null, $subjectTitles);
            $groupName = $conflict['group_name'] ?? 'другой группы';
            $subgroupLabel = $conflict['subgroup_label'] ?? 'без подгруппы';
            return response()->json([
                'status' => 'busy',
                'message' => sprintf('Преподаватель занят у группы %s (%s, %s)', $groupName, $subgroupLabel, $subjectTitle),
            ]);
        }

        $teacherNames = $this->teacherNamesByIds([$teacherId], $tables['teachers'] ?? null);
        $normalizedNames = $this->normalizeNames($teacherNames);
        if (!empty($normalizedNames)) {
            foreach ([1, 2, 3, 4] as $courseCheck) {
                if ($courseCheck === $course) {
                    continue;
                }
                $courseTables = CourseContext::tables($courseCheck);
                if (!Schema::hasTable($courseTables['schedules']) || !Schema::hasTable($courseTables['teachers'])) {
                    continue;
                }
                $matchingTeacherIds = $this->teacherIdsByNames($courseTables['teachers'], $normalizedNames);
                if (empty($matchingTeacherIds)) {
                    continue;
                }
                foreach ($matchingTeacherIds as $matchingId) {
                    $conflict = $this->teacherBusyInTable(
                        (int) $matchingId,
                        $mode,
                        $studyDay,
                        $lessonNumber,
                        $weekStart,
                        $courseTables['schedules'],
                        $courseTables['groups'] ?? null
                    );
                    if ($conflict) {
                        $subjectTitles = $this->subjectTitlesByIds([$conflict['subject_id'] ?? null], $courseTables['subjects'] ?? null);
                        $subjectTitle = $this->subjectTitleFromMap($conflict['subject_id'] ?? null, $subjectTitles);
                        $groupName = $conflict['group_name'] ?? 'другой группы';
                        $label = $this->formatCourseGroupLabel($courseCheck, $groupName);
                        $subgroupLabel = $conflict['subgroup_label'] ?? 'без подгруппы';
                        return response()->json([
                            'status' => 'busy',
                            'message' => sprintf('Преподаватель занят: %s (%s, %s)', $label, $subgroupLabel, $subjectTitle),
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'free', 'message' => 'Свободно']);
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

                $hasDenominatorMainInput = $subjectDenominator || $teacherDenominator || $roomDenominator;
                $hasDenominatorSub2Input = $subjectSecondDenominator || $teacherSecondDenominator || $roomSecondDenominator
                    || $subjectSecondDenominator2 || $teacherSecondDenominator2 || $roomSecondDenominator2;
                if ($hasSubgroups && !$hasDenominatorMainInput && $hasDenominatorSub2Input) {
                    // Если знаменатель внесли только в подгруппу 2, считаем это общим знаменателем.
                    $subjectDenominator = $subjectSecondDenominator ?: $subjectSecondDenominator2;
                    $teacherDenominator = $teacherSecondDenominator ?: $teacherSecondDenominator2;
                    $roomDenominator = $roomSecondDenominator ?: $roomSecondDenominator2;
                    $subjectSecondDenominator = null;
                    $teacherSecondDenominator = null;
                    $roomSecondDenominator = null;
                    $subjectSecondDenominator2 = null;
                    $teacherSecondDenominator2 = null;
                    $roomSecondDenominator2 = null;
                }

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
                    $teacherSlots[] = [
                        'id' => $teacherId,
                        'mode' => 'numerator',
                        'subgroup' => 1,
                        'subject_id' => $subjectId ?: null,
                    ];
                }
                if ($teacherDenominator) {
                    $teacherSlots[] = [
                        'id' => $teacherDenominator,
                        'mode' => 'denominator',
                        'subgroup' => 1,
                        'subject_id' => $subjectDenominator ?: null,
                    ];
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
                            $teacherSlotsSecond[] = [
                                'id' => $teacherSecond ?: $teacherId,
                                'mode' => 'numerator',
                                'subgroup' => 2,
                                'subject_id' => $subjectSecond ?: null,
                            ];
                        }
                        if ($teacherSecondDenominator || $teacherSecondDenominator2) {
                            $teacherSlotsSecond[] = [
                                'id' => $teacherSecondDenominator ?: $teacherSecondDenominator2,
                                'mode' => 'denominator',
                                'subgroup' => 2,
                                'subject_id' => $subjectSecondDenominator ?: $subjectSecondDenominator2 ?: null,
                            ];
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
                        $tables['subjects'],
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

        if ($hasDenominatorData && $rows) {
            $otherWeekStart = $weekMode === 'denominator'
                ? $weekStart->copy()->subWeek()
                : $weekStart->copy()->addWeek();

            DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $otherWeekStart->toDateString())
                ->delete();

            $nextId = (int) DB::table($tables['schedules'])->max('id') + 1;
            $rowsForOtherWeek = [];
            foreach ($rows as $row) {
                $rowsForOtherWeek[] = array_merge($row, [
                    'id' => $nextId++,
                    'week_start' => $otherWeekStart->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            DB::table($tables['schedules'])->insert($rowsForOtherWeek);
        }

        $sync->syncWeekWithAlternation($groupId, $weekStart, $course);

        return redirect()
            ->route('first.schedule.week', ['group_id' => $groupId, 'week_start' => $weekStart->toDateString(), 'course' => $course])
            ->with('success', 'Недельное расписание сохранено.');
    }

    protected function isVacationHoliday(?array $holiday): bool
    {
        if (!$holiday) {
            return false;
        }

        $name = (string) ($holiday['name'] ?? '');
        return $name !== '' && mb_stripos($name, 'каникул') !== false;
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
            'lesson_number' => 'required|integer|min:1|max:7',
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
            'has_denominator' => 'sometimes|boolean',
            'is_absent_1' => 'sometimes|boolean',
            'is_absent_2' => 'sometimes|boolean',
            'is_replacement_1' => 'sometimes|boolean',
            'is_replacement_2' => 'sometimes|boolean',
            'replacement_teacher_id_1' => 'nullable|integer',
            'replacement_subject_id_1' => 'nullable|integer',
            'replacement_comment_1' => 'nullable|string|max:255',
            'replacement_teacher_id_2' => 'nullable|integer',
            'replacement_subject_id_2' => 'nullable|integer',
            'replacement_comment_2' => 'nullable|string|max:255',
            'den_is_replacement_1' => 'sometimes|boolean',
            'den_is_replacement_2' => 'sometimes|boolean',
            'replacement_teacher_id_1_den' => 'nullable|integer',
            'replacement_subject_id_1_den' => 'nullable|integer',
            'replacement_comment_1_den' => 'nullable|string|max:255',
            'replacement_teacher_id_2_den' => 'nullable|integer',
            'replacement_subject_id_2_den' => 'nullable|integer',
            'replacement_comment_2_den' => 'nullable|string|max:255',
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
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $weekMode = $sync->resolveWeekMode($weekStart, $course);
        $editingDenominator = $weekMode === 'denominator' && $request->boolean('has_denominator');

        $existingRows = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->where('study_day', $day)
            ->where('lesson_number', $lesson)
            ->whereDate('week_start', $weekStart->toDateString())
            ->get()
            ->keyBy(fn ($row) => $row->subgroup ?? '1');
        $prev1 = $existingRows['1'] ?? null;
        $prev2 = $existingRows['2'] ?? null;
        $prevHasDen1 = $prev1 && (
            ($prev1->subject_id_denominator ?? null)
            || ($prev1->teacher_id_denominator ?? null)
            || ($prev1->room_id_denominator ?? null)
            || ($prev1->is_absent_1_den ?? false)
            || ($prev1->is_replacement_1_den ?? false)
            || ($prev1->replacement_teacher_id_1_den ?? null)
            || ($prev1->replacement_subject_id_1_den ?? null)
            || ($prev1->replacement_comment_1_den ?? null)
        );
        $prevHasDen2 = $prev2 && (
            ($prev2->subject_id_denominator_2 ?? null)
            || ($prev2->teacher_id_denominator_2 ?? null)
            || ($prev2->room_id_denominator_2 ?? null)
            || ($prev2->is_absent_2_den ?? false)
        );

        if ($editingDenominator) {
            if ($prev1) {
                $data['subject_id'] = $data['subject_id'] ?? $prev1->subject_id;
                $data['teacher_id'] = $data['teacher_id'] ?? $prev1->teacher_id;
                $data['room_id'] = $data['room_id'] ?? $prev1->room_id;
            }
            if ($prev2) {
                $data['subject_id_2'] = $data['subject_id_2'] ?? $prev2->subject_id;
                $data['teacher_id_2'] = $data['teacher_id_2'] ?? $prev2->teacher_id;
                $data['room_id_2'] = $data['room_id_2'] ?? $prev2->room_id;
            }
        } else {
            if ($prev1) {
                $data['den_subject_id'] = $data['den_subject_id'] ?? $prev1->subject_id_denominator;
                $data['den_teacher_id'] = $data['den_teacher_id'] ?? $prev1->teacher_id_denominator;
                $data['den_room_id'] = $data['den_room_id'] ?? $prev1->room_id_denominator;
            }
            if ($prev2) {
                $data['den_subject_id_2'] = $data['den_subject_id_2'] ?? $prev2->subject_id_denominator_2;
                $data['den_teacher_id_2'] = $data['den_teacher_id_2'] ?? $prev2->teacher_id_denominator_2;
                $data['den_room_id_2'] = $data['den_room_id_2'] ?? $prev2->room_id_denominator_2;
            }
        }

        $hasSub2 = $request->boolean('has_sub2') || (bool) $prev2;

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
        if (!$editingDenominator && $prevHasDen2) {
            $hasSub2Denominator = true;
        }
        $denSubject1 = $data['den_subject_id'] ?? null;
        $denTeacher1 = $data['den_teacher_id'] ?? null;
        $denRoom1 = $data['den_room_id'] ?? null;
        $denSubject2 = $data['den_subject_id_2'] ?? null;
        $denTeacher2 = $data['den_teacher_id_2'] ?? null;
        $denRoom2 = $data['den_room_id_2'] ?? null;

        $hasDenominatorMain = $denSubject1 || $denTeacher1 || $denRoom1;
        if ($hasSub2 && !$hasDenominatorMain && $hasSub2Denominator) {
            // Если знаменатель внесли только в подгруппу 2, считаем это общим знаменателем.
            $denSubject1 = $denSubject2;
            $denTeacher1 = $denTeacher2;
            $denRoom1 = $denRoom2;
            $denSubject2 = null;
            $denTeacher2 = null;
            $denRoom2 = null;
            $hasDenominatorMain = true;
            $hasSub2Denominator = false;
        }
        $hasSub2Data = $hasSub2Numerator || $hasSub2Denominator;
        if (!$editingDenominator && $prevHasDen1) {
            $hasDenominatorMain = true;
        }

        $teacherSlots = [];
        if (!empty($data['teacher_id'])) {
            $teacherSlots[] = [
                'id' => $data['teacher_id'],
                'mode' => 'numerator',
                'subgroup' => 1,
                'subject_id' => $data['subject_id'] ?? null,
            ];
        }
        if (!empty($denTeacher1)) {
            $teacherSlots[] = [
                'id' => $denTeacher1,
                'mode' => 'denominator',
                'subgroup' => 1,
                'subject_id' => $denSubject1,
            ];
        }
        if ($hasSub2Numerator && ($data['teacher_id_2'] ?? null)) {
            $teacherSlots[] = [
                'id' => $data['teacher_id_2'],
                'mode' => 'numerator',
                'subgroup' => 2,
                'subject_id' => $data['subject_id_2'] ?? null,
            ];
        }
        if ($hasSub2Denominator && $denTeacher2) {
            $teacherSlots[] = [
                'id' => $denTeacher2,
                'mode' => 'denominator',
                'subgroup' => 2,
                'subject_id' => $denSubject2,
            ];
        }

        $roomSlots = [];

        if (!empty($data['room_id'])) {
            $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'numerator'];
            if (!$hasDenominatorMain) {
                $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'denominator'];
            }
        }
        if (!empty($denRoom1)) {
            $roomSlots[] = ['room' => $denRoom1, 'mode' => 'denominator'];
        }

        if ($hasSub2Numerator) {
            if (!empty($data['room_id_2'])) {
                $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'numerator'];
                if (!$hasSub2Denominator) {
                    $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'denominator'];
                }
            }
        }

        if ($hasSub2Denominator && !empty($denRoom2)) {
            $roomSlots[] = ['room' => $denRoom2, 'mode' => 'denominator'];
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
                $tables['subjects'],
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
            $editingDenominator,
            $prev1,
            $prev2,
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
            $isReplacement2 = (bool)($data['is_replacement_2'] ?? false);
            $isReplacement2DenInput = (bool)($data['den_is_replacement_2'] ?? false);

            $subjectDen1 = $hasDenominatorMain ? $denSubject1 : null;
            $teacherDen1 = $hasDenominatorMain ? $denTeacher1 : null;
            $roomDen1 = $hasDenominatorMain ? $denRoom1 : null;

            $denAbsent1 = false;
            $denReplacement1 = false;
            $denReplacementTeacher1 = null;
            $denReplacementSubject1 = null;
            $denReplacementComment1 = null;
            if ($hasDenominatorMain) {
                $denAbsent1 = $editingDenominator
                    ? $absent1
                    : ($prev1?->is_absent_1_den ?? false);

                if ($editingDenominator) {
                    $denReplacement1 = $isReplacement1DenInput;
                    $denReplacementTeacher1 = $data['replacement_teacher_id_1_den'] ?? null;
                    $denReplacementSubject1 = $data['replacement_subject_id_1_den'] ?? null;
                    $denReplacementComment1 = $data['replacement_comment_1_den'] ?? null;
                } else {
                    $denReplacement1 = $prev1?->is_replacement_1_den ?? false;
                    $denReplacementTeacher1 = $prev1?->replacement_teacher_id_1_den ?? null;
                    $denReplacementSubject1 = $prev1?->replacement_subject_id_1_den ?? null;
                    $denReplacementComment1 = $prev1?->replacement_comment_1_den ?? null;
                }
            }

            $denReplacement2 = false;
            $denReplacementTeacher2 = null;
            $denReplacementSubject2 = null;
            $denReplacementComment2 = null;
            if ($hasSub2Denominator) {
                if ($editingDenominator) {
                    $denReplacement2 = $isReplacement2DenInput;
                    $denReplacementTeacher2 = $data['replacement_teacher_id_2_den'] ?? null;
                    $denReplacementSubject2 = $data['replacement_subject_id_2_den'] ?? null;
                    $denReplacementComment2 = $data['replacement_comment_2_den'] ?? null;
                } else {
                    $denReplacement2 = $prev2?->is_replacement_2_den ?? false;
                    $denReplacementTeacher2 = $prev2?->replacement_teacher_id_2_den ?? null;
                    $denReplacementSubject2 = $prev2?->replacement_subject_id_2_den ?? null;
                    $denReplacementComment2 = $prev2?->replacement_comment_2_den ?? null;
                }
            }

            $subjectId2 = $data['subject_id_2'] ?? null;
            $teacherId2 = $data['teacher_id_2'] ?? null;
            $roomId2 = $data['room_id_2'] ?? null;

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
                'is_absent_1_num' => $editingDenominator ? ($prev1?->is_absent_1_num ?? false) : $absent1,
                'is_replacement_1_num' => $editingDenominator ? ($prev1?->is_replacement_1_num ?? false) : $isReplacement1,
                'replacement_teacher_id_1_num' => $editingDenominator ? ($prev1?->replacement_teacher_id_1_num ?? null) : ($data['replacement_teacher_id_1'] ?? null),
                'replacement_subject_id_1_num' => $editingDenominator ? ($prev1?->replacement_subject_id_1_num ?? null) : ($data['replacement_subject_id_1'] ?? null),
                'replacement_comment_1_num' => $editingDenominator ? ($prev1?->replacement_comment_1_num ?? null) : ($data['replacement_comment_1'] ?? null),
                'is_absent_1_den' => $denAbsent1,
                'is_replacement_1_den' => $denReplacement1,
                'replacement_teacher_id_1_den' => $denReplacementTeacher1,
                'replacement_subject_id_1_den' => $denReplacementSubject1,
                'replacement_comment_1_den' => $denReplacementComment1,
            ]];

            if ($hasSub2Data) {
                $subjectDen2 = $hasSub2Denominator ? $denSubject2 : null;
                $teacherDen2 = $hasSub2Denominator ? $denTeacher2 : null;
                $roomDen2 = $hasSub2Denominator ? $denRoom2 : null;

                $denAbsent2 = false;
                if ($hasSub2Denominator) {
                    $denAbsent2 = $editingDenominator
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
                    'is_absent_2_num' => $editingDenominator ? ($prev2?->is_absent_2_num ?? false) : $absent2,
                    'is_replacement_2_num' => $editingDenominator ? ($prev2?->is_replacement_2_num ?? false) : $isReplacement2,
                    'replacement_teacher_id_2_num' => $editingDenominator ? ($prev2?->replacement_teacher_id_2_num ?? null) : ($data['replacement_teacher_id_2'] ?? null),
                    'replacement_subject_id_2_num' => $editingDenominator ? ($prev2?->replacement_subject_id_2_num ?? null) : ($data['replacement_subject_id_2'] ?? null),
                    'replacement_comment_2_num' => $editingDenominator ? ($prev2?->replacement_comment_2_num ?? null) : ($data['replacement_comment_2'] ?? null),
                    'is_absent_2_den' => $denAbsent2,
                    'is_replacement_2_den' => $denReplacement2,
                    'replacement_teacher_id_2_den' => $denReplacementTeacher2,
                    'replacement_subject_id_2_den' => $denReplacementSubject2,
                    'replacement_comment_2_den' => $denReplacementComment2,
                ];
            }

            foreach ($rows as $row) {
                DB::table($tables['schedules'])->insert($row);
            }
        });

        $sync->syncWeek($groupId, $weekStart, $weekStart, null, $course);

        return response()->json(['message' => 'Пара обновлена']);
    }

    /**
     * Удалить пару из расписания (и синхронизировать Form 2).
     */
    public function deletePair(Request $request)
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
            'group_id' => 'required|integer',
            'study_day' => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:7',
            'week_start' => 'required|date',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        if (!in_array($data['study_day'], $dayMap, true)) {
            return response()->json(['message' => 'Некорректный день недели'], 422);
        }

        $course = CourseContext::normalize($data['course'] ?? request()->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groupId = (int) $data['group_id'];
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);

        DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->where('study_day', $data['study_day'])
            ->where('lesson_number', $data['lesson_number'])
            ->whereDate('week_start', $weekStart->toDateString())
            ->delete();

        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $sync->syncWeekWithAlternation($groupId, $weekStart, $course);

        return response()->json(['message' => 'Пара удалена']);
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

    protected function teacherBusyInTable(
        int $teacherId,
        string $mode,
        string $studyDay,
        int $lessonNumber,
        ?Carbon $weekStart,
        string $table,
        ?string $groupTable = null
    ): ?array {
        if (!Schema::hasTable($table)) {
            return null;
        }

        $rows = DB::table($table)
            ->where('study_day', $studyDay)
            ->where('lesson_number', $lessonNumber)
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

            $teacherNum1 = $subgroupFlag === '1' ? ($row->teacher_id ?? null) : null;
            $teacherNum2 = ($row->teacher_id_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->teacher_id ?? null) : null);
            $teacherDen1 = $subgroupFlag === '1' ? ($row->teacher_id_denominator ?? null) : null;
            $teacherDen2 = ($row->teacher_id_denominator_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->teacher_id_denominator ?? null) : null);

            $rowSlots = [];
            foreach ($modes as $m) {
                $rowSlots[] = ['teacher' => $teacherNum1, 'mode' => $m];
                $rowSlots[] = ['teacher' => $teacherNum2, 'mode' => $m];
            }
            $rowSlots[] = ['teacher' => $teacherDen1, 'mode' => 'denominator'];
            $rowSlots[] = ['teacher' => $teacherDen2, 'mode' => 'denominator'];

            foreach ($rowSlots as $rowSlot) {
                $rowTeacher = (int) ($rowSlot['teacher'] ?? 0);
                if (!$rowTeacher) {
                    continue;
                }
                if ($rowTeacher === $teacherId && $rowSlot['mode'] === $mode) {
                    $groupId = (int) ($row->group_id ?? 0);
                    return [
                        'group_id' => $groupId,
                        'group_name' => $this->groupNameById($groupId, $groupTable),
                        'subject_id' => $this->resolveSubjectIdForTeacherRow($row, $teacherId),
                        'subgroup_label' => $this->resolveSubgroupLabelForTeacherRow($row, $teacherId),
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
        ?string $subjectTable = null,
        int $currentCourse = 1
    ): void
    {
        if (empty($slots)) {
            return;
        }

        $courseTables = CourseContext::tables($currentCourse);
        $groupTable = $courseTables['groups'] ?? null;

        $teacherIds = collect($slots)->pluck('id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
        $subjectIds = collect($slots)->pluck('subject_id')->filter()->map(fn($id) => (int) $id)->unique()->values()->all();

        $teacherNameById = [];
        if (!empty($teacherIds) && $teacherTable && Schema::hasTable($teacherTable)) {
            $teacherNameById = DB::table($teacherTable)
                ->whereIn('id', $teacherIds)
                ->pluck('teacher_name', 'id')
                ->all();
        }
        $subjectTitleById = $this->subjectTitlesByIds($subjectIds, $subjectTable);

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
            if (isset($occupiedByMode[$mode][$teacherId])) {
                $teacherName = $teacherNameById[$teacherId] ?? 'Преподаватель';
                $groupName = $this->groupNameById($groupId, $groupTable) ?? 'группы';
                $existing = $occupiedByMode[$mode][$teacherId];
                $subjectTitle = $this->subjectTitleFromMap($existing['subject_id'] ?? null, $subjectTitleById);
                $subgroup = $existing['subgroup'] ?? null;
                $subgroupLabel = $subgroup ? ('подгр. ' . $subgroup) : 'без подгруппы';
                throw ValidationException::withMessages([
                    'teacher_id' => sprintf(
                        '%s уже назначен на %s у группы %s (%s, %s)',
                        $teacherName,
                        $pairLabel,
                        $groupName,
                        $subgroupLabel,
                        $subjectTitle
                    ),
                ]);
            }
            $occupiedByMode[$mode][$teacherId] = [
                'subgroup' => $slot['subgroup'] ?? null,
                'subject_id' => $slot['subject_id'] ?? null,
            ];
        }

        foreach ($slots as $slot) {
            $teacherId = $slot['id'] ?? null;
            if (!$teacherId) {
                continue;
            }
            $mode = $slot['mode'] ?? null;

            $conflict = DB::table($table ?? 'first_course_schedules')
                ->select(
                    'group_id',
                    'teacher_id',
                    'teacher_id_2',
                    'teacher_id_denominator',
                    'teacher_id_denominator_2',
                    'subject_id',
                    'subject_id_2',
                    'subject_id_denominator',
                    'subject_id_denominator_2'
                )
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
                $subjectId = $this->resolveSubjectIdForTeacherRow($conflict, (int) $teacherId);
                $subjectTitle = $this->subjectTitleFromMap($subjectId, $subjectTitleById);
                $subgroupLabel = $this->resolveSubgroupLabelForTeacherRow($conflict, (int) $teacherId);
                throw ValidationException::withMessages([
                    'teacher_id' => sprintf(
                        'Преподаватель занят на %s у группы %s (%s, %s)',
                        $pairLabel,
                        $groupName,
                        $subgroupLabel,
                        $subjectTitle
                    ),
                ]);
            }
        }

        if (!$teacherTable) {
            return;
        }

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
                ->select(
                    'group_id',
                    'teacher_id',
                    'teacher_id_2',
                    'teacher_id_denominator',
                    'teacher_id_denominator_2',
                    'subject_id',
                    'subject_id_2',
                    'subject_id_denominator',
                    'subject_id_denominator_2'
                )
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
                $subjectTitlesOther = $this->subjectTitlesByIds(
                    [
                        $conflict->subject_id ?? null,
                        $conflict->subject_id_2 ?? null,
                        $conflict->subject_id_denominator ?? null,
                        $conflict->subject_id_denominator_2 ?? null,
                    ],
                    $courseTables['subjects'] ?? null
                );
                $matchTeacherId = $this->resolveTeacherIdForRowMatch($conflict, $matchingTeacherIds);
                $subjectId = $this->resolveSubjectIdForTeacherRow($conflict, $matchTeacherId);
                $subjectTitle = $this->subjectTitleFromMap($subjectId, $subjectTitlesOther);
                $subgroupLabel = $this->resolveSubgroupLabelForTeacherRow($conflict, $matchTeacherId);
                throw ValidationException::withMessages([
                    'teacher_id' => sprintf(
                        'Преподаватель занят на %s на другом курсе (группа %s, %s, %s)',
                        $pairLabel,
                        $groupName,
                        $subgroupLabel,
                        $subjectTitle
                    ),
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

    protected function mergeTeacherConflicts(array $base, array $extra): array
    {
        foreach ($extra as $groupId => $days) {
            foreach ($days as $day => $lessons) {
                foreach ($lessons as $lesson => $modes) {
                    foreach ($modes as $mode => $subgroups) {
                        foreach ($subgroups as $subgroup => $payload) {
                            $existing = $base[$groupId][$day][$lesson][$mode][$subgroup] ?? [];
                            $groupsNamed = $payload['groups_named'] ?? [];
                            if (!empty($groupsNamed)) {
                                $existingGroups = $existing['groups_named'] ?? [];
                                $existing['groups_named'] = array_values(array_unique(array_merge($existingGroups, $groupsNamed)));
                            }
                            $base[$groupId][$day][$lesson][$mode][$subgroup] = $existing ?: $payload;
                        }
                    }
                }
            }
        }

        return $base;
    }

    protected function detectTeacherConflictsAcrossCourses(
        \Illuminate\Support\Collection $rows,
        Carbon $weekStart,
        int $currentCourse,
        array $currentTeachers
    ): array {
        if ($rows->isEmpty()) {
            return [];
        }

        $normalize = fn (?string $name): string => mb_strtolower(trim((string) ($name ?? '')));
        $teacherNameById = [];
        foreach ($currentTeachers as $id => $name) {
            $normalized = $normalize($name);
            if ($normalized !== '') {
                $teacherNameById[(int) $id] = $normalized;
            }
        }

        $otherSlots = [];
        foreach ([1, 2, 3, 4] as $course) {
            if ($course === $currentCourse) {
                continue;
            }
            $tables = CourseContext::tables($course);
            if (
                !Schema::hasTable($tables['schedules'])
                || !Schema::hasTable($tables['teachers'])
                || !Schema::hasTable($tables['groups'])
            ) {
                continue;
            }

            $teacherRows = DB::table($tables['teachers'])->select('id', 'teacher_name')->get();
            $teacherNames = [];
            foreach ($teacherRows as $row) {
                $normalized = $normalize($row->teacher_name ?? null);
                if ($normalized !== '') {
                    $teacherNames[(int) $row->id] = $normalized;
                }
            }
            if (empty($teacherNames)) {
                continue;
            }

            $groupNames = DB::table($tables['groups'])->pluck('group_name', 'id')->all();
            $courseRows = DB::table($tables['schedules'])
                ->whereDate('week_start', $weekStart->toDateString())
                ->get();

            foreach ($courseRows as $row) {
                foreach ($this->teacherSlotsForRow($row) as $slot) {
                    $teacherName = $teacherNames[$slot['teacher_id']] ?? null;
                    if (!$teacherName) {
                        continue;
                    }
                    $key = $this->teacherConflictKey($teacherName, $slot['day'], $slot['lesson'], $slot['mode']);
                    $groupName = $groupNames[$slot['group_id']] ?? ('Группа ' . (int) $slot['group_id']);
                    $label = $this->formatCourseGroupLabel($course, $groupName);
                    $otherSlots[$key][$label] = true;
                }
            }
        }

        if (empty($otherSlots)) {
            return [];
        }

        $conflicts = [];
        foreach ($rows as $row) {
            foreach ($this->teacherSlotsForRow($row) as $slot) {
                $teacherName = $teacherNameById[$slot['teacher_id']] ?? null;
                if (!$teacherName) {
                    continue;
                }
                $key = $this->teacherConflictKey($teacherName, $slot['day'], $slot['lesson'], $slot['mode']);
                if (empty($otherSlots[$key])) {
                    continue;
                }
                $conflicts[$slot['group_id']]
                    [$slot['day']]
                    [$slot['lesson']]
                    [$slot['mode']]
                    [$slot['subgroup']] = [
                        'groups_named' => array_keys($otherSlots[$key]),
                    ];
            }
        }

        return $conflicts;
    }

    protected function teacherSlotsForRow(object $row): array
    {
        $day = $row->study_day ?? null;
        $lesson = $row->lesson_number ?? null;
        $groupId = $row->group_id ?? null;
        if (!$day || !$lesson || !$groupId) {
            return [];
        }

        $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? '2' : '1';
        $teacherNum1 = $subgroupFlag === '1' ? ($row->teacher_id ?? null) : null;
        $teacherNum2 = ($row->teacher_id_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->teacher_id ?? null) : null);
        $teacherDen1 = $subgroupFlag === '1' ? ($row->teacher_id_denominator ?? null) : null;
        $teacherDen2 = ($row->teacher_id_denominator_2 ?? null) ?: ($subgroupFlag === '2' ? ($row->teacher_id_denominator ?? null) : null);

        $result = [];
        $append = function ($teacherId, string $mode, int $subgroup) use (&$result, $groupId, $day, $lesson) {
            if (!$teacherId) {
                return;
            }
            $result[] = [
                'group_id' => (int) $groupId,
                'day' => (string) $day,
                'lesson' => (int) $lesson,
                'mode' => $mode,
                'subgroup' => $subgroup,
                'teacher_id' => (int) $teacherId,
            ];
        };

        $append($teacherNum1, 'numerator', 1);
        $append($teacherNum2, 'numerator', 2);
        $append($teacherDen1, 'denominator', 1);
        $append($teacherDen2, 'denominator', 2);

        return $result;
    }

    protected function teacherConflictKey(string $teacherName, string $day, int $lesson, string $mode): string
    {
        return implode('|', [$teacherName, $day, $lesson, $mode]);
    }

    protected function formatCourseGroupLabel(int $course, string $groupName): string
    {
        return $course . ' курс: ' . $groupName;
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

    protected function subjectTitlesByIds(array $subjectIds, ?string $subjectTable): array
    {
        if (empty($subjectIds) || !$subjectTable || !Schema::hasTable($subjectTable)) {
            return [];
        }

        $ids = collect($subjectIds)->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table($subjectTable)
            ->whereIn('id', $ids)
            ->get(['id', 'subject_name', 'name_ru', 'name_kz']);

        $map = [];
        foreach ($rows as $row) {
            $title = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
            if ($title !== null && $title !== '') {
                $map[(int) $row->id] = $title;
            }
        }

        return $map;
    }

    protected function subjectTitleFromMap(?int $subjectId, array $subjectTitles): string
    {
        if (!$subjectId) {
            return 'предмет не указан';
        }

        return $subjectTitles[$subjectId] ?? 'предмет не указан';
    }

    protected function resolveSubjectIdForTeacherRow(object $row, ?int $teacherId): ?int
    {
        if (!$teacherId) {
            return null;
        }
        if (($row->teacher_id ?? null) == $teacherId) {
            return $row->subject_id ?? null;
        }
        if (($row->teacher_id_2 ?? null) == $teacherId) {
            return $row->subject_id_2 ?? null;
        }
        if (($row->teacher_id_denominator ?? null) == $teacherId) {
            return $row->subject_id_denominator ?? null;
        }
        if (($row->teacher_id_denominator_2 ?? null) == $teacherId) {
            return $row->subject_id_denominator_2 ?? null;
        }

        return null;
    }

    protected function resolveSubgroupLabelForTeacherRow(object $row, ?int $teacherId): string
    {
        if (!$teacherId) {
            return 'без подгруппы';
        }
        if (($row->teacher_id_2 ?? null) == $teacherId || ($row->teacher_id_denominator_2 ?? null) == $teacherId) {
            return 'подгр. 2';
        }
        return 'подгр. 1';
    }

    protected function resolveTeacherIdForRowMatch(object $row, array $matchingTeacherIds): ?int
    {
        foreach (['teacher_id', 'teacher_id_2', 'teacher_id_denominator', 'teacher_id_denominator_2'] as $field) {
            $value = (int) ($row->{$field} ?? 0);
            if ($value && in_array($value, $matchingTeacherIds, true)) {
                return $value;
            }
        }

        return null;
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
        $teachers = DB::table(CourseContext::tables(1)['teachers'])
            ->orderBy('teacher_name')
            ->get(['id', 'teacher_name']);
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
