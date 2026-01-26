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

        $subjects = DB::table('second_course_subjects')
            ->select('id', 'subject_name', 'module_title')
            ->orderBy('id')
            ->get();

        $normalizedMap = [];
        foreach ($subjects as $row) {
            $canonical = $this->canonicalSubjectName((string) ($row->subject_name ?? ''), (string) ($row->module_title ?? ''));
            if ($canonical === '') {
                continue;
            }
            $normalizedMap[$canonical][] = (int) $row->id;
            if ($canonical !== ($row->subject_name ?? '')) {
                DB::table('second_course_subjects')
                    ->where('id', $row->id)
                    ->update(['subject_name' => $canonical]);
            }
        }

        foreach ($normalizedMap as $canonical => $ids) {
            if (count($ids) < 2) {
                continue;
            }
            $keepId = array_shift($ids);
            $dupIds = $ids;

            $this->relinkDuplicates($dupIds, $keepId);

            DB::table('second_course_subjects')
                ->whereIn('id', $dupIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // no-op
    }

    private function canonicalSubjectName(string $subjectName, string $moduleTitle): string
    {
        $name = trim(preg_replace('/\\s+/u', ' ', $subjectName));
        $moduleTitle = trim(preg_replace('/\\s+/u', ' ', $moduleTitle));

        if ($moduleTitle !== '' && mb_stripos($name, $moduleTitle, 0, 'UTF-8') === 0) {
            $name = trim(mb_substr($name, mb_strlen($moduleTitle, 'UTF-8'), null, 'UTF-8'));
        }

        $name = preg_replace('/^(?:ООМ|ПМ|БМ|ЖММ|КМ|ООД)\\s*\\d+(?:\\.\\d+)?\\s*/iu', '', $name);
        $name = trim(preg_replace('/\\s+/u', ' ', $name));

        return $name;
    }

    private function relinkDuplicates(array $dupIds, int $keepId): void
    {
        if (Schema::hasTable('second_course_schedules')) {
            $columns = [
                'subject_id',
                'subject_id_2',
                'subject_id_denominator',
                'subject_id_denominator_2',
                'replacement_subject_id_1_num',
                'replacement_subject_id_1_den',
                'replacement_subject_id_2_num',
                'replacement_subject_id_2_den',
            ];
            foreach ($columns as $column) {
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
    }
};
