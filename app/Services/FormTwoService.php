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

        $groupRow = DB::table($tables['groups'])
            ->select('group_name')
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn($tables['groups'], 'group_type'),
                fn ($q) => $q->addSelect('group_type')
            )
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn($tables['groups'], 'has_subgroups'),
                fn ($q) => $q->addSelect('has_subgroups')
            )
            ->where('id', $groupId)
            ->first();
        $groupType = $groupRow->group_type ?? null;
        if ($groupType === 'ru') {
            $useKazakh = false;
        } elseif ($groupType === 'kz') {
            $useKazakh = true;
        } else {
            $groupName = $groupRow->group_name ?? '';
            $useKazakh = (bool) preg_match('/[ҚқӘәҢңӨөҰұҮүІіҺһҒғ]/u', (string) $groupName);
        }
        $hasSubgroups = (bool) ($groupRow->has_subgroups ?? false);
        $subjectNames = DB::table($tables['subjects'])
            ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
            ->orderByRaw('COALESCE(name_ru, subject_name)')
            ->get()
            ->mapWithKeys(function ($row) use ($course, $useKazakh) {
                $name = $useKazakh
                    ? ($row->name_kz ?: ($row->name_ru ?: $row->subject_name))
                    : ($row->name_ru ?: ($row->name_kz ?: $row->subject_name));
                if ($course !== 1) {
                    $title = $this->formatSecondCourseTitle((string) ($row->subject_name ?? ''), $name);
                } else {
                    $title = $name;
                }
                return [$row->id => $title];
            });

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
            ->select('id', DB::raw('COALESCE(initials, teacher_name) as display_name'))
            ->pluck('display_name', 'id');

        $kzOrder = [
            'Қазақ тілі',
            'Қазақ әдебиеті',
            'Орыс тілі және әдәбиеті',
            'Шетел тілі',
            'Математика',
            'Информатика',
            'Қазақстан тарихы',
            'Дене тәрбиесі',
            'Бастапқы әскери және технологиялық дайындық',
            'Физика',
            'Химия',
            'Биология',
            'География',
            'Графика және жобалау',
            'Дүние жүзі тарихы',
        ];
        $ruOrder = [
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
            'Глобальные компетенции',
        ];
        $kzGlobalCompetencies = 'Ғаламдық құзыреттер';
        $kzOrderWithGlobal = array_merge($kzOrder, [$kzGlobalCompetencies]);
        $customTemplate = $this->resolveStoredTemplate($course, (string) ($groupRow->group_name ?? ''));
        if ($customTemplate && !empty($customTemplate['order'])) {
            $preferredOrder = $customTemplate['order'];
        } else {
            $preferredOrder = $this->resolveSpecialtyOrder(
                $groupRow->group_name ?? '',
                $useKazakh,
                $ruOrder,
                $kzOrder,
                $kzOrderWithGlobal,
                $course
            )
                ?? ($useKazakh ? $kzOrder : $ruOrder);
            if ($course === 1 && $this->groupHasToken($groupRow->group_name ?? '', ['БҚЕ', 'БКЕ', 'BKE'])) {
                $globalCandidates = [
                    'Ғаламдық құзыреттер',
                    'Жаһандық құзыреттілік',
                    'Глобальные компетенции',
                ];
                $targetGlobal = null;
                foreach ($globalCandidates as $candidate) {
                    if ($this->subjectNamesContain($subjectNames, $candidate)) {
                        $targetGlobal = $candidate;
                        break;
                    }
                }
                if ($targetGlobal) {
                    $ignoreKeys = array_map(fn (string $name): string => $this->normalizeOrderName($name), $globalCandidates);
                    $preferredOrder = array_values(array_filter($preferredOrder, function (string $name) use ($ignoreKeys): bool {
                        return !in_array($this->normalizeOrderName($name), $ignoreKeys, true);
                    }));
                    $preferredOrder[] = $targetGlobal;
                }
            }
        }

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
        $allowedOrder = $preferredOrder;
        $allowedNormalized = [];
        foreach ($allowedOrder as $name) {
            $key = $this->normalizeOrderName($name);
            if ($key !== '') {
                $allowedNormalized[$key] = true;
            }
        }
        $mainReport['rows'] = array_values(array_filter($mainReport['rows'], function (array $row) use ($allowedOrder, $allowedNormalized): bool {
            $subjectName = $row['subject_name'] ?? '';
            if (in_array($subjectName, $allowedOrder, true)) {
                return true;
            }
            $key = $this->normalizeOrderName($subjectName);
            return $key !== '' && isset($allowedNormalized[$key]);
        }));
        $mainReport['rows'] = $this->collapseNullTeacherRows($mainReport['rows']);
        $mainReport['replacement_rows'] = array_values(array_filter($mainReport['replacement_rows'], function (array $row) use ($allowedOrder, $allowedNormalized): bool {
            $subjectName = $row['subject_name'] ?? '';
            if (in_array($subjectName, $allowedOrder, true)) {
                return true;
            }
            $key = $this->normalizeOrderName($subjectName);
            return $key !== '' && isset($allowedNormalized[$key]);
        }));
        $mainReport['totals'] = $this->calculateTotals($mainReport['rows'], $days);

        $sortedReplacements = $this->sortReplacementRows($mainReport['replacement_rows']);
        $report = [
            'days' => $days,
            'rows' => $mainReport['rows'],
            'replacement_rows' => $sortedReplacements,
            'replacement_table_rows' => $this->buildReplacementTableRows($sortedReplacements, $days),
        ];

        $report['totals'] = $mainReport['totals'];

        if ($hasSubgroups) {
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
            // В подвоении показываем только строки с активностью в текущем месяце.
            $filteredSubgroupTwoRows = $this->filterRowsByActivity($subgroupTwoReport['rows'], $days);
            $filteredSubgroupTwoRows = array_values(array_filter($filteredSubgroupTwoRows, function (array $row) use ($allowedOrder, $allowedNormalized): bool {
                $subjectName = $row['subject_name'] ?? '';
                if (in_array($subjectName, $allowedOrder, true)) {
                    return true;
                }
                $key = $this->normalizeOrderName($subjectName);
                return $key !== '' && isset($allowedNormalized[$key]);
            }));
            if ($customTemplate && !empty($customTemplate['subgroup_two'])) {
                $subgroupTwoRows = $this->applyStoredSubgroupTwoTemplate(
                    $filteredSubgroupTwoRows,
                    $normatives,
                    $subjectNames,
                    $teachers,
                    $days,
                    $customTemplate['subgroup_two']
                );
            } else {
                $subgroupTwoRows = $this->applyFirstCourseSubgroupTwoTemplate(
                    $filteredSubgroupTwoRows,
                    $normatives,
                    $subjectNames,
                    $teachers,
                    $days,
                    $useKazakh,
                    $course
                );
                $subgroupTwoRows = $this->applySecondCourseSubgroupTwoTemplate(
                    $subgroupTwoRows,
                    $normatives,
                    $subjectNames,
                    $teachers,
                    $days,
                    (string) ($groupRow->group_name ?? ''),
                    $course
                );
            }
            $report['subgroup_two_rows'] = $subgroupTwoRows;
            $report['subgroup_two_totals'] = $this->calculateTotals($subgroupTwoRows, $days);
        } else {
            $report['subgroup_two_rows'] = [];
            $report['subgroup_two_totals'] = $this->calculateTotals([], $days);
        }

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
    public function saveMonthRecords(
        int $groupId,
        int $year,
        int $month,
        array $rows,
        int $course = 1,
        array $holidayDays = [],
        array $replacementNormatives = [],
        array $subgroupTwoNormatives = []
    ): void
    {
        $tables = CourseContext::tables($course);
        $payload = [];
        $normativePayload = [];
        $now = now();
        $holidayFlags = $this->normalizeHolidayDays($holidayDays);
        $rowSubjectIds = [];

        foreach ($rows as $row) {
            $subjectId = $row['subject_id'] ?? null;
            if (!$subjectId) {
                continue;
            }
            $rowSubjectIds[(int) $subjectId] = true;
            $teacherId = $row['teacher_id'] ?? null;
            $totalHours = $row['total_hours'] ?? 0;
            $hoursPerClass = $row['hours_per_class'] ?? 2;
            $days = $row['days'] ?? [];

            // Сохраняем норматив отдельно, даже если по дням пока пусто
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
            $this->persistNormatives($tables['form_two_normatives'], $normativePayload);
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

        $extraNormatives = array_merge($replacementNormatives, $subgroupTwoNormatives);
        if ($extraNormatives) {
            $extraPayload = [];
            foreach ($extraNormatives as $row) {
                $subjectId = $row['subject_id'] ?? null;
                if (!$subjectId) {
                    continue;
                }
                if (isset($rowSubjectIds[(int) $subjectId])) {
                    continue;
                }
                $teacherId = $row['teacher_id'] ?? null;
                $extraPayload[] = [
                    'group_id' => $groupId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'month' => $month,
                    'year' => $year,
                    'total_hours' => (int) ($row['total_hours'] ?? 0),
                    'hours_per_class' => (int) ($row['hours_per_class'] ?? 2),
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
            if ($extraPayload) {
                $this->persistNormatives($tables['form_two_normatives'], $extraPayload);
            }
        }
    }

    protected function persistNormatives(string $table, array $payload): void
    {
        $withTeacher = [];
        $withoutTeacher = [];
        foreach ($payload as $row) {
            if (!empty($row['teacher_id'])) {
                $withTeacher[] = $row;
            } else {
                $key = $row['group_id'] . '-' . $row['subject_id'] . '-' . $row['month'] . '-' . $row['year'];
                $withoutTeacher[$key] = $row;
            }
        }

        if ($withTeacher) {
            DB::table($table)->upsert(
                $withTeacher,
                ['group_id', 'subject_id', 'teacher_id', 'month', 'year'],
                ['total_hours', 'hours_per_class', 'updated_at']
            );
        }

        if ($withoutTeacher) {
            foreach ($withoutTeacher as $row) {
                DB::table($table)
                    ->where('group_id', $row['group_id'])
                    ->where('subject_id', $row['subject_id'])
                    ->where('month', $row['month'])
                    ->where('year', $row['year'])
                    ->whereNull('teacher_id')
                    ->delete();
            }
            DB::table($table)->insert(array_values($withoutTeacher));
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
                'bonus_hours' => 0,
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
                    'total_hours' => 0,
                    'hours_per_class' => $replacement['hours_per_class'] ?? 2,
                    'days' => $template,
                    'used_hours_total' => 0,
                    'bonus_hours_total' => 0,
                    'hours_left' => 0,
                    'hours_left_start' => 0,
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
                'bonus_hours' => (int) ($replacement['replacement_hours'] ?? 0),
            ];

            $replacementHours = (int) ($replacement['replacement_hours'] ?? 0);
            $rows[$key]['bonus_hours_total'] += $replacementHours;
            $rows[$key]['total_hours'] += $replacementHours;
            $rows[$key]['hours_left_start'] = $rows[$key]['total_hours'];
        }

        foreach ($rows as &$row) {
            $row['hours_left'] = max(0, (int) ($row['total_hours'] ?? 0) - (int) ($row['bonus_hours_total'] ?? 0));
        }
        unset($row);

        $result = array_values($rows);
        usort($result, function (array $a, array $b) {
            return ($a['subject_name'] ?? '') <=> ($b['subject_name'] ?? '');
        });

        return $result;
    }

    public function calculateReplacementTotals(array $rows, array $days): array
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
                    $dayTotals[$day] += (int) ($cell['bonus_hours'] ?? 0);
                }
            }
        }

        return [
            'day_totals' => $dayTotals,
            'column_totals' => $columnTotals,
        ];
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
        $orderNormalized = [];
        foreach ($preferredOrder as $idx => $name) {
            $orderNormalized[$this->normalizeOrderName($name)] = $idx;
        }

        usort($rows, function (array $a, array $b) use ($preferredOrder, $orderNormalized) {
            $posA = array_search($a['subject_name'], $preferredOrder, true);
            $posB = array_search($b['subject_name'], $preferredOrder, true);
            if ($posA === false) {
                $posA = $orderNormalized[$this->normalizeOrderName($a['subject_name'] ?? '')] ?? PHP_INT_MAX;
            }
            if ($posB === false) {
                $posB = $orderNormalized[$this->normalizeOrderName($b['subject_name'] ?? '')] ?? PHP_INT_MAX;
            }

            if ($posA !== $posB) {
                return $posA <=> $posB;
            }

            if (($a['subject_name'] ?? '') !== ($b['subject_name'] ?? '')) {
                return ($a['subject_name'] ?? '') <=> ($b['subject_name'] ?? '');
            }

            return ($a['teacher_name'] ?? '') <=> ($b['teacher_name'] ?? '');
        });

        return $rows;
    }

    protected function normalizeOrderName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\\s+/u', ' ', $value);
        $value = preg_replace('/^(?:ООМ|ПМ|БМ|РО|PO)\\s*\\d+(?:\\.\\d+)?\\.?\\s+/iu', '', $value);
        $value = rtrim($value, '. ');
        $value = str_ireplace('видеонабледния', 'видеонаблюдения', $value);
        $value = str_replace(['ё', 'Ё'], ['е', 'Е'], $value);
        return mb_strtolower($value);
    }

    protected function formatSecondCourseTitle(string $subjectName, string $fallbackName): string
    {
        $subjectName = trim($subjectName);
        $fallbackName = trim($fallbackName);
        if ($subjectName === '' && $fallbackName === '') {
            return '';
        }

        $code = null;
        if (preg_match('/^(РО|PO)\\s*\\d+(?:\\.\\d+)?/iu', $subjectName, $matches) === 1) {
            $code = mb_strtoupper($matches[0], 'UTF-8');
        }

        $name = $fallbackName !== '' ? $fallbackName : $subjectName;
        if ($code === null) {
            return $name;
        }

        return rtrim($code, '. ') . '. ' . ltrim($name, '. ');
    }

    protected function filterRowsByActivity(array $rows, array $days): array
    {
        return array_values(array_filter($rows, function (array $row) use ($days) {
            foreach ($days as $day) {
                $cell = $row['days'][$day] ?? null;
                if (!$cell) {
                    continue;
                }
                if (($cell['status'] ?? 'empty') !== 'empty') {
                    return true;
                }
            }
            return false;
        }));
    }

    protected function collapseNullTeacherRows(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $subjectId = $row['subject_id'] ?? null;
            if (!$subjectId) {
                $grouped[] = [$row];
                continue;
            }
            $grouped[$subjectId][] = $row;
        }

        $result = [];
        foreach ($grouped as $subjectRows) {
            if (!is_array($subjectRows)) {
                $result[] = $subjectRows;
                continue;
            }
            $nullIndex = null;
            $teacherRows = [];
            foreach ($subjectRows as $index => $row) {
                if (empty($row['teacher_id'])) {
                    $nullIndex = $index;
                    continue;
                }
                $teacherRows[] = $index;
            }

            if ($nullIndex !== null && count($teacherRows) >= 1) {
                if (count($teacherRows) === 1) {
                    $targetIndex = $teacherRows[0];
                    $target = $subjectRows[$targetIndex];
                    $nullRow = $subjectRows[$nullIndex];
                    $target['total_hours'] = max((int) ($target['total_hours'] ?? 0), (int) ($nullRow['total_hours'] ?? 0));
                    $target['hours_per_class'] = $target['hours_per_class'] ?? $nullRow['hours_per_class'] ?? 2;
                    $target['hours_left_start'] = max(
                        (int) ($target['hours_left_start'] ?? 0),
                        (int) ($nullRow['hours_left_start'] ?? 0)
                    );
                    $used = (int) ($target['used_hours_total'] ?? 0);
                    $bonus = (int) ($target['bonus_hours_total'] ?? 0);
                    $target['hours_left'] = max(0, (int) $target['hours_left_start'] - ($used - $bonus));
                    $subjectRows[$targetIndex] = $target;
                }
                unset($subjectRows[$nullIndex]);
            }

            foreach ($subjectRows as $row) {
                $result[] = $row;
            }
        }

        return $result;
    }

    protected function applyFirstCourseSubgroupTwoTemplate(
        array $rows,
        Collection $normatives,
        Collection $subjectNames,
        Collection $teachers,
        array $days,
        bool $useKazakh,
        int $course
    ): array {
        if ($course !== 1) {
            return $rows;
        }

        $desiredSubjects = $useKazakh
            ? [
                'Орыс тілі және әдәбиеті',
                'Шетел тілі',
                'Информатика',
                'Дене тәрбиесі',
            ]
            : [
                'Казахский язык и литература',
                'Иностранный язык',
                'Информатика',
                'Физическая культура',
            ];

        $bySubject = [];
        foreach ($rows as $row) {
            $name = $row['subject_name'] ?? '';
            if ($name !== '') {
                $bySubject[$name] = $row;
            }
        }

        $subjectIdByName = array_flip($subjectNames->all());
        $normMap = $this->buildNormativeLookup($normatives);
        $result = [];

        foreach ($desiredSubjects as $subjectName) {
            if (!empty($bySubject[$subjectName])) {
                $result[] = $bySubject[$subjectName];
                continue;
            }

            $subjectId = $subjectIdByName[$subjectName] ?? null;
            if (!$subjectId) {
                continue;
            }

            $norm = $this->matchNormative($normMap, (int) $subjectId, null);
            $result[] = $this->emptyRow(
                (int) $subjectId,
                null,
                (int) ($norm['total_hours'] ?? 0),
                (int) ($norm['hours_per_class'] ?? 2),
                $days,
                $subjectNames,
                $teachers
            );
        }

        return $result;
    }

    protected function applySecondCourseSubgroupTwoTemplate(
        array $rows,
        Collection $normatives,
        Collection $subjectNames,
        Collection $teachers,
        array $days,
        string $groupName,
        int $course
    ): array {
        if ($course !== 2) {
            return $rows;
        }

        $upper = mb_strtoupper(trim($groupName), 'UTF-8');
        if ($upper === '') {
            return $rows;
        }

        $tokens = preg_split('/[\\s\\-\\/]+/u', $upper, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $isSib = mb_strpos($upper, 'СИБ') !== false;
        $isAkzh = mb_strpos($upper, 'АКЖ') !== false || mb_strpos($upper, 'АҚЖ') !== false;
        $isM = false;
        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, ['М', 'M'])) {
                $isM = true;
                break;
            }
        }
        $isBke = false;
        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, ['БҚЕ', 'БКЕ', 'BKE'])) {
                $isBke = true;
                break;
            }
        }
        $isPo = false;
        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, ['ПО'])) {
                $isPo = true;
                break;
            }
        }
        if (!$isSib && !$isAkzh && !$isPo && !$isBke && !$isM) {
            return $rows;
        }

        if ($isPo) {
            $desiredSubjects = [
                'Укреплять здоровье и соблюдать принципы здорового образа жизни',
                'Применять терминологию на государственном языке при разработке и администрировании web-ресурсов',
                'Применять иностранную терминологию при разработке и администрировании web-ресурсов',
            ];
        } elseif ($isM) {
            $desiredSubjects = [
                'Укреплять здоровье и соблюдать принципы здорового образа жизни',
            ];
        } elseif ($isBke) {
            $desiredSubjects = [
                'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
            ];
        } else {
            $desiredSubjects = $isSib
                ? [
                'Укреплять здоровье и соблюдать принципы здорового образа жизни',
                'Конфигурировать сетевые сервисы и сетевое оборудование',
                ]
                : [
                'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
                'Желілік қызметтер мен желілік жабдықты конфигурациялау',
                ];
        }

        $bySubject = [];
        foreach ($rows as $row) {
            $name = $row['subject_name'] ?? '';
            if ($name !== '') {
                $bySubject[$this->normalizeOrderName($name)] = $row;
            }
        }

        $subjectIdByName = [];
        foreach ($subjectNames->all() as $id => $name) {
            $key = $this->normalizeOrderName((string) $name);
            if ($key !== '') {
                $subjectIdByName[$key] = (int) $id;
            }
        }

        $normMap = $this->buildNormativeLookup($normatives);
        $result = $rows;

        foreach ($desiredSubjects as $subjectName) {
            $key = $this->normalizeOrderName($subjectName);
            if ($key === '') {
                continue;
            }
            if (!empty($bySubject[$key])) {
                continue;
            }

            $subjectId = $subjectIdByName[$key] ?? null;
            if (!$subjectId) {
                continue;
            }

            $norm = $this->matchNormative($normMap, (int) $subjectId, null);
            $result[] = $this->emptyRow(
                (int) $subjectId,
                null,
                (int) ($norm['total_hours'] ?? 0),
                (int) ($norm['hours_per_class'] ?? 2),
                $days,
                $subjectNames,
                $teachers
            );
        }

        return $result;
    }

    protected function resolveStoredTemplate(int $course, string $groupName): ?array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('form_two_templates')
            || !\Illuminate\Support\Facades\Schema::hasTable('form_two_template_items')) {
            return null;
        }

        $groupTokens = preg_split('/[\\s\\-\\/]+/u', mb_strtoupper(trim($groupName), 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$groupTokens) {
            return null;
        }

        $templates = DB::table('form_two_templates')
            ->where('course', $course)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        foreach ($templates as $template) {
            $templateTokens = $this->parseTemplateTokens((string) ($template->group_tokens ?? ''));
            if (!$templateTokens) {
                continue;
            }
            if (!$this->groupMatchesTemplateTokens($groupTokens, $templateTokens)) {
                continue;
            }

            $items = DB::table('form_two_template_items')
                ->where('template_id', $template->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $order = [];
            $subgroupTwo = [];
            foreach ($items as $item) {
                $subject = trim((string) ($item->subject_name ?? ''));
                if ($subject === '') {
                    continue;
                }
                $order[] = $subject;
                if ((bool) ($item->include_subgroup_two ?? false)) {
                    $subgroupTwo[] = $subject;
                }
            }

            return [
                'order' => array_values(array_unique($order)),
                'subgroup_two' => array_values(array_unique($subgroupTwo)),
            ];
        }

        return null;
    }

    protected function parseTemplateTokens(string $raw): array
    {
        $parts = preg_split('/[,;\\s]+/u', mb_strtoupper($raw, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_values(array_unique(array_map('trim', $parts)));
        return array_values(array_filter($parts, fn (string $part): bool => $part !== ''));
    }

    protected function groupMatchesTemplateTokens(array $groupTokens, array $templateTokens): bool
    {
        foreach ($groupTokens as $groupToken) {
            if ($this->tokenMatchesPrefix($groupToken, $templateTokens)) {
                return true;
            }
        }

        return false;
    }

    protected function applyStoredSubgroupTwoTemplate(
        array $rows,
        Collection $normatives,
        Collection $subjectNames,
        Collection $teachers,
        array $days,
        array $desiredSubjects
    ): array {
        if (!$desiredSubjects) {
            return [];
        }

        $bySubject = [];
        foreach ($rows as $row) {
            $name = $row['subject_name'] ?? '';
            if ($name !== '') {
                $bySubject[$this->normalizeOrderName($name)] = $row;
            }
        }

        $subjectIdByName = [];
        foreach ($subjectNames->all() as $id => $name) {
            $key = $this->normalizeOrderName((string) $name);
            if ($key !== '') {
                $subjectIdByName[$key] = (int) $id;
            }
        }

        $normMap = $this->buildNormativeLookup($normatives);
        $result = [];

        foreach ($desiredSubjects as $subjectName) {
            $key = $this->normalizeOrderName((string) $subjectName);
            if ($key === '') {
                continue;
            }
            if (!empty($bySubject[$key])) {
                $result[] = $bySubject[$key];
                continue;
            }

            $subjectId = $subjectIdByName[$key] ?? null;
            if (!$subjectId) {
                continue;
            }

            $norm = $this->matchNormative($normMap, (int) $subjectId, null);
            $result[] = $this->emptyRow(
                (int) $subjectId,
                null,
                (int) ($norm['total_hours'] ?? 0),
                (int) ($norm['hours_per_class'] ?? 2),
                $days,
                $subjectNames,
                $teachers
            );
        }

        return $result;
    }

    protected function resolveSpecialtyOrder(
        string $groupName,
        bool $useKazakh,
        array $ruOrder,
        array $kzOrder,
        array $kzOrderWithGlobal,
        int $course
    ): ?array
    {
        $name = trim($groupName);
        if ($name === '') {
            return null;
        }

        $upper = mb_strtoupper($name, 'UTF-8');
        $tokens = preg_split('/[\\s\\-\\/]+/u', $upper, -1, PREG_SPLIT_NO_EMPTY);
        if (!$tokens) {
            return null;
        }

        $special15 = ['М', 'СИБ', 'АҚЖ'];
        $special16 = ['ПО', 'БҚЕ','ТЭ'];

        if ($course === 2) {
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['БҚЕ', 'БКЕ', 'BKE'])) {
                    return $this->bkeCourse2OrderKz();
                }
            }
        }

        if ($course === 2 && !$useKazakh) {
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['М', 'M'])) {
                    return $this->mCourse2Order();
                }
            }
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['ПО'])) {
                    return $this->poCourse2Order();
                }
            }
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['СИБ'])) {
                    return $this->sibCourse2Order();
                }
            }
        }
        if ($course === 2 && $useKazakh) {
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['БҚЕ', 'БКЕ', 'BKE'])) {
                    return $this->bkeCourse2OrderKz();
                }
            }
            foreach ($tokens as $token) {
                if ($this->tokenMatchesPrefix($token, ['АКЖ', 'АҚЖ'])) {
                    return $this->sibCourse2OrderKz();
                }
            }
        }

        if ($useKazakh) {
            $order15 = $kzOrder;
            $order16 = $kzOrderWithGlobal;
        } else {
            $order15 = array_values(array_filter($ruOrder, fn (string $subject): bool => $subject !== 'Глобальные компетенции'));
            $order16 = $ruOrder;
        }

        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, $special15)) {
                return $order15;
            }
        }

        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, $special16)) {
                return $order16;
            }
        }

        return null;
    }

    protected function tokenMatchesPrefix(string $token, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if ($token === $prefix) {
                return true;
            }
            if (preg_match('/^' . preg_quote($prefix, '/') . '\\d+$/u', $token) === 1) {
                return true;
            }
        }

        return false;
    }

    protected function groupHasToken(string $groupName, array $prefixes): bool
    {
        $name = trim($groupName);
        if ($name === '') {
            return false;
        }
        $upper = mb_strtoupper($name, 'UTF-8');
        $tokens = preg_split('/[\\s\\-\\/]+/u', $upper, -1, PREG_SPLIT_NO_EMPTY);
        if (!$tokens) {
            return false;
        }
        foreach ($tokens as $token) {
            if ($this->tokenMatchesPrefix($token, $prefixes)) {
                return true;
            }
        }
        return false;
    }

    protected function subjectNamesContain($subjectNames, string $needle): bool
    {
        $target = $this->normalizeOrderName($needle);
        foreach ($subjectNames as $name) {
            if ($this->normalizeOrderName((string) $name) === $target) {
                return true;
            }
        }
        return false;
    }

    protected function sibCourse2Order(): array
    {
        return [
            'Укреплять здоровье и соблюдать принципы здорового образа жизни',
            'Владеть основами информационно-коммуникационных технологий',
            'Использовать услуги информационно-справочных и интерактивных веб-порталов',
            'Владеть основными вопросами в области экономической теории',
            'Анализировать и оценивать экономические процессы, происходящие на предприятии',
            'Производить монтаж сетевого и серверного оборудования, систем видеонабледния и систем контроля управления данными',
            'Конфигурировать сетевые сервисы и сетевое оборудование',
            'Обеспечивать информационную безопасность',
            'Интегрировать облачную инфраструктуры с сервисами предприятия',
            'Автоматизировать задачи обслуживания информационных систем',
            'Разрабатывать скрипты для автоматизации задач администрирования',
            'Администрировать базы данных',
            'Администрировать Web-ресурсы',
            'Создавать системные приложения',
        ];
    }

    protected function sibCourse2OrderKz(): array
    {
        return [
            'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
            'Ақпараттық-коммуникациялық технологиялар негіздерін меңгеру',
            'Ақпараттық, анықтамалық және интерактивті веб-порталдардың қызметтерін пайдалану',
            'Экономикалық теория саласындағы негізгі мәселелерді білу',
            'Кәсіпорында болып жатқан экономикалық процестерді талдау және бағалау',
            'Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу',
            'Желілік қызметтер мен желілік жабдықты конфигурациялау',
            'Ақпараттық қауіпсіздікті қамтамасыз ету',
            'Бұлтты инфрақұрылымдарды кәсіпорын қызметтерімен біріктіру',
            'Ақпараттық жүйеге техникалық қызмет көрсету тапсырмаларын автоматтандыру',
            'Әкімшілік тапсырмаларын автоматтандыру үшін сценарийлерді әзірлеу',
            'Мәліметтер базасын басқару',
            'Веб-ресурстарды басқару',
            'Жүйелік қолданбаларды құру',
        ];
    }

    protected function poCourse2Order(): array
    {
        return [
            'Укреплять здоровье и соблюдать принципы здорового образа жизни',
            'Анализировать и оценивать экономические процессы, происходящие на предприятии',
            'Владеть принципами и методами обработки графики для различных целей',
            'Определять стратегию дизайна и разрабатывать макеты пользовательского интерфейса относительно функциональности ПО',
            'Разрабатывать визуальное представление сайта',
            'Конструировать функциональные возможности сайта',
            'Применять терминологию на государственном языке при разработке и администрировании web-ресурсов',
            'Применять иностранную терминологию при разработке и администрировании web-ресурсов',
            'Разрабатывать программные решения на языках программирования',
        ];
    }

    protected function bkeCourse2OrderKz(): array
    {
        return [
            'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
            'Кәсіпорында болып жатқан экономикалық процестерді талдау және бағалау',
            'Әртүрлі мақсаттағы графиканы өңдеудің принциптері мен әдістерін меңгеру',
            'Бағдарламалық жасақтаманың функционалдығына қатысты дизайн стратегиясын анықтау',
            'Сайттың көрнекі презентациясын әзірлеу',
            'Сайттың функционалдық мүмкіндіктерін құрастыру',
            'Бағдарламалық шешімдерді бағдарламалау тілдерінде әзірлеу',
        ];
    }

    protected function mCourse2Order(): array
    {
        return [
            'Укреплять здоровье и соблюдать принципы здорового образа жизни',
            'Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию',
            'Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации',
            'Владеть сведениями об основных отраслях права',
            'Владеть основными понятиями социологии и политологии',
            'Разрабатывать стратегии привлечения клиентов с целью увеличения объемов продаж, в том числе через Интернет',
            'Контролировать и прогнозировать цикл продаж',
            'Разрабатывать планы презентаций продукта, PR-акций, рекламных акций по стимулированию продаж',
            'Устанавливать и поддерживать контакты с клиентами',
            'Подбирать оптимальные программы продвижения',
            'Создавать, поддерживать, контролировать и осуществлять постоянный мониторинг социальных сетей',
        ];
    }
}
