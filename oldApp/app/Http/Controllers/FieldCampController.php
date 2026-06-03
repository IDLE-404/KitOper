<?php

namespace App\Http\Controllers;

use App\Models\FieldCampPeriod;
use App\Services\FieldCampService;
use App\Support\CourseContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class FieldCampController extends Controller
{
    public function index(Request $request)
    {
        $course = 1;
        $tables = CourseContext::tables($course);

        $groups = DB::table($tables['groups'])->orderBy('group_name')->get();
        $nvpSubjectIds = $this->nvpSubjectIds($tables['subjects']);

        if (Schema::hasTable($tables['teacher_subjects']) && !empty($nvpSubjectIds)) {
            $teacherIds = DB::table($tables['teacher_subjects'])
                ->whereIn('subject_id', $nvpSubjectIds)
                ->pluck('teacher_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $teachers = empty($teacherIds)
                ? collect()
                : DB::table($tables['teachers'])->whereIn('id', $teacherIds)->orderBy('teacher_name')->get();
        } else {
            $teachers = collect();
        }

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

        $nvpSubjectIds = $this->nvpSubjectIds($tables['subjects']);
        if (Schema::hasTable($tables['teacher_subjects']) && !empty($nvpSubjectIds)) {
            $isLinked = DB::table($tables['teacher_subjects'])
                ->whereIn('subject_id', $nvpSubjectIds)
                ->where('teacher_id', (int) $data['teacher_id'])
                ->exists();
            if (!$isLinked) {
                return back()->withErrors(['teacher_id' => 'Выберите преподавателя, который ведет НВП (начальная военная подготовка).'])->withInput();
            }
        } else {
            return back()->withErrors(['teacher_id' => 'Не найдены предметы НВП или привязки преподавателей к НВП.'])->withInput();
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

    private function nvpSubjectIds(string $subjectTable): array
    {
        return DB::table($subjectTable)
            ->where(function ($query) {
                $query->where('subject_name', 'like', 'НВП%')
                    ->orWhere('name_ru', 'like', 'НВП%')
                    ->orWhere('subject_name', 'like', '%Начальная военная%')
                    ->orWhere('name_ru', 'like', '%Начальная военная%');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
