<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $courses = [
            'form_two_normatives',
            'second_form_two_normatives',
            'third_form_two_normatives',
            'fourth_form_two_normatives',
        ];

        foreach ($courses as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Создаём новую таблицу с правильной структурой
            $newTable = $table . '_new';
            Schema::create($newTable, function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('group_id');
                $t->unsignedBigInteger('subject_id');
                $t->unsignedBigInteger('teacher_id')->nullable();
                $t->tinyInteger('semester')->unsigned();
                $t->integer('total_hours')->unsigned()->default(0);
                $t->integer('hours_per_class')->unsigned()->default(2);
                $t->timestamps();
            });

            // Добавляем уникальный индекс (имя уникально по таблице, т.к. в SQLite индексы глобальные)
            Schema::table($newTable, function (Blueprint $t) use ($newTable) {
                $t->unique(['group_id', 'subject_id', 'teacher_id', 'semester'], "uk_norm_{$newTable}");
            });

            // Копируем данные: для каждого (group_id, subject_id, teacher_id, semester)
            // берём норматив с наибольшим total_hours (самый полный)
            DB::statement("
                INSERT INTO {$newTable} (id, group_id, subject_id, teacher_id, semester, total_hours, hours_per_class, created_at, updated_at)
                SELECT
                    MAX(id) as id,
                    group_id,
                    subject_id,
                    teacher_id,
                    CASE
                        WHEN month >= 9 THEN 1
                        ELSE 2
                    END as semester,
                    MAX(total_hours) as total_hours,
                    MAX(hours_per_class) as hours_per_class,
                    MAX(created_at) as created_at,
                    MAX(updated_at) as updated_at
                FROM {$table}
                GROUP BY group_id, subject_id, teacher_id, semester
            ");

            // Удаляем старую таблицу
            Schema::drop($table);

            // Переименовываем новую в старую
            Schema::rename($newTable, $table);
        }
    }

    public function down(): void
    {
        // Откат: восстанавливаем старую структуру (если нужно)
    }
};
