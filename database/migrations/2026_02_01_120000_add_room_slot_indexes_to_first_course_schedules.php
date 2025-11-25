<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $hasModeColumn = Schema::hasColumn('first_course_schedules', 'mode');

        Schema::table('first_course_schedules', function (Blueprint $table) use ($driver, $hasModeColumn) {
            if (!$hasModeColumn) {
                if (in_array($driver, ['mysql', 'mariadb'], true)) {
                    $table->string('mode', 12)
                        ->storedAs("case when subject_id_denominator is null and teacher_id_denominator is null and room_id_denominator is null then 'single' else 'numerator' end")
                        ->nullable();
                } else {
                    $table->string('mode', 12)->nullable()->default('single');
                }
            }

            $table->index(['room_id', 'study_day', 'lesson_number', 'mode'], 'first_course_schedules_room_mode_idx');
            $table->index(['room_id_denominator', 'study_day', 'lesson_number'], 'first_course_schedules_room_den_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_course_schedules', function (Blueprint $table) {
            $table->dropIndex('first_course_schedules_room_mode_idx');
            $table->dropIndex('first_course_schedules_room_den_idx');

            if (Schema::hasColumn('first_course_schedules', 'mode')) {
                $table->dropColumn('mode');
            }
        });
    }
};
