<?php

namespace App\Services;

use App\Models\FormTwoRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormTwoService
{
    public function loadMonthRecords(int $groupId, int $year, int $month): array
    {
        $records = FormTwoRecord::query()
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $subjectIds = $records->pluck('subject_id')->filter()->unique();
        $teacherIds = $records->pluck('teacher_id')
            ->merge($records->pluck('replacement_teacher_id'))
            ->filter()
            ->unique();

        $subjects = DB::table('first_course_subjects')
            ->whereIn('id', $subjectIds)
            ->pluck(DB::raw('COALESCE(name_ru, subject_name)'), 'id');

        $teachers = DB::table('frist_course_teachers')
            ->whereIn('id', $teacherIds)
            ->pluck('teacher_name', 'id');

        $grouped = [];

        /** @var FormTwoRecord $rec */
        foreach ($records as $rec) {
            $sid = $rec->subject_id;
            if (!$sid) {
                continue;
            }
            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'subject_id' => $sid,
                    'subject_name' => $subjects[$sid] ?? '—',
                    'teacher_id' => $rec->teacher_id,
                    'teacher_name' => $rec->teacher_id ? ($teachers[$rec->teacher_id] ?? '—') : '—',
                    'total_hours' => $rec->total_hours ?? 0,
                    'hours_per_class' => $rec->hours_per_class ?? 2,
                    'days' => [],
                    'used_hours_total' => 0,
                ];
            }

            $dayData = [
                'status' => $rec->status,
                'replacement_teacher_id' => $rec->replacement_teacher_id,
                'replacement_teacher_name' => $rec->replacement_teacher_id ? ($teachers[$rec->replacement_teacher_id] ?? '—') : null,
                'bonus_hours' => $rec->bonus_hours,
                'used_hours' => $rec->used_hours,
            ];

            $grouped[$sid]['days'][$rec->day] = $dayData;
            $grouped[$sid]['used_hours_total'] += (int) $rec->used_hours + (int) ($rec->bonus_hours ?? 0);
        }

        // Пересчитать остаток
        foreach ($grouped as &$row) {
            $row['hours_left'] = max(0, (int) $row['total_hours'] - (int) $row['used_hours_total']);
        }

        return array_values($grouped);
    }

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
                    $bonusHours = 2;
                }

                $payload[] = [
                    'group_id' => $groupId,
                    'year' => $year,
                    'month' => $month,
                    'day' => (int) $day,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
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
                'updated_at',
            ]
        );
    }
}
