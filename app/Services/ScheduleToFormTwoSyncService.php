<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleToFormTwoSyncService
{
    /**
      * Синхронизация расписания за неделю в Form 2.
      */
    public function syncWeek(
        int $groupId,
        Carbon $weekStart,
        ?Carbon $classWeekStart = null,
        ?\Illuminate\Support\Collection $rows = null,
        int $course = 1
    ): void
    {
        $tables = \App\Support\CourseContext::tables($course);
        $classWeekStart = ($classWeekStart ?? $weekStart)->copy()->startOfDay();
        $weekStart = $weekStart->copy()->startOfDay();
        $weekMode = $this->resolveWeekMode($classWeekStart, $course);
        $weekEnd = $classWeekStart->copy()->addDays(6);

        $dayOffset = [
            'Понедельник' => 0,
            'Вторник' => 1,
            'Среда' => 2,
            'Четверг' => 3,
            'Пятница' => 4,
            'Суббота' => 5,
        ];

        DB::table($tables['form_two_records'])
            ->where('group_id', $groupId)
            ->whereBetween('class_date', [$classWeekStart->toDateString(), $weekEnd->toDateString()])
            ->delete();

        $rows = $rows ?? $this->fetchWeekRows($groupId, $weekStart, $tables['schedules']);

        if ($rows->isEmpty()) {
            return;
        }

        $payload = [];

        foreach ($rows as $row) {
            $dayName = $row->study_day;
            if (!isset($dayOffset[$dayName])) {
                continue;
            }

            $lessonNumber = (int) $row->lesson_number;
            $classDate = $classWeekStart->copy()->addDays($dayOffset[$dayName]);

            foreach ($this->subgroupsForRow($row) as $subgroup) {
                $payload = array_merge($payload, $this->syncSubgroup(
                    $row,
                    $subgroup,
                    $weekMode,
                    $classDate,
                    $groupId,
                    $lessonNumber,
                    $course
                ));
            }
        }

        if ($payload) {
            $payload = $this->deduplicatePayload($payload);
            DB::table($tables['form_two_records'])->insert($payload);
        }
    }

    protected function subgroupsForRow(object $row): array
    {
        $flag = $row->subgroup ?? null;
        if (in_array($flag, ['2', 'B'], true)) {
            return [2];
        }

        $hasSubgroup2Data = ($row->subject_id_2 ?? null)
            || ($row->teacher_id_2 ?? null)
            || ($row->subject_id_denominator_2 ?? null)
            || ($row->teacher_id_denominator_2 ?? null);

        return $hasSubgroup2Data ? [1, 2] : [1];
    }

    protected function syncSubgroup(
        object $row,
        int $subgroup,
        string $weekMode,
        Carbon $classDate,
        int $groupId,
        int $lessonNumber,
        int $course
    ): array {
        $isSub2 = $subgroup === 2;
        $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? 2 : 1;

        // ---------- БАЗОВЫЕ subject / teacher ----------
        $subjectNum = $isSub2
            ? ($row->subject_id_2 ?? ($subgroupFlag === 2 ? $row->subject_id : null))
            : ($row->subject_id ?? null);

        $teacherNum = $isSub2
            ? ($row->teacher_id_2 ?? ($subgroupFlag === 2 ? $row->teacher_id : null))
            : ($row->teacher_id ?? null);

        $subjectDen = $isSub2
            ? ($row->subject_id_denominator_2 ?? null)
            : ($row->subject_id_denominator ?? null);

        $teacherDen = $isSub2
            ? ($row->teacher_id_denominator_2 ?? null)
            : ($row->teacher_id_denominator ?? null);

        $roomDen = $isSub2
            ? ($row->room_id_denominator_2 ?? null)
            : ($row->room_id_denominator ?? null);

        $hasDenominator = $subjectDen || $teacherDen || $roomDen;
        $useDenominator = $hasDenominator && $weekMode === 'denominator';

        $activeSubject = $useDenominator ? ($subjectDen ?: $subjectNum) : $subjectNum;
        $activeTeacher = $useDenominator ? ($teacherDen ?: $teacherNum) : $teacherNum;
        $mode = $hasDenominator ? $weekMode : 'single';

        if (!$activeSubject && !$activeTeacher) {
            return [];
        }

        // ---------- СТАТУС И ЗАМЕНЫ ИЗ schedules ----------
        $statusMode = $useDenominator ? 'denominator' : 'numerator';

        // При mode = 'single' замены всегда записываются в поля _1_num, независимо от subgroup
        $replacementSubgroup = $mode === 'single' ? 1 : $subgroup;

        $isAbsent = $this->isAbsent($row, $subgroup, $statusMode);
        $isReplacement = $this->isReplacement($row, $replacementSubgroup, $statusMode);

        $replacementTeacherId = $this->replacementTeacherId($row, $replacementSubgroup, $statusMode);
        $replacementSubjectId = $this->replacementSubjectId($row, $replacementSubgroup, $statusMode);
        $replacementComment = $this->replacementComment($row, $replacementSubgroup, $statusMode);

        // ---------- ДЕТЕКЦИЯ ЗАМЕНЫ (КЛЮЧЕВОЕ МЕСТО) ----------
        $isTeacherChanged = false;
        $isSubjectChanged = false;

        // Проверяем явные поля замены из schedules
        if ($isReplacement && $replacementTeacherId) {
            $isTeacherChanged = true;
        }

        if ($replacementSubjectId) {
            $isSubjectChanged = true;
        }

        // ДОПОЛНИТЕЛЬНО: для подгруппы 2 сравниваем с подгруппой 1
        // Если teacher_id_2 отличается от teacher_id - это тоже замена
        if ($isSub2 && !$isTeacherChanged) {
            $originalTeacherId = $useDenominator
                ? ($row->teacher_id_denominator ?? $row->teacher_id ?? null)
                : ($row->teacher_id ?? null);
            
            if ($activeTeacher && $originalTeacherId && $activeTeacher !== $originalTeacherId) {
                $isTeacherChanged = true;
                // Если replacement_teacher_id не установлен явно, используем активного учителя
                if (!$replacementTeacherId) {
                    $replacementTeacherId = $activeTeacher;
                }
            }
        }

        // Аналогично для предмета
        if ($isSub2 && !$isSubjectChanged) {
            $originalSubjectId = $useDenominator
                ? ($row->subject_id_denominator ?? $row->subject_id ?? null)
                : ($row->subject_id ?? null);
            
            if ($activeSubject && $originalSubjectId && $activeSubject !== $originalSubjectId) {
                $isSubjectChanged = true;
                // Если replacement_subject_id не установлен явно, используем активный предмет
                if (!$replacementSubjectId) {
                    $replacementSubjectId = $activeSubject;
                }
            }
        }

        $isChanged = $isTeacherChanged || $isSubjectChanged;

        // ЛОГИРОВАНИЕ ДЛЯ ОТЛАДКИ
        \Log::info('FORM2 REPL DEBUG', [
            'group_id' => $groupId,
            'date' => $classDate->toDateString(),
            'lesson' => $lessonNumber,
            'subgroup' => $subgroup,
            'isSub2' => $isSub2,
            'mode' => $mode,
            'statusMode' => $statusMode,
            'useDenominator' => $useDenominator,
            'activeTeacher' => $activeTeacher,
            'activeSubject' => $activeSubject,
            'replacementTeacherId' => $replacementTeacherId,
            'replacementSubjectId' => $replacementSubjectId,
            'isReplacement' => $isReplacement,
            'isAbsent' => $isAbsent,
            'isTeacherChanged' => $isTeacherChanged,
            'isSubjectChanged' => $isSubjectChanged,
            'isChanged' => $isChanged,
            'teacher_id_1' => $useDenominator ? ($row->teacher_id_denominator ?? $row->teacher_id ?? null) : ($row->teacher_id ?? null),
            'teacher_id_2' => $activeTeacher,
            'subject_id_1' => $useDenominator ? ($row->subject_id_denominator ?? $row->subject_id ?? null) : ($row->subject_id ?? null),
            'subject_id_2' => $activeSubject,
            'repl_teacher_2_num' => $row->replacement_teacher_id_2_num ?? null,
            'repl_teacher_2_den' => $row->replacement_teacher_id_2_den ?? null,
            'repl_teacher_1_num' => $row->replacement_teacher_id_1_num ?? null,
            'repl_teacher_1_den' => $row->replacement_teacher_id_1_den ?? null,
            'is_repl_2_num' => $row->is_replacement_2_num ?? false,
            'is_repl_2_den' => $row->is_replacement_2_den ?? false,
            'is_repl_1_num' => $row->is_replacement_1_num ?? false,
            'is_repl_1_den' => $row->is_replacement_1_den ?? false,
        ]);

        // ---------- РАСЧЁТ ЧАСОВ ----------
        $teacherForHours = $activeTeacher;
        $hoursPerClass = $this->hoursPerClass($groupId, $activeSubject, $teacherForHours, $classDate, $course);
        $totalHours = $this->totalHours($groupId, $activeSubject, $teacherForHours, $classDate, $course);

        // ---------- БАЗОВАЯ ЗАПИСЬ ----------
        $base = [
            'group_id' => $groupId,
            'subject_id' => $activeSubject,
            'teacher_id' => $activeTeacher,
            'class_date' => $classDate->toDateString(),
            'year' => (int) $classDate->year,
            'month' => (int) $classDate->month,
            'day' => (int) $classDate->day,
            'lesson_number' => $lessonNumber,
            'subgroup' => (string) $subgroup,
            'mode' => $mode,
            'hours_per_class' => $hoursPerClass,
            'total_hours' => $totalHours,
            'replacement_comment' => $replacementComment,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $payload = [];

        // ---------- ЛОГИКА ФОРМЫ 2 ----------
        if ($isAbsent) {
            $payload[] = array_merge($base, [
                'status' => 'replaced',
                'used_hours' => 0,
                'bonus_hours' => null,
                'replacement_teacher_id' => $replacementTeacherId,
                'replacement_subject_id' => $replacementSubjectId,
            ]);
            return $payload;
        }

        if ($isChanged) {
            // replaced — всегда
            $payload[] = array_merge($base, [
                'status' => 'replaced',
                'used_hours' => 0,
                'bonus_hours' => null,
                'replacement_teacher_id' => $replacementTeacherId,
                'replacement_subject_id' => $replacementSubjectId,
            ]);

            // replacement — ТОЛЬКО если заменён предмет
            if ($isSubjectChanged && $replacementSubjectId !== $activeSubject) {
                $replacementHours = $this->hoursPerClass(
                    $groupId,
                    $replacementSubjectId,
                    $replacementTeacherId,
                    $classDate,
                    $course
                );

                $replacementTotal = $this->totalHours(
                    $groupId,
                    $replacementSubjectId,
                    $replacementTeacherId,
                    $classDate,
                    $course
                );

                $payload[] = array_merge($base, [
                    'subject_id' => $replacementSubjectId,
                    'teacher_id' => $replacementTeacherId,
                    'hours_per_class' => $replacementHours,
                    'total_hours' => $replacementTotal,
                    'status' => 'replacement',
                    'used_hours' => 0,
                    'bonus_hours' => $replacementHours,
                    'replacement_teacher_id' => $replacementTeacherId,
                    'replacement_subject_id' => $replacementSubjectId,
                ]);
            }

            return $payload;
        }

        // ---------- ОБЫЧНАЯ ПАРА ----------
        $payload[] = array_merge($base, [
            'status' => 'normal',
            'used_hours' => $hoursPerClass,
            'bonus_hours' => null,
            'replacement_teacher_id' => null,
            'replacement_subject_id' => null,
        ]);

        return $payload;
    }

    protected function deduplicatePayload(array $payload): array
    {
        $seen = [];
        $result = [];

        foreach ($payload as $row) {
            $key = implode('|', [
                $row['group_id'] ?? '',
                $row['class_date'] ?? '',
                $row['lesson_number'] ?? '', // 🔥 ОБЯЗАТЕЛЬНО
                $row['year'] ?? '',
                $row['month'] ?? '',
                $row['day'] ?? '',
                $row['subject_id'] ?? '',
                $row['teacher_id'] ?? '', // 🔥 ВАЖНО: добавляем teacher_id для различения записей с разными учителями
                $row['mode'] ?? '',
                $row['subgroup'] ?? '',
            ]);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Синхронизирует текущую неделю и соседнюю (чередование ч/з) в Форму 2.
     * Режим недели определяется от даты начала семестра.
     */
    public function syncWeekWithAlternation(int $groupId, Carbon $weekStart, int $course = 1): void
    {
        $baseMode = $this->resolveWeekMode($weekStart, $course);
        $otherWeekStart = $baseMode === 'denominator'
            ? $weekStart->copy()->subWeek()
            : $weekStart->copy()->addWeek();

        $tables = \App\Support\CourseContext::tables($course);
        $rows = $this->fetchWeekRows($groupId, $weekStart, $tables['schedules']);
        if ($rows->isEmpty()) {
            return;
        }

        // Текущая неделя
        $this->syncWeek($groupId, $weekStart, $weekStart, $rows, $course);
        // Чередующаяся неделя
        $this->syncWeek($groupId, $otherWeekStart, $otherWeekStart, $rows, $course);
    }

    protected function fetchWeekRows(int $groupId, Carbon $weekStart, string $scheduleTable)
    {
        return DB::table($scheduleTable)
            ->where('group_id', $groupId)
            ->whereDate('week_start', $weekStart->toDateString())
            ->get();
    }

    /**
     * Определяем режим недели по дате начала семестра.
     * Неделя семестра считается числителем.
     */
    public function resolveWeekMode(Carbon $weekStart, int $course = 1): string
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY);
        $semesterStart = \App\Support\CourseContext::semesterStart($course, $weekStart);
        $weekIndex = $semesterStart->diffInWeeks($weekStart);
        return $weekIndex % 2 === 0 ? 'numerator' : 'denominator';
    }

    protected function isAbsent(object $row, int $subgroup, string $weekMode): bool
    {
        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $weekMode === 'denominator' ? '_den' : '_num';
        $key = "is_absent{$suffix}{$modeSuffix}";
        return (bool) ($row->{$key} ?? false);
    }

    protected function isReplacement(object $row, int $subgroup, string $weekMode): bool
    {
        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $weekMode === 'denominator' ? '_den' : '_num';
        $key = "is_replacement{$suffix}{$modeSuffix}";
        return (bool) ($row->{$key} ?? false);
    }

    protected function replacementTeacherId(object $row, int $subgroup, string $weekMode): ?int
    {
        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $weekMode === 'denominator' ? '_den' : '_num';
        $key = "replacement_teacher_id{$suffix}{$modeSuffix}";
        return $row->{$key} ?? null;
    }

    protected function replacementSubjectId(object $row, int $subgroup, string $weekMode): ?int
    {
        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $weekMode === 'denominator' ? '_den' : '_num';
        $key = "replacement_subject_id{$suffix}{$modeSuffix}";
        return $row->{$key} ?? null;
    }

    protected function replacementComment(object $row, int $subgroup, string $weekMode): ?string
    {
        $suffix = $subgroup === 2 ? '_2' : '_1';
        $modeSuffix = $weekMode === 'denominator' ? '_den' : '_num';
        $key = "replacement_comment{$suffix}{$modeSuffix}";
        return $row->{$key} ?? null;
    }

    protected function hoursPerClass(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date, int $course = 1): int
    {
        $norm = $this->fetchNormative($groupId, $subjectId, $teacherId, $date, $course);
        return (int) ($norm['hours_per_class'] ?? 2);
    }

    protected function totalHours(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date, int $course = 1): int
    {
        $norm = $this->fetchNormative($groupId, $subjectId, $teacherId, $date, $course);
        return (int) ($norm['total_hours'] ?? 0);
    }

    protected function fetchNormative(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date, int $course = 1): array
    {
        if (!$subjectId) {
            return [];
        }

        $tables = \App\Support\CourseContext::tables($course);

        $row = \Illuminate\Support\Facades\DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where('subject_id', $subjectId)
            ->when($teacherId, fn ($q) => $q->where(function ($qq) use ($teacherId) {
                $qq->where('teacher_id', $teacherId)->orWhereNull('teacher_id');
            }))
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->orderByRaw('teacher_id is null') // предпочитаем точное совпадение
            ->first();

        return [
            'total_hours' => $row->total_hours ?? 0,
            'hours_per_class' => $row->hours_per_class ?? 2,
        ];
    }

    /**
     * Получает ID учителя из нормативов для данного предмета и группы
     * Ищет в текущем месяце и предыдущих месяцах учебного года
     */
    protected function getNormativeTeacherId(int $groupId, ?int $subjectId, Carbon $date, int $course = 1): ?int
    {
        if (!$subjectId) {
            return null;
        }

        $tables = \App\Support\CourseContext::tables($course);

        // Учебный год начинается 1 сентября
        $studyYearStart = $date->month >= 9
            ? Carbon::create($date->year, 9, 1)
            : Carbon::create($date->year - 1, 9, 1);

        $row = \Illuminate\Support\Facades\DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where('subject_id', $subjectId)
            ->whereNotNull('teacher_id')
            ->where(function ($q) use ($date, $studyYearStart) {
                $q->where(function ($qStart) use ($studyYearStart) {
                    $qStart->where('year', '>', $studyYearStart->year)
                        ->orWhere(function ($q2) use ($studyYearStart) {
                            $q2->where('year', $studyYearStart->year)
                                ->where('month', '>=', $studyYearStart->month);
                        });
                })->where(function ($qEnd) use ($date) {
                    $qEnd->where('year', '<', $date->year)
                        ->orWhere(function ($q3) use ($date) {
                            $q3->where('year', $date->year)
                                ->where('month', '<=', $date->month);
                        });
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        return $row->teacher_id ?? null;
    }

    /**
     * Получает ID предмета из нормативов для данного учителя и группы
     * Ищет в текущем месяце и предыдущих месяцах учебного года
     */
    protected function getNormativeSubjectId(int $groupId, ?int $teacherId, Carbon $date, int $course = 1): ?int
    {
        if (!$teacherId) {
            return null;
        }

        $tables = \App\Support\CourseContext::tables($course);

        // Учебный год начинается 1 сентября
        $studyYearStart = $date->month >= 9
            ? Carbon::create($date->year, 9, 1)
            : Carbon::create($date->year - 1, 9, 1);

        $row = \Illuminate\Support\Facades\DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where('teacher_id', $teacherId)
            ->whereNotNull('subject_id')
            ->where(function ($q) use ($date, $studyYearStart) {
                $q->where(function ($qStart) use ($studyYearStart) {
                    $qStart->where('year', '>', $studyYearStart->year)
                        ->orWhere(function ($q2) use ($studyYearStart) {
                            $q2->where('year', $studyYearStart->year)
                                ->where('month', '>=', $studyYearStart->month);
                        });
                })->where(function ($qEnd) use ($date) {
                    $qEnd->where('year', '<', $date->year)
                        ->orWhere(function ($q3) use ($date) {
                            $q3->where('year', $date->year)
                                ->where('month', '<=', $date->month);
                        });
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        return $row->subject_id ?? null;
    }
}
