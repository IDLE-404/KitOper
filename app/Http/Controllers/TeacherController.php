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
        $teacherIds = $teachers->pluck('id')->map(fn ($id) => (int) $id)->all();

        $subjectsByCourse = [];
        $subjectTitleMapByCourse = [];
        $teacherSubjectsByCourse = [];

        foreach ([1, 2, 3, 4] as $courseNo) {
            $courseTables = CourseContext::tables($courseNo);
            if (!Schema::hasTable($courseTables['subjects'])) {
                $subjectsByCourse[$courseNo] = collect();
                $subjectTitleMapByCourse[$courseNo] = [];
                $teacherSubjectsByCourse[$courseNo] = [];
                continue;
            }

            $subjects = DB::table($courseTables['subjects'])
                ->select('id', 'subject_name', 'name_ru', 'name_kz', 'module_title')
                ->orderBy('module_title')
                ->orderByRaw('COALESCE(subject_name, name_ru, name_kz)')
                ->get()
                ->map(function ($row) use ($courseNo) {
                    // Keep full competency code (e.g. RO/ON) in UI labels.
                    $name = $row->subject_name ?: ($row->name_ru ?: $row->name_kz);
                    $module = trim((string) ($row->module_title ?? ''));
                    $row->title = ($courseNo !== 1 && $module !== '') ? trim($module . ' ' . $name) : $name;
                    return $row;
                });

            $subjectsByCourse[$courseNo] = $subjects;
            $subjectTitleMapByCourse[$courseNo] = $subjects->pluck('title', 'id')->all();
            $teacherSubjectsByCourse[$courseNo] = [];

            $teacherSubjectTable = $courseTables['teacher_subjects'] ?? null;
            if (!$teacherSubjectTable || !Schema::hasTable($teacherSubjectTable) || empty($teacherIds)) {
                continue;
            }

            $pairs = DB::table($teacherSubjectTable)
                ->select('teacher_id', 'subject_id')
                ->whereIn('teacher_id', $teacherIds)
                ->get();
            foreach ($pairs as $pair) {
                $teacherId = (int) $pair->teacher_id;
                $teacherSubjectsByCourse[$courseNo][$teacherId][] = (int) $pair->subject_id;
            }
        }

        $subjects = $subjectsByCourse[$course] ?? collect();
        $subjectTitleMap = $subjectTitleMapByCourse[$course] ?? [];
        $teacherSubjects = $teacherSubjectsByCourse[$course] ?? [];

        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');
        $duplicateInitials = [];
        if ($hasInitials) {
            $duplicateInitials = DB::table($tables['teachers'])
                ->select('initials')
                ->whereNotNull('initials')
                ->where('initials', '<>', '')
                ->groupBy('initials')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('initials')
                ->all();
        }
        if (Schema::hasTable('rooms')) {
            $roomsQuery = DB::table('rooms');
            if (Schema::hasColumn('rooms', 'is_active')) {
                $roomsQuery->where('is_active', true);
            }
            $rooms = $roomsQuery->orderBy('code')->get();
        } else {
            $rooms = collect();
        }

        return view('teachers.index', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'subjectTitleMap' => $subjectTitleMap,
            'teacherSubjects' => $teacherSubjects,
            'subjectsByCourse' => $subjectsByCourse,
            'subjectTitleMapByCourse' => $subjectTitleMapByCourse,
            'teacherSubjectsByCourse' => $teacherSubjectsByCourse,
            'course' => $course,
            'hasInitials' => $hasInitials,
            'rooms' => $rooms,
            'duplicateInitials' => $duplicateInitials,
        ]);
    }

    public function store(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        $hasDefaultRoom = Schema::hasColumn($tables['teachers'], 'default_room_id');
        $data = $request->validate([
            'teacher_name' => 'required|string|max:255',
            'initials' => $hasInitials ? 'nullable|string|max:20' : 'nullable',
            'subjects_by_course_mode' => 'sometimes|boolean',
            'subject_ids' => 'sometimes|array',
            'subject_ids.*' => 'integer',
            'subject_ids_by_course' => 'sometimes|array',
            'subject_ids_by_course.1' => 'sometimes|array',
            'subject_ids_by_course.1.*' => 'integer',
            'subject_ids_by_course.2' => 'sometimes|array',
            'subject_ids_by_course.2.*' => 'integer',
            'subject_ids_by_course.3' => 'sometimes|array',
            'subject_ids_by_course.3.*' => 'integer',
            'subject_ids_by_course.4' => 'sometimes|array',
            'subject_ids_by_course.4.*' => 'integer',
            'default_room_id' => ($hasDefaultRoom && Schema::hasTable('rooms')) ? 'nullable|integer|exists:rooms,id' : 'nullable',
        ]);

        $payload = [
            'teacher_name' => $data['teacher_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasInitials) {
            $payload['initials'] = $this->resolveInitials($data['teacher_name'], $data['initials'] ?? null);
            if ($payload['initials'] && $this->teacherWithInitialsExists($tables['teachers'], $payload['initials'])) {
                return redirect()
                    ->route('teachers.index', ['course' => $course])
                    ->withErrors(['teacher_name' => 'Похоже, такой преподаватель уже есть (по инициалам). Откройте и отредактируйте существующую запись.'])
                    ->withInput();
            }
        }
        if ($hasDefaultRoom && !empty($data['default_room_id'])) {
            $payload['default_room_id'] = (int) $data['default_room_id'];
        }

        $teacherId = DB::table($tables['teachers'])->insertGetId($payload);
        $subjectIdsByCourse = $this->extractSubjectIdsByCourse($data, $course);
        $this->syncTeacherSubjectsForCourses($teacherId, $subjectIdsByCourse);

        return redirect()
            ->route('teachers.index', ['course' => $course])
            ->with('success', 'Преподаватель добавлен.');
    }

    public function update(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $hasInitials = Schema::hasColumn($tables['teachers'], 'initials');

        $hasDefaultRoom = Schema::hasColumn($tables['teachers'], 'default_room_id');
        $data = $request->validate([
            'teacher_name' => 'required|string|max:255',
            'initials' => $hasInitials ? 'nullable|string|max:20' : 'nullable',
            'subjects_by_course_mode' => 'sometimes|boolean',
            'subject_ids' => 'sometimes|array',
            'subject_ids.*' => 'integer',
            'subject_ids_by_course' => 'sometimes|array',
            'subject_ids_by_course.1' => 'sometimes|array',
            'subject_ids_by_course.1.*' => 'integer',
            'subject_ids_by_course.2' => 'sometimes|array',
            'subject_ids_by_course.2.*' => 'integer',
            'subject_ids_by_course.3' => 'sometimes|array',
            'subject_ids_by_course.3.*' => 'integer',
            'subject_ids_by_course.4' => 'sometimes|array',
            'subject_ids_by_course.4.*' => 'integer',
            'default_room_id' => ($hasDefaultRoom && Schema::hasTable('rooms')) ? 'nullable|integer|exists:rooms,id' : 'nullable',
        ]);

        $payload = [
            'teacher_name' => $data['teacher_name'],
            'updated_at' => now(),
        ];
        if ($hasInitials) {
            $payload['initials'] = $this->resolveInitials($data['teacher_name'], $data['initials'] ?? null);
            if ($payload['initials'] && $this->teacherWithInitialsExists($tables['teachers'], $payload['initials'], $id)) {
                return redirect()
                    ->route('teachers.index', ['course' => $course])
                    ->withErrors(['teacher_name' => 'Похоже, такой преподаватель уже есть (по инициалам). Откройте и отредактируйте существующую запись.'])
                    ->withInput();
            }
        }
        if ($hasDefaultRoom) {
            $payload['default_room_id'] = !empty($data['default_room_id']) ? (int) $data['default_room_id'] : null;
        }

        DB::table($tables['teachers'])
            ->where('id', $id)
            ->update($payload);
        $subjectIdsByCourse = $this->extractSubjectIdsByCourse($data, $course);
        $this->syncTeacherSubjectsForCourses($id, $subjectIdsByCourse);

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
            return $this->limitInitialsLength($initialsInput);
        }

        $clean = trim(preg_replace('/\s+/u', ' ', $teacherName));
        if ($clean === '') {
            return null;
        }
        if (mb_strpos($clean, '.') !== false) {
            return $this->limitInitialsLength($clean);
        }

        $parts = array_values(array_filter(explode(' ', $clean), fn($part) => $part !== ''));
        if (count($parts) < 2) {
            return $this->limitInitialsLength($clean);
        }

        $surname = array_shift($parts);
        $initials = $surname . ' ';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1) . '.';
        }

        return $this->limitInitialsLength(trim($initials));
    }

    private function limitInitialsLength(string $initials, int $max = 20): string
    {
        $initials = trim($initials);
        if ($initials === '') {
            return $initials;
        }
        if (mb_strlen($initials) <= $max) {
            return $initials;
        }
        return rtrim(mb_substr($initials, 0, $max));
    }

    private function teacherWithInitialsExists(string $table, string $initials, ?int $excludeId = null): bool
    {
        $query = DB::table($table)
            ->where('initials', $initials);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->exists();
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

    private function syncTeacherSubjectsForCourses(int $teacherId, array $subjectIdsByCourse): void
    {
        foreach ([1, 2, 3, 4] as $courseNo) {
            $tables = CourseContext::tables($courseNo);
            $subjectIds = $subjectIdsByCourse[$courseNo] ?? [];
            $this->syncTeacherSubjects($tables, $teacherId, $subjectIds);
        }
    }

    private function extractSubjectIdsByCourse(array $data, int $selectedCourse): array
    {
        $result = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
        ];

        $byCourse = $data['subject_ids_by_course'] ?? null;
        $forceByCourse = (bool) ($data['subjects_by_course_mode'] ?? false);
        if ($forceByCourse || is_array($byCourse)) {
            foreach ([1, 2, 3, 4] as $courseNo) {
                $result[$courseNo] = is_array($byCourse[$courseNo] ?? null)
                    ? $byCourse[$courseNo]
                    : [];
            }
            return $result;
        }

        $result[$selectedCourse] = is_array($data['subject_ids'] ?? null)
            ? $data['subject_ids']
            : [];

        return $result;
    }
}
