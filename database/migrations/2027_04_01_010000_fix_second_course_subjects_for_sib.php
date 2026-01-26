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

        $this->mergeDuplicateSubjects();
        $this->ensureSibSubjects();
        $this->ensureSibTeacherLinks();
    }

    public function down(): void
    {
        // Данные восстановлению не подлежат.
    }

    protected function mergeDuplicateSubjects(): void
    {
        $subjects = DB::table('second_course_subjects')
            ->select('id', 'subject_name', 'name_ru', 'name_kz')
            ->get();

        $groups = [];
        foreach ($subjects as $subject) {
            $label = $subject->name_ru ?: ($subject->subject_name ?: '');
            $key = $this->normalizeSubject($label);
            if ($key === '') {
                continue;
            }
            $groups[$key][] = (int) $subject->id;
        }

        $replaceMap = [];
        foreach ($groups as $ids) {
            if (count($ids) < 2) {
                continue;
            }
            sort($ids);
            $keep = array_shift($ids);
            foreach ($ids as $drop) {
                $replaceMap[$drop] = $keep;
            }
        }

        if (empty($replaceMap)) {
            return;
        }

        $this->replaceSubjectIds('second_course_teacher_subjects', ['subject_id'], $replaceMap);
        $this->replaceSubjectIds(
            'second_course_schedules',
            [
                'subject_id',
                'subject_id_2',
                'subject_id_denominator',
                'subject_id_denominator_2',
                'replacement_subject_id_1_num',
                'replacement_subject_id_1_den',
                'replacement_subject_id_2_num',
                'replacement_subject_id_2_den',
            ],
            $replaceMap
        );
        $this->replaceSubjectIds('second_form_two_normatives', ['subject_id'], $replaceMap);
        $this->replaceSubjectIds('second_form_two_records', ['subject_id', 'replacement_subject_id'], $replaceMap);

        DB::table('second_course_subjects')->whereIn('id', array_keys($replaceMap))->delete();
    }

    protected function ensureSibSubjects(): void
    {
        $now = now();
        $missing = [
            'Администрировать базы данных.',
            'Интегрировать облачную инфраструктуры с сервисами предприятия.',
            'Обеспечивать информационную безопасность.',
            'Разрабатывать скрипты для автоматизации задач администрирования.',
            'Создавать системные приложения.',
        ];

        $existing = $this->subjectIndex();
        $payload = [];
        foreach ($missing as $name) {
            $key = $this->normalizeSubject($name);
            if (isset($existing[$key])) {
                continue;
            }
            $payload[] = [
                'module_title' => null,
                'module_index' => null,
                'subject_name' => $name,
                'name_ru' => $name,
                'name_kz' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($payload) {
            DB::table('second_course_subjects')->insert($payload);
        }
    }

    protected function ensureSibTeacherLinks(): void
    {
        if (!Schema::hasTable('second_course_teacher_subjects')) {
            return;
        }

        $teacherIndex = $this->teacherIndex();
        $subjectIndex = $this->subjectIndex();

        $links = [
            ['subject' => 'Укреплять здоровье и соблюдать принципы здорового образа жизни.', 'teacher' => 'Серёгина Е.А.'],
            ['subject' => 'Анализировать и оценивать экономические процессы, происходящие на предприятии.', 'teacher' => 'Жадрин А.Е.'],
            ['subject' => 'Производить монтаж сетевого и серверного оборудования, систем видеонабледния и систем контроля управления данными.', 'teacher' => 'Тетерина С.В.'],
            ['subject' => 'Конфигурировать сетевые сервисы и сетевое оборудование.', 'teacher' => 'Крыжановский С.А.'],
            ['subject' => 'Интегрировать облачную инфраструктуры с сервисами предприятия.', 'teacher' => 'Брусенко В.С.'],
            ['subject' => 'Администрировать базы данных.', 'teacher' => 'Тетерина С.В.'],
            ['subject' => 'Администрировать Web-ресурсы.', 'teacher' => 'Тетерина С.В.'],
            ['subject' => 'Создавать системные приложения.', 'teacher' => 'Брусенко В.С.'],
        ];

        $pairs = [];
        $seen = [];
        foreach ($links as $link) {
            $subjectKey = $this->normalizeSubject($link['subject']);
            $teacherKey = $this->normalizeTeacher($link['teacher']);
            $subjectId = $subjectIndex[$subjectKey] ?? null;
            $teacherId = $teacherIndex[$teacherKey] ?? null;
            if (!$subjectId || !$teacherId) {
                continue;
            }
            $pairKey = $subjectId . ':' . $teacherId;
            if (isset($seen[$pairKey])) {
                continue;
            }
            $seen[$pairKey] = true;
            $pairs[] = [
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
            ];
        }

        if (!$pairs) {
            return;
        }

        foreach ($pairs as $pair) {
            DB::table('second_course_teacher_subjects')->updateOrInsert($pair, $pair);
        }
    }

    protected function subjectIndex(): array
    {
        $index = [];
        $subjects = DB::table('second_course_subjects')
            ->select('id', 'subject_name', 'name_ru', 'name_kz')
            ->get();
        foreach ($subjects as $subject) {
            foreach (['subject_name', 'name_ru', 'name_kz'] as $field) {
                $value = trim((string) ($subject->{$field} ?? ''));
                if ($value === '') {
                    continue;
                }
                $key = $this->normalizeSubject($value);
                $index[$key] = (int) $subject->id;
            }
        }

        return $index;
    }

    protected function teacherIndex(): array
    {
        $index = [];
        $teachers = DB::table('teachers')->select('id', 'teacher_name', 'initials')->get();
        foreach ($teachers as $teacher) {
            $id = (int) $teacher->id;
            foreach (['teacher_name', 'initials'] as $field) {
                $value = trim((string) ($teacher->{$field} ?? ''));
                if ($value === '') {
                    continue;
                }
                $index[$this->normalizeTeacher($value)] = $id;
            }
        }

        return $index;
    }

    protected function normalizeSubject(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\\s+/u', ' ', $value);
        $value = rtrim($value, '. ');
        $value = preg_replace('/^(?:ООМ|ПМ|БМ|РО|PO)\\s*\\d+(?:\\.\\d+)?\\s+/iu', '', $value);
        $value = str_ireplace('видеонабледния', 'видеонаблюдения', $value);
        $value = str_replace(['ё', 'Ё'], ['е', 'Е'], $value);
        return mb_strtolower($value);
    }

    protected function normalizeTeacher(string $value): string
    {
        $value = trim($value);
        $value = str_replace(['ё', 'Ё'], ['е', 'Е'], $value);
        $value = str_replace('.', '', $value);
        $value = preg_replace('/\\s+/u', ' ', $value);
        return mb_strtolower($value);
    }

    protected function replaceSubjectIds(string $table, array $columns, array $replaceMap): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($replaceMap as $from => $to) {
            foreach ($columns as $column) {
                DB::table($table)->where($column, $from)->update([$column => $to]);
            }
        }
    }
};
