<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('form_two_templates') || !Schema::hasTable('form_two_template_items')) {
            return;
        }

        $now = now();
        $templateName = 'СИБ 3 курс (РО)';
        $groupTokens = 'СИБ';

        DB::table('form_two_templates')->updateOrInsert(
            ['course' => 3, 'name' => $templateName],
            [
                'group_tokens' => $groupTokens,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $template = DB::table('form_two_templates')
            ->where('course', 3)
            ->where('name', $templateName)
            ->first();

        if (!$template) {
            return;
        }

        DB::table('form_two_template_items')->where('template_id', $template->id)->delete();

        $subjects = [
            'РО 1.2 Совершенствовать физические качества и психофизиологические способности',
            'РО 4.1 Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию',
            'РО 4.2 Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации',
            'РО 4.3 Владеть сведениями об основных отраслях права',
            'РО 4.4 Владеть основными понятиями социологии и политологии',
            'РО 4.1 Администрировать механизмы безопасности',
            'РО 4.2 Реагировать на инциденты информационной безопасности',
            'РО 4.3 Планировать процессы управления и обеспечения информационной безопасности организации',
            'РО 4.4 Контролировать процессы управления и обеспечения информационной безопасности организации',
            'РО 4.5 Тестировать аппаратно- программные средства обеспечения информационной безопасности',
            'РО 4.6 Восстанавливать работоспособность аппаратно- программных средств обеспечения информационной безопасности',
            'РО 4.7 Анализировать защищенность, проектировать и создавать безопасные конфигурации информационной системы; расследование инцидентов',
        ];

        $duplicateSubjects = [
            'РО 1.2 Совершенствовать физические качества и психофизиологические способности',
            'РО 4.3 Планировать процессы управления и обеспечения информационной безопасности организации',
        ];

        $payload = [];
        $sort = 1;
        foreach ($subjects as $subject) {
            $payload[] = [
                'template_id' => $template->id,
                'sort_order' => $sort++,
                'subject_name' => $subject,
                'include_subgroup_two' => in_array($subject, $duplicateSubjects, true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('form_two_template_items')->insert($payload);
    }

    public function down(): void
    {
        if (!Schema::hasTable('form_two_templates') || !Schema::hasTable('form_two_template_items')) {
            return;
        }

        $template = DB::table('form_two_templates')
            ->where('course', 3)
            ->where('name', 'СИБ 3 курс (РО)')
            ->first();

        if ($template) {
            DB::table('form_two_template_items')->where('template_id', $template->id)->delete();
            DB::table('form_two_templates')->where('id', $template->id)->delete();
        }
    }
};

