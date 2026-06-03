<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SchedulePlanningService
{
    private array $coursePrefixes = [
        1 => 'first_course',
        2 => 'second_course',
        3 => 'third_course',
        4 => 'fourth_course',
    ];

    public function getScheduleData(int $course, ?string $weekStart = null): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $tableSchedules = "{$prefix}_course_schedules";
        $tableGroups = "{$prefix}_course_group";
        $tableSubjects = "{$prefix}_course_subjects";
        $tableTeacherSubjects = "{$prefix}_course_teacher_subjects";

        if (!DB::getSchemaBuilder()->hasTable($tableSchedules)) {
            return ['error' => 'Таблица расписания не найдена для курса ' . $course];
        }

        $schedules = DB::table($tableSchedules)
            ->when($weekStart, fn($q) => $q->where('week_start', $weekStart))
            ->get()
            ->toArray();

        $groups = DB::table($tableGroups)->get()->keyBy('id')->toArray();
        $subjects = DB::table($tableSubjects)->get()->keyBy('id')->toArray();
        $teachers = DB::table('teachers')->get()->keyBy('id')->toArray();
        $rooms = DB::table('rooms')->where('is_active', true)->get()->keyBy('id')->toArray();

        return [
            'schedules' => $schedules,
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'teacher_subjects' => DB::table($tableTeacherSubjects)->get()->toArray(),
        ];
    }

    public function analyzeConflicts(int $course, string $weekStart): array
    {
        $data = $this->getScheduleData($course, $weekStart);
        
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        $conflicts = [
            'room_conflicts' => [],
            'teacher_conflicts' => [],
            'normative_violations' => [],
        ];

        $roomUsage = [];
        $teacherUsage = [];

        foreach ($data['schedules'] as $schedule) {
            $day = $schedule->study_day;
            $lesson = $schedule->lesson_number;
            $key = "{$day}-{$lesson}";

            foreach (['room_id', 'room_id_denominator', 'room_id_denominator_2'] as $roomField) {
                $roomId = $schedule->$roomField;
                if (!$roomId) continue;

                $roomKey = "{$roomId}-{$key}";
                if (isset($roomUsage[$roomKey])) {
                    $conflicts['room_conflicts'][] = [
                        'day' => $day,
                        'lesson' => $lesson,
                        'room_id' => $roomId,
                        'room_code' => $data['rooms'][$roomId]->code ?? '?',
                        'schedules' => [$roomUsage[$roomKey], $schedule->id],
                    ];
                }
                $roomUsage[$roomKey] = $schedule->id;
            }

            foreach (['teacher_id', 'teacher_id_denominator', 'teacher_id_denominator_2'] as $teacherField) {
                $teacherId = $schedule->$teacherField;
                if (!$teacherId) continue;

                $teacherKey = "{$teacherId}-{$key}";
                if (isset($teacherUsage[$teacherKey])) {
                    $conflicts['teacher_conflicts'][] = [
                        'day' => $day,
                        'lesson' => $lesson,
                        'teacher_id' => $teacherId,
                        'teacher_name' => $data['teachers'][$teacherId]->teacher_name ?? '?',
                        'schedules' => [$teacherUsage[$teacherKey], $schedule->id],
                    ];
                }
                $teacherUsage[$teacherKey] = $schedule->id;
            }
        }

        return $conflicts;
    }

    public function findReplacements(int $course, int $teacherId, int $day, int $lesson): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $tableTeacherSubjects = "{$prefix}_course_teacher_subjects";

        $teacherSubjects = DB::table($tableTeacherSubjects)
            ->where('teacher_id', $teacherId)
            ->pluck('subject_id')
            ->toArray();

        if (empty($teacherSubjects)) {
            return ['error' => 'Не найдены предметы для преподавателя'];
        }

        $scheduleTable = "{$prefix}_course_schedules";
        $subjects = DB::table("{$prefix}_course_subjects")->get()->keyBy('id');
        $teachers = DB::table('teachers')->get()->keyBy('id');

        $occupiedTeachers = DB::table($scheduleTable)
            ->where('study_day', $day)
            ->where('lesson_number', $lesson)
            ->where(function ($q) {
                $q->whereNotNull('teacher_id')
                    ->orWhereNotNull('teacher_id_denominator')
                    ->orWhereNotNull('teacher_id_denominator_2');
            })
            ->get()
            ->flatMap(function ($row) {
                return array_filter([
                    $row->teacher_id,
                    $row->teacher_id_denominator,
                    $row->teacher_id_denominator_2
                ]);
            })
            ->unique()
            ->toArray();

        $potentialReplacements = DB::table($tableTeacherSubjects)
            ->whereIn('subject_id', $teacherSubjects)
            ->whereNotIn('teacher_id', $occupiedTeachers)
            ->where('teacher_id', '!=', $teacherId)
            ->get()
            ->groupBy('teacher_id');

        $result = [];
        foreach ($potentialReplacements as $teacherIdKey => $assignments) {
            $teacher = $teachers[$teacherIdKey] ?? null;
            if (!$teacher) continue;

            $subjectNames = $assignments->map(fn($a) => $subjects[$a->subject_id]->subject_name ?? '?')->unique()->values()->toArray();

            $maxHours = DB::table("{$prefix}_course_schedules")
                ->whereIn('teacher_id', [$teacherIdKey, $teacherIdKey])
                ->count() * 1.5;

            $currentHours = DB::table("{$prefix}_course_schedules")
                ->where(function ($q) use ($teacherIdKey) {
                    $q->where('teacher_id', $teacherIdKey)
                        ->orWhere('teacher_id_denominator', $teacherIdKey)
                        ->orWhere('teacher_id_denominator_2', $teacherIdKey);
                })
                ->count();

            $result[] = [
                'teacher_id' => $teacherIdKey,
                'teacher_name' => $teacher->teacher_name,
                'initials' => $teacher->initials,
                'subjects' => $subjectNames,
                'current_hours' => $currentHours,
                'availability' => $maxHours > $currentHours ? 'свободен' : 'занят частично',
            ];
        }

        usort($result, fn($a, $b) => ($b['availability'] === 'свободен') - ($a['availability'] === 'свободен'));

        return $result;
    }

    public function suggestSchedulePlacement(int $course, int $groupId, int $subjectId, ?int $teacherId = null): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $scheduleTable = "{$prefix}_course_schedules";

        $existingSchedule = DB::table($scheduleTable)
            ->where('group_id', $groupId)
            ->get();

        $busySlots = [];
        foreach ($existingSchedule as $row) {
            $busySlots[] = [
                'day' => $row->study_day,
                'lesson' => $row->lesson_number,
            ];
        }

        $occupiedRooms = [];
        foreach ($existingSchedule as $row) {
            foreach (['room_id', 'room_id_denominator'] as $roomField) {
                if ($row->$roomField) {
                    $key = "{$row->$roomField}-{$row->study_day}-{$row->lesson_number}";
                    $occupiedRooms[$key] = $row->$roomField;
                }
            }
        }

        $rooms = DB::table('rooms')->where('is_active', true)->get()->keyBy('id');
        $availableRooms = [];
        for ($day = 1; $day <= 6; $day++) {
            for ($lesson = 1; $lesson <= 6; $lesson++) {
                foreach ($rooms as $roomId => $room) {
                    $key = "{$roomId}-{$day}-{$lesson}";
                    if (!isset($occupiedRooms[$key])) {
                        $slotKey = "{$day}-{$lesson}";
                        if (!isset($availableRooms[$slotKey])) {
                            $availableRooms[$slotKey] = [];
                        }
                        $availableRooms[$slotKey][] = [
                            'id' => $roomId,
                            'code' => $room->code,
                            'type' => $room->type,
                        ];
                    }
                }
            }
        }

        $freeSlots = [];
        for ($day = 1; $day <= 6; $day++) {
            for ($lesson = 1; $lesson <= 6; $lesson++) {
                $slotKey = "{$day}-{$lesson}";
                $isBusy = false;
                foreach ($busySlots as $busy) {
                    if ($busy['day'] === $day && $busy['lesson'] === $lesson) {
                        $isBusy = true;
                        break;
                    }
                }
                if (!$isBusy) {
                    $freeSlots[] = [
                        'day' => $day,
                        'lesson' => $lesson,
                        'available_rooms' => $availableRooms[$slotKey] ?? [],
                    ];
                }
            }
        }

        if ($teacherId) {
            $teacherSchedule = DB::table($scheduleTable)
                ->where('teacher_id', $teacherId)
                ->orWhere('teacher_id_denominator', $teacherId)
                ->orWhere('teacher_id_denominator_2', $teacherId)
                ->get();

            $teacherBusy = [];
            foreach ($teacherSchedule as $row) {
                $teacherBusy[] = [
                    'day' => $row->study_day,
                    'lesson' => $row->lesson_number,
                ];
            }

            foreach ($freeSlots as $idx => $slot) {
                foreach ($teacherBusy as $busy) {
                    if ($busy['day'] === $slot['day'] && $busy['lesson'] === $slot['lesson']) {
                        $freeSlots[$idx]['teacher_conflict'] = true;
                        break;
                    }
                }
            }
        }

        return $freeSlots;
    }

    public function getAvailableTeachers(int $course, int $day, int $lesson, ?int $subjectId = null): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $scheduleTable = "{$prefix}_course_schedules";
        $teacherSubjectsTable = "{$prefix}_course_teacher_subjects";

        $occupiedTeacherIds = DB::table($scheduleTable)
            ->where('study_day', $day)
            ->where('lesson_number', $lesson)
            ->get()
            ->flatMap(fn($row) => array_filter([
                $row->teacher_id,
                $row->teacher_id_denominator,
                $row->teacher_id_denominator_2
            ]))
            ->unique()
            ->toArray();

        $query = DB::table('teachers');

        if (!empty($occupiedTeacherIds)) {
            $query->whereNotIn('id', $occupiedTeacherIds);
        }

        if ($subjectId) {
            $teacherIdsForSubject = DB::table($teacherSubjectsTable)
                ->where('subject_id', $subjectId)
                ->pluck('teacher_id')
                ->toArray();

            if (!empty($teacherIdsForSubject)) {
                $query->whereIn('id', $teacherIdsForSubject);
            }
        }

        return $query->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->teacher_name,
            'initials' => $t->initials,
        ])->toArray();
    }

    public function getAvailableRooms(int $course, int $day, int $lesson): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $scheduleTable = "{$prefix}_course_schedules";

        $occupiedRoomIds = DB::table($scheduleTable)
            ->where('study_day', $day)
            ->where('lesson_number', $lesson)
            ->get()
            ->flatMap(fn($row) => array_filter([
                $row->room_id,
                $row->room_id_denominator,
                $row->room_id_denominator_2,
                $row->room_id_2
            ]))
            ->unique()
            ->toArray();

        return DB::table('rooms')
            ->where('is_active', true)
            ->whereNotIn('id', $occupiedRoomIds)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'title' => $r->title,
                'type' => $r->type,
            ])
            ->toArray();
    }

    public function generateScheduleSuggestion(int $course, array $groupIds, int $subjectId, int $hoursPerWeek = 4): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $groupTable = "{$prefix}_course_group";
        $subjectTable = "{$prefix}_course_subjects";

        $groups = DB::table($groupTable)->whereIn('id', $groupIds)->get();
        $subject = DB::table($subjectTable)->where('id', $subjectId)->first();

        if (!$subject) {
            return ['error' => 'Предмет не найден'];
        }

        $scheduleTable = "{$prefix}_course_schedules";
        $teacherSubjectsTable = "{$prefix}_course_teacher_subjects";

        $potentialTeachers = DB::table($teacherSubjectsTable)
            ->where('subject_id', $subjectId)
            ->pluck('teacher_id')
            ->toArray();

        $suggestions = [];

        foreach ($groups as $group) {
            $existingSlots = DB::table($scheduleTable)
                ->where('group_id', $group->id)
                ->get(['study_day', 'lesson_number'])
                ->toArray();

            $busySlots = array_map(fn($s) => "{$s->study_day}-{$s->lesson_number}", $existingSlots);

            $groupSuggestions = [];
            $hoursPlaced = 0;

            for ($day = 1; $day <= 6 && $hoursPlaced < $hoursPerWeek; $day++) {
                for ($lesson = 1; $lesson <= 6 && $hoursPlaced < $hoursPerWeek; $lesson++) {
                    $slotKey = "{$day}-{$lesson}";
                    if (in_array($slotKey, $busySlots)) continue;

                    $availableRooms = $this->getAvailableRooms($course, $day, $lesson);
                    $availableTeachers = array_filter(
                        $this->getAvailableTeachers($course, $day, $lesson, $subjectId),
                        fn($t) => in_array($t['id'], $potentialTeachers)
                    );

                    if (!empty($availableRooms) && !empty($availableTeachers)) {
                        $groupSuggestions[] = [
                            'day' => $day,
                            'lesson' => $lesson,
                            'room' => $availableRooms[0],
                            'teacher' => $availableTeachers[array_key_first($availableTeachers)],
                        ];
                        $busySlots[] = $slotKey;
                        $hoursPlaced++;
                    }
                }
            }

            $suggestions[] = [
                'group' => [
                    'id' => $group->id,
                    'name' => $group->group_name,
                ],
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                ],
                'planned_hours' => $hoursPlaced,
                'slots' => $groupSuggestions,
            ];
        }

        return $suggestions;
    }

    public function getWeekStats(int $course, string $weekStart): array
    {
        $prefix = $this->coursePrefixes[$course] ?? 'first_course';
        $scheduleTable = "{$prefix}_course_schedules";

        $schedules = DB::table($scheduleTable)
            ->where('week_start', $weekStart)
            ->get();

        $stats = [
            'total_pairs' => $schedules->count(),
            'by_day' => array_fill(1, 6, 0),
            'by_teacher' => [],
            'by_room' => [],
            'unassigned_rooms' => 0,
            'replacements' => 0,
        ];

        foreach ($schedules as $schedule) {
            $stats['by_day'][$schedule->study_day]++;

            if (!$schedule->room_id && !$schedule->room_id_denominator) {
                $stats['unassigned_rooms']++;
            }

            if ($schedule->is_replacement || $schedule->is_replacement_1_num || $schedule->is_replacement_1_den) {
                $stats['replacements']++;
            }

            foreach (['teacher_id', 'teacher_id_denominator', 'teacher_id_denominator_2'] as $field) {
                if ($schedule->$field) {
                    if (!isset($stats['by_teacher'][$schedule->$field])) {
                        $stats['by_teacher'][$schedule->$field] = 0;
                    }
                    $stats['by_teacher'][$schedule->$field]++;
                }
            }

            foreach (['room_id', 'room_id_denominator', 'room_id_denominator_2'] as $field) {
                if ($schedule->$field) {
                    if (!isset($stats['by_room'][$schedule->$field])) {
                        $stats['by_room'][$schedule->$field] = 0;
                    }
                    $stats['by_room'][$schedule->$field]++;
                }
            }
        }

        $teachers = DB::table('teachers')->get()->keyBy('id');
        $rooms = DB::table('rooms')->get()->keyBy('id');

        $stats['by_teacher'] = array_map(fn($id, $count) => [
            'id' => $id,
            'name' => $teachers[$id]->teacher_name ?? '?',
            'pairs' => $count,
        ], array_keys($stats['by_teacher']), array_values($stats['by_teacher']));

        usort($stats['by_teacher'], fn($a, $b) => $b['pairs'] <=> $a['pairs']);

        $stats['by_room'] = array_map(fn($id, $count) => [
            'id' => $id,
            'code' => $rooms[$id]->code ?? '?',
            'pairs' => $count,
        ], array_keys($stats['by_room']), array_values($stats['by_room']));

        usort($stats['by_room'], fn($a, $b) => $b['pairs'] <=> $a['pairs']);

        return $stats;
    }
}
