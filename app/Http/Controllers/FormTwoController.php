<?php

namespace App\Http\Controllers;

use App\Services\FormTwoService;
use App\Services\KazakhstanHolidayService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormTwoController extends Controller
{
    public function __construct(
        private readonly FormTwoService $service,
        private readonly KazakhstanHolidayService $holidayService
    ) {
    }

    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();
        $groupId = (int) ($request->input('group_id') ?? ($groups->first()->id ?? 0));
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        $holidayDays = $this->holidayService->getMonthHolidays($year, $month);
        $report = $groupId ? $this->service->buildMonthReport($groupId, $year, $month, $course, $holidayDays) : ['rows' => [], 'days' => []];
        $days = $report['days'] ?? range(1, Carbon::create($year, max(1, min(12, $month)), 1)->daysInMonth);
        $rows = $report['rows'] ?? [];
        $replacementRows = $report['replacement_rows'] ?? [];
        $replacementTableRows = $report['replacement_table_rows'] ?? [];
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

        $teachers = DB::table($tables['teachers'])->orderBy('teacher_name')->get(['id', 'teacher_name']);
        $subjects = DB::table($tables['subjects'])
            ->select('id', DB::raw('COALESCE(name_ru, subject_name) as title'))
            ->orderBy('title')
            ->get();

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
            'subgroupTwoRows' => $subgroupTwoRows,
            'subgroupTwoDayTotals' => $subgroupTwoDayTotals,
            'subgroupTwoColumnTotals' => $subgroupTwoColumnTotals,
            'subjects' => $subjects,
            'course' => $course,
            'holidayDays' => $holidayDays,
            'dayTotals' => $dayTotals,
            'columnTotals' => $columnTotals,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'rows' => 'required|array',
            'allow_manual' => 'nullable|boolean',
            'course' => 'nullable|integer|min:1|max:4',
        ]);

        if (!$request->boolean('allow_manual')) {
            return response()->json(['message' => 'Форма 2 открыта в режиме отчёта. Включите коррекцию, если хотите вручную поправить статусы.'], 422);
        }

        $course = CourseContext::normalize($data['course'] ?? $request->integer('course') ?? 1);

        $holidayDays = $this->holidayService->getMonthHolidays($data['year'], $data['month']);

        $this->service->saveMonthRecords(
            (int) $data['group_id'],
            (int) $data['year'],
            (int) $data['month'],
            $data['rows'],
            $course,
            $holidayDays
        );

        return response()->json(['status' => 'ok']);
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
            $filename = $exportService->export($groupId, $year, $month, $course);

            $months = [
                1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
                5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
                9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
            ];
            $downloadName = 'Форма_2_' . $months[$month] . '_' . $year . '.csv';

            return response()->download($filename, $downloadName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка при экспорте: ' . $e->getMessage());
        }
    }
}
