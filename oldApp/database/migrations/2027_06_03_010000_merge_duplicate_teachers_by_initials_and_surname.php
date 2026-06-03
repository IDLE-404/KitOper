<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teachers')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $duplicates = DB::table('teachers')
            ->select('initials')
            ->whereNotNull('initials')
            ->where('initials', '<>', '')
            ->groupBy('initials')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('initials')
            ->all();

        if (empty($duplicates)) {
            return;
        }

        foreach ($duplicates as $initials) {
            $rows = DB::table('teachers')
                ->select('id', 'teacher_name', 'initials', 'default_room_id')
                ->where('initials', $initials)
                ->orderBy('id')
                ->get()
                ->map(function ($row) {
                    $row->teacher_name = trim((string) ($row->teacher_name ?? ''));
                    $row->surname = $this->extractSurname($row->teacher_name, (string) ($row->initials ?? ''));
                    return $row;
                })
                ->groupBy('surname');

            foreach ($rows as $surname => $group) {
                if ($surname === '' || $group->count() < 2) {
                    continue;
                }

                $keep = $this->pickCanonicalTeacher($group->all(), (string) $initials);
                if (!$keep) {
                    continue;
                }

                $keepId = (int) $keep->id;

                foreach ($group as $candidate) {
                    $dropId = (int) $candidate->id;
                    if ($dropId === $keepId) {
                        continue;
                    }

                    $this->mergeTeacher($keepId, $dropId);
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }

    private function pickCanonicalTeacher(array $teachers, string $initials): ?object
    {
        usort($teachers, function ($a, $b) use ($initials) {
            $scoreA = $this->teacherQualityScore((string) ($a->teacher_name ?? ''), $initials);
            $scoreB = $this->teacherQualityScore((string) ($b->teacher_name ?? ''), $initials);
            if ($scoreA !== $scoreB) {
                return $scoreB <=> $scoreA;
            }

            $lenA = mb_strlen((string) ($a->teacher_name ?? ''), 'UTF-8');
            $lenB = mb_strlen((string) ($b->teacher_name ?? ''), 'UTF-8');
            if ($lenA !== $lenB) {
                return $lenB <=> $lenA;
            }

            return ((int) $a->id) <=> ((int) $b->id);
        });

        return $teachers[0] ?? null;
    }

    private function teacherQualityScore(string $teacherName, string $initials): int
    {
        $teacherNorm = $this->normName($teacherName);
        $initialsNorm = $this->normName($initials);

        $score = 0;
        if ($teacherNorm !== '' && $teacherNorm !== $initialsNorm) {
            $score += 50;
        }

        if (preg_match('/\s/u', $teacherName) === 1) {
            $score += 20;
        }

        if (preg_match('/\./u', $teacherName) === 0) {
            $score += 10;
        }

        return $score;
    }

    private function mergeTeacher(int $keepId, int $dropId): void
    {
        if ($keepId === $dropId) {
            return;
        }

        $foreignColumns = $this->teacherForeignKeyColumns();
        foreach ($foreignColumns as $ref) {
            $this->repointForeignColumn($ref['table'], $ref['column'], $keepId, $dropId);
        }

        $keep = DB::table('teachers')->where('id', $keepId)->first();
        $drop = DB::table('teachers')->where('id', $dropId)->first();

        if ($keep && $drop) {
            $update = [];
            if (empty($keep->default_room_id) && !empty($drop->default_room_id)) {
                $update['default_room_id'] = $drop->default_room_id;
            }
            if (empty($keep->initials) && !empty($drop->initials)) {
                $update['initials'] = $drop->initials;
            }
            if (!empty($update)) {
                $update['updated_at'] = now();
                DB::table('teachers')->where('id', $keepId)->update($update);
            }
        }

        DB::table('teachers')->where('id', $dropId)->delete();
    }

    private function repointForeignColumn(string $table, string $column, int $keepId, int $dropId): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        foreach ($this->uniqueIndexesForColumn($table, $column) as $columns) {
            $this->deleteConflictsForUniqueIndex($table, $column, $columns, $keepId, $dropId);
        }

        DB::table($table)
            ->where($column, $dropId)
            ->update([$column => $keepId]);
    }

    private function deleteConflictsForUniqueIndex(string $table, string $teacherColumn, array $indexColumns, int $keepId, int $dropId): void
    {
        $quote = fn (string $name): string => '`' . str_replace('`', '``', $name) . '`';

        $joinParts = [];
        foreach ($indexColumns as $col) {
            if ($col === $teacherColumn) {
                $joinParts[] = 'new.' . $quote($col) . ' = ?';
            } else {
                $joinParts[] = 'new.' . $quote($col) . ' <=> old.' . $quote($col);
            }
        }

        $sql = 'DELETE old FROM ' . $quote($table) . ' old '
            . 'JOIN ' . $quote($table) . ' new ON ' . implode(' AND ', $joinParts) . ' '
            . 'WHERE old.' . $quote($teacherColumn) . ' = ?';

        DB::statement($sql, [$keepId, $dropId]);
    }

    private function teacherForeignKeyColumns(): array
    {
        $dbName = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT TABLE_NAME as table_name, COLUMN_NAME as column_name '
            . 'FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_SCHEMA = ? '
            . 'AND REFERENCED_TABLE_NAME = ?'
            , [$dbName, $dbName, 'teachers']
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'table' => (string) $row->table_name,
                'column' => (string) $row->column_name,
            ];
        }

        return $result;
    }

    private function uniqueIndexesForColumn(string $table, string $column): array
    {
        $dbName = DB::getDatabaseName();

        $rows = DB::select(
            'SELECT INDEX_NAME as index_name, COLUMN_NAME as column_name, SEQ_IN_INDEX as seq_in_index '
            . 'FROM information_schema.STATISTICS '
            . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND NON_UNIQUE = 0',
            [$dbName, $table]
        );

        $byIndex = [];
        foreach ($rows as $row) {
            $indexName = (string) $row->index_name;
            if (!isset($byIndex[$indexName])) {
                $byIndex[$indexName] = [];
            }
            $byIndex[$indexName][(int) $row->seq_in_index] = (string) $row->column_name;
        }

        $result = [];
        foreach ($byIndex as $cols) {
            ksort($cols);
            $columns = array_values($cols);
            if (in_array($column, $columns, true)) {
                $result[] = $columns;
            }
        }

        return $result;
    }

    private function extractSurname(string $teacherName, string $initials): string
    {
        $source = $teacherName !== '' ? $teacherName : $initials;
        $source = trim(preg_replace('/\s+/u', ' ', $source) ?: $source);
        if ($source === '') {
            return '';
        }

        $parts = explode(' ', $source);
        $surname = $parts[0] ?? '';

        return $this->normName((string) $surname);
    }

    private function normName(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = str_replace('ё', 'е', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?: $value;

        return trim($value);
    }
};
