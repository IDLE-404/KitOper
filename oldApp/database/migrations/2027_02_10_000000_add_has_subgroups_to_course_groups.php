<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'first_course_group',
            'second_course_group',
            'third_course_group',
            'fourth_course_group',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'has_subgroups')) {
                continue;
            }
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('has_subgroups')->default(false)->after('subgroup');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'first_course_group',
            'second_course_group',
            'third_course_group',
            'fourth_course_group',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'has_subgroups')) {
                continue;
            }
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('has_subgroups');
            });
        }
    }
};
