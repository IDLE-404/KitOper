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

        $now = now();
        $entries = [
            [
                'module_title' => 'ЖММ 01',
                'module_index' => 1,
                'code' => 'ОН 1.1',
                'name_kz' => 'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
                'teacher' => 'Алданов Р.А.',
            ],
            [
                'module_title' => 'ЖММ 01',
                'module_index' => 1,
                'code' => 'ОН 1.1',
                'name_kz' => 'Денсаулықты нығайту және салауатты өмір салты қағидаттарын сақтау',
                'teacher' => 'Косбармаков А.Д.',
            ],
            [
                'module_title' => 'ЖММ 03',
                'module_index' => 3,
                'code' => 'ОН 3.2',
                'name_kz' => 'Кәсіпорында болып жатқан экономикалық процестерді талдау және бағалау',
                'teacher' => 'вакансия',
            ],
            [
                'module_title' => 'КМ 01',
                'module_index' => 1,
                'code' => 'ОН 1.1',
                'name_kz' => 'Желілік және серверлік жабдықтарды, бейнебақылау жүйелерін және деректерді кешенді басқару жүйелерін монтаждауды жүргізу',
                'teacher' => 'Мирбеков Б.С.',
            ],
            [
                'module_title' => 'КМ 01',
                'module_index' => 1,
                'code' => 'ОН 1.3',
                'name_kz' => 'Ақпараттық қауіпсіздікті қамтамасыз ету',
                'teacher' => 'вакансия',
            ],
            [
                'module_title' => 'КМ 01',
                'module_index' => 1,
                'code' => 'ОН 1.5',
                'name_kz' => 'Ақпараттық жүйеге техникалық қызмет көрсету тапсырмаларын автоматтандыру',
                'teacher' => 'вакансия',
            ],
            [
                'module_title' => 'КМ 02',
                'module_index' => 2,
                'code' => 'ОН 2.1',
                'name_kz' => 'Әкімшілік тапсырмаларын автоматтандыру үшін сценарийлерді әзірлеу',
                'teacher' => 'Мирбеков Б.С.',
            ],
            [
                'module_title' => 'КМ 02',
                'module_index' => 2,
                'code' => 'ОН 2.2',
                'name_kz' => 'Мәліметтер базасын басқару',
                'teacher' => 'Ташимов Д.К.',
            ],
            [
                'module_title' => 'КМ 02',
                'module_index' => 2,
                'code' => 'ОН 2.3',
                'name_kz' => 'Веб-ресурстарды басқару',
                'teacher' => 'Ташимов Д.К.',
            ],
        ];

        foreach ($entries as $entry) {
            $subjectName = $this->canonicalSubjectName($entry['code'], $entry['name_kz']);
            if ($subjectName === '') {
                continue;
            }

            $subjectId = DB::table('second_course_subjects')
                ->where('subject_name', $subjectName)
                ->value('id');

            if (!$subjectId) {
                continue;
            }

            DB::table('second_course_subjects')
                ->where('id', $subjectId)
                ->update([
                    'module_title' => $entry['module_title'],
                    'module_index' => $entry['module_index'],
                    'name_kz' => $entry['name_kz'],
                    'updated_at' => $now,
                ]);

            if (!Schema::hasTable('second_course_teacher_subjects')) {
                continue;
            }

            $teacherName = trim((string) ($entry['teacher'] ?? ''));
            if ($teacherName === '' || mb_strtolower($teacherName, 'UTF-8') === 'вакансия') {
                continue;
            }

            DB::table('teachers')->updateOrInsert(
                ['teacher_name' => $teacherName],
                ['created_at' => $now, 'updated_at' => $now]
            );
            $teacherId = DB::table('teachers')
                ->where('teacher_name', $teacherName)
                ->value('id');
            if (!$teacherId) {
                continue;
            }

            DB::table('second_course_teacher_subjects')->updateOrInsert(
                ['teacher_id' => $teacherId, 'subject_id' => $subjectId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        // no-op
    }

    private function canonicalSubjectName(string $code, string $name): string
    {
        $code = trim(preg_replace('/\\s+/u', ' ', $code));
        $name = trim(preg_replace('/\\s+/u', ' ', $name));
        if ($code === '' || $name === '') {
            return '';
        }
        return trim($code . ' ' . $name);
    }
};
