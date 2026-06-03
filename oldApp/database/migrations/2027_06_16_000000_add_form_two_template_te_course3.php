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
        $templateName = 'ТЭ 3 курс (РО)';
        $groupTokens = 'ТЭ';

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
            'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни',
            'РО 4.1 Понимать морально-нравственные ценности и нормы, формирующие толерантность и активную личностную позицию',
            'РО 4.2 Понимать роль и место культуры народов Республики Казахстан в мировой цивилизации',
            'РО 4.3 Владеть сведениями об основных отраслях права',
            'РО 4.4 Владеть основными понятиями социологии и политологии',
            'РО 5.1 Организовывать деятельность по соблюдению требований охраны труда и техники безопасности на производстве',
            'РО 5.2 Составлять графики организации ремонта, наладки и обслуживания электрооборудования для структурного подразделения в соответствии с экологическими, архитектурными и нормативными требованиями',
            'РО 5.3 Оформлять работы нарядом, распоряжением на производство работ',
            'РО 5.4 Оформлять техническую документацию на ремонтные работы',
            'РО 6.1 Применять методы наладки и регулировки электрооборудования',
            'РО 6.2 Выполнять наладку общестанционных устройств и дистанционного оборудования',
            'РО 6.3 Проводить и выполнять наладку релейной защиты и автоматики',
        ];

        $payload = [];
        $sort = 1;
        foreach ($subjects as $subject) {
            $payload[] = [
                'template_id' => $template->id,
                'sort_order' => $sort++,
                'subject_name' => $subject,
                'include_subgroup_two' => false,
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
            ->where('name', 'ТЭ 3 курс (РО)')
            ->first();

        if ($template) {
            DB::table('form_two_template_items')->where('template_id', $template->id)->delete();
            DB::table('form_two_templates')->where('id', $template->id)->delete();
        }
    }
};

