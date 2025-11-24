<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FirstCourseSchedulePageController extends Controller
{
    /**
     * Показать расписание 1 курса.
     */
    public function index()
    {
        $weekMode = request()->get('week_mode');
        $isDenominatorWeek = match ($weekMode) {
            'den', 'denominator' => true,
            'num', 'numerator' => false,
            default => now()->isoWeek() % 2 === 0,
        };

        $subjects = DB::table('first_course_subjects')
            ->select('id', DB::raw('COALESCE(name_ru, subject_name) as title'))
            ->pluck('title', 'id');

        $teachers = DB::table('frist_course_teachers')
            ->pluck('teacher_name', 'id');

        $groups = DB::table('first_course_group')
            ->pluck('group_name', 'id');

        $raw = DB::table('first_course_schedules as s')
            ->orderBy('s.study_day')
            ->orderBy('s.lesson_number')
            ->get();

        $schedule = [];

        $addSub = function (
            &$pair,
            $slot,
            $subjectId,
            $teacherId,
            $roomId,
            $label,
            $denSubjectId = null,
            $denTeacherId = null,
            $denRoomId = null
        ) use ($subjects, $teachers, $isDenominatorWeek) {
            $pair["sub{$slot}"] = [
                'subject' => $subjectId ? ($subjects[$subjectId] ?? '—') : null,
                'subject_id' => $subjectId,
                'teacher' => $teacherId ? ($teachers[$teacherId] ?? '—') : null,
                'teacher_id' => $teacherId,
                'room'    => $roomId ?: null,
                'den_subject' => $denSubjectId ? ($subjects[$denSubjectId] ?? '—') : null,
                'den_subject_id' => $denSubjectId,
                'den_teacher' => $denTeacherId ? ($teachers[$denTeacherId] ?? '—') : null,
                'den_teacher_id' => $denTeacherId,
                'den_room'    => $denRoomId ?: null,
                'label'   => $label ?: (string)$slot,
                'active_subject' => $isDenominatorWeek && $denSubjectId ? ($subjects[$denSubjectId] ?? '—') : ($subjectId ? ($subjects[$subjectId] ?? '—') : null),
                'active_subject_id' => $isDenominatorWeek && $denSubjectId ? $denSubjectId : $subjectId,
                'active_teacher' => $isDenominatorWeek && $denTeacherId ? ($teachers[$denTeacherId] ?? '—') : ($teacherId ? ($teachers[$teacherId] ?? '—') : null),
                'active_teacher_id' => $isDenominatorWeek && $denTeacherId ? $denTeacherId : $teacherId,
                'active_room' => $isDenominatorWeek && $denRoomId ? $denRoomId : ($roomId ?: null),
                'active_label' => $isDenominatorWeek && ($denSubjectId || $denTeacherId || $denRoomId) ? 'Знаменатель' : 'Числитель',
            ];
        };

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
                ];
            }

            // Подгруппа из строки
            $slotFromRow = $row->subgroup === '2' ? 2 : 1;
            $addSub(
                $schedule[$groupId]['days'][$day][$lesson],
                $slotFromRow,
                $row->subject_id,
                $row->teacher_id,
                $row->room_id,
                $row->subgroup,
                $row->subject_id_denominator ?? null,
                $row->teacher_id_denominator ?? null,
                $row->room_id_denominator ?? null
            );

            // Данные второй подгруппы в той же строке
            if ($row->subject_id_2 || $row->teacher_id_2 || $row->room_id_2) {
                $addSub(
                    $schedule[$groupId]['days'][$day][$lesson],
                    2,
                    $row->subject_id_2,
                    $row->teacher_id_2,
                    $row->room_id_2,
                    '2'
                );
            }
        }

        return view('first_course.schedule.index', [
            'schedule' => $schedule,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'weekMode' => $isDenominatorWeek ? 'denominator' : 'numerator',
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
            'subject_id_denominator' => 'nullable|integer',
            'teacher_id_denominator' => 'nullable|integer',
            'room_id_denominator'    => 'nullable|integer',
            'subject_id_second_denominator' => 'nullable|integer',
            'teacher_id_second_denominator' => 'nullable|integer',
            'room_id_second_denominator'    => 'nullable|integer',
        ]);

        $hasSubgroups = $request->boolean('has_subgroups');

        $base = [
            'study_day'     => $validated['study_day'],
            'lesson_number' => $validated['lesson_number'],
            'group_id'      => $validated['group_id'],
            'room_id'       => $validated['room_id'] ?? null,
            'teacher_id'    => $validated['teacher_id'] ?? null,
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
        ]);

        if ($hasSubgroups && !empty($validated['subject_id_second'])) {
            $rows[] = array_merge($base, [
                'subject_id' => $validated['subject_id_second'],
                'teacher_id' => $validated['teacher_id_second'] ?? $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id_second'] ?? $validated['room_id'] ?? null,
                'subgroup'   => '2',
                'subject_id_denominator' => $validated['subject_id_second_denominator'] ?? null,
                'teacher_id_denominator' => $validated['teacher_id_second_denominator'] ?? null,
                'room_id_denominator'    => $validated['room_id_second_denominator'] ?? null,
            ]);
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
            'schedule' => 'array',
        ]);

        $groupId = $validated['group_id'];
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

                // Если вообще нет данных по строке — пропускаем
                if (
                    !$subjectId && !$subjectSecond && !$teacherId && !$teacherSecond
                    && !$roomId && !$roomSecond
                    && !$subjectDenominator && !$subjectSecondDenominator
                    && !$teacherDenominator && !$teacherSecondDenominator
                    && !$roomDenominator && !$roomSecondDenominator
                ) {
                    continue;
                }

                $base = [
                    'study_day'     => $dayMap[$dayKey],
                    'lesson_number' => (int) $lessonNumber,
                    'group_id'      => $groupId,
                    'room_id'       => $roomId ?: null,
                    'teacher_id'    => $teacherId ?: null,
                    'subject_id_denominator' => $subjectDenominator ?: null,
                    'teacher_id_denominator' => $teacherDenominator ?: null,
                    'room_id_denominator'    => $roomDenominator ?: null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $rows[] = array_merge($base, [
                    'subject_id' => $subjectId ?: null,
                    'subgroup'   => $hasSubgroups ? '1' : null,
                ]);

                if ($hasSubgroups && $subjectSecond) {
                    $rows[] = array_merge($base, [
                        'subject_id' => $subjectSecond,
                        'teacher_id' => $teacherSecond ?: $teacherId ?: null,
                        'room_id'    => $roomSecond ?: $roomId ?: null,
                        'subgroup'   => '2',
                        'subject_id_denominator' => $subjectSecondDenominator ?: null,
                        'teacher_id_denominator' => $teacherSecondDenominator ?: null,
                        'room_id_denominator'    => $roomSecondDenominator ?: null,
                    ]);
                }
            }
        }

        DB::transaction(function () use ($groupId, $rows) {
            DB::table('first_course_schedules')->where('group_id', $groupId)->delete();
            if ($rows) {
                $nextId = (int) DB::table('first_course_schedules')->max('id') + 1;
                $rowsWithIds = [];
                foreach ($rows as $r) {
                    $rowsWithIds[] = array_merge(['id' => $nextId++], $r);
                }
                DB::table('first_course_schedules')->insert($rowsWithIds);
            }
        });

        return redirect()
            ->route('first.schedule.week', ['group_id' => $groupId])
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
        ]);

        if (!in_array($data['study_day'], $dayMap, true)) {
            return response()->json(['message' => 'Некорректный день недели'], 422);
        }

        $groupId = $data['group_id'];
        $day = $data['study_day'];
        $lesson = $data['lesson_number'];
        $hasSub2 = $request->boolean('has_sub2');

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

        DB::transaction(function () use ($groupId, $day, $lesson, $data, $hasSub2) {
            DB::table('first_course_schedules')
                ->where('group_id', $groupId)
                ->where('study_day', $day)
                ->where('lesson_number', $lesson)
                ->delete();

            $now = now();

            $rows = [[
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
            ]];

            if ($hasSub2 && ($data['subject_id_2'] ?? null)) {
                $rows[] = [
                    'study_day'     => $day,
                    'lesson_number' => $lesson,
                    'group_id'      => $groupId,
                    'subject_id'    => $data['subject_id_2'] ?? null,
                    'teacher_id'    => $data['teacher_id_2'] ?? null,
                    'room_id'       => $data['room_id_2'] ?? null,
                    'subgroup'      => '2',
                    'subject_id_denominator' => $data['den_subject_id_2'] ?? null,
                    'teacher_id_denominator' => $data['den_teacher_id_2'] ?? null,
                    'room_id_denominator'    => $data['den_room_id_2'] ?? null,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            DB::table('first_course_schedules')->insert($rows);
        });

        return response()->json(['message' => 'Пара обновлена']);
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
