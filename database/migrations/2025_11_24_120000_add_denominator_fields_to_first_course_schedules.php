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
            $table->unsignedBigInteger('subject_id_denominator')->nullable()->after('subject_id');
            $table->unsignedBigInteger('teacher_id_denominator')->nullable()->after('teacher_id');
            $table->unsignedBigInteger('room_id_denominator')->nullable()->after('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('first_course_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'subject_id_denominator',
                'teacher_id_denominator',
                'room_id_denominator',
            ]);
        });
    }
};
