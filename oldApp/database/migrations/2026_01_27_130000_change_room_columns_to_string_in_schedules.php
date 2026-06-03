<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'first_course_schedules',
            'second_course_schedules',
            'third_course_schedules',
            'fourth_course_schedules',
        ];

        $columns = [
            'room_id',
            'room_id_denominator',
            'room_id_denominator_2',
            'room_id_2',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                // В проекте кабинеты используются как строковые коды (например, "1it"),
                // поэтому переводим room-поля расписания в VARCHAR.
                DB::statement(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` VARCHAR(50) NULL',
                    $table,
                    $column
                ));
            }
        }
    }

    public function down(): void
    {
        // Обратное преобразование в bigint может повредить данные,
        // поэтому даун-миграцию оставляем пустой намеренно.
    }
};

