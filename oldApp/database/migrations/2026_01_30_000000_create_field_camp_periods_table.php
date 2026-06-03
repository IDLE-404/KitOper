<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_camp_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('course')->default(1);
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('room_id', 50)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('hours_per_day')->default(6);
            $table->timestamps();

            $table->index(['course', 'group_id', 'start_date', 'end_date'], 'field_camp_periods_course_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_camp_periods');
    }
};
