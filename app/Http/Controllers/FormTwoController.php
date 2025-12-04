<?php

namespace App\Http\Controllers;

use App\Services\FormTwoService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormTwoController extends Controller
{
    public function __construct(private readonly FormTwoService $service)
    {
    }

    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();
        $groupId = (int) ($request->input('group_id') ?? ($groups->first()->id ?? 0));
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        $report = $groupId ? $this->service->buildMonthReport($groupId, $year, $month, $course) : ['rows' => [], 'days' => []];
        $days = $report['days'] ?? range(1, Carbon::create($year, max(1, min(12, $month)), 1)->daysInMonth);
        $rows = $report['rows'] ?? [];
        $replacementRows = $report['replacement_rows'] ?? [];

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
            'subjects' => $subjects,
            'course' => $course,
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

        $this->service->saveMonthRecords(
            (int) $data['group_id'],
            (int) $data['year'],
            (int) $data['month'],
            $data['rows'],
            $course
        );

        return response()->json(['status' => 'ok']);
    }
}
