<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teachers')) {
            return;
        }

        $teacherId = DB::table('teachers')
            ->where('teacher_name', 'Вакансия')
            ->value('id');

        if (!$teacherId) {
            $payload = [
                'teacher_name' => 'Вакансия',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('teachers', 'initials')) {
                $payload['initials'] = null;
            }
            $teacherId = (int) DB::table('teachers')->insertGetId($payload);
        }

        if (!Schema::hasTable('first_course_subjects') || !Schema::hasTable('first_course_teacher_subjects')) {
            return;
        }

        $subjectIds = DB::table('first_course_subjects')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if (empty($subjectIds)) {
            return;
        }

        $existing = DB::table('first_course_teacher_subjects')
            ->where('teacher_id', $teacherId)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missing = array_values(array_diff($subjectIds, $existing));
        if (empty($missing)) {
            return;
        }

        $now = now();
        $payload = array_map(
            fn (int $subjectId) => [
                'teacher_id' => (int) $teacherId,
                'subject_id' => $subjectId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $missing
        );

        DB::table('first_course_teacher_subjects')->insert($payload);
    }

    public function down(): void
    {
        // Откат не удаляет преподавателя и связи, чтобы не потерять данные.
    }
};
