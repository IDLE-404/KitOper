<?php

namespace App\Services;

use App\Models\FormTwoNormative;
use App\Models\FormTwoRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormTwoService
{
    public function buildMonthReport(int $groupId, int $year, int $month): array
    {
        $daysCount = Carbon::create($year, $month, 1)->daysInMonth;
        $days = range(1, $daysCount);

        $normatives = FormTwoNormative::query()
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $records = FormTwoRecord::query()
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $subjectIds = $normatives->pluck('subject_id')
            ->merge($records->pluck('subject_id'))
            ->filter()
            ->unique();
        $teacherIds = $normatives->pluck('teacher_id')
            ->merge($records->pluck('teacher_id'))
            ->merge($records->pluck('replacement_teacher_id'))
            ->filter()
            ->unique();

        $subjects = DB::table('first_course_subjects')
            ->whereIn('id', $subjectIds)
            ->pluck(DB::raw('COALESCE(name_ru, subject_name)'), 'id');

        $teachers = DB::table('frist_course_teachers')
            ->whereIn('id', $teacherIds)
            ->pluck('teacher_name', 'id');

        $rows = [];
        $normMap = $this->buildNormativeLookup($normatives);
        foreach ($normatives as $norm) {
            $key = $this->rowKey($norm->subject_id, $norm->teacher_id);
            $rows[$key] = $this->emptyRow(
                $norm->subject_id,
                $norm->teacher_id,
                (int) ($norm->total_hours ?? 0),
                (int) ($norm->hours_per_class ?? 2),
                $days,
                $subjects,
                $teachers
            );
        }

        /** @var FormTwoRecord $rec */
        foreach ($records as $rec) {
            $subjectId = $rec->subject_id;
            $teacherId = $rec->teacher_id;
            if (!$subjectId) {
                continue;
            }

            $key = $this->rowKey($subjectId, $teacherId);
            if (!isset($rows[$key])) {
                $norm = $this->matchNormative($normMap, $subjectId, $teacherId);
                $rows[$key] = $this->emptyRow(
                    $subjectId,
                    $teacherId,
                    $norm['total_hours'] ?? (int) ($rec->total_hours ?? 0),
                    $norm['hours_per_class'] ?? (int) ($rec->hours_per_class ?? 2),
                    $days,
                    $subjects,
                    $teachers
                );
            }

            $day = (int) ($rec->day ?? 0);
            if ($day < 1 || $day > $daysCount) {
                $day = (int) Carbon::parse($rec->class_date ?? now())->day;
                if ($day < 1 || $day > $daysCount) {
                    continue;
                }
            }

            $dayData = $rows[$key]['days'][$day] ?? $this->emptyDay();
            $dayData['used_hours'] += (int) ($rec->used_hours ?? 0);
            $dayData['bonus_hours'] += (int) ($rec->bonus_hours ?? 0);
            $dayData['status'] = $this->resolveStatus($dayData['status'], $rec);
            $dayData['mode'] = $rec->mode ?? $dayData['mode'];
            $dayData['lesson_number'] = $rec->lesson_number ?? $dayData['lesson_number'];
            $dayData['subgroup'] = $rec->subgroup ?? $dayData['subgroup'];
            $dayData['replacement_teacher_id'] = $rec->replacement_teacher_id ?: $dayData['replacement_teacher_id'];
            $dayData['replacement_teacher_name'] = $rec->replacement_teacher_id
                ? ($teachers[$rec->replacement_teacher_id] ?? '—')
                : ($dayData['replacement_teacher_name'] ?? null);
            $dayData['replacement_comment'] = $rec->replacement_comment ?? $dayData['replacement_comment'];
            $dayData['details'][] = [
                'status' => $rec->status,
                'lesson_number' => $rec->lesson_number,
                'subgroup' => $rec->subgroup,
                'mode' => $rec->mode,
                'used_hours' => (int) ($rec->used_hours ?? 0),
                'bonus_hours' => (int) ($rec->bonus_hours ?? 0),
                'replacement_teacher_id' => $rec->replacement_teacher_id,
                'replacement_teacher_name' => $rec->replacement_teacher_id ? ($teachers[$rec->replacement_teacher_id] ?? '—') : null,
                'comment' => $rec->replacement_comment,
                'absent_reason' => $rec->absent_reason,
            ];

            $rows[$key]['days'][$day] = $dayData;
        }

        foreach ($rows as &$row) {
            $used = 0;
            $bonus = 0;
            foreach ($row['days'] as $cell) {
                $used += (int) ($cell['used_hours'] ?? 0);
                $bonus += (int) ($cell['bonus_hours'] ?? 0);
            }
            $row['used_hours_total'] = $used;
            $row['bonus_hours_total'] = $bonus;
            $row['hours_left'] = max(0, (int) $row['total_hours'] - $used + $bonus);
        }

        ksort($rows);

        return [
            'days' => $days,
            'rows' => array_values($rows),
        ];
    }

    /**
     * Ручная коррекция (используется редко). Ограничиваемся изменением статуса/комментария.
     */
    public function saveMonthRecords(int $groupId, int $year, int $month, array $rows): void
    {
        $payload = [];
        $now = now();

        foreach ($rows as $row) {
            $subjectId = $row['subject_id'] ?? null;
            if (!$subjectId) {
                continue;
            }
            $teacherId = $row['teacher_id'] ?? null;
            $totalHours = $row['total_hours'] ?? 0;
            $hoursPerClass = $row['hours_per_class'] ?? 2;
            $days = $row['days'] ?? [];

            foreach ($days as $day => $cell) {
                $status = $cell['status'] ?? 'normal';
                $replacementTeacherId = $cell['replacement_teacher_id'] ?? null;
                $classDate = Carbon::create($year, $month, (int) $day)->toDateString();

                $usedHours = 0;
                $bonusHours = null;

                if ($status === 'normal') {
                    $usedHours = $hoursPerClass;
                    $bonusHours = null;
                    $replacementTeacherId = null;
                } elseif ($status === 'sick') {
                    $usedHours = 0;
                    $bonusHours = null;
                    $replacementTeacherId = null;
                } elseif ($status === 'replacement') {
                    $usedHours = 0;
                    $bonusHours = $cell['bonus_hours'] ?? $hoursPerClass;
                }

                $payload[] = [
                    'group_id' => $groupId,
                    'year' => $year,
                    'month' => $month,
                    'day' => (int) $day,
                    'class_date' => $classDate,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'lesson_number' => $cell['lesson_number'] ?? null,
                    'subgroup' => $cell['subgroup'] ?? null,
                    'total_hours' => $totalHours,
                    'hours_per_class' => $hoursPerClass,
                    'status' => $status,
                    'replacement_teacher_id' => $replacementTeacherId,
                    'bonus_hours' => $bonusHours,
                    'used_hours' => $usedHours,
                    'absent_reason' => $cell['absent_reason'] ?? null,
                    'replacement_comment' => $cell['replacement_comment'] ?? null,
                    'mode' => $cell['mode'] ?? 'single',
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
        }

        if (!$payload) {
            return;
        }

        $uniqueBy = ['group_id', 'year', 'month', 'day', 'subject_id', 'mode'];

        FormTwoRecord::upsert(
            $payload,
            $uniqueBy,
            [
                'teacher_id',
                'total_hours',
                'hours_per_class',
                'status',
                'replacement_teacher_id',
                'bonus_hours',
                'used_hours',
                'absent_reason',
                'replacement_comment',
                'class_date',
                'lesson_number',
                'subgroup',
                'mode',
                'updated_at',
            ]
        );
    }

    protected function buildNormativeLookup(Collection $normatives): array
    {
        $map = [];
        foreach ($normatives as $norm) {
            $map[$norm->subject_id][$norm->teacher_id ?? 0] = [
                'total_hours' => (int) ($norm->total_hours ?? 0),
                'hours_per_class' => (int) ($norm->hours_per_class ?? 2),
            ];
        }
        return $map;
    }

    protected function matchNormative(array $map, int $subjectId, ?int $teacherId): array
    {
        $byTeacher = $map[$subjectId][$teacherId ?? 0] ?? null;
        if ($byTeacher !== null) {
            return $byTeacher;
        }

        return $map[$subjectId][0] ?? [
            'total_hours' => 0,
            'hours_per_class' => 2,
        ];
    }

    protected function emptyRow(
        int $subjectId,
        ?int $teacherId,
        int $totalHours,
        int $hoursPerClass,
        array $days,
        Collection $subjects,
        Collection $teachers
    ): array {
        $daysPayload = [];
        foreach ($days as $d) {
            $daysPayload[$d] = $this->emptyDay();
        }

        return [
            'subject_id' => $subjectId,
            'subject_name' => $subjects[$subjectId] ?? '—',
            'teacher_id' => $teacherId,
            'teacher_name' => $teacherId ? ($teachers[$teacherId] ?? '—') : '—',
            'total_hours' => $totalHours,
            'hours_per_class' => $hoursPerClass,
            'days' => $daysPayload,
            'used_hours_total' => 0,
            'bonus_hours_total' => 0,
            'hours_left' => $totalHours,
        ];
    }

    protected function emptyDay(): array
    {
        return [
            'status' => 'empty',
            'used_hours' => 0,
            'bonus_hours' => 0,
            'lesson_number' => null,
            'subgroup' => null,
            'mode' => null,
            'replacement_teacher_id' => null,
            'replacement_teacher_name' => null,
            'replacement_comment' => null,
            'details' => [],
        ];
    }

    protected function rowKey(int $subjectId, ?int $teacherId): string
    {
        return $subjectId . ':' . ($teacherId ?? 0);
    }

    protected function resolveStatus(string $current, FormTwoRecord $rec): string
    {
        $incoming = $rec->status ?: (($rec->used_hours ?? 0) > 0 ? 'normal' : 'empty');
        $priority = [
            'empty' => 0,
            'normal' => 1,
            'sick' => 2,
            'replacement' => 3,
        ];

        return ($priority[$incoming] ?? 0) >= ($priority[$current] ?? 0) ? $incoming : $current;
    }
}
