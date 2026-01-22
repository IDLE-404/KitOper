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

        $subjects = DB::table($tables['subjects'])
            ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
            ->orderBy('module_title')
            ->orderByRaw('COALESCE(name_ru, subject_name)')
            ->get()
            ->map(function ($row) use ($course) {
                $name = $row->name_ru ?: ($row->name_kz ?: $row->subject_name);
                $module = trim((string) ($row->module_title ?? ''));
                $row->title = ($course !== 1 && $module !== '') ? trim($module . ' ' . $name) : $name;
                return $row;
            });
        $subjectTitleMap = $subjects->pluck('title', 'id')->all();

        $teacherSubjects = [];
        $teacherSubjectTable = $tables['teacher_subjects'] ?? null;
        if ($teacherSubjectTable && Schema::hasTable($teacherSubjectTable)) {
            $pairs = DB::table($teacherSubjectTable)
                ->select('teacher_id', 'subject_id')
                ->whereIn('teacher_id', $teachers->pluck('id')->all())
                ->get();
            foreach ($pairs as $pair) {
                $teacherId = (int) $pair->teacher_id;
                $teacherSubjects[$teacherId][] = (int) $pair->subject_id;
            }
        }

        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        return view('teachers.index', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'subjectTitleMap' => $subjectTitleMap,
            'teacherSubjects' => $teacherSubjects,
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
            'subject_ids' => 'sometimes|array',
            'subject_ids.*' => 'integer',
        ]);

        $payload = [
            'teacher_name' => $data['teacher_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasInitials) {
            $payload['initials'] = $this->resolveInitials($data['teacher_name'], $data['initials'] ?? null);
        }

        $teacherId = DB::table($tables['teachers'])->insertGetId($payload);
        $subjectIds = $data['subject_ids'] ?? [];
        $this->syncTeacherSubjects($tables, $teacherId, $subjectIds);

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
            'subject_ids' => 'sometimes|array',
            'subject_ids.*' => 'integer',
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
        $subjectIds = $data['subject_ids'] ?? [];
        $this->syncTeacherSubjects($tables, $id, $subjectIds);

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

    private function syncTeacherSubjects(array $tables, int $teacherId, array $subjectIds): void
    {
        $teacherSubjectTable = $tables['teacher_subjects'] ?? null;
        if (!$teacherSubjectTable || !Schema::hasTable($teacherSubjectTable)) {
            return;
        }

        $subjectIds = array_values(array_unique(array_filter($subjectIds, fn($id) => $id !== null && $id !== '')));
        if (count($subjectIds) > 0) {
            $subjectIds = DB::table($tables['subjects'])
                ->whereIn('id', $subjectIds)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        DB::table($teacherSubjectTable)
            ->where('teacher_id', $teacherId)
            ->delete();

        if (empty($subjectIds)) {
            return;
        }

        $now = now();
        $rows = array_map(fn($subjectId) => [
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $subjectIds);

        DB::table($teacherSubjectTable)->insert($rows);
    }
}
