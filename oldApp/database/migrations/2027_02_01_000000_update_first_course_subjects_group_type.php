<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('first_course_subjects')) {
            return;
        }

        if (!Schema::hasColumn('first_course_subjects', 'group_type')) {
            Schema::table('first_course_subjects', function (Blueprint $table) {
                $table->string('group_type', 8)->default('both')->index();
            });
        }

        DB::table('first_course_subjects')->update([
            'group_type' => 'hidden',
            'updated_at' => now(),
        ]);

        $subjects = [
            [
                'subject_name' => 'Русский язык',
                'name_ru' => 'Русский язык',
                'name_kz' => 'Орыс тілі',
                'group_type' => 'ru',
            ],
            [
                'subject_name' => 'Русская литература',
                'name_ru' => 'Русская литература',
                'name_kz' => 'Орыс әдебиеті',
                'group_type' => 'ru',
            ],
            [
                'subject_name' => 'Казахский язык и литература',
                'name_ru' => 'Казахский язык и литература',
                'name_kz' => 'Қазақ тілі мен әдебиеті',
                'group_type' => 'ru',
            ],
            [
                'subject_name' => 'Иностранный язык',
                'name_ru' => 'Иностранный язык',
                'name_kz' => 'Шетел тілі',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Математика',
                'name_ru' => 'Математика',
                'name_kz' => 'Математика',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Информатика',
                'name_ru' => 'Информатика',
                'name_kz' => 'Информатика',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'История Казахстана',
                'name_ru' => 'История Казахстана',
                'name_kz' => 'Қазақстан тарихы',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Физическая культура',
                'name_ru' => 'Физическая культура',
                'name_kz' => 'Дене тәрбиесі',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Начальная военная и технологическая подготовка',
                'name_ru' => 'Начальная военная и технологическая подготовка',
                'name_kz' => 'Бастапқы әскери және технологиялық дайындық',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Физика',
                'name_ru' => 'Физика',
                'name_kz' => 'Физика',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Химия',
                'name_ru' => 'Химия',
                'name_kz' => 'Химия',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Биология',
                'name_ru' => 'Биология',
                'name_kz' => 'Биология',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'География',
                'name_ru' => 'География',
                'name_kz' => 'География',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Графика и проектирование',
                'name_ru' => 'Графика и проектирование',
                'name_kz' => 'Графика және жобалау',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Всемирная история',
                'name_ru' => 'Всемирная история',
                'name_kz' => 'Дүние жүзі тарихы',
                'group_type' => 'both',
            ],
            [
                'subject_name' => 'Глобальные компетенции',
                'name_ru' => 'Глобальные компетенции',
                'name_kz' => 'Ғаламдық құзыреттер',
                'group_type' => 'ru',
            ],
            [
                'subject_name' => 'Қазақ тілі',
                'name_ru' => null,
                'name_kz' => 'Қазақ тілі',
                'group_type' => 'kz',
            ],
            [
                'subject_name' => 'Қазақ әдебиеті',
                'name_ru' => null,
                'name_kz' => 'Қазақ әдебиеті',
                'group_type' => 'kz',
            ],
            [
                'subject_name' => 'Орыс тілі және әдәбиеті',
                'name_ru' => null,
                'name_kz' => 'Орыс тілі және әдәбиеті',
                'group_type' => 'kz',
            ],
        ];

        $now = now();
        foreach ($subjects as $subject) {
            $match = ['subject_name' => $subject['subject_name']];
            $values = [
                'name_ru' => $subject['name_ru'],
                'name_kz' => $subject['name_kz'],
                'group_type' => $subject['group_type'],
                'updated_at' => $now,
            ];

            $exists = DB::table('first_course_subjects')
                ->where('subject_name', $subject['subject_name'])
                ->exists();

            if ($exists) {
                DB::table('first_course_subjects')
                    ->where('subject_name', $subject['subject_name'])
                    ->update($values);
                continue;
            }

            DB::table('first_course_subjects')->insert(array_merge($values, [
                'subject_name' => $subject['subject_name'],
                'created_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('first_course_subjects')) {
            return;
        }

        if (Schema::hasColumn('first_course_subjects', 'group_type')) {
            Schema::table('first_course_subjects', function (Blueprint $table) {
                $table->dropColumn('group_type');
            });
        }
    }
};
