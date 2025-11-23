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

        $addSub = function (&$pair, $slot, $subjectId, $teacherId, $roomId, $label) use ($subjects, $teachers) {
            $pair["sub{$slot}"] = [
                'subject' => $subjectId ? ($subjects[$subjectId] ?? '—') : null,
                'teacher' => $teacherId ? ($teachers[$teacherId] ?? '—') : null,
                'room'    => $roomId ?: null,
                'label'   => $label ?: (string)$slot,
            ];
        };

        foreach ($raw as $row) {
            $groupName = $groups[$row->group_id] ?? 'Без группы';
            $day = $row->study_day;
            $lesson = $row->lesson_number;

            if (!isset($schedule[$groupName][$day][$lesson])) {
                $schedule[$groupName][$day][$lesson] = [
                    'lesson' => $lesson,
                    'sub1' => null,
                    'sub2' => null,
                ];
            }

            // Подгруппа из строки
            $slotFromRow = $row->subgroup === '2' ? 2 : 1;
            $addSub($schedule[$groupName][$day][$lesson], $slotFromRow, $row->subject_id, $row->teacher_id, $row->room_id, $row->subgroup);

            // Данные второй подгруппы в той же строке
            if ($row->subject_id_2 || $row->teacher_id_2 || $row->room_id_2) {
                $addSub($schedule[$groupName][$day][$lesson], 2, $row->subject_id_2, $row->teacher_id_2, $row->room_id_2, '2');
            }
        }

        return view('first_course.schedule.index', [
            'schedule' => $schedule,
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
        ]);

        if ($hasSubgroups && !empty($validated['subject_id_second'])) {
            $rows[] = array_merge($base, [
                'subject_id' => $validated['subject_id_second'],
                'teacher_id' => $validated['teacher_id_second'] ?? $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id_second'] ?? $validated['room_id'] ?? null,
                'subgroup'   => '2',
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

                // Если вообще нет данных по строке — пропускаем
                if (!$subjectId && !$subjectSecond && !$teacherId && !$teacherSecond && !$roomId && !$roomSecond) {
                    continue;
                }

                $base = [
                    'study_day'     => $dayMap[$dayKey],
                    'lesson_number' => (int) $lessonNumber,
                    'group_id'      => $groupId,
                    'room_id'       => $roomId ?: null,
                    'teacher_id'    => $teacherId ?: null,
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
}
