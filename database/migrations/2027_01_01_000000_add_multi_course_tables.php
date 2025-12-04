<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $courses = [
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
        ];

        foreach ($courses as $prefix) {
            $groupTable = "{$prefix}_course_group";
            $subjectTable = "{$prefix}_course_subjects";
            $teacherTable = "{$prefix}_course_teachers";
            $scheduleTable = "{$prefix}_course_schedules";
            $formTwoNormatives = "{$prefix}_form_two_normatives";
            $formTwoRecords = "{$prefix}_form_two_records";

            if (!Schema::hasTable($groupTable)) {
                Schema::create($groupTable, function (Blueprint $table) {
                    $table->id();
                    $table->string('group_name');
                    $table->unsignedSmallInteger('group_number');
                    $table->string('subgroup', 1)->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable($subjectTable)) {
                Schema::create($subjectTable, function (Blueprint $table) {
                    $table->id();
                    $table->string('module_title')->nullable();
                    $table->integer('module_index')->nullable();
                    $table->string('subject_name')->nullable();
                    $table->string('name_ru')->nullable();
                    $table->string('name_kz')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable($teacherTable)) {
                Schema::create($teacherTable, function (Blueprint $table) {
                    $table->id();
                    $table->string('teacher_name');
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable($scheduleTable)) {
                Schema::create($scheduleTable, function (Blueprint $table) use ($groupTable, $subjectTable, $teacherTable, $scheduleTable) {
                    $table->id();
                    $table->unsignedBigInteger('replaces_schedule_id')->nullable();
                    $table->date('week_start')->nullable()->index();
                    $table->enum('study_day', ['Понедельник','Вторник','Среда','Четверг','Пятница','Суббота']);
                    $table->unsignedTinyInteger('lesson_number');
                    $table->foreignId('group_id')->constrained($groupTable)->cascadeOnDelete();
                    $table->foreignId('subject_id')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->foreignId('subject_id_denominator')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->foreignId('subject_id_denominator_2')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->foreignId('subject_id_2')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->foreignId('teacher_id')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('teacher_id_denominator')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('teacher_id_denominator_2')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('teacher_id_2')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->unsignedBigInteger('room_id')->nullable();

                    $table->boolean('is_absent_1_num')->default(false);
                    $table->boolean('is_replacement_1_num')->default(false);
                    $table->foreignId('replacement_teacher_id_1_num')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('replacement_subject_id_1_num')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->string('replacement_comment_1_num', 255)->nullable();

                    $table->unsignedBigInteger('room_id_denominator')->nullable();
                    $table->boolean('is_absent_1_den')->default(false);
                    $table->boolean('is_replacement_1_den')->default(false);
                    $table->foreignId('replacement_teacher_id_1_den')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('replacement_subject_id_1_den')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->string('replacement_comment_1_den', 255)->nullable();

                    $table->unsignedBigInteger('room_id_denominator_2')->nullable();
                    $table->boolean('is_absent_2_den')->default(false);
                    $table->boolean('is_replacement_2_den')->default(false);
                    $table->foreignId('replacement_teacher_id_2_den')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('replacement_subject_id_2_den')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->string('replacement_comment_2_den', 255)->nullable();

                    $table->unsignedBigInteger('room_id_2')->nullable();
                    $table->boolean('is_absent_2_num')->default(false);
                    $table->boolean('is_replacement_2_num')->default(false);
                    $table->foreignId('replacement_teacher_id_2_num')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('replacement_subject_id_2_num')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->string('replacement_comment_2_num', 255)->nullable();

                    $table->string('subgroup', 1)->nullable();
                    $table->boolean('is_replacement')->default(false);
                    $table->timestamps();

                    $table->index(['group_id', 'week_start'], "{$scheduleTable}_group_week_idx");
                    $table->index(['room_id', 'study_day', 'lesson_number'], "{$scheduleTable}_room_idx");
                    $table->index(['room_id_denominator', 'study_day', 'lesson_number'], "{$scheduleTable}_room_den_idx");
                });
            }

            if (!Schema::hasTable($formTwoNormatives)) {
                Schema::create($formTwoNormatives, function (Blueprint $table) use ($groupTable, $subjectTable, $teacherTable, $formTwoNormatives) {
                    $table->id();
                    $table->foreignId('group_id')->constrained($groupTable)->cascadeOnDelete();
                    $table->foreignId('subject_id')->constrained($subjectTable)->cascadeOnDelete();
                    $table->foreignId('teacher_id')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->unsignedTinyInteger('month');
                    $table->unsignedSmallInteger('year');
                    $table->integer('total_hours')->default(0);
                    $table->integer('hours_per_class')->default(2);
                    $table->timestamps();
                    $table->unique(['group_id', 'subject_id', 'teacher_id', 'month', 'year'], "{$formTwoNormatives}_uniq");
                });
            }

            if (!Schema::hasTable($formTwoRecords)) {
                Schema::create($formTwoRecords, function (Blueprint $table) use ($groupTable, $subjectTable, $teacherTable, $formTwoRecords) {
                    $table->id();
                    $table->foreignId('group_id')->constrained($groupTable)->cascadeOnDelete();
                    $table->unsignedSmallInteger('year');
                    $table->unsignedTinyInteger('month');
                    $table->unsignedTinyInteger('day')->nullable();
                    $table->date('class_date')->nullable();
                    $table->unsignedTinyInteger('lesson_number')->nullable();
                    $table->string('subgroup', 2)->nullable();
                    $table->foreignId('subject_id')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->foreignId('teacher_id')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->integer('total_hours')->default(0);
                    $table->integer('hours_per_class')->default(2);
                    $table->enum('status', ['normal', 'sick', 'replacement', 'replaced'])->default('normal');
                    $table->foreignId('replacement_teacher_id')->nullable()->constrained($teacherTable)->nullOnDelete();
                    $table->foreignId('replacement_subject_id')->nullable()->constrained($subjectTable)->nullOnDelete();
                    $table->integer('bonus_hours')->nullable();
                    $table->integer('used_hours')->default(0);
                    $table->string('absent_reason')->nullable();
                    $table->string('replacement_comment')->nullable();
                    $table->string('mode', 20)->default('single');
                    $table->timestamps();

                    $table->index(['group_id', 'class_date', 'lesson_number', 'subgroup', 'mode'], "{$formTwoRecords}_date_idx");
                });
            }
        }
    }

    public function down(): void
    {
        $courses = ['second', 'third', 'fourth'];
        foreach ($courses as $prefix) {
            Schema::dropIfExists("{$prefix}_form_two_records");
            Schema::dropIfExists("{$prefix}_form_two_normatives");
            Schema::dropIfExists("{$prefix}_course_schedules");
            Schema::dropIfExists("{$prefix}_course_teachers");
            Schema::dropIfExists("{$prefix}_course_subjects");
            Schema::dropIfExists("{$prefix}_course_group");
        }
    }
};
