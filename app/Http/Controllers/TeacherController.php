<?php

namespace App\Http\Controllers;

use App\Support\CourseContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        $teachers = DB::table($tables['teachers'])
            ->orderBy('teacher_name')
            ->get();

        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        return view('teachers.index', [
            'teachers' => $teachers,
            'course' => $course,
            'hasInitials' => $hasInitials,
        ]);
    }

    public function store(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        $data = $request->validate([
            'teacher_name' => 'required|string|max:255',
            'initials' => $hasInitials ? 'nullable|string|max:20' : 'nullable',
        ]);

        $payload = [
            'teacher_name' => $data['teacher_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasInitials) {
            $payload['initials'] = $this->resolveInitials($data['teacher_name'], $data['initials'] ?? null);
        }

        DB::table($tables['teachers'])->insert($payload);

        return redirect()
            ->route('teachers.index', ['course' => $course])
            ->with('success', 'Преподаватель добавлен.');
    }

    public function update(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        $data = $request->validate([
            'teacher_name' => 'required|string|max:255',
            'initials' => $hasInitials ? 'nullable|string|max:20' : 'nullable',
        ]);

        $payload = [
            'teacher_name' => $data['teacher_name'],
            'updated_at' => now(),
        ];
        if ($hasInitials) {
            $payload['initials'] = $this->resolveInitials($data['teacher_name'], $data['initials'] ?? null);
        }

        DB::table($tables['teachers'])
            ->where('id', $id)
            ->update($payload);

        return redirect()
            ->route('teachers.index', ['course' => $course])
            ->with('success', 'Данные преподавателя обновлены.');
    }

    public function destroy(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);

        try {
            DB::table($tables['teachers'])
                ->where('id', $id)
                ->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('teachers.index', ['course' => $course])
                ->withErrors(['delete' => 'Не удалось удалить преподавателя: есть связанные записи.']);
        }

        return redirect()
            ->route('teachers.index', ['course' => $course])
            ->with('success', 'Преподаватель удален.');
    }

    private function resolveInitials(string $teacherName, ?string $initialsInput = null): ?string
    {
        $initialsInput = $initialsInput !== null ? trim($initialsInput) : null;
        if ($initialsInput !== null && $initialsInput !== '') {
            return $initialsInput;
        }

        $clean = trim(preg_replace('/\s+/u', ' ', $teacherName));
        if ($clean === '') {
            return null;
        }
        if (mb_strpos($clean, '.') !== false) {
            return $clean;
        }

        $parts = array_values(array_filter(explode(' ', $clean), fn($part) => $part !== ''));
        if (count($parts) < 2) {
            return $clean;
        }

        $surname = array_shift($parts);
        $initials = $surname . ' ';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1) . '.';
        }

        return trim($initials);
    }
}
