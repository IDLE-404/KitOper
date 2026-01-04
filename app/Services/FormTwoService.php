<?php

namespace App\Services;

use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormTwoService
{
    public function buildMonthReport(int $groupId, int $year, int $month, int $course = 1, array $holidayDays = []): array
    {
        $tables = CourseContext::tables($course);
        $holidayFlags = $this->normalizeHolidayDays($holidayDays);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        // Учебный год начинается 1 сентября: всё, что раньше текущего месяца в рамках учебного года,
        // считается "расходовано ранее" и должно уменьшать остаток на текущий месяц.
        $studyYearStart = $monthStart->month >= 9
            ? Carbon::create($monthStart->year, 9, 1)
            : Carbon::create($monthStart->year - 1, 9, 1);

        $daysCount = $monthStart->daysInMonth;
        $days = range(1, $daysCount);

        $subjectNames = DB::table($tables['subjects'])
            ->orderBy('name_ru')
            ->pluck(DB::raw('COALESCE(name_ru, subject_name)'), 'id');

        $normatives = DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where(function ($q) use ($year, $month, $studyYearStart) {
                $startYear = $studyYearStart->year;
                $startMonth = $studyYearStart->month;
                $q->where(function ($qStart) use ($startYear, $startMonth) {
                    $qStart->where('year', '>', $startYear)
                        ->orWhere(function ($q2) use ($startYear, $startMonth) {
                            $q2->where('year', $startYear)->where('month', '>=', $startMonth);
                        });
                })->where(function ($qEnd) use ($year, $month) {
                    $qEnd->where('year', '<', $year)
                        ->orWhere(function ($q3) use ($year, $month) {
                            $q3->where('year', $year)->where('month', '<=', $month);
                        });
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $records = DB::table($tables['form_two_records'])
            ->where('group_id', $groupId)
            ->whereBetween('class_date', [$studyYearStart->toDateString(), $monthEnd->toDateString()])
            ->get();
        $records = $this->expandReplacements($records);
        $recordsMain = $records->filter(function ($rec) {
            return $this->subgroupValue($rec) !== 2;
        })->values();
        $recordsSubgroupTwo = $records->filter(function ($rec) {
            return $this->subgroupValue($rec) === 2;
        })->values();

        $teacherIds = $normatives->pluck('teacher_id')
            ->merge($records->pluck('teacher_id'))
            ->merge($records->pluck('replacement_teacher_id'))
            ->filter()
            ->unique();

        $teachers = DB::table($tables['teachers'])
            ->whereIn('id', $teacherIds)
            ->pluck('teacher_name', 'id');

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

        $mainReport = $this->buildReportData(
            $recordsMain,
            $normatives,
            $subjectNames,
            $teachers,
            $days,
            $holidayFlags,
            $monthStart,
            $monthEnd,
            $studyYearStart,
            $year,
            $month,
            $daysCount,
            $preferredOrder,
            true
        );
        $sortedReplacements = $this->sortReplacementRows($mainReport['replacement_rows']);
        $report = [
            'days' => $days,
            'rows' => $mainReport['rows'],
            'replacement_rows' => $sortedReplacements,
            'replacement_table_rows' => $this->buildReplacementTableRows($sortedReplacements, $days),
        ];

        $report['totals'] = $mainReport['totals'];

        $subgroupTwoReport = $this->buildReportData(
            $recordsSubgroupTwo,
            $normatives,
            $subjectNames,
            $teachers,
            $days,
            $holidayFlags,
            $monthStart,
            $monthEnd,
            $studyYearStart,
            $year,
            $month,
            $daysCount,
            $preferredOrder,
            false
        );
        $report['subgroup_two_rows'] = $subgroupTwoReport['rows'];
        $report['subgroup_two_totals'] = $subgroupTwoReport['totals'];

        return $report;
    }

    protected function subgroupValue(object $rec): int
    {
        $value = (int) ($rec->subgroup ?? 1);
        return $value ?: 1;
    }

    protected function buildReportData(
        Collection $records,
        Collection $normatives,
        Collection $subjectNames,
        Collection $teachers,
        array $days,
        array $holidayFlags,
        Carbon $monthStart,
        Carbon $monthEnd,
        Carbon $studyYearStart,
        int $year,
        int $month,
        int $daysCount,
        array $preferredOrder,
        bool $includeEmptySubjects
    ): array {
        $rows = [];
        $replacementRows = [];
        $normMap = $this->buildNormativeLookup($normatives);
        $subjectsUsed = [];
        $spentBefore = [];
        $spentCurrent = [];
        if ($includeEmptySubjects) {
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
        }

        /** @var object $rec */
        foreach ($records as $rec) {
            $subjectId = $rec->subject_id;
            $teacherId = $rec->teacher_id;
            if (!$subjectId) {
                continue;
            }

            $recordDate = $rec->class_date
                ? Carbon::parse($rec->class_date)
                : Carbon::create((int) ($rec->year ?? $year), (int) ($rec->month ?? $month), (int) ($rec->day ?? 1));
            $day = $this->resolveRecordDay($rec, $recordDate, $daysCount);
            if ($day === null) {
                continue;
            }
            $inCurrentMonth = $recordDate->between($monthStart, $monthEnd);

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

            $usedHoursValue = (int) ($rec->used_hours ?? 0);
            $bonusHoursValue = (int) ($rec->bonus_hours ?? 0);
            $spentDelta = $usedHoursValue - $bonusHoursValue;

            if ($inCurrentMonth) {
                if (isset($holidayFlags[$day])) {
                    continue;
                }
                $spentCurrent[$key] = ($spentCurrent[$key] ?? 0) + $spentDelta;
            } else {
                $spentBefore[$key] = ($spentBefore[$key] ?? 0) + $spentDelta;
                continue;
            }

            if (
                $rec->status === 'replaced'
                && $rec->replacement_teacher_id
                && (!$rec->replacement_subject_id || $rec->replacement_subject_id === $rec->subject_id)
            ) {
                $replacementRows[] = $this->buildReplacementRow(
                    $rec,
                    $recordDate,
                    $day,
                    $subjectNames,
                    $teachers
                );
            }

            $dayData = $rows[$key]['days'][$day] ?? $this->emptyDay();
            $dayData['used_hours'] += $usedHoursValue;
            $dayData['bonus_hours'] += $bonusHoursValue;

            // Определяем приоритет входящего статуса
            $incomingStatus = $rec->status ?: (($rec->used_hours ?? 0) > 0 ? 'normal' : 'empty');
            if ($incomingStatus === 'sick') {
                $incomingStatus = 'replaced';
            }
            $statusPriority = [
                'empty' => 0,
                'normal' => 1,
                'replaced' => 2,
                'sick' => 2,
                'replacement' => 3,
            ];
            $currentStatus = $dayData['status'] ?? 'empty';
            $currentPriority = $statusPriority[$currentStatus] ?? 0;
            $incomingPriority = $statusPriority[$incomingStatus] ?? 0;

            // Определяем итоговый статус (используем resolveStatus для совместимости)
            $newStatus = $this->resolveStatus($currentStatus, $rec);

            // КРИТИЧЕСКИ ВАЖНО: Данные о замене должны браться из записи, которая "победила" по статусу
            // Если новый статус основан на входящей записи (incomingPriority >= currentPriority),
            // берём данные о замене из входящей записи
            // Это гарантирует, что данные о замене не потеряются, если запись с replaced обрабатывается первой
            if ($incomingPriority >= $currentPriority) {
                // Входящий статус имеет более высокий или равный приоритет - он "победит"
                // Обновляем данные о замене из входящей записи (даже если они null)
                // Это важно, так как запись с replaced может идти первой в цикле
                $dayData['replacement_teacher_id'] = $rec->replacement_teacher_id;
                $dayData['replacement_teacher_name'] = $rec->replacement_teacher_id
                    ? ($teachers[$rec->replacement_teacher_id] ?? '—')
                    : null;
                $dayData['replacement_subject_id'] = $rec->replacement_subject_id;
                $dayData['replacement_subject_name'] = $rec->replacement_subject_id
                    ? ($subjectNames[$rec->replacement_subject_id] ?? '—')
                    : null;
                $dayData['replacement_comment'] = $rec->replacement_comment;
            }
            // Если currentPriority > incomingPriority, существующие данные о замене сохраняются

            $dayData['status'] = $newStatus;
            $dayData['mode'] = $rec->mode ?? $dayData['mode'];
            $dayData['lesson_number'] = $rec->lesson_number ?? $dayData['lesson_number'];
            $dayData['subgroup'] = $rec->subgroup ?? $dayData['subgroup'];
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
            $key = $this->rowKey($row['subject_id'], $row['teacher_id']);
            $spentBeforeTotal = $spentBefore[$key] ?? 0;
            $spentCurrentTotal = $spentCurrent[$key] ?? ($used - $bonus);
            $startLeft = max(0, (int) $row['total_hours'] - $spentBeforeTotal);
            $endLeft = max(0, $startLeft - $spentCurrentTotal);
            $row['hours_left_start'] = $startLeft;
            $row['hours_left'] = $endLeft;
        }

        // Добавляем пустые строки для предметов, по которым нет нормативов и записей
        if ($includeEmptySubjects) {
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
        }

        $rows = $this->sortRows($rows, $preferredOrder);
        $totals = $this->calculateTotals($rows, $days);

        return [
            'rows' => $rows,
            'replacement_rows' => $replacementRows,
            'totals' => $totals,
        ];
    }

    /**
     * Ручная коррекция (используется редко). Ограничиваемся изменением статуса/комментария.
     */
    public function saveMonthRecords(int $groupId, int $year, int $month, array $rows, int $course = 1, array $holidayDays = []): void
    {
        $tables = CourseContext::tables($course);
        $payload = [];
        $normativePayload = [];
        $now = now();
        $holidayFlags = $this->normalizeHolidayDays($holidayDays);

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
                $dayIndex = (int) $day;
                if (isset($holidayFlags[$dayIndex])) {
                    continue;
                }
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
            $teacherKey = $norm->teacher_id ?? 0;
            // Нормативы отсортированы от новых к старым; заполняем только первый раз, чтобы брать свежие данные.
            if (isset($map[$norm->subject_id][$teacherKey])) {
                continue;
            }
            $map[$norm->subject_id][$teacherKey] = [
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
            'hours_left_start' => $totalHours,
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

    protected function buildReplacementRow(
        object $rec,
        Carbon $recordDate,
        int $day,
        Collection $subjectNames,
        Collection $teachers
    ): array {
        $subjectName = $subjectNames[$rec->subject_id] ?? '—';
        $teacherName = $rec->teacher_id ? ($teachers[$rec->teacher_id] ?? '—') : '—';
        $replacementTeacherName = $rec->replacement_teacher_id ? ($teachers[$rec->replacement_teacher_id] ?? '—') : null;
        $replacementSubjectName = null;

        if (!empty($rec->replacement_subject_id) && $rec->replacement_subject_id !== $rec->subject_id) {
            $replacementSubjectName = $subjectNames[$rec->replacement_subject_id] ?? '—';
        }

        $hoursPerClass = (int) ($rec->hours_per_class ?? 2);
        $replacementHours = (int) ($rec->bonus_hours ?? $hoursPerClass);

        return [
            'class_date' => $recordDate->toDateString(),
            'class_date_label' => $recordDate->format('d.m.Y'),
            'day' => $day,
            'lesson_number' => $rec->lesson_number,
            'subgroup' => $rec->subgroup ?? 1,
            'mode' => $rec->mode ?? 'single',
            'subject_id' => $rec->subject_id,
            'teacher_id' => $rec->teacher_id,
            'subject_name' => $subjectName,
            'teacher_name' => $teacherName,
            'replacement_teacher_name' => $replacementTeacherName,
            'replacement_teacher_id' => $rec->replacement_teacher_id,
            'replacement_subject_id' => $rec->replacement_subject_id,
            'replacement_subject_name' => $replacementSubjectName,
            'comment' => (string) ($rec->replacement_comment ?? ''),
            'total_hours' => (int) ($rec->total_hours ?? 0),
            'hours_per_class' => $hoursPerClass,
            'replacement_hours' => $replacementHours,
            'used_hours_total' => 0,
            'bonus_hours_total' => 0,
            'hours_left' => 0,
            'status' => 'replacement',
        ];
    }

    protected function sortReplacementRows(array $rows): array
    {
        usort($rows, function (array $a, array $b) {
            $dateA = $a['class_date'] ?? '';
            $dateB = $b['class_date'] ?? '';
            if ($dateA !== $dateB) {
                return strcmp($dateA, $dateB);
            }

            $lessonA = (int) ($a['lesson_number'] ?? 0);
            $lessonB = (int) ($b['lesson_number'] ?? 0);
            if ($lessonA !== $lessonB) {
                return $lessonA <=> $lessonB;
            }

            $subA = (int) ($a['subgroup'] ?? 1);
            $subB = (int) ($b['subgroup'] ?? 1);
            if ($subA !== $subB) {
                return $subA <=> $subB;
            }

            return strcmp($a['subject_name'] ?? '', $b['subject_name'] ?? '');
        });

        return $rows;
    }

    protected function buildReplacementTableRows(array $replacementRows, array $days): array
    {
        $template = [];
        foreach ($days as $day) {
            $template[(int) $day] = [
                'status' => 'empty',
                'value' => '',
                'replacement_teacher_name' => null,
            ];
        }

        $rows = [];
        foreach ($replacementRows as $replacement) {
            $replacementTeacherId = $replacement['replacement_teacher_id'] ?? null;
            $key = $this->rowKey($replacement['subject_id'] ?? 0, $replacementTeacherId);
            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'subject_id' => $replacement['subject_id'] ?? null,
                    'teacher_id' => $replacementTeacherId,
                    'subject_name' => $replacement['subject_name'] ?? '—',
                    'teacher_name' => $replacement['replacement_teacher_name'] ?? '—',
                    'total_hours' => $replacement['total_hours'] ?? 0,
                    'hours_per_class' => $replacement['hours_per_class'] ?? 0,
                    'days' => $template,
                    'used_hours_total' => 0,
                    'bonus_hours_total' => 0,
                    'hours_left' => 0,
                    'hours_left_start' => $replacement['total_hours'] ?? 0,
                ];
            }

            $day = (int) ($replacement['day'] ?? 0);
            if (!$day || !isset($rows[$key]['days'][$day])) {
                continue;
            }

            $value = $replacement['replacement_hours'] ? (string) $replacement['replacement_hours'] : 'З';
            $rows[$key]['days'][$day] = [
                'status' => 'replacement',
                'value' => $value,
                'replacement_teacher_name' => $replacement['replacement_teacher_name'],
            ];

            $rows[$key]['bonus_hours_total'] += $replacement['replacement_hours'] ?? 0;
        }

        $result = array_values($rows);
        usort($result, function (array $a, array $b) {
            return ($a['subject_name'] ?? '') <=> ($b['subject_name'] ?? '');
        });

        return $result;
    }

    public function calculateTotals(array $rows, array $days): array
    {
        $dayTotals = [];
        foreach ($days as $day) {
            $dayTotals[$day] = 0;
        }

        $columnTotals = [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];

        foreach ($rows as $row) {
            $columnTotals['normative'] += (int) ($row['total_hours'] ?? 0);
            $columnTotals['used'] += (int) ($row['used_hours_total'] ?? 0);
            $columnTotals['bonus'] += (int) ($row['bonus_hours_total'] ?? 0);
            $columnTotals['left'] += (int) ($row['hours_left'] ?? 0);

            foreach ($row['days'] as $day => $cell) {
                if (array_key_exists($day, $dayTotals)) {
                    $dayTotals[$day] += (int) ($cell['used_hours'] ?? 0);
                }
            }
        }

        return [
            'day_totals' => $dayTotals,
            'column_totals' => $columnTotals,
        ];
    }

    protected function normalizeHolidayDays(array $holidayDays): array
    {
        $flags = [];
        foreach ($holidayDays as $day => $meta) {
            $dayIndex = (int) $day;
            if ($dayIndex < 1 || $dayIndex > 31) {
                continue;
            }
            $flags[$dayIndex] = true;
        }
        return $flags;
    }

    protected function resolveRecordDay(object $rec, Carbon $recordDate, int $daysCount): ?int
    {
        $day = (int) ($rec->day ?? 0);
        if ($day < 1 || $day > $daysCount) {
            $day = (int) $recordDate->day;
        }
        if ($day < 1 || $day > $daysCount) {
            return null;
        }
        return $day;
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
