<?php

namespace App\Http\Controllers;

use App\Models\FieldCampPeriod;
use App\Services\FieldCampService;
use App\Support\CourseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FieldCampController extends Controller
{
    public function index(Request $request)
    {
        $course = 1;
        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();
        $teachers = DB::table($tables['teachers'])->orderBy('teacher_name')->get();

        $periods = FieldCampPeriod::query()
            ->where('course', $course)
            ->orderByDesc('start_date')
            ->get();

        return view('field_camps.index', [
            'course' => $course,
            'groups' => $groups,
            'teachers' => $teachers,
            'periods' => $periods,
        ]);
    }

    public function store(Request $request, FieldCampService $fieldCampService)
    {
        $course = 1;
        $tables = CourseContext::tables($course);

        $data = $request->validate([
            'course' => 'required|integer|in:1',
            'group_id' => ['required', 'integer', Rule::exists($tables['groups'], 'id')],
            'teacher_id' => ['required', 'integer', Rule::exists($tables['teachers'], 'id')],
            'room_id' => ['nullable', 'string', 'max:50'],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'hours_per_day' => 'nullable|integer|min:1|max:10',
        ]);

        if (isset($data['room_id']) && $data['room_id'] === '') {
            $data['room_id'] = null;
        }

        if ($data['end_date'] < $data['start_date']) {
            return back()->withErrors(['end_date' => 'Дата окончания не может быть раньше начала.'])->withInput();
        }

        $overlap = FieldCampPeriod::query()
            ->where('course', $course)
            ->where('group_id', $data['group_id'])
            ->whereDate('end_date', '>=', $data['start_date'])
            ->whereDate('start_date', '<=', $data['end_date'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Период сборов пересекается с уже существующим.'])->withInput();
        }

        $period = FieldCampPeriod::create([
            'course' => $course,
            'group_id' => (int) $data['group_id'],
            'teacher_id' => (int) $data['teacher_id'],
            'room_id' => $data['room_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'hours_per_day' => (int) ($data['hours_per_day'] ?? 6),
        ]);

        $fieldCampService->applyPeriod($period);

        return redirect()
            ->route('field_camps.index')
            ->with('success', 'Полевые сборы сохранены. Расписание скрыто на выбранный период.');
    }

    public function destroy(FieldCampPeriod $fieldCampPeriod, FieldCampService $fieldCampService)
    {
        $fieldCampService->removePeriod($fieldCampPeriod);
        $fieldCampPeriod->delete();

        return redirect()
            ->route('field_camps.index')
            ->with('success', 'Полевые сборы удалены. Расписание восстановлено.');
    }
}
