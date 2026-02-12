<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = 58; // ПО-234
        $year = 2026;
        $month = 2;

        DB::table('second_form_two_records')
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->update(['hours_per_class' => 2]);

        DB::table('second_form_two_records')
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'normal')
            ->update(['used_hours' => 2]);

        DB::table('second_form_two_records')
            ->where('group_id', $groupId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'replacement')
            ->update(['bonus_hours' => 2]);
    }

    public function down(): void
    {
        // нет безопасного отката: исходные значения неизвестны
    }
};
