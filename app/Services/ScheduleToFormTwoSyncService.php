<?php

namespace App\Services;

use App\Models\FormTwoNormative;
use App\Models\FormTwoRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleToFormTwoSyncService
{
    /**
      * Синхронизация расписания за неделю в Form 2.
      */
    public function syncWeek(int $groupId, Carbon $weekStart, string $weekMode = 'numerator'): void
    {
        $weekMode = in_array($weekMode, ['numerator', 'denominator'], true) ? $weekMode : 'numerator';
        $weekStart = $weekStart->copy()->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(6);

        $dayOffset = [
            'Понедельник' => 0,
            'Вторник' => 1,
            'Среда' => 2,
            'Четверг' => 3,
            'Пятница' => 4,
            'Суббота' => 5,
        ];

        DB::table('form_two_records')
            ->where('group_id', $groupId)
            ->whereBetween('class_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->delete();

        $rows = DB::table('first_course_schedules')
            ->where('group_id', $groupId)
            ->whereDate('week_start', $weekStart->toDateString())
            ->get();

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
            $classDate = $weekStart->copy()->addDays($dayOffset[$dayName]);

            foreach ($this->subgroupsForRow($row) as $subgroup) {
                $payload = array_merge($payload, $this->syncSubgroup(
                    $row,
                    $subgroup,
                    $weekMode,
                    $classDate,
                    $groupId,
                    $lessonNumber
                ));
            }
        }

        if ($payload) {
            $payload = $this->deduplicatePayload($payload);
            FormTwoRecord::insert($payload);
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
        int $lessonNumber
    ): array {
        $isSub2 = $subgroup === 2;
        $subgroupFlag = in_array($row->subgroup ?? null, ['2', 'B'], true) ? 2 : 1;

        $subjectNum = $isSub2
            ? ($row->subject_id_2 ?? ($subgroupFlag === 2 ? ($row->subject_id ?? null) : null))
            : ($row->subject_id ?? null);
        $teacherNum = $isSub2
            ? ($row->teacher_id_2 ?? ($subgroupFlag === 2 ? ($row->teacher_id ?? null) : null))
            : ($row->teacher_id ?? null);

        $subjectDen = $isSub2
            ? ($row->subject_id_denominator_2 ?? ($subgroupFlag === 2 ? ($row->subject_id_denominator ?? null) : null))
            : ($row->subject_id_denominator ?? null);
        $teacherDen = $isSub2
            ? ($row->teacher_id_denominator_2 ?? ($subgroupFlag === 2 ? ($row->teacher_id_denominator ?? null) : null))
            : ($row->teacher_id_denominator ?? null);

        $hasDenominator = $subjectDen || $teacherDen;
        $activeSubject = $hasDenominator && $weekMode === 'denominator' ? $subjectDen : $subjectNum;
        $activeTeacher = $hasDenominator && $weekMode === 'denominator' ? $teacherDen : $teacherNum;
        $mode = $hasDenominator ? $weekMode : 'single';

        if (!$activeSubject && !$activeTeacher) {
            return [];
        }

        $isAbsent = $this->isAbsent($row, $subgroup, $weekMode);
        $isReplacement = $this->isReplacement($row, $subgroup, $weekMode);
        $replacementTeacherId = $this->replacementTeacherId($row, $subgroup, $weekMode);
        $replacementSubjectId = $this->replacementSubjectId($row, $subgroup, $weekMode);
        $replacementComment = $this->replacementComment($row, $subgroup, $weekMode);

        $hoursPerClass = $this->hoursPerClass($groupId, $activeSubject, $activeTeacher, $classDate);
        $totalHours = $this->totalHours($groupId, $activeSubject, $activeTeacher, $classDate);

        $payload = [];
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

        if ($isReplacement) {
            // Пара используется как замена: добавляем нагрузку через bonus_hours.
            $payload[] = array_merge($base, [
                'status' => 'replacement',
                'used_hours' => 0,
                'bonus_hours' => 2,
                'replacement_teacher_id' => $replacementTeacherId,
                'replacement_subject_id' => $replacementSubjectId,
            ]);
        } elseif ($isAbsent) {
            // Пара снята/отменена: отмечаем как заменённую без часов.
            $payload[] = array_merge($base, [
                'status' => 'replaced',
                'used_hours' => 0,
                'bonus_hours' => null,
                'replacement_teacher_id' => $replacementTeacherId,
                'replacement_subject_id' => $replacementSubjectId,
            ]);

            // Если указали, чем заменили (предмет/учитель), добавляем строку замещения.
            if ($replacementSubjectId || $replacementTeacherId) {
                $replacementHoursPerClass = $this->hoursPerClass(
                    $groupId,
                    $replacementSubjectId ?: $activeSubject,
                    $replacementTeacherId ?: $activeTeacher,
                    $classDate
                );
                $replacementTotalHours = $this->totalHours(
                    $groupId,
                    $replacementSubjectId ?: $activeSubject,
                    $replacementTeacherId ?: $activeTeacher,
                    $classDate
                );

                $payload[] = array_merge($base, [
                    'subject_id' => $replacementSubjectId ?: $activeSubject,
                    'teacher_id' => $replacementTeacherId ?: $activeTeacher,
                    'hours_per_class' => $replacementHoursPerClass,
                    'total_hours' => $replacementTotalHours,
                    'status' => 'replacement',
                    'used_hours' => 0,
                    'bonus_hours' => $replacementHoursPerClass,
                    'replacement_teacher_id' => $replacementTeacherId,
                    'replacement_subject_id' => $replacementSubjectId,
                ]);
            }
        } else {
            $payload[] = array_merge($base, [
                'status' => 'normal',
                'used_hours' => $hoursPerClass,
                'bonus_hours' => null,
                'replacement_teacher_id' => null,
                'replacement_subject_id' => null,
            ]);
        }

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
                $row['year'] ?? '',
                $row['month'] ?? '',
                $row['day'] ?? '',
                $row['subject_id'] ?? '',
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

    protected function hoursPerClass(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date): int
    {
        $norm = $this->fetchNormative($groupId, $subjectId, $teacherId, $date);
        return (int) ($norm['hours_per_class'] ?? 2);
    }

    protected function totalHours(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date): int
    {
        $norm = $this->fetchNormative($groupId, $subjectId, $teacherId, $date);
        return (int) ($norm['total_hours'] ?? 0);
    }

    protected function fetchNormative(int $groupId, ?int $subjectId, ?int $teacherId, Carbon $date): array
    {
        if (!$subjectId) {
            return [];
        }

        $row = FormTwoNormative::query()
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
}
