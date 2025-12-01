<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_two_records', function (Blueprint $table) {
            if (!Schema::hasColumn('form_two_records', 'replacement_subject_id')) {
                $table->unsignedBigInteger('replacement_subject_id')
                    ->nullable()
                    ->after('replacement_teacher_id');
            }
        });

        Schema::table('first_course_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('first_course_schedules', 'replacement_subject_id_1_num')) {
                $table->unsignedBigInteger('replacement_subject_id_1_num')->nullable()->after('replacement_teacher_id_1_num');
            }
            if (!Schema::hasColumn('first_course_schedules', 'replacement_subject_id_1_den')) {
                $table->unsignedBigInteger('replacement_subject_id_1_den')->nullable()->after('replacement_teacher_id_1_den');
            }
            if (!Schema::hasColumn('first_course_schedules', 'replacement_subject_id_2_num')) {
                $table->unsignedBigInteger('replacement_subject_id_2_num')->nullable()->after('replacement_teacher_id_2_num');
            }
            if (!Schema::hasColumn('first_course_schedules', 'replacement_subject_id_2_den')) {
                $table->unsignedBigInteger('replacement_subject_id_2_den')->nullable()->after('replacement_teacher_id_2_den');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_two_records', function (Blueprint $table) {
            if (Schema::hasColumn('form_two_records', 'replacement_subject_id')) {
                $table->dropColumn('replacement_subject_id');
            }
        });

        Schema::table('first_course_schedules', function (Blueprint $table) {
            $cols = [
                'replacement_subject_id_1_num',
                'replacement_subject_id_1_den',
                'replacement_subject_id_2_num',
                'replacement_subject_id_2_den',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('first_course_schedules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
