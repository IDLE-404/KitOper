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

        $updates = [
            'Обеспечивать информационную безопасность.' => 'РО 1.3 Обеспечивать информационную безопасность.',
            'Интегрировать облачную инфраструктуры с сервисами предприятия.' => 'РО 1.4 Интегрировать облачную инфраструктуры с сервисами предприятия.',
            'Разрабатывать скрипты для автоматизации задач администрирования.' => 'РО 2.1 Разрабатывать скрипты для автоматизации задач администрирования.',
            'Администрировать базы данных.' => 'РО 2.2 Администрировать базы данных.',
            'Создавать системные приложения.' => 'РО 2.4 Создавать системные приложения.',
        ];

        foreach ($updates as $nameRu => $subjectName) {
            DB::table('second_course_subjects')
                ->where('name_ru', $nameRu)
                ->update(['subject_name' => $subjectName]);
        }
    }

    public function down(): void
    {
        // Безопасный откат не требуется для отображения.
    }
};
