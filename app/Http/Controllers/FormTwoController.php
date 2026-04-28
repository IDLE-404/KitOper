<?php

namespace App\Http\Controllers;

use App\Services\FormTwoService;
use App\Services\KazakhstanHolidayService;
use App\Services\SemesterGhostService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormTwoController extends Controller
{
    public function __construct(
        private readonly FormTwoService $service,
        private readonly KazakhstanHolidayService $holidayService,
        private readonly SemesterGhostService $ghostService
    ) {
    }

    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groupsQuery = DB::table($tables['groups'])
            ->select('id', 'group_name')
            ->orderBy('group_name');
        if (\Illuminate\Support\Facades\Schema::hasColumn($tables['groups'], 'has_subgroups')) {
            $groupsQuery->addSelect('has_subgroups');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn($tables['groups'], 'group_type')) {
            $groupsQuery->addSelect('group_type');
        }
        $groups = $groupsQuery->get();
        $groupId = (int) ($request->input('group_id') ?? ($groups->first()->id ?? 0));
        $explicitPeriod = $request->query->has('month') || $request->query->has('year');
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        if (!$explicitPeriod && $groupId) {
            $today = now();
            $holidayDaysNow = $this->holidayService->getMonthHolidays($today->year, $today->month);
            $todayHoliday = $holidayDaysNow[$today->day] ?? null;
            if ($this->isVacationHoliday($todayHoliday) && (int) $today->month === 1) {
                return redirect()->route('first.schedule.form_two', [
                    'group_id' => $groupId,
                    'month' => 2,
                    'year' => $today->year,
                    'course' => $course,
                ]);
            }
        }

        $holidayDays = $this->holidayService->getMonthHolidays($year, $month);
        $report = $groupId ? $this->service->buildMonthReport($groupId, $year, $month, $course, $holidayDays) : ['rows' => [], 'days' => []];
        $days = $report['days'] ?? range(1, Carbon::create($year, max(1, min(12, $month)), 1)->daysInMonth);
        $rows = $report['rows'] ?? [];
        $replacementRows = $report['replacement_rows'] ?? [];
        $replacementTableRows = $report['replacement_table_rows'] ?? [];
        $practiceRows = $report['practice_rows'] ?? [];
        $practiceTotals = $report['practice_totals'] ?? ['day_totals' => [], 'column_totals' => []];
        $practiceDates = $report['practice_dates'] ?? [];
        $subgroupTwoRows = $report['subgroup_two_rows'] ?? [];
        $subgroupTwoTotals = $report['subgroup_two_totals'] ?? ['day_totals' => [], 'column_totals' => []];
        $totals = $report['totals'] ?? ['day_totals' => [], 'column_totals' => []];
        $dayTotals = $totals['day_totals'] ?? [];
        $columnTotals = $totals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        $replacementTotals = $this->service->calculateReplacementTotals($replacementTableRows, $days);
        $practiceDayTotals = $practiceTotals['day_totals'] ?? [];
        $practiceColumnTotals = $practiceTotals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        $replacementDayTotals = $replacementTotals['day_totals'] ?? [];
        $replacementColumnTotals = $replacementTotals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        foreach ($days as $day) {
            if (!isset($dayTotals[$day])) {
                $dayTotals[$day] = 0;
            }
        }
        foreach ($days as $day) {
            if (!isset($practiceDayTotals[$day])) {
                $practiceDayTotals[$day] = 0;
            }
        }
        $subgroupTwoDayTotals = $subgroupTwoTotals['day_totals'] ?? [];
        $subgroupTwoColumnTotals = $subgroupTwoTotals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        foreach ($days as $day) {
            if (!isset($subgroupTwoDayTotals[$day])) {
                $subgroupTwoDayTotals[$day] = 0;
            }
        }

        $selectedGroup = $groups->firstWhere('id', $groupId);
        $groupType = $selectedGroup->group_type ?? null;
        $hasSubgroups = (bool) ($selectedGroup->has_subgroups ?? false);
        if ($groupType === 'ru') {
            $useKazakh = false;
        } elseif ($groupType === 'kz') {
            $useKazakh = true;
        } else {
            $selectedGroupName = $selectedGroup->group_name ?? '';
            $useKazakh = (bool) preg_match('/[ҚқӘәҢңӨөҰұҮүІіҺһҒғ]/u', (string) $selectedGroupName);
        }

        $teachers = DB::table($tables['teachers'])
            ->select('id', DB::raw('COALESCE(initials, teacher_name) as teacher_name'))
            ->orderBy('teacher_name')
            ->get();
        $includeModule = $course !== 1;
        $subjectsQuery = DB::table($tables['subjects']);
        if (\Illuminate\Support\Facades\Schema::hasColumn($tables['subjects'], 'group_type')) {
            $subjectsQuery->whereIn('group_type', [$useKazakh ? 'kz' : 'ru', 'both']);
        }
        $subjects = $subjectsQuery
            ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
            ->orderByRaw('COALESCE(name_ru, subject_name)')
            ->get()
            ->map(function ($row) use ($includeModule, $useKazakh) {
                $name = $useKazakh
                    ? ($row->name_kz ?: ($row->name_ru ?: $row->subject_name))
                    : ($row->name_ru ?: ($row->name_kz ?: $row->subject_name));
                $module = trim((string) ($row->module_title ?? ''));
                $row->title = ($includeModule && $module !== '') ? trim($module . ' ' . $name) : $name;
                return $row;
            });

        // Активный семестр: 1 = сен–янв, 2 = фев–июн (используется всегда, не только в ghost)
        $activeSemester = in_array((int) $request->input('semester'), [1, 2])
            ? (int) $request->input('semester')
            : ($month >= 9 || $month === 1 ? 1 : 2);

        // Ghost-режим: призрачные данные из расписания-шаблона
        $ghostMode = (bool) $request->input('ghost', false);
        $ghostSemester = $activeSemester;
        $ghostCells = [];
        $ghostConflicts = [];
        if ($ghostMode && $groupId) {
            $ghostResult = $this->ghostService->ghostMonthData($groupId, $year, $month, $course);
            $ghostCells = $ghostResult['cells'] ?? [];
            $ghostConflicts = $ghostResult['conflicts'] ?? [];
        }

        return view('first_course.form_two', [
            'groups' => $groups,
            'groupId' => $groupId,
            'month' => $month,
            'year' => $year,
            'rows' => $rows,
            'days' => $days,
            'teachers' => $teachers,
            'replacementRows' => $replacementRows,
            'replacementTableRows' => $replacementTableRows,
            'replacementDayTotals' => $replacementDayTotals,
            'replacementColumnTotals' => $replacementColumnTotals,
            'practiceRows' => $practiceRows,
            'practiceDayTotals' => $practiceDayTotals,
            'practiceColumnTotals' => $practiceColumnTotals,
            'practiceDates' => $practiceDates,
            'subgroupTwoRows' => $subgroupTwoRows,
            'subgroupTwoDayTotals' => $subgroupTwoDayTotals,
            'subgroupTwoColumnTotals' => $subgroupTwoColumnTotals,
            'subjects' => $subjects,
            'course' => $course,
            'holidayDays' => $holidayDays,
            'dayTotals' => $dayTotals,
            'columnTotals' => $columnTotals,
            'hasSubgroups' => $hasSubgroups,
            'activeSemester' => $activeSemester,
            'ghostMode' => $ghostMode,
            'ghostSemester' => $ghostSemester,
            'ghostCells' => $ghostCells,
            'ghostConflicts' => $ghostConflicts,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'rows' => 'required|array',
            'replacement_normatives' => 'nullable|array',
            'subgroup_two_normatives' => 'nullable|array',
            'subgroup_two_rows' => 'nullable|array',
            'allow_manual' => 'nullable|boolean',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        if (!$request->boolean('allow_manual')) {
            return response()->json(['message' => 'Форма 2 открыта в режиме отчёта. Включите коррекцию, если хотите вручную поправить статусы.'], 422);
        }

        $course = CourseContext::normalize($data['course'] ?? $request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $subjectsMap = DB::table($tables['subjects'])
            ->select('id', 'subject_name', 'name_ru', 'name_kz')
            ->get()
            ->mapWithKeys(function ($row) {
                $name = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
                return [(int) $row->id => (string) $name];
            });

        $dupErrors = $this->validateDuplicateSubjectsRequireTeacher($data['rows'] ?? [], $subjectsMap, 'основной таблице');
        $dupSubErrors = $this->validateDuplicateSubjectsRequireTeacher($data['subgroup_two_rows'] ?? [], $subjectsMap, 'подвоении');
        $dupErrors = array_merge($dupErrors, $dupSubErrors);
        if ($dupErrors) {
            return response()->json(['message' => $dupErrors[0]], 422);
        }

        $holidayDays = $this->holidayService->getMonthHolidays($data['year'], $data['month']);

        $this->service->saveMonthRecords(
            (int) $data['group_id'],
            (int) $data['year'],
            (int) $data['month'],
            $data['rows'],
            $course,
            $holidayDays,
            $data['replacement_normatives'] ?? [],
            $data['subgroup_two_normatives'] ?? [],
            $data['subgroup_two_rows'] ?? []
        );

        return response()->json(['status' => 'ok']);
    }

    private function validateDuplicateSubjectsRequireTeacher(array $rows, $subjectsMap, string $tableLabel): array
    {
        if (!$rows) {
            return [];
        }

        $subjectCounts = [];
        foreach ($rows as $row) {
            $subjectId = (int) ($row['subject_id'] ?? 0);
            if (!$subjectId) {
                continue;
            }
            $subjectCounts[$subjectId] = ($subjectCounts[$subjectId] ?? 0) + 1;
        }

        $errors = [];
        foreach ($rows as $row) {
            $subjectId = (int) ($row['subject_id'] ?? 0);
            if (!$subjectId) {
                continue;
            }
            if (($subjectCounts[$subjectId] ?? 0) <= 1) {
                continue;
            }
            $teacherId = $row['teacher_id'] ?? null;
            if (!$teacherId) {
                $subjectName = $subjectsMap[$subjectId] ?? ('ID ' . $subjectId);
                $errors[] = "Для повторяющегося предмета \"{$subjectName}\" в {$tableLabel} нужно выбрать преподавателя.";
                break;
            }
        }

        return $errors;
    }

    public function export(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $groupId = (int) $request->input('group_id');
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        if (!$groupId) {
            return redirect()->back()->with('error', 'Не указана группа');
        }

        try {
            $exportService = new \App\Services\FormTwoExportService();
            $filename = $exportService->exportXlsx($groupId, $year, $month, $course);
            $downloadName = 'Форма_2_' . $month . '_' . $year . '.xlsx';
            return response()->download($filename, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка при экспорте: ' . $e->getMessage());
        }
    }

    public function exportSemester(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $groupId = (int) $request->input('group_id');
        $semester = (int) ($request->input('semester') ?? 1);
        $year = (int) ($request->input('year') ?? now()->year);

        if (!$groupId) {
            return redirect()->back()->with('error', 'Не указана группа');
        }
        if (!in_array($semester, [1, 2], true)) {
            return redirect()->back()->with('error', 'Некорректный семестр');
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        try {
            $exportService = new \App\Services\FormTwoExportService();
            $filename = $exportService->exportSemesterXlsx($groupId, $semester, $year, $course);
            $rangeLabel = $semester === 1 ? "{$year}-" . ($year + 1) : (string) $year;
            $downloadName = "Форма_2_Семестр_{$semester}_{$rangeLabel}.xlsx";
            return response()->download($filename, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка при экспорте: ' . $e->getMessage());
        }
    }

    protected function isVacationHoliday(?array $holiday): bool
    {
        if (!$holiday) {
            return false;
        }

        $name = (string) ($holiday['name'] ?? '');
        return $name !== '' && mb_stripos($name, 'каникул') !== false;
    }
}
