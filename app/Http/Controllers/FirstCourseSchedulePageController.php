<?php

namespace App\Http\Controllers;

use App\Models\FirstCourseSchedule;
use App\Services\ScheduleToFormTwoSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FirstCourseSchedulePageController extends Controller
{
    /**
     * Показать расписание 1 курса.
     */
    public function index()
    {
        $weekStartInput = request()->get('week_start');
        $weekStart = $weekStartInput ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekMode = request()->get('week_mode');
        $isDenominatorWeek = match ($weekMode) {
            'den', 'denominator' => true,
            'num', 'numerator' => false,
            default => false, // по умолчанию стартуем с числителя
        };

        $subjects = DB::table('first_course_subjects')
            ->select('id', DB::raw('COALESCE(name_ru, subject_name) as title'))
            ->pluck('title', 'id');

        $teachers = DB::table('frist_course_teachers')
            ->pluck('teacher_name', 'id');

        $groups = DB::table('first_course_group')
            ->pluck('group_name', 'id');

        $raw = DB::table('first_course_schedules as s')
            ->whereDate('s.week_start', $weekStart->toDateString())
            ->orderBy('s.study_day')
            ->orderBy('s.lesson_number')
            ->get();

        $currentMode = $isDenominatorWeek ? 'denominator' : 'numerator';
        $roomConflicts = FirstCourseSchedule::detectRoomConflicts($raw);
        $schedule = [];

        foreach ($raw as $row) {
            $groupId = $row->group_id;
            $groupName = $groups[$groupId] ?? 'Без группы';
            $day = $row->study_day;
            $lesson = $row->lesson_number;

            if (!isset($schedule[$groupId])) {
                $schedule[$groupId] = [
                    'name' => $groupName,
                    'days' => [],
                ];
            }

            if (!isset($schedule[$groupId]['days'][$day][$lesson])) {
                $schedule[$groupId]['days'][$day][$lesson] = [
                    'lesson' => $lesson,
                    'sub1' => null,
                    'sub2' => null,
                    'has_denominator' => false,
                ];
            }

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

            $den2 = $row->subject_id_denominator_2 ?? ($subgroupFlag === 2 ? ($row->subject_id_denominator ?? null) : null);
            $teacherDen2 = $row->teacher_id_denominator_2 ?? ($subgroupFlag === 2 ? ($row->teacher_id_denominator ?? null) : null);
            $roomDen2 = $row->room_id_denominator_2 ?? ($subgroupFlag === 2 ? ($row->room_id_denominator ?? null) : null);

            $confNum1 = $roomConflicts[$groupId][$day][$lesson]['numerator'][1] ?? false;
            $confNum2 = $roomConflicts[$groupId][$day][$lesson]['numerator'][2] ?? false;
            $confDen1 = $roomConflicts[$groupId][$day][$lesson]['denominator'][1] ?? false;
            $confDen2 = $roomConflicts[$groupId][$day][$lesson]['denominator'][2] ?? false;

            $schedule[$groupId]['days'][$day][$lesson]['has_denominator'] = $schedule[$groupId]['days'][$day][$lesson]['has_denominator']
                || $den1 || $den2 || $teacherDen1 || $teacherDen2 || $roomDen1 || $roomDen2;

            $sub1Data = $schedule[$groupId]['days'][$day][$lesson]['sub1'] ?? [];
            if ($subgroupFlag === 1 || $num1 || $teacherNum1 || $roomNum1 || $den1 || $teacherDen1 || $roomDen1) {
                $sub1Data = array_merge($sub1Data, [
                    'subject_num' => $num1 ? ($subjects[$num1] ?? '—') : ($sub1Data['subject_num'] ?? null),
                    'subject_num_id' => $num1 ?? ($sub1Data['subject_num_id'] ?? null),
                    'teacher_num' => $teacherNum1 ? ($teachers[$teacherNum1] ?? '—') : ($sub1Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum1 ?? ($sub1Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum1 ?? ($sub1Data['room_num'] ?? null),
                    'subject_den' => $den1 ? ($subjects[$den1] ?? '—') : ($sub1Data['subject_den'] ?? null),
                    'subject_den_id' => $den1 ?? ($sub1Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen1 ? ($teachers[$teacherDen1] ?? '—') : ($sub1Data['teacher_den'] ?? null),
                    'teacher_den_id' => $teacherDen1 ?? ($sub1Data['teacher_den_id'] ?? null),
                    'room_den' => $roomDen1 ?? ($sub1Data['room_den'] ?? null),
                    'conflict_num' => $confNum1 ?? ($sub1Data['conflict_num'] ?? false),
                    'conflict_den' => $confDen1 ?? ($sub1Data['conflict_den'] ?? false),
                    'absent_num' => $row->is_absent_1_num ?? false,
                    'absent_den' => $row->is_absent_1_den ?? false,
                    'replacement_flag_num' => $row->is_replacement_1_num ?? false,
                    'replacement_teacher_num' => $row->replacement_teacher_id_1_num ?? null,
                    'replacement_comment_num' => $row->replacement_comment_1_num ?? null,
                    'replacement_flag_den' => $row->is_replacement_1_den ?? false,
                    'replacement_teacher_den' => $row->replacement_teacher_id_1_den ?? null,
                    'replacement_comment_den' => $row->replacement_comment_1_den ?? null,
                ]);
            }
            $schedule[$groupId]['days'][$day][$lesson]['sub1'] = $sub1Data;

            $sub2Data = $schedule[$groupId]['days'][$day][$lesson]['sub2'] ?? [];
            if ($subgroupFlag === 2 || $num2 || $teacherNum2 || $roomNum2 || $den2 || $teacherDen2 || $roomDen2) {
                $sub2Data = array_merge($sub2Data, [
                    'subject_num' => $num2 ? ($subjects[$num2] ?? '—') : ($sub2Data['subject_num'] ?? null),
                    'subject_num_id' => $num2 ?? ($sub2Data['subject_num_id'] ?? null),
                    'teacher_num' => $teacherNum2 ? ($teachers[$teacherNum2] ?? '—') : ($sub2Data['teacher_num'] ?? null),
                    'teacher_num_id' => $teacherNum2 ?? ($sub2Data['teacher_num_id'] ?? null),
                    'room_num' => $roomNum2 ?? ($sub2Data['room_num'] ?? null),
                    'subject_den' => $den2 ? ($subjects[$den2] ?? '—') : ($sub2Data['subject_den'] ?? null),
                    'subject_den_id' => $den2 ?? ($sub2Data['subject_den_id'] ?? null),
                    'teacher_den' => $teacherDen2 ? ($teachers[$teacherDen2] ?? '—') : ($sub2Data['teacher_den'] ?? null),
                    'teacher_den_id' => $teacherDen2 ?? ($sub2Data['teacher_den_id'] ?? null),
                    'room_den' => $roomDen2 ?? ($sub2Data['room_den'] ?? null),
                    'conflict_num' => $confNum2 ?? ($sub2Data['conflict_num'] ?? false),
                    'conflict_den' => $confDen2 ?? ($sub2Data['conflict_den'] ?? false),
                    'absent_num' => $row->is_absent_2_num ?? false,
                    'absent_den' => $row->is_absent_2_den ?? false,
                    'replacement_flag_num' => $row->is_replacement_2_num ?? false,
                    'replacement_teacher_num' => $row->replacement_teacher_id_2_num ?? null,
                    'replacement_comment_num' => $row->replacement_comment_2_num ?? null,
                    'replacement_flag_den' => $row->is_replacement_2_den ?? false,
                    'replacement_teacher_den' => $row->replacement_teacher_id_2_den ?? null,
                    'replacement_comment_den' => $row->replacement_comment_2_den ?? null,
                ]);
            }
            $schedule[$groupId]['days'][$day][$lesson]['sub2'] = $sub2Data;
        }

        // Проставляем активные значения по чётности недели
        foreach ($schedule as $groupId => $groupData) {
            foreach ($groupData['days'] as $day => $lessons) {
                foreach ($lessons as $lesson => $pair) {
                    foreach ([1, 2] as $subIndex) {
                        $key = "sub{$subIndex}";
                        $numExists = !empty($pair[$key]['subject_num_id']) || !empty($pair[$key]['teacher_num_id']) || !empty($pair[$key]['room_num']);
                        $denExists = !empty($pair[$key]['subject_den_id']) || !empty($pair[$key]['teacher_den_id']) || !empty($pair[$key]['room_den']);
                        $activeSubject = ($isDenominatorWeek && $denExists) ? ($pair[$key]['subject_den'] ?? null) : ($pair[$key]['subject_num'] ?? null);
                        $activeTeacher = ($isDenominatorWeek && $denExists) ? ($pair[$key]['teacher_den'] ?? null) : ($pair[$key]['teacher_num'] ?? null);
                        $activeRoom = ($isDenominatorWeek && $denExists) ? ($pair[$key]['room_den'] ?? null) : ($pair[$key]['room_num'] ?? null);
                        $activeConflict = ($isDenominatorWeek && $denExists) ? ($pair[$key]['conflict_den'] ?? false) : ($pair[$key]['conflict_num'] ?? false);
                        $originalTeacherId = ($isDenominatorWeek && $denExists) ? ($pair[$key]['teacher_den_id'] ?? null) : ($pair[$key]['teacher_num_id'] ?? null);
                        $absent = ($isDenominatorWeek && $denExists) ? ($pair[$key]['absent_den'] ?? false) : ($pair[$key]['absent_num'] ?? false);
                        $replacementFlag = ($isDenominatorWeek && $denExists) ? ($pair[$key]['replacement_flag_den'] ?? false) : ($pair[$key]['replacement_flag_num'] ?? false);
                        $replacementTeacherId = ($isDenominatorWeek && $denExists) ? ($pair[$key]['replacement_teacher_den'] ?? null) : ($pair[$key]['replacement_teacher_num'] ?? null);
                        $replacementComment = ($isDenominatorWeek && $denExists) ? ($pair[$key]['replacement_comment_den'] ?? null) : ($pair[$key]['replacement_comment_num'] ?? null);
                        $replacementTeacherName = $replacementTeacherId ? ($teachers[$replacementTeacherId] ?? '—') : null;

                        if ($replacementFlag && $replacementTeacherName) {
                            $activeTeacher = $replacementTeacherName;
                        } elseif ($absent && !$replacementFlag) {
                            $activeTeacher = $activeTeacher ?: 'Учитель отсутствует';
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
                    }
                    $schedule[$groupId]['days'][$day][$lesson] = $pair;
                }
            }
        }

        return view('first_course.schedule.index', [
            'schedule' => $schedule,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'weekMode' => $isDenominatorWeek ? 'den' : 'num',
            'weekStart' => $weekStart->toDateString(),
        ]);
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

        $base = [
            'study_day'     => $validated['study_day'],
            'lesson_number' => $validated['lesson_number'],
            'group_id'      => $validated['group_id'],
            'room_id'       => $validated['room_id'] ?? null,
            'teacher_id'    => $validated['teacher_id'] ?? null,
            'subject_id_denominator_2' => null,
            'teacher_id_denominator_2' => null,
            'room_id_denominator_2'    => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        $rows = [];

        $rows[] = array_merge($base, [
            'subject_id' => $validated['subject_id'] ?? null,
            'subgroup'   => $hasSubgroups ? '1' : null,
            'subject_id_denominator' => $validated['subject_id_denominator'] ?? null,
            'teacher_id_denominator' => $validated['teacher_id_denominator'] ?? null,
            'room_id_denominator'    => $validated['room_id_denominator'] ?? null,
            'subject_id_denominator_2' => $validated['subject_id_denominator'] ?? null,
            'teacher_id_denominator_2' => $validated['teacher_id_denominator'] ?? null,
            'room_id_denominator_2'    => $validated['room_id_denominator'] ?? null,
        ]);

        if ($hasSubgroups && !empty($validated['subject_id_second'])) {
            $rows[] = array_merge($base, [
                'subject_id' => $validated['subject_id_second'],
                'teacher_id' => $validated['teacher_id_second'] ?? $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id_second'] ?? $validated['room_id'] ?? null,
                'subgroup'   => '2',
                'subject_id_denominator_2' => $validated['subject_id_second_denominator'] ?? null,
                'teacher_id_denominator_2' => $validated['teacher_id_second_denominator'] ?? null,
                'room_id_denominator_2'    => $validated['room_id_second_denominator'] ?? null,
                'subject_id_denominator' => $validated['subject_id_denominator'] ?? null,
                'teacher_id_denominator' => $validated['teacher_id_denominator'] ?? null,
                'room_id_denominator'    => $validated['room_id_denominator'] ?? null,
            ]);
        }

        try {
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

                $this->validateRoomsOrFail(
                    (int) $rowToCheck['group_id'],
                    $rowToCheck['study_day'],
                    (int) $rowToCheck['lesson_number'],
                    $slots
                );

                $this->validateTeachersOrFail(
                    (int) $rowToCheck['group_id'],
                    $rowToCheck['study_day'],
                    (int) $rowToCheck['lesson_number'],
                    $teacherSlots
                );
            }
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
        $groups = DB::table('first_course_group')->orderBy('group_name')->get();
        $subjects = DB::table('first_course_subjects')->orderBy('name_ru')->get();
        $teachers = DB::table('frist_course_teachers')->orderBy('teacher_name')->get();

        $selectedGroupId = request()->integer('group_id') ?: ($groups->first()->id ?? null);
        $weekStartInput = request()->get('week_start');
        $weekStart = $weekStartInput ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekModeParam = request()->get('week_mode');
        $weekMode = match ($weekModeParam) {
            'den', 'denominator' => 'denominator',
            default => 'numerator',
        };

        $days = [
            ['key' => 'mon', 'label' => 'Пн', 'full' => 'Понедельник'],
            ['key' => 'tue', 'label' => 'Вт', 'full' => 'Вторник'],
            ['key' => 'wed', 'label' => 'Ср', 'full' => 'Среда'],
            ['key' => 'thu', 'label' => 'Чт', 'full' => 'Четверг'],
            ['key' => 'fri', 'label' => 'Пт', 'full' => 'Пятница'],
        ];

        $pairs = [1, 2, 3, 4, 5];

        $dayNames = collect($days)->mapWithKeys(fn($d) => [$d['key'] => $d['full']]);

        $existing = [];
        if ($selectedGroupId) {
            $existingRows = DB::table('first_course_schedules')
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

        return view('first_course.schedule.week', [
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'days' => $days,
            'pairs' => $pairs,
            'selectedGroupId' => $selectedGroupId,
            'existing' => $existing,
            'weekStart' => $weekStart->toDateString(),
            'weekMode' => $weekMode,
        ]);
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
            'week_mode' => 'nullable|string|in:numerator,denominator',
            'schedule' => 'array',
        ]);

        $groupId = $validated['group_id'];
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekMode = $validated['week_mode'] ?? 'numerator';
        $schedule = $validated['schedule'] ?? [];

        $rows = [];
        $now = now();

        foreach ($schedule as $dayKey => $lessons) {
            if (!isset($dayMap[$dayKey])) {
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

                $base = [
                    'week_start'   => $weekStart->toDateString(),
                    'study_day'     => $dayMap[$dayKey],
                    'lesson_number' => (int) $lessonNumber,
                    'group_id'      => $groupId,
                    'room_id'       => $roomId ?: null,
                    'teacher_id'    => $teacherId ?: null,
                    'subject_id_denominator' => $subjectDenominator ?: null,
                    'teacher_id_denominator' => $teacherDenominator ?: null,
                    'room_id_denominator'    => $roomDenominator ?: null,
                    'subject_id_denominator_2' => $subjectDenominator2 ?: null,
                    'teacher_id_denominator_2' => $teacherDenominator2 ?: null,
                    'room_id_denominator_2'    => $roomDenominator2 ?: null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $rows[] = array_merge($base, [
                    'subject_id' => $subjectId ?: null,
                    'subgroup'   => $hasSubgroups ? '1' : null,
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
                try {
                    $this->validateRoomsOrFail($groupId, $dayMap[$dayKey], (int) $lessonNumber, $slots, $weekStart);
                    $this->validateTeachersOrFail($groupId, $dayMap[$dayKey], (int) $lessonNumber, $teacherSlots, $weekStart);
                } catch (ValidationException $e) {
                    $msg = collect($e->errors())->flatten()->first() ?: 'Недоступно в это время';
                    return back()
                        ->withErrors(['room_id' => $msg])
                        ->withInput();
                }

                if ($hasSubgroups && $subjectSecond) {
                    $hasDenominatorSecond = $subjectSecondDenominator || $teacherSecondDenominator || $roomSecondDenominator
                        || $subjectSecondDenominator2 || $teacherSecondDenominator2 || $roomSecondDenominator2;
                    $rows[] = array_merge($base, [
                    'subject_id' => $subjectSecond,
                    'teacher_id' => $teacherSecond ?: $teacherId ?: null,
                    'room_id'    => $roomSecond ?: $roomId ?: null,
                    'subgroup'   => '2',
                    'subject_id_denominator_2' => $subjectSecondDenominator ?: $subjectSecondDenominator2 ?: null,
                    'teacher_id_denominator_2' => $teacherSecondDenominator ?: $teacherSecondDenominator2 ?: null,
                    'room_id_denominator_2'    => $roomSecondDenominator ?: $roomSecondDenominator2 ?: null,
                ]);

                    $slotsSecond = [];
                    if ($roomSecond ?: $roomId) {
                        $roomForSubgroup = $roomSecond ?: $roomId;
                        $slotsSecond[] = ['room' => $roomForSubgroup, 'mode' => 'numerator'];
                        if (!$hasDenominatorSecond) {
                            $slotsSecond[] = ['room' => $roomForSubgroup, 'mode' => 'denominator'];
                        }
                    }
                    if ($roomSecondDenominator || $roomSecondDenominator2) {
                        $slotsSecond[] = ['room' => $roomSecondDenominator ?: $roomSecondDenominator2, 'mode' => 'denominator'];
                    }
                    $teacherSlotsSecond = [];
                    if ($teacherSecond ?: $teacherId) {
                        $teacherSlotsSecond[] = ['id' => $teacherSecond ?: $teacherId, 'mode' => 'numerator'];
                    }
                    if ($teacherSecondDenominator || $teacherSecondDenominator2) {
                        $teacherSlotsSecond[] = ['id' => $teacherSecondDenominator ?: $teacherSecondDenominator2, 'mode' => 'denominator'];
                    }
                    try {
                        $this->validateRoomsOrFail($groupId, $dayMap[$dayKey], (int) $lessonNumber, $slotsSecond, $weekStart);
                        $this->validateTeachersOrFail($groupId, $dayMap[$dayKey], (int) $lessonNumber, $teacherSlotsSecond, $weekStart);
                    } catch (ValidationException $e) {
                        $msg = collect($e->errors())->flatten()->first() ?: 'Недоступно в это время';
                        return back()
                            ->withErrors(['room_id' => $msg])
                            ->withInput();
                    }
                }
            }
        }

        DB::transaction(function () use ($groupId, $rows, $weekStart) {
            DB::table('first_course_schedules')
                ->where('group_id', $groupId)
                ->whereDate('week_start', $weekStart->toDateString())
                ->delete();
            if ($rows) {
                $nextId = (int) DB::table('first_course_schedules')->max('id') + 1;
                $rowsWithIds = [];
                foreach ($rows as $r) {
                    $rowsWithIds[] = array_merge(['id' => $nextId++], $r);
                }
                DB::table('first_course_schedules')->insert($rowsWithIds);
            }
        });

        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $sync->syncWeek($groupId, $weekStart, $weekMode);

        return redirect()
            ->route('first.schedule.week', ['group_id' => $groupId, 'week_start' => $weekStart->toDateString()])
            ->with('success', 'Недельное расписание сохранено.');
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
            'week_mode' => 'nullable|string|in:numerator,denominator,single',
            'is_absent_1' => 'sometimes|boolean',
            'is_absent_2' => 'sometimes|boolean',
            'is_replacement_1' => 'sometimes|boolean',
            'replacement_teacher_id_1' => 'nullable|integer',
            'replacement_comment_1' => 'nullable|string|max:255',
            'is_replacement_2' => 'sometimes|boolean',
            'replacement_teacher_id_2' => 'nullable|integer',
            'replacement_comment_2' => 'nullable|string|max:255',
        ]);

        if (!in_array($data['study_day'], $dayMap, true)) {
            return response()->json(['message' => 'Некорректный день недели'], 422);
        }

        $groupId = $data['group_id'];
        $day = $data['study_day'];
        $lesson = $data['lesson_number'];
        $hasSub2 = $request->boolean('has_sub2');
        $weekMode = $data['week_mode'] ?? 'numerator';
        if (!in_array($weekMode, ['numerator', 'denominator', 'single'], true)) {
            $weekMode = 'numerator';
        }
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);

        $existingRows = DB::table('first_course_schedules')
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
            $busy = DB::table('first_course_schedules')
                ->where('study_day', $day)
                ->where('lesson_number', $lesson)
                ->where('group_id', '<>', $groupId)
                ->whereDate('week_start', $weekStart->toDateString())
                ->where(function ($q) use ($teacherIdsToCheck) {
                    $q->whereIn('teacher_id', $teacherIdsToCheck)
                        ->orWhereIn('teacher_id_2', $teacherIdsToCheck)
                        ->orWhereIn('teacher_id_denominator', $teacherIdsToCheck);
                })
                ->exists();

            if ($busy) {
                return response()->json(['message' => 'Выбранный преподаватель занят в это время у другой группы'], 422);
            }
        }

        $teacherSlots = [];
        if (!empty($data['teacher_id'])) {
            $teacherSlots[] = ['id' => $data['teacher_id'], 'mode' => 'numerator'];
        }
        if (!empty($data['den_teacher_id'])) {
            $teacherSlots[] = ['id' => $data['den_teacher_id'], 'mode' => 'denominator'];
        }
        if ($hasSub2 && ($data['teacher_id_2'] ?? null)) {
            $teacherSlots[] = ['id' => $data['teacher_id_2'], 'mode' => 'numerator'];
        }
        if ($hasSub2 && ($data['den_teacher_id_2'] ?? null)) {
            $teacherSlots[] = ['id' => $data['den_teacher_id_2'], 'mode' => 'denominator'];
        }

        $roomSlots = [];
        $hasDenominatorMain = ($data['den_subject_id'] ?? null)
            || ($data['den_teacher_id'] ?? null)
            || ($data['den_room_id'] ?? null);

        if (!empty($data['room_id'])) {
            $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'numerator'];
            if (!$hasDenominatorMain) {
                $roomSlots[] = ['room' => $data['room_id'], 'mode' => 'denominator'];
            }
        }
        if (!empty($data['den_room_id'])) {
            $roomSlots[] = ['room' => $data['den_room_id'], 'mode' => 'denominator'];
        }

        if ($hasSub2 && ($data['subject_id_2'] ?? null)) {
            $hasDenominatorSub2 = ($data['den_subject_id_2'] ?? null)
                || ($data['den_teacher_id_2'] ?? null)
                || ($data['den_room_id_2'] ?? null);

            if (!empty($data['room_id_2'])) {
                $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'numerator'];
                if (!$hasDenominatorSub2) {
                    $roomSlots[] = ['room' => $data['room_id_2'], 'mode' => 'denominator'];
                }
            }

            if (!empty($data['den_room_id_2'])) {
                $roomSlots[] = ['room' => $data['den_room_id_2'], 'mode' => 'denominator'];
            }
        }

        try {
            $this->validateRoomsOrFail($groupId, $day, $lesson, $roomSlots, $weekStart);
            $this->validateTeachersOrFail($groupId, $day, $lesson, $teacherSlots, $weekStart);
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
            $weekStart,
            $weekMode,
            $existingRows
        ) {
            DB::table('first_course_schedules')
                ->where('group_id', $groupId)
                ->where('study_day', $day)
                ->where('lesson_number', $lesson)
                ->whereDate('week_start', $weekStart->toDateString())
                ->delete();

            $now = now();

            $absent1 = (bool)($data['is_absent_1'] ?? false);
            $absent2 = (bool)($data['is_absent_2'] ?? false);
            $isReplacement1 = (bool)($data['is_replacement_1'] ?? false);
            $isReplacement2 = (bool)($data['is_replacement_2'] ?? false);

            $prev1 = $existingRows['1'] ?? null;
            $prev2 = $existingRows['2'] ?? null;

            $rows = [[
                'week_start'   => $weekStart->toDateString(),
                'study_day'     => $day,
                'lesson_number' => $lesson,
                'group_id'      => $groupId,
                'subject_id'    => $data['subject_id'] ?? null,
                'teacher_id'    => $data['teacher_id'] ?? null,
                'room_id'       => $data['room_id'] ?? null,
                'subgroup'      => '1',
                'subject_id_denominator' => $data['den_subject_id'] ?? null,
                'teacher_id_denominator' => $data['den_teacher_id'] ?? null,
                'room_id_denominator'    => $data['den_room_id'] ?? null,
                'created_at'    => $now,
                'updated_at'    => $now,
                'is_absent_1_num' => $weekMode === 'denominator' ? ($prev1->is_absent_1_num ?? false) : $absent1,
                'is_replacement_1_num' => $weekMode === 'denominator' ? ($prev1->is_replacement_1_num ?? false) : $isReplacement1,
                'replacement_teacher_id_1_num' => $weekMode === 'denominator' ? ($prev1->replacement_teacher_id_1_num ?? null) : ($data['replacement_teacher_id_1'] ?? null),
                'replacement_comment_1_num' => $weekMode === 'denominator' ? ($prev1->replacement_comment_1_num ?? null) : ($data['replacement_comment_1'] ?? null),
                'is_absent_1_den' => $weekMode === 'denominator' ? $absent1 : ($prev1->is_absent_1_den ?? false),
                'is_replacement_1_den' => $weekMode === 'denominator' ? $isReplacement1 : ($prev1->is_replacement_1_den ?? false),
                'replacement_teacher_id_1_den' => $weekMode === 'denominator' ? ($data['replacement_teacher_id_1'] ?? null) : ($prev1->replacement_teacher_id_1_den ?? null),
                'replacement_comment_1_den' => $weekMode === 'denominator' ? ($data['replacement_comment_1'] ?? null) : ($prev1->replacement_comment_1_den ?? null),
            ]];

            if ($hasSub2 && ($data['subject_id_2'] ?? null)) {
                $rows[] = [
                    'week_start'   => $weekStart->toDateString(),
                    'study_day'     => $day,
                    'lesson_number' => $lesson,
                    'group_id'      => $groupId,
                    'subject_id'    => $data['subject_id_2'] ?? null,
                    'teacher_id'    => $data['teacher_id_2'] ?? null,
                    'room_id'       => $data['room_id_2'] ?? null,
                    'subgroup'      => '2',
                    'subject_id_denominator_2' => $data['den_subject_id_2'] ?? null,
                    'teacher_id_denominator_2' => $data['den_teacher_id_2'] ?? null,
                    'room_id_denominator_2'    => $data['den_room_id_2'] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'is_absent_2_num' => $weekMode === 'denominator' ? ($prev2->is_absent_2_num ?? false) : $absent2,
                    'is_replacement_2_num' => $weekMode === 'denominator' ? ($prev2->is_replacement_2_num ?? false) : $isReplacement2,
                    'replacement_teacher_id_2_num' => $weekMode === 'denominator' ? ($prev2->replacement_teacher_id_2_num ?? null) : ($data['replacement_teacher_id_2'] ?? null),
                    'replacement_comment_2_num' => $weekMode === 'denominator' ? ($prev2->replacement_comment_2_num ?? null) : ($data['replacement_comment_2'] ?? null),
                    'is_absent_2_den' => $weekMode === 'denominator' ? $absent2 : ($prev2->is_absent_2_den ?? false),
                    'is_replacement_2_den' => $weekMode === 'denominator' ? $isReplacement2 : ($prev2->is_replacement_2_den ?? false),
                    'replacement_teacher_id_2_den' => $weekMode === 'denominator' ? ($data['replacement_teacher_id_2'] ?? null) : ($prev2->replacement_teacher_id_2_den ?? null),
                    'replacement_comment_2_den' => $weekMode === 'denominator' ? ($data['replacement_comment_2'] ?? null) : ($prev2->replacement_comment_2_den ?? null),
                ];
            }

            DB::table('first_course_schedules')->insert($rows);
        });

        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $sync->syncWeek($groupId, $weekStart, $weekMode === 'single' ? 'numerator' : $weekMode);

        return response()->json(['message' => 'Пара обновлена']);
    }

    /**
     * Проверяем занятость кабинетов по режимам недели.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRoomsOrFail(int $groupId, string $studyDay, int $lessonNumber, array $slots, ?Carbon $weekStart = null): void
    {
        foreach ($slots as $slot) {
            $room = $slot['room'] ?? null;
            $mode = $slot['mode'] ?? 'numerator';

            if ($room === null || $room === '') {
                continue;
            }

            if (FirstCourseSchedule::roomConflictExists($groupId, $studyDay, $lessonNumber, $room, $mode, $weekStart)) {
                throw ValidationException::withMessages([
                    'room_id' => 'Кабинет занят другой группой',
                ]);
            }
        }
    }

    /**
     * Проверка занятости преподавателей по режимам недели.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateTeachersOrFail(int $groupId, string $studyDay, int $lessonNumber, array $slots, ?Carbon $weekStart = null): void
    {
        foreach ($slots as $slot) {
            $teacherId = $slot['id'] ?? null;
            if (!$teacherId) {
                continue;
            }
            $mode = $slot['mode'] ?? null;

            $busy = DB::table('first_course_schedules')
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
                ->exists();

            if ($busy) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Преподаватель занят в это время у другой группы',
                ]);
            }
        }
    }

    /**
     * Форма 2: отображение таблицы.
     */
    public function showFormTwo(Request $request)
    {
        $month = (int) ($request->input('month') ?? 9);
        if ($month < 1 || $month > 12) {
            $month = 9;
        }

        $currentYear = now()->year;
        $year = (int) ($request->input('year') ?? $currentYear);
        if ($year < 2000 || $year > 2100) {
            $year = $currentYear;
        }

        /** @var \App\Services\FormTwoExcelService $parser */
        $parser = app(\App\Services\FormTwoExcelService::class);
        $parsed = $parser->parse($month, $year);

        $groups = DB::table('first_course_group')->orderBy('group_name')->get();
        $allowedSubjects = [
            'Русский язык',
            'Русская литература',
            'Казахский язык и литература',
            'Иностранный язык',
            'Математика',
            'Информатика',
            'История Казахстана',
            'Физическая культура',
            'Начальная военная и технологическая подготовка',
            'Физика',
            'Химия',
            'Биология',
            'География',
            'Графика и проектирование',
            'Всемирная история',
        ];

        $subjects = DB::table('first_course_subjects')
            ->whereIn('subject_name', $allowedSubjects)
            ->orWhereIn('name_ru', $allowedSubjects)
            ->orderByRaw(
                'FIELD(subject_name, ' . implode(',', array_fill(0, count($allowedSubjects), '?')) . ')',
                $allowedSubjects
            )
            ->get(['id', 'subject_name', 'name_ru']);

        $teachers = DB::table('frist_course_teachers')
            ->orderBy('teacher_name')
            ->get(['id', 'teacher_name']);

        $selectedGroupId = $request->input('group_id');
        if (!$selectedGroupId && $groups->count()) {
            $selectedGroupId = $groups->first()->id;
        }

        $selectedGroupName = optional($groups->firstWhere('id', $selectedGroupId))->group_name;

        $groupData = ['subjects' => []];
        if ($selectedGroupName && isset($parsed['groups'][$selectedGroupName])) {
            $groupData = $parsed['groups'][$selectedGroupName];
        } elseif ($subjects->count()) {
            // Fallback: заполнить предметы из first_course_subjects без расписания
            $groupData['subjects'] = $subjects->map(function ($s) {
                return [
                    'subject' => $s->subject_name ?? $s->name_ru ?? '',
                    'teacher' => '—',
                    'total_hours' => 0,
                    'used_hours' => 0,
                    'hours_left' => 0,
                    'hours_per_class' => 2,
                    'days' => [],
                ];
            })->all();
        }

        $days = $parsed['days'] ?? range(1, 30);
        $replacements = $selectedGroupName && isset($parsed['replacements'][$selectedGroupName])
            ? $parsed['replacements'][$selectedGroupName]
            : [];
        $teachersSummary = $parsed['teachers'] ?? [];

        return view('first_course.form_two', [
            'groups' => $groups,
            'days' => $days,
            'selectedGroupId' => $selectedGroupId,
            'subjectRows' => $groupData['subjects'] ?? [],
            'month' => $month,
            'year' => $year,
            'currentYear' => $currentYear,
            'replacements' => $replacements,
            'teachersSummary' => $teachersSummary,
            'selectedGroupName' => $selectedGroupName,
            'teachers' => $teachers,
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
