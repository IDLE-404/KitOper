<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->coursePrefixes() as $course => $prefix) {
            $practiceTable = $this->practiceTableName($course, $prefix);
            $groupTable = "{$prefix}_course_group";
            $subjectTable = "{$prefix}_course_subjects";
            $teacherTable = 'teachers';

            if (!Schema::hasTable($practiceTable)) {
                Schema::create($practiceTable, function (Blueprint $table) use ($practiceTable, $groupTable, $subjectTable, $teacherTable) {
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

                    $table->index(['group_id', 'class_date', 'lesson_number', 'subgroup', 'mode'], "{$practiceTable}_date_idx");
                });
            }
        }

        if (!Schema::hasTable('practice_periods')) {
            return;
        }

        $periods = DB::table('practice_periods')->get();
        if ($periods->isEmpty()) {
            return;
        }

        foreach ($periods as $period) {
            $course = (int) ($period->course ?? 0);
            $prefix = $this->coursePrefixes()[$course] ?? null;
            if (!$prefix) {
                continue;
            }
            $tables = $this->tablesForCourse($course, $prefix);
            $sourceTable = $tables['form_two_records'];
            $targetTable = $tables['form_two_practice_records'];

            if (!Schema::hasTable($sourceTable) || !Schema::hasTable($targetTable)) {
                continue;
            }

            $query = DB::table($sourceTable)
                ->where('group_id', $period->group_id)
                ->whereBetween('class_date', [$period->start_date, $period->end_date]);
            if (!empty($period->subject_id)) {
                $query->where('subject_id', $period->subject_id);
            }

            $query->orderBy('id')->chunkById(500, function ($rows) use ($sourceTable, $targetTable) {
                $payload = [];
                $ids = [];
                foreach ($rows as $row) {
                    $data = (array) $row;
                    unset($data['id']);
                    $payload[] = $data;
                    $ids[] = $row->id;
                }
                if ($payload) {
                    DB::table($targetTable)->insert($payload);
                    DB::table($sourceTable)->whereIn('id', $ids)->delete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->coursePrefixes() as $course => $prefix) {
            $practiceTable = $this->practiceTableName($course, $prefix);
            Schema::dropIfExists($practiceTable);
        }
    }

    private function coursePrefixes(): array
    {
        return [
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
        ];
    }

    private function practiceTableName(int $course, string $prefix): string
    {
        return $course === 1 ? 'form_two_practice_records' : "{$prefix}_form_two_practice_records";
    }

    private function tablesForCourse(int $course, string $prefix): array
    {
        return [
            'form_two_records' => $course === 1 ? 'form_two_records' : "{$prefix}_form_two_records",
            'form_two_practice_records' => $this->practiceTableName($course, $prefix),
        ];
    }
};
