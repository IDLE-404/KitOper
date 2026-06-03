<?php

namespace App\Http\Controllers;

use App\Services\ScheduleGeneratorService;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleGeneratorController extends Controller
{
    public function __construct(
        private readonly ScheduleGeneratorService $generator
    ) {}

    public function index(Request $request): View
    {
        $course  = CourseContext::normalize($request->integer('course', 1));
        $tables  = CourseContext::tables($course);
        $groups  = DB::table($tables['groups'])->orderBy('group_name')->get(['id', 'group_name']);

        // Дефолтная неделя — ближайший понедельник
        $defaultWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        return view('first_course.schedule.generate', compact('groups', 'course', 'defaultWeek'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id'          => 'required|integer|min:1',
            'course'            => 'required|integer|min:1|max:4',
            'semester'          => 'required|in:1,2',
            'weeks_in_semester' => 'nullable|integer|min:8|max:24',
            'max_pairs_per_day' => 'nullable|integer|min:2|max:7',
            'allow_saturday'    => 'nullable|boolean',
            'assign_rooms'      => 'nullable|boolean',
            'template_week'     => 'required|date',
            'overwrite'         => 'nullable|boolean',
        ]);

        $course       = CourseContext::normalize($data['course']);
        $groupId      = (int) $data['group_id'];
        $templateWeek = Carbon::parse($data['template_week'])->startOfWeek(Carbon::MONDAY);
        $tables       = CourseContext::tables($course);

        // Проверка что группа принадлежит курсу
        if (!DB::table($tables['groups'])->where('id', $groupId)->exists()) {
            return back()->withErrors(['group_id' => 'Группа не найдена для выбранного курса.'])->withInput();
        }

        // Проверка существующего расписания
        $existing = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->whereDate('week_start', $templateWeek)
            ->exists();

        if ($existing && !($data['overwrite'] ?? false)) {
            return back()->withErrors([
                'template_week' => 'На этой неделе уже есть расписание. Включите «Перезаписать» чтобы заменить.',
            ])->withInput();
        }

        if ($existing) {
            DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $templateWeek)
                ->delete();
        }

        $result = $this->generator->generate(
            groupId:      $groupId,
            course:       $course,
            semester:     (int) $data['semester'],
            templateWeek: $templateWeek,
            params:       [
                'weeks_in_semester' => (int) ($data['weeks_in_semester'] ?? 18),
                'max_pairs_per_day' => (int) ($data['max_pairs_per_day'] ?? 4),
                'allow_saturday'    => (bool) ($data['allow_saturday'] ?? false),
                'assign_rooms'      => (bool) ($data['assign_rooms'] ?? false),
            ]
        );

        $stats = $result['stats'];

        if ($stats['inserted_rows'] === 0) {
            return back()->withErrors([
                'group_id' => 'Не удалось сгенерировать расписание. Проверьте что для группы заполнены нормативы Формы 2.',
            ])->withInput();
        }

        $msg = "Сгенерировано {$stats['placed']} из {$stats['total_demand']} пар ({$stats['inserted_rows']} строк).";

        if (!empty($result['unplaced'])) {
            $names = array_unique(array_column($result['unplaced'], 'subject_name'));
            $msg  .= ' Не удалось разместить: ' . implode(', ', $names) . '.';
        }

        return redirect()->route('first.schedule.week', [
            'course'     => $course,
            'group_id'   => $groupId,
            'week_start' => $templateWeek->toDateString(),
        ])->with('success', $msg);
    }
}
