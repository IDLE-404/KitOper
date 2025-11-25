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
        Schema::create('schedule_replacements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->string('study_day');
            $table->unsignedTinyInteger('lesson_number');
            $table->string('week_mode', 20)->default('single'); // single/numerator/denominator
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('absent_teacher_id')->nullable();
            $table->unsignedBigInteger('replacement_teacher_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('comment', 255)->nullable();
            $table->timestamps();

            $table->index(['group_id', 'study_day', 'lesson_number', 'week_mode'], 'schedule_repl_group_slot_idx');
            $table->index(['absent_teacher_id', 'study_day', 'lesson_number'], 'schedule_repl_absent_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_replacements');
    }
};
