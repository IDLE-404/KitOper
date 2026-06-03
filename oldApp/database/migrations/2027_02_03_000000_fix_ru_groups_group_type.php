<?php

use Illuminate\Database\Migrations\Migration;
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
            return;
        }

        $ruGroups = [
            'ПО-115',
            'ПО-145',
            'ПО-155',
            'ПО-165',
            'ПО-175',
            'ПО-185',
            'ПО-195',
            'СИБ-135',
            'СИБ-145',
            'ТЭ-115',
        ];

        DB::table('first_course_group')
            ->whereIn('group_name', $ruGroups)
            ->update(['group_type' => 'ru', 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('first_course_group')) {
            return;
        }
        if (!Schema::hasColumn('first_course_group', 'group_type')) {
            return;
        }

        $ruGroups = [
            'ПО-115',
            'ПО-145',
            'ПО-155',
            'ПО-165',
            'ПО-175',
            'ПО-185',
            'ПО-195',
            'СИБ-135',
            'СИБ-145',
            'ТЭ-115',
        ];

        DB::table('first_course_group')
            ->whereIn('group_name', $ruGroups)
            ->update(['group_type' => 'kz', 'updated_at' => now()]);
    }
};
