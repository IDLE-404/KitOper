<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $courses = [
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
        ];

        foreach ($courses as $prefix) {
            $table = "{$prefix}_course_teacher_subjects";
            $subjectTable = "{$prefix}_course_subjects";

            if (Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $table) use ($prefix, $subjectTable) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained($subjectTable)->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['teacher_id', 'subject_id'], "{$prefix}_teacher_subject_uniq");
                $table->index(['subject_id', 'teacher_id'], "{$prefix}_teacher_subject_idx");
            });
        }
    }

    public function down(): void
    {
        $courses = ['first', 'second', 'third', 'fourth'];

        foreach ($courses as $prefix) {
            Schema::dropIfExists("{$prefix}_course_teacher_subjects");
        }
    }
};
