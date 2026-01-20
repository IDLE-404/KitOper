<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = [
            'form_two_normatives',
            'second_form_two_normatives',
            'third_form_two_normatives',
            'fourth_form_two_normatives',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'teacher_id')) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` MODIFY `teacher_id` BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = [
            'form_two_normatives',
            'second_form_two_normatives',
            'third_form_two_normatives',
            'fourth_form_two_normatives',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'teacher_id')) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` MODIFY `teacher_id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
