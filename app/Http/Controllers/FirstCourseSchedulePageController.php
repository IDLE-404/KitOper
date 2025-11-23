<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FirstCourseSchedulePageController extends Controller
{
    /**
     * Показать расписание 1 курса.
     */
    public function index()
    {
        $items = DB::table('first_course_schedules as s')
            ->leftJoin('groups as g', 's.group_id', '=', 'g.id')
            ->leftJoin('first_course_subjects as subj', 's.subject_id', '=', 'subj.id')
            ->leftJoin('frist_course_teachers as t', 's.teacher_id', '=', 't.id')
            ->orderBy('s.study_day')
            ->orderBy('s.lesson_number')
            ->select(
                's.id',
                's.study_day',
                's.lesson_number',
                's.room_id',
                's.subgroup',
                'g.group_name',
                'subj.name_ru as subject_name_ru',
                'subj.subject_name as subject_fallback',
                't.teacher_name'
            )
            ->get();

        return view('first_course.schedule.index', [
            'items' => $items,
        ]);
    }

    /**
     * Форма создания строки расписания.
     */
    public function create()
    {
        $groups = DB::table('groups')->where('year', 1)->get();
        $subjects = DB::table('first_course_subjects')->get();
        $teachers = DB::table('frist_course_teachers')->get();

        $days = [
            'Понедельник',
            'Вторник',
            'Среда',
            'Четверг',
            'Пятница',
        ];

        return view('first_course.schedule.create', [
            'groups' => $groups,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'days' => $days,
        ]);
    }

    /**
     * Сохранить новую строку расписания.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'study_day'     => 'required|string',
            'lesson_number' => 'required|integer|min:1|max:8',
            'group_id'      => 'required|integer',
            'subject_id'    => 'nullable|integer',
            'teacher_id'    => 'nullable|integer',
            'room_id'       => 'nullable|integer',
            'has_subgroups'     => 'sometimes|boolean',
            'subject_id_second' => 'required_if:has_subgroups,1|integer|nullable',
            'teacher_id_second' => 'nullable|integer',
            'room_id_second'    => 'nullable|integer',
        ]);

        $hasSubgroups = $request->boolean('has_subgroups');

        $base = [
            'study_day'     => $validated['study_day'],
            'lesson_number' => $validated['lesson_number'],
            'group_id'      => $validated['group_id'],
            'room_id'       => $validated['room_id'] ?? null,
            'teacher_id'    => $validated['teacher_id'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        $rows = [];

        $rows[] = array_merge($base, [
            'subject_id' => $validated['subject_id'] ?? null,
            'subgroup'   => $hasSubgroups ? 'A' : null,
        ]);

        if ($hasSubgroups && !empty($validated['subject_id_second'])) {
            $rows[] = array_merge($base, [
                'subject_id' => $validated['subject_id_second'],
                'teacher_id' => $validated['teacher_id_second'] ?? $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id_second'] ?? $validated['room_id'] ?? null,
                'subgroup'   => 'B',
            ]);
        }

        DB::table('first_course_schedules')->insert($rows);

        return redirect()
            ->route('first.schedule.index')
            ->with('success', 'Запись добавлена!');
    }
}
