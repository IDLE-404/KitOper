<?php

namespace App\Http\Controllers;

use App\Models\PracticePeriod;
use App\Services\PracticeService;
use App\Support\CourseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PracticeController extends Controller
{
    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 2);
        if ($course < 2) {
            $course = 2;
        }

        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();

        $periods = PracticePeriod::query()
            ->where('course', $course)
            ->orderByDesc('start_date')
            ->get();

        return view('practice.index', [
            'course' => $course,
            'groups' => $groups,
            'periods' => $periods,
        ]);
    }

    public function store(Request $request, PracticeService $practiceService)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 2);
        if ($course < 2 || $course > 4) {
            return back()->withErrors(['course' => 'Практика доступна только для 2–4 курсов.'])->withInput();
        }

        $tables = CourseContext::tables($course);
        $typeRule = $course === 2 ? 'in:educational,production' : 'in:production';

        $data = $request->validate([
            'course' => 'required|integer|min:2|max:4',
            'group_id' => ['required', 'integer', Rule::exists($tables['groups'], 'id')],
            'type' => ['required', 'string', $typeRule],
            'room_id' => ['nullable', 'string', 'max:50'],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if (isset($data['room_id']) && $data['room_id'] === '') {
            $data['room_id'] = null;
        }

        if ($data['type'] !== 'educational') {
            $data['room_id'] = null;
        }

        if ($data['end_date'] < $data['start_date']) {
            return back()->withErrors(['end_date' => 'Дата окончания не может быть раньше начала.'])->withInput();
        }

        $overlap = PracticePeriod::query()
            ->where('course', $course)
            ->where('group_id', $data['group_id'])
            ->whereDate('end_date', '>=', $data['start_date'])
            ->whereDate('start_date', '<=', $data['end_date'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Период практики пересекается с уже существующим.'])->withInput();
        }

        $period = PracticePeriod::create([
            'course' => $course,
            'group_id' => (int) $data['group_id'],
            'subject_id' => null,
            'type' => $data['type'],
            'teacher_id' => null,
            'room_id' => $data['room_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'hours_per_day' => 0,
        ]);

        $practiceService->applyPeriod($period);

        return redirect()
            ->route('practice.index', ['course' => $course])
            ->with('success', 'Практика сохранена. Расписание скрыто на выбранный период.');
    }

    public function destroy(PracticePeriod $practicePeriod, PracticeService $practiceService)
    {
        $course = (int) $practicePeriod->course;
        $practiceService->removePeriod($practicePeriod);
        $practicePeriod->delete();

        return redirect()
            ->route('practice.index', ['course' => $course])
            ->with('success', 'Практика удалена. Расписание восстановлено.');
    }
}
