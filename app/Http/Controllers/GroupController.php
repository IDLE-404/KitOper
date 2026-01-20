<?php

namespace App\Http\Controllers;

use App\Support\CourseContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $groupsTable = $tables['groups'];

        $groupsQuery = DB::table($groupsTable)
            ->select('id', 'group_name', 'group_number', 'subgroup')
            ->orderBy('group_name');
        $hasSubgroupsColumn = Schema::hasColumn($groupsTable, 'has_subgroups');
        if ($hasSubgroupsColumn) {
            $groupsQuery->addSelect('has_subgroups');
        }
        if (Schema::hasColumn($groupsTable, 'group_type')) {
            $groupsQuery->addSelect('group_type');
        }
        $groups = $groupsQuery->get();

        return view('groups.index', [
            'groups' => $groups,
            'course' => $course,
            'hasGroupType' => Schema::hasColumn($groupsTable, 'group_type'),
            'hasSubgroupsColumn' => $hasSubgroupsColumn,
        ]);
    }

    public function store(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $groupsTable = $tables['groups'];

        $rules = [
            'group_name' => 'required|string|max:255',
        ];
        if (Schema::hasColumn($groupsTable, 'group_type')) {
            $rules['group_type'] = 'nullable|string|in:ru,kz';
        }
        if (Schema::hasColumn($groupsTable, 'has_subgroups')) {
            $rules['has_subgroups'] = 'nullable|boolean';
        }

        $data = $request->validate($rules);
        $groupNumber = $this->extractGroupNumber($data['group_name']);
        if ($groupNumber === null) {
            return back()
                ->withErrors(['group_name' => 'Не найден номер группы в названии. Пример: ПО-115'])
                ->withInput();
        }

        $payload = [
            'group_name' => $data['group_name'],
            'group_number' => $groupNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn($groupsTable, 'group_type')) {
            $payload['group_type'] = $data['group_type'] ?? 'kz';
        }
        if (Schema::hasColumn($groupsTable, 'has_subgroups')) {
            $payload['has_subgroups'] = $request->boolean('has_subgroups');
        }

        DB::table($groupsTable)->insert($payload);

        return redirect()
            ->route('groups.index', ['course' => $course])
            ->with('success', 'Группа добавлена.');
    }

    public function update(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $groupsTable = $tables['groups'];

        $rules = [
            'group_name' => 'required|string|max:255',
        ];
        if (Schema::hasColumn($groupsTable, 'group_type')) {
            $rules['group_type'] = 'nullable|string|in:ru,kz';
        }
        if (Schema::hasColumn($groupsTable, 'has_subgroups')) {
            $rules['has_subgroups'] = 'nullable|boolean';
        }

        $data = $request->validate($rules);
        $groupNumber = $this->extractGroupNumber($data['group_name']);
        if ($groupNumber === null) {
            return back()
                ->withErrors(['group_name' => 'Не найден номер группы в названии. Пример: ПО-115'])
                ->withInput();
        }

        $payload = [
            'group_name' => $data['group_name'],
            'group_number' => $groupNumber,
            'updated_at' => now(),
        ];
        if (Schema::hasColumn($groupsTable, 'group_type')) {
            $payload['group_type'] = $data['group_type'] ?? 'kz';
        }
        if (Schema::hasColumn($groupsTable, 'has_subgroups')) {
            $payload['has_subgroups'] = $request->boolean('has_subgroups');
        }

        DB::table($groupsTable)
            ->where('id', $id)
            ->update($payload);

        return redirect()
            ->route('groups.index', ['course' => $course])
            ->with('success', 'Группа обновлена.');
    }

    public function destroy(Request $request, int $id)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $groupsTable = $tables['groups'];

        try {
            DB::table($groupsTable)
                ->where('id', $id)
                ->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('groups.index', ['course' => $course])
                ->withErrors(['delete' => 'Не удалось удалить группу: есть связанные записи.']);
        }

        return redirect()
            ->route('groups.index', ['course' => $course])
            ->with('success', 'Группа удалена.');
    }

    protected function extractGroupNumber(string $groupName): ?int
    {
        if (!preg_match('/(\d{2,})/', $groupName, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
