<?php

namespace App\Http\Controllers;

use App\Services\FormTwoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormTwoController extends Controller
{
    public function __construct(private readonly FormTwoService $service)
    {
    }

    public function index(Request $request)
    {
        $groups = DB::table('first_course_group')->orderBy('group_name')->get();
        $groupId = (int) ($request->input('group_id') ?? ($groups->first()->id ?? 0));
        $month = (int) ($request->input('month') ?? now()->month);
        $year = (int) ($request->input('year') ?? now()->year);

        $records = $groupId ? $this->service->loadMonthRecords($groupId, $year, $month) : [];

        $days = range(1, 31);
        $teachers = DB::table('frist_course_teachers')->orderBy('teacher_name')->get(['id', 'teacher_name']);

        return view('first_course.form_two', [
            'groups' => $groups,
            'groupId' => $groupId,
            'month' => $month,
            'year' => $year,
            'records' => $records,
            'days' => $days,
            'teachers' => $teachers,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'rows' => 'required|array',
        ]);

        $this->service->saveMonthRecords(
            (int) $data['group_id'],
            (int) $data['year'],
            (int) $data['month'],
            $data['rows']
        );

        return response()->json(['status' => 'ok']);
    }
}
