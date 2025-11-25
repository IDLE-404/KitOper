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
        Schema::table('first_course_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('first_course_schedules', 'subject_id_denominator_2')) {
                $table->unsignedBigInteger('subject_id_denominator_2')->nullable()->after('subject_id_denominator');
            }
            if (!Schema::hasColumn('first_course_schedules', 'teacher_id_denominator_2')) {
                $table->unsignedBigInteger('teacher_id_denominator_2')->nullable()->after('teacher_id_denominator');
            }
            if (!Schema::hasColumn('first_course_schedules', 'room_id_denominator_2')) {
                $table->unsignedBigInteger('room_id_denominator_2')->nullable()->after('room_id_denominator');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_course_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('first_course_schedules', 'room_id_denominator_2')) {
                $table->dropColumn('room_id_denominator_2');
            }
            if (Schema::hasColumn('first_course_schedules', 'teacher_id_denominator_2')) {
                $table->dropColumn('teacher_id_denominator_2');
            }
            if (Schema::hasColumn('first_course_schedules', 'subject_id_denominator_2')) {
                $table->dropColumn('subject_id_denominator_2');
            }
        });
    }
};
