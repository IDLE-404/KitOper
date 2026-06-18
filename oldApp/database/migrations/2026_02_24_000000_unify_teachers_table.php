<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teachers')) {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->string('teacher_name');
                $table->string('initials', 20)->nullable();
                $table->timestamps();
            });
        }

        $this->seedTeachers();
        $this->remapTeacherIds();
        $this->dropTeacherForeignKeys();
        $this->dropOldTeacherTables();
    }

    public function down(): void
    {
        // Rollback for this consolidation is intentionally omitted.
    }

    private function seedTeachers(): void
    {
        $this->seedFromTable('frist_course_teachers', true);
        $this->seedFromTable('second_course_teachers');
        $this->seedFromTable('third_course_teachers');
        $this->seedFromTable('fourth_course_teachers');

        if (Schema::hasTable('frist_course_teachers')) {
            DB::statement(
                "UPDATE `teachers` t\n" .
                "JOIN `frist_course_teachers` f ON f.teacher_name = t.teacher_name\n" .
                "SET t.initials = COALESCE(t.initials, f.initials)"
            );
        }
    }

    private function seedFromTable(string $sourceTable, bool $withInitials = false): void
    {
        if (!Schema::hasTable($sourceTable)) {
            return;
        }

        $initials = $withInitials ? 's.initials' : 'NULL';

        DB::statement(
            "INSERT INTO `teachers` (teacher_name, initials, created_at, updated_at)\n" .
            "SELECT s.teacher_name, {$initials}, s.created_at, s.updated_at\n" .
            "FROM `{$sourceTable}` s\n" .
            "LEFT JOIN `teachers` t ON t.teacher_name = s.teacher_name\n" .
            "WHERE s.teacher_name IS NOT NULL AND t.id IS NULL"
        );
    }

    private function remapTeacherIds(): void
    {
        $this->remapTableColumns('first_course_schedules', 'frist_course_teachers', [
            'teacher_id',
            'teacher_id_denominator',
            'teacher_id_denominator_2',
            'teacher_id_2',
            'replacement_teacher_id_1_num',
            'replacement_teacher_id_1_den',
            'replacement_teacher_id_2_den',
            'replacement_teacher_id_2_num',
        ]);

        $this->remapTableColumns('second_course_schedules', 'second_course_teachers', [
            'teacher_id',
            'teacher_id_denominator',
            'teacher_id_denominator_2',
            'teacher_id_2',
            'replacement_teacher_id_1_num',
            'replacement_teacher_id_1_den',
            'replacement_teacher_id_2_den',
            'replacement_teacher_id_2_num',
        ]);

        $this->remapTableColumns('third_course_schedules', 'third_course_teachers', [
            'teacher_id',
            'teacher_id_denominator',
            'teacher_id_denominator_2',
            'teacher_id_2',
            'replacement_teacher_id_1_num',
            'replacement_teacher_id_1_den',
            'replacement_teacher_id_2_den',
            'replacement_teacher_id_2_num',
        ]);

        $this->remapTableColumns('fourth_course_schedules', 'fourth_course_teachers', [
            'teacher_id',
            'teacher_id_denominator',
            'teacher_id_denominator_2',
            'teacher_id_2',
            'replacement_teacher_id_1_num',
            'replacement_teacher_id_1_den',
            'replacement_teacher_id_2_den',
            'replacement_teacher_id_2_num',
        ]);

        $this->remapTableColumns('form_two_normatives', 'frist_course_teachers', ['teacher_id']);
        $this->remapTableColumns('second_form_two_normatives', 'second_course_teachers', ['teacher_id']);
        $this->remapTableColumns('third_form_two_normatives', 'third_course_teachers', ['teacher_id']);
        $this->remapTableColumns('fourth_form_two_normatives', 'fourth_course_teachers', ['teacher_id']);

        $this->remapTableColumns('form_two_records', 'frist_course_teachers', ['teacher_id', 'replacement_teacher_id']);
        $this->remapTableColumns('second_form_two_records', 'second_course_teachers', ['teacher_id', 'replacement_teacher_id']);
        $this->remapTableColumns('third_form_two_records', 'third_course_teachers', ['teacher_id', 'replacement_teacher_id']);
        $this->remapTableColumns('fourth_form_two_records', 'fourth_course_teachers', ['teacher_id', 'replacement_teacher_id']);

        $this->remapTableColumns('schedule_replacements', 'frist_course_teachers', ['absent_teacher_id', 'replacement_teacher_id']);

        $this->remapPracticePeriods(1, 'frist_course_teachers');
        $this->remapPracticePeriods(2, 'second_course_teachers');
        $this->remapPracticePeriods(3, 'third_course_teachers');
        $this->remapPracticePeriods(4, 'fourth_course_teachers');
    }

    private function remapPracticePeriods(int $course, string $sourceTable): void
    {
        if (!Schema::hasTable('practice_periods') || !Schema::hasTable($sourceTable)) {
            return;
        }

        DB::statement(
            "UPDATE `practice_periods` p\n" .
            "LEFT JOIN `{$sourceTable}` s ON p.teacher_id = s.id\n" .
            "LEFT JOIN `teachers` t ON t.teacher_name = s.teacher_name\n" .
            "SET p.teacher_id = t.id\n" .
            "WHERE p.course = {$course} AND p.teacher_id IS NOT NULL"
        );
    }

    private function remapTableColumns(string $table, string $sourceTable, array $columns): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($sourceTable)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement(
                "UPDATE `{$table}` t\n" .
                "LEFT JOIN `{$sourceTable}` s ON t.{$column} = s.id\n" .
                "LEFT JOIN `teachers` n ON n.teacher_name = s.teacher_name\n" .
                "SET t.{$column} = n.id\n" .
                "WHERE t.{$column} IS NOT NULL"
            );
        }
    }

    private function dropTeacherForeignKeys(): void
    {
        $this->dropForeignKey('first_course_schedules', 'teacher_id');
        $this->dropForeignKey('first_course_schedules', 'teacher_id_denominator');
        $this->dropForeignKey('first_course_schedules', 'teacher_id_denominator_2');
        $this->dropForeignKey('first_course_schedules', 'teacher_id_2');
        $this->dropForeignKey('first_course_schedules', 'replacement_teacher_id_1_num');
        $this->dropForeignKey('first_course_schedules', 'replacement_teacher_id_1_den');
        $this->dropForeignKey('first_course_schedules', 'replacement_teacher_id_2_den');
        $this->dropForeignKey('first_course_schedules', 'replacement_teacher_id_2_num');

        $this->dropForeignKey('second_course_schedules', 'teacher_id');
        $this->dropForeignKey('second_course_schedules', 'teacher_id_denominator');
        $this->dropForeignKey('second_course_schedules', 'teacher_id_denominator_2');
        $this->dropForeignKey('second_course_schedules', 'teacher_id_2');
        $this->dropForeignKey('second_course_schedules', 'replacement_teacher_id_1_num');
        $this->dropForeignKey('second_course_schedules', 'replacement_teacher_id_1_den');
        $this->dropForeignKey('second_course_schedules', 'replacement_teacher_id_2_den');
        $this->dropForeignKey('second_course_schedules', 'replacement_teacher_id_2_num');

        $this->dropForeignKey('third_course_schedules', 'teacher_id');
        $this->dropForeignKey('third_course_schedules', 'teacher_id_denominator');
        $this->dropForeignKey('third_course_schedules', 'teacher_id_denominator_2');
        $this->dropForeignKey('third_course_schedules', 'teacher_id_2');
        $this->dropForeignKey('third_course_schedules', 'replacement_teacher_id_1_num');
        $this->dropForeignKey('third_course_schedules', 'replacement_teacher_id_1_den');
        $this->dropForeignKey('third_course_schedules', 'replacement_teacher_id_2_den');
        $this->dropForeignKey('third_course_schedules', 'replacement_teacher_id_2_num');

        $this->dropForeignKey('fourth_course_schedules', 'teacher_id');
        $this->dropForeignKey('fourth_course_schedules', 'teacher_id_denominator');
        $this->dropForeignKey('fourth_course_schedules', 'teacher_id_denominator_2');
        $this->dropForeignKey('fourth_course_schedules', 'teacher_id_2');
        $this->dropForeignKey('fourth_course_schedules', 'replacement_teacher_id_1_num');
        $this->dropForeignKey('fourth_course_schedules', 'replacement_teacher_id_1_den');
        $this->dropForeignKey('fourth_course_schedules', 'replacement_teacher_id_2_den');
        $this->dropForeignKey('fourth_course_schedules', 'replacement_teacher_id_2_num');

        $this->dropForeignKey('form_two_normatives', 'teacher_id');
        $this->dropForeignKey('second_form_two_normatives', 'teacher_id');
        $this->dropForeignKey('third_form_two_normatives', 'teacher_id');
        $this->dropForeignKey('fourth_form_two_normatives', 'teacher_id');

        $this->dropForeignKey('form_two_records', 'teacher_id');
        $this->dropForeignKey('form_two_records', 'replacement_teacher_id');
        $this->dropForeignKey('second_form_two_records', 'teacher_id');
        $this->dropForeignKey('second_form_two_records', 'replacement_teacher_id');
        $this->dropForeignKey('third_form_two_records', 'teacher_id');
        $this->dropForeignKey('third_form_two_records', 'replacement_teacher_id');
        $this->dropForeignKey('fourth_form_two_records', 'teacher_id');
        $this->dropForeignKey('fourth_form_two_records', 'replacement_teacher_id');

        $this->dropForeignKey('schedule_replacements', 'absent_teacher_id');
        $this->dropForeignKey('schedule_replacements', 'replacement_teacher_id');
    }

    private function dropOldTeacherTables(): void
    {
        Schema::dropIfExists('frist_course_teachers');
        Schema::dropIfExists('second_course_teachers');
        Schema::dropIfExists('third_course_teachers');
        Schema::dropIfExists('fourth_course_teachers');
    }

    private function addTeacherForeignKeys(): void
    {
        $this->addTeacherForeignKeysToSchedules('first_course_schedules');
        $this->addTeacherForeignKeysToSchedules('second_course_schedules');
        $this->addTeacherForeignKeysToSchedules('third_course_schedules');
        $this->addTeacherForeignKeysToSchedules('fourth_course_schedules');

        $this->addTeacherForeignKey('form_two_normatives', 'teacher_id');
        $this->addTeacherForeignKey('second_form_two_normatives', 'teacher_id');
        $this->addTeacherForeignKey('third_form_two_normatives', 'teacher_id');
        $this->addTeacherForeignKey('fourth_form_two_normatives', 'teacher_id');

        $this->addTeacherForeignKey('form_two_records', 'teacher_id');
        $this->addTeacherForeignKey('form_two_records', 'replacement_teacher_id');
        $this->addTeacherForeignKey('second_form_two_records', 'teacher_id');
        $this->addTeacherForeignKey('second_form_two_records', 'replacement_teacher_id');
        $this->addTeacherForeignKey('third_form_two_records', 'teacher_id');
        $this->addTeacherForeignKey('third_form_two_records', 'replacement_teacher_id');
        $this->addTeacherForeignKey('fourth_form_two_records', 'teacher_id');
        $this->addTeacherForeignKey('fourth_form_two_records', 'replacement_teacher_id');

        $this->addTeacherForeignKey('schedule_replacements', 'absent_teacher_id');
        $this->addTeacherForeignKey('schedule_replacements', 'replacement_teacher_id');
    }

    private function addTeacherForeignKeysToSchedules(string $table): void
    {
        $this->addTeacherForeignKey($table, 'teacher_id');
        $this->addTeacherForeignKey($table, 'teacher_id_denominator');
        $this->addTeacherForeignKey($table, 'teacher_id_denominator_2');
        $this->addTeacherForeignKey($table, 'teacher_id_2');
        $this->addTeacherForeignKey($table, 'replacement_teacher_id_1_num');
        $this->addTeacherForeignKey($table, 'replacement_teacher_id_1_den');
        $this->addTeacherForeignKey($table, 'replacement_teacher_id_2_den');
        $this->addTeacherForeignKey($table, 'replacement_teacher_id_2_num');
    }

    private function addTeacherForeignKey(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->foreignKeyExists($table, $column)) {
            return;
        }

        $this->ensureUnsignedBigint($table, $column);
        $this->nullInvalidTeacherIds($table, $column);

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column) {
            $tableBlueprint->foreign($column)->references('id')->on('teachers')->nullOnDelete();
        });
    }

    private function dropForeignKey(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $constraint = DB::selectOne(
            "SELECT CONSTRAINT_NAME AS name\n" .
            "FROM information_schema.KEY_COLUMN_USAGE\n" .
            "WHERE TABLE_SCHEMA = DATABASE()\n" .
            "AND TABLE_NAME = ?\n" .
            "AND COLUMN_NAME = ?\n" .
            "AND REFERENCED_TABLE_NAME IS NOT NULL\n" .
            "LIMIT 1",
            [$table, $column]
        );

        if ($constraint && isset($constraint->name)) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint->name}`");
        }
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return false;
        }

        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $constraint = DB::selectOne(
            "SELECT CONSTRAINT_NAME AS name\n" .
            "FROM information_schema.KEY_COLUMN_USAGE\n" .
            "WHERE TABLE_SCHEMA = DATABASE()\n" .
            "AND TABLE_NAME = ?\n" .
            "AND COLUMN_NAME = ?\n" .
            "AND REFERENCED_TABLE_NAME IS NOT NULL\n" .
            "LIMIT 1",
            [$table, $column]
        );

        return $constraint && isset($constraint->name);
    }

    private function ensureUnsignedBigint(string $table, string $column): void
    {
        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` BIGINT UNSIGNED NULL"
        );
    }

    private function nullInvalidTeacherIds(string $table, string $column): void
    {
        DB::statement(
            "UPDATE `{$table}` t\n" .
            "LEFT JOIN `teachers` n ON n.id = t.`{$column}`\n" .
            "SET t.`{$column}` = NULL\n" .
            "WHERE t.`{$column}` IS NOT NULL AND n.id IS NULL"
        );
    }
};
