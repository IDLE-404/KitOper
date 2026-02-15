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
        $templateName = 'ТЭ 2 курс (РО)';
        $groupTokens = 'ТЭ';

        $templateId = DB::table('form_two_templates')->updateOrInsert(
            ['course' => 2, 'name' => $templateName],
            [
                'group_tokens' => $groupTokens,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $template = DB::table('form_two_templates')
            ->where('course', 2)
            ->where('name', $templateName)
            ->first();

        if (!$template) {
            return;
        }

        DB::table('form_two_template_items')->where('template_id', $template->id)->delete();

        $subjects = [
            'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни',
            'РО 3.3 Понимать тенденции развития мировой экономики, основные задачи перехода государства к «зеленой» экономике',
            'РО 3.4 Владеть научными и законодательными основами организации и ведения предпринимательской деятельности в Республике Казахстан',
            'РО 3.5 Соблюдать этику делового общения',
            'РО 1.1 Разрабатывать план монтажа с изложением оперативно-технической документации',
            'РО 1.2 Организовывать условия труда на производстве, соответствующие современным стандартам экологической и промышленной безопасности',
            'РО 1.3 Применять правила технического обслуживания электрооборудования, электроизмерительных приборов, инструментов и приспособлений',
            'РО 2.1 Организовывать и анализировать ситуации работ по переходу от монтажа к наладке с разработкой соответствующей документации',
            'РО 2.2 Проводить расчеты в сфере организации и контроля строительно-монтажных работ',
            'РО 2.3 Выполнять настройку автоматики на основе знаний выбора подходящей технологии для монтажных работ',
            'РО 3.2 Работать с программным компьютерным обеспечением и современными средствами связи при ремонте электрооборудования',
            'РО 3.3 Проводить расчеты в сфере организации контроля строительно-монтажных работ, соответственно нормам, стандартам, инструкциям и схемам',
            'РО 3.4 Проводить ремонт внутрицеховых сетей и осветительных электроустановок',
            'РО 3.5 Проводить техническую эксплуатацию, ремонт кабельных и воздушных линий',
            'РО 3.6 Проводить техническую эксплуатацию и ремонт электрических машин и пусконаладочной аппаратуры',
            'РО 3.7 Проводить техническую эксплуатацию и ремонт электрооборудования трансформаторов',
        ];

        $duplicateSubjects = [
            'РО 1.1 Укреплять здоровье и соблюдать принципы здорового образа жизни',
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
            ->where('course', 2)
            ->where('name', 'ТЭ 2 курс (РО)')
            ->first();

        if ($template) {
            DB::table('form_two_template_items')->where('template_id', $template->id)->delete();
            DB::table('form_two_templates')->where('id', $template->id)->delete();
        }
    }
};

