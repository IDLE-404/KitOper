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

    /**
     * Глобальное завершение учебного года: группы переводятся из таблицы
     * текущего курса в таблицу следующего курса с переименованием (233→333).
     * Группы последнего года обучения удаляются (выпуск).
     * Учителя и дисциплины не затрагиваются — они хранятся в отдельных
     * таблицах и автоматически доступны в новом учебном году.
     */
    public function finishYearGlobal(Request $request)
    {
        $stats = ['promoted' => 0, 'graduated' => 0];
        $now   = now();

        DB::transaction(function () use (&$stats, $now) {
            // Обрабатываем с конца, чтобы освободить таблицу следующего курса
            // до того, как туда переместятся группы предыдущего
            for ($course = 4; $course >= 1; $course--) {
                $tables      = CourseContext::tables($course);
                $groupsTable = $tables['groups'];

                if (!Schema::hasTable($groupsTable)) {
                    continue;
                }

                $groups = DB::table($groupsTable)->get();

                foreach ($groups as $group) {
                    $groupNumber = (int) ($group->group_number ?? 0);

                    // Группы без курсового префикса в номере (< 100) пропускаем
                    if ($groupNumber < 100) {
                        continue;
                    }

                    $firstDigit = intdiv($groupNumber, 100);
                    $prefix     = $this->groupPrefix((string) ($group->group_name ?? ''));
                    $maxYear    = $prefix === 'ТЭ' ? 4 : 3;

                    // 4-й курс или группа достигла максимального года → выпуск
                    if ($course === 4 || $firstDigit >= $maxYear) {
                        DB::table($groupsTable)->where('id', $group->id)->delete();
                        $stats['graduated']++;
                        continue;
                    }

                    $tail      = $groupNumber % 100;
                    $newNumber = ($firstDigit + 1) * 100 + $tail;
                    $newName   = $this->replaceLastNumber((string) ($group->group_name ?? ''), $newNumber);

                    $nextTables      = CourseContext::tables($course + 1);
                    $nextGroupsTable = $nextTables['groups'];

                    $payload = [
                        'group_name'   => $newName,
                        'group_number' => $newNumber,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];

                    // Переносим необязательные атрибуты группы, если они есть в целевой таблице
                    foreach (['subgroup', 'group_type', 'has_subgroups'] as $col) {
                        if (Schema::hasColumn($nextGroupsTable, $col) && property_exists($group, $col)) {
                            $payload[$col] = $group->{$col};
                        }
                    }

                    DB::table($nextGroupsTable)->insert($payload);
                    DB::table($groupsTable)->where('id', $group->id)->delete();
                    $stats['promoted']++;
                }
            }
        });

        return redirect()
            ->route('groups.index')
            ->with('success', sprintf(
                'Учебный год завершён: переведено %d групп, выпущено %d групп.',
                $stats['promoted'],
                $stats['graduated']
            ));
    }

    public function finishYear(Request $request)
    {
        $course = CourseContext::normalize($request->integer('course') ?? 1);
        $tables = CourseContext::tables($course);
        $groupsTable = $tables['groups'];

        $groups = DB::table($groupsTable)
            ->select('id', 'group_name', 'group_number')
            ->get();

        $now = now();
        $deleted = 0;
        $updated = 0;

        DB::transaction(function () use ($groups, $groupsTable, $now, &$deleted, &$updated) {
            foreach ($groups as $group) {
                $groupNumber = (int) ($group->group_number ?? 0);
                if ($groupNumber < 100) {
                    continue;
                }

                $firstDigit = intdiv($groupNumber, 100);
                $tail = $groupNumber % 100;
                $prefix = $this->groupPrefix((string) ($group->group_name ?? ''));

                $maxYear = $prefix === 'ТЭ' ? 4 : 3;
                if ($firstDigit >= $maxYear) {
                    DB::table($groupsTable)->where('id', $group->id)->delete();
                    $deleted++;
                    continue;
                }

                $newNumber = ($firstDigit + 1) * 100 + $tail;
                $newName = $this->replaceLastNumber((string) ($group->group_name ?? ''), $newNumber);

                DB::table($groupsTable)
                    ->where('id', $group->id)
                    ->update([
                        'group_number' => $newNumber,
                        'group_name' => $newName,
                        'updated_at' => $now,
                    ]);
                $updated++;
            }
        });

        return redirect()
            ->route('groups.index', ['course' => $course])
            ->with('success', "Учебный год завершен: обновлено {$updated}, удалено {$deleted}.");
    }

    protected function extractGroupNumber(string $groupName): ?int
    {
        if (!preg_match('/(\d{2,})/', $groupName, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function groupPrefix(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        $parts = preg_split('/[\\s\\-\\/]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        $prefix = $parts[0] ?? '';
        return mb_strtoupper($prefix, 'UTF-8');
    }

    private function replaceLastNumber(string $name, int $number): string
    {
        if ($name === '') {
            return (string) $number;
        }
        $updated = preg_replace('/(\\d+)(?!.*\\d)/u', (string) $number, $name);
        return $updated ?: $name;
    }
}
