<?php

namespace App\Http\Controllers;

use App\Support\CourseContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $subjects = DB::table($tables['subjects'])
            ->orderBy('module_title')
            ->orderByRaw('COALESCE(name_ru, subject_name)')
            ->get();

        $hasModules = $course !== 1;

        return view('subjects.index', [
            'subjects' => $subjects,
            'course' => $course,
            'hasModules' => $hasModules,
        ]);
    }

    public function store(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasModules = $course !== 1;

        $rules = [
            'subject_name' => 'required|string|max:255',
        ];
        if ($hasModules) {
            $rules['module_title'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        $payload = [
            'subject_name' => $data['subject_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($hasModules) {
            $payload['module_title'] = $data['module_title'] ?? null;
        }

        DB::table($tables['subjects'])->insert($payload);

        return redirect()
            ->route('subjects.index', ['course' => $course])
            ->with('success', 'Предмет добавлен.');
    }

    public function update(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasModules = $course !== 1;

        $rules = [
            'subject_name' => 'required|string|max:255',
        ];
        if ($hasModules) {
            $rules['module_title'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        $payload = [
            'subject_name' => $data['subject_name'],
            'updated_at' => now(),
        ];

        if ($hasModules) {
            $payload['module_title'] = $data['module_title'] ?? null;
        }

        DB::table($tables['subjects'])
            ->where('id', $id)
            ->update($payload);

        return redirect()
            ->route('subjects.index', ['course' => $course])
            ->with('success', 'Данные предмета обновлены.');
    }

    public function destroy(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        try {
            DB::table($tables['subjects'])
                ->where('id', $id)
                ->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('subjects.index', ['course' => $course])
                ->withErrors(['delete' => 'Не удалось удалить предмет: есть связанные записи.']);
        }

        return redirect()
            ->route('subjects.index', ['course' => $course])
            ->with('success', 'Предмет удален.');
    }
}
