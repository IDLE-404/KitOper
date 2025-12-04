<?php

namespace App\Services;

use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormTwoService
{
    public function buildMonthReport(int $groupId, int $year, int $month, int $course = 1): array
    {
        $tables = CourseContext::tables($course);

        $daysCount = Carbon::create($year, $month, 1)->daysInMonth;
        $days = range(1, $daysCount);

        $subjectNames = DB::table($tables['subjects'])
            ->orderBy('name_ru')
            ->pluck(DB::raw('COALESCE(name_ru, subject_name)'), 'id');

        $normatives = DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $records = DB::table($tables['form_two_records'])
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();
        $records = $this->expandReplacements($records);

        $teacherIds = $normatives->pluck('teacher_id')
            ->merge($records->pluck('teacher_id'))
            ->merge($records->pluck('replacement_teacher_id'))
            ->filter()
            ->unique();

        $teachers = DB::table($tables['teachers'])
            ->whereIn('id', $teacherIds)
            ->pluck('teacher_name', 'id');

        $rows = [];
        $replacementRows = [];
        $normMap = $this->buildNormativeLookup($normatives);
        $subjectsUsed = [];
        foreach ($normatives as $norm) {
            $key = $this->rowKey($norm->subject_id, $norm->teacher_id);
            $rows[$key] = $this->emptyRow(
                $norm->subject_id,
                $norm->teacher_id,
                (int) ($norm->total_hours ?? 0),
                (int) ($norm->hours_per_class ?? 2),
                $days,
                $subjectNames,
                $teachers
            );
            $subjectsUsed[$norm->subject_id] = true;
        }

        /** @var object $rec */
        foreach ($records as $rec) {
            $subjectId = $rec->subject_id;
            $teacherId = $rec->teacher_id;
            if (!$subjectId) {
                continue;
            }

            $day = (int) ($rec->day ?? 0);
            if ($day < 1 || $day > $daysCount) {
                $day = (int) Carbon::parse($rec->class_date ?? now())->day;
                if ($day < 1 || $day > $daysCount) {
                    continue;
                }
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
                    $subjectNames,
                    $teachers
                );
            }
            $subjectsUsed[$subjectId] = true;

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
            $dayData['replacement_subject_id'] = $rec->replacement_subject_id ?: $dayData['replacement_subject_id'];
            $dayData['replacement_subject_name'] = $rec->replacement_subject_id
                ? ($subjectNames[$rec->replacement_subject_id] ?? '—')
                : ($dayData['replacement_subject_name'] ?? null);
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
                'replacement_subject_id' => $rec->replacement_subject_id,
                'replacement_subject_name' => $rec->replacement_subject_id ? ($subjectNames[$rec->replacement_subject_id] ?? '—') : null,
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

        // Добавляем пустые строки для предметов, по которым нет нормативов и записей
        foreach ($subjectNames as $subjectId => $subjectName) {
            if (isset($subjectsUsed[$subjectId])) {
                continue;
            }
            $key = $this->rowKey($subjectId, null);
            $rows[$key] = $this->emptyRow(
                $subjectId,
                null,
                0,
                2,
                $days,
                $subjectNames,
                $teachers
            );
        }

        $preferredOrder = [
            'Русский язык',
            'Русская литература',
            'Казахский язык и литература',
            'Иностранный язык',
            'Математика',
            '2',
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

        $rows = $this->sortRows($rows, $preferredOrder);

        return [
            'days' => $days,
            'rows' => $rows,
            'replacement_rows' => [],
        ];
    }

    /**
     * Ручная коррекция (используется редко). Ограничиваемся изменением статуса/комментария.
     */
    public function saveMonthRecords(int $groupId, int $year, int $month, array $rows, int $course = 1): void
    {
        $tables = CourseContext::tables($course);
        $payload = [];
        $normativePayload = [];
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

            // Сохраняем норматив отдельно, даже если по дням пока пусто
            if ($teacherId) {
                $normativePayload[] = [
                    'group_id' => $groupId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'month' => $month,
                    'year' => $year,
                    'total_hours' => $totalHours,
                    'hours_per_class' => $hoursPerClass,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }

            foreach ($days as $day => $cell) {
                $status = $cell['status'] ?? 'normal';
                if ($status === 'empty') {
                    continue; // не пишем пустые клетки в базу, чтобы не ловить enum-тринкат
                }
                $replacementTeacherId = $cell['replacement_teacher_id'] ?? null;
                $replacementSubjectId = $cell['replacement_subject_id'] ?? null;
                $classDate = Carbon::create($year, $month, (int) $day)->toDateString();

                $usedHours = 0;
                $bonusHours = null;

                if ($status === 'normal') {
                    $usedHours = $hoursPerClass;
                    $bonusHours = null;
                    $replacementTeacherId = null;
                    $replacementSubjectId = null;
                } elseif ($status === 'sick') {
                    $usedHours = 0;
                    $bonusHours = null;
                    $replacementTeacherId = null;
                    $replacementSubjectId = null;
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
                    'subgroup' => $cell['subgroup'] ?? 1,
                    'total_hours' => $totalHours,
                    'hours_per_class' => $hoursPerClass,
                    'status' => $status,
                    'replacement_teacher_id' => $replacementTeacherId,
                    'replacement_subject_id' => $replacementSubjectId,
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

        if ($normativePayload) {
            DB::table($tables['form_two_normatives'])->upsert(
                $normativePayload,
                ['group_id', 'subject_id', 'teacher_id', 'month', 'year'],
                ['total_hours', 'hours_per_class', 'updated_at']
            );
        }

        if ($payload) {
            $uniqueBy = ['group_id', 'year', 'month', 'day', 'subject_id', 'mode'];

            DB::table($tables['form_two_records'])->upsert(
                $payload,
                $uniqueBy,
                [
                    'teacher_id',
                    'total_hours',
                    'hours_per_class',
                    'status',
                    'replacement_teacher_id',
                    'replacement_subject_id',
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
    }

    /**
     * Разворачиваем запись с заменой на две строки: больничный у основного и бонус у замещающего.
     */
    protected function expandReplacements(Collection $records): Collection
    {
        return $records->flatMap(function ($rec) {
            $recArr = (array) $rec;
            $recObj = (object) $recArr;

            if (($recArr['status'] ?? null) !== 'replacement') {
                return [$recObj];
            }
            $hasReplacement = (
                !empty($recArr['replacement_teacher_id'])
                && !empty($recArr['teacher_id'])
                && $recArr['teacher_id'] !== $recArr['replacement_teacher_id']
            ) || (
                !empty($recArr['replacement_subject_id'])
                && !empty($recArr['subject_id'])
                && $recArr['subject_id'] !== $recArr['replacement_subject_id']
            );

            if (!$hasReplacement) {
                return [$recObj];
            }

            $bonusHours = (int) ($recArr['bonus_hours'] ?? $recArr['hours_per_class'] ?? 0);
            $replacementSubjectId = $recArr['replacement_subject_id'] ?: $recArr['subject_id'];
            $replacementTeacherId = $recArr['replacement_teacher_id'] ?: $recArr['teacher_id'];

            $replaced = $recArr;
            $replaced['status'] = 'replaced';
            $replaced['used_hours'] = 0;
            $replaced['bonus_hours'] = 0;

            $replacement = $recArr;
            unset($replacement['id']);
            $replacement['teacher_id'] = $replacementTeacherId;
            $replacement['subject_id'] = $replacementSubjectId;
            $replacement['status'] = 'replacement';
            $replacement['used_hours'] = 0;
            $replacement['bonus_hours'] = $bonusHours;
            $replacement['replacement_teacher_id'] = $recArr['replacement_teacher_id'] ?? null;
            $replacement['replacement_subject_id'] = $recArr['replacement_subject_id'] ?? null;

            return [(object) $replaced, (object) $replacement];
        });
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
            'replacement_subject_id' => null,
            'replacement_subject_name' => null,
            'replacement_comment' => null,
            'details' => [],
        ];
    }

    protected function rowKey(int $subjectId, ?int $teacherId): string
    {
        return $subjectId . ':' . ($teacherId ?? 0);
    }

    protected function resolveStatus(string $current, object $rec): string
    {
        $incoming = $rec->status ?: (($rec->used_hours ?? 0) > 0 ? 'normal' : 'empty');
        if ($incoming === 'sick') {
            $incoming = 'replaced';
        }
        $priority = [
            'empty' => 0,
            'normal' => 1,
            'replaced' => 2,
            'sick' => 2,
            'replacement' => 3,
        ];

        return ($priority[$incoming] ?? 0) >= ($priority[$current] ?? 0) ? $incoming : $current;
    }

    protected function sortRows(array $rows, array $preferredOrder): array
    {
        $rows = array_values($rows);
        usort($rows, function (array $a, array $b) use ($preferredOrder) {
            $posA = array_search($a['subject_name'], $preferredOrder, true);
            $posB = array_search($b['subject_name'], $preferredOrder, true);
            $posA = $posA === false ? PHP_INT_MAX : $posA;
            $posB = $posB === false ? PHP_INT_MAX : $posB;

            if ($posA !== $posB) {
                return $posA <=> $posB;
            }

            if ($a['subject_name'] !== $b['subject_name']) {
                return $a['subject_name'] <=> $b['subject_name'];
            }

            return ($a['teacher_name'] ?? '') <=> ($b['teacher_name'] ?? '');
        });

        return $rows;
    }
}
