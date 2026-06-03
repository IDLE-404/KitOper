<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('first_course_group')) {
            return;
        }

        if (!Schema::hasColumn('first_course_group', 'group_type')) {
            Schema::table('first_course_group', function (Blueprint $table) {
                $table->string('group_type', 4)->default('kz')->index();
            });
        }

        $ruPrefixes = ['М', 'ПО', 'ТЭ', 'СИБ'];
        $now = now();

        $ruQuery = DB::table('first_course_group');
        foreach ($ruPrefixes as $prefix) {
            $ruQuery->orWhere('group_name', 'like', $prefix . '%');
        }
        $ruQuery->update(['group_type' => 'ru', 'updated_at' => $now]);

        $kzQuery = DB::table('first_course_group');
        foreach ($ruPrefixes as $prefix) {
            $kzQuery->where('group_name', 'not like', $prefix . '%');
        }
        $kzQuery->update(['group_type' => 'kz', 'updated_at' => $now]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('first_course_group')) {
            return;
        }

        if (Schema::hasColumn('first_course_group', 'group_type')) {
            Schema::table('first_course_group', function (Blueprint $table) {
                $table->dropColumn('group_type');
            });
        }
    }
};
