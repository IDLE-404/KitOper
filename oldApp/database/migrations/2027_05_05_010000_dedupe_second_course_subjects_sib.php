<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('second_course_subjects')) {
            return;
        }

        $duplicateNames = DB::table('second_course_subjects')
            ->select('subject_name')
            ->groupBy('subject_name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('subject_name')
            ->all();

        if (empty($duplicateNames)) {
            return;
        }

        $scheduleColumns = [
            'subject_id',
            'subject_id_2',
            'subject_id_denominator',
            'subject_id_denominator_2',
            'replacement_subject_id_1_num',
            'replacement_subject_id_1_den',
            'replacement_subject_id_2_num',
            'replacement_subject_id_2_den',
        ];

        foreach ($duplicateNames as $subjectName) {
            $ids = DB::table('second_course_subjects')
                ->where('subject_name', $subjectName)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (count($ids) < 2) {
                continue;
            }

            $keepId = array_shift($ids);
            $dupIds = $ids;

            if (Schema::hasTable('second_course_schedules')) {
                foreach ($scheduleColumns as $column) {
                    if (Schema::hasColumn('second_course_schedules', $column)) {
                        DB::table('second_course_schedules')
                            ->whereIn($column, $dupIds)
                            ->update([$column => $keepId]);
                    }
                }
            }

            if (Schema::hasTable('second_form_two_normatives') && Schema::hasColumn('second_form_two_normatives', 'subject_id')) {
                DB::table('second_form_two_normatives')
                    ->whereIn('subject_id', $dupIds)
                    ->update(['subject_id' => $keepId]);
            }

            if (Schema::hasTable('second_form_two_records')) {
                if (Schema::hasColumn('second_form_two_records', 'subject_id')) {
                    DB::table('second_form_two_records')
                        ->whereIn('subject_id', $dupIds)
                        ->update(['subject_id' => $keepId]);
                }
                if (Schema::hasColumn('second_form_two_records', 'replacement_subject_id')) {
                    DB::table('second_form_two_records')
                        ->whereIn('replacement_subject_id', $dupIds)
                        ->update(['replacement_subject_id' => $keepId]);
                }
            }

            if (Schema::hasTable('second_course_teacher_subjects') && Schema::hasColumn('second_course_teacher_subjects', 'subject_id')) {
                DB::table('second_course_teacher_subjects')
                    ->whereIn('subject_id', $dupIds)
                    ->update(['subject_id' => $keepId]);
            }

            DB::table('second_course_subjects')
                ->whereIn('id', $dupIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // no-op
    }
};
