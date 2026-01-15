<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('start_month');
            $table->unsignedTinyInteger('start_day');
            $table->unsignedTinyInteger('end_month');
            $table->unsignedTinyInteger('end_day');
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['year', 'start_month'], 'holidays_year_month_idx');
        });

        $now = now();
        $rows = [];

        foreach (config('kazakhstan_holidays.default', []) as $definition => $name) {
            if (!preg_match('/^(?<month>\d{2})-(?<day>\d{2})$/', $definition, $matches)) {
                continue;
            }
            $month = (int) $matches['month'];
            $day = (int) $matches['day'];
            $rows[] = [
                'name' => $name,
                'start_month' => $month,
                'start_day' => $day,
                'end_month' => $month,
                'end_day' => $day,
                'year' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $rows[] = [
            'name' => 'Каникулы',
            'start_month' => 1,
            'start_day' => 19,
            'end_month' => 2,
            'end_day' => 1,
            'year' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (!empty($rows)) {
            DB::table('holidays')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
