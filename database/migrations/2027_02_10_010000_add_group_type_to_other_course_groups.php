<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'second_course_group',
            'third_course_group',
            'fourth_course_group',
        ];
        $ruPrefixes = ['М', 'ПО', 'ТЭ', 'СИБ'];
        $now = now();

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'group_type')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('group_type', 4)->default('kz')->index();
                });
            }

            $ruQuery = DB::table($table);
            foreach ($ruPrefixes as $prefix) {
                $ruQuery->orWhere('group_name', 'like', $prefix . '%');
            }
            $ruQuery->update(['group_type' => 'ru', 'updated_at' => $now]);

            $kzQuery = DB::table($table);
            foreach ($ruPrefixes as $prefix) {
                $kzQuery->where('group_name', 'not like', $prefix . '%');
            }
            $kzQuery->update(['group_type' => 'kz', 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        $tables = [
            'second_course_group',
            'third_course_group',
            'fourth_course_group',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'group_type')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('group_type');
                });
            }
        }
    }
};
