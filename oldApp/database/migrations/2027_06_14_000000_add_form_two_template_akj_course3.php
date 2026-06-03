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
        $templateName = 'АКЖ 3 курс (ОН)';
        $groupTokens = 'АКЖ АҚЖ';

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
            'ОН 1.2 Дене қасиеттері мен психофизиологиялық қабілеттерді жетілдіру',
            'ОН 4.1 Төзімді және белсенді жеке ұстанымды қалыптастыратын моральдық-адамгершілік құндылықтар мен нормаларды түсіну',
            'ОН 4.2 Әлемдік өркениеттегі Қазақстан Республикасы халықтары мәдениетінің рөлі мен орнын түсіну',
            'ОН 4.3 Құқықтың негізгі салалары туралы мәліметтерді меңгеру',
            'ОН 4.4 Әлеуметтану мен саясаттанудың негізгі түсініктерін меңгеру',
            'ОН 4.1 Қауіпсіздік тетіктерін басқару',
            'ОН 4.2 Ақпараттық қауіпсіздік инциденттеріне әрекет ету',
            'ОН 4.3 Ұйымның ақпараттық қауіпсіздігін басқару және қамтамасыз ету үрдісін жоспарлау',
            'ОН 4.4 Ұйымның ақпараттық қауіпсіздігін басқару және қамтамасыз ету үрдісін бақылау',
            'ОН 4.5 Ақпараттық қауіпсіздікті қамтамасыз етудің аппараттық - бағдарламалық құралдарын тестілеу (байқаудан өткізу)',
            'ОН 4.6 Ақпараттық қауіпсіздікті қамтамасыз етудің аппараттық - бағдарламалық құралдарының жұмысқа қабілеттілігін қалпына келтіру',
            'ОН 4.7 Қауіпсіздікті талдау, ақпараттық жүйенің қауіпсіз конфигурацияларын жобалау. және құру, оқиғаларды зерттеу',
        ];

        $duplicateSubjects = [
            'ОН 1.2 Дене қасиеттері мен психофизиологиялық қабілеттерді жетілдіру',
            'ОН 4.1 Қауіпсіздік тетіктерін басқару',
            'ОН 4.2 Ақпараттық қауіпсіздік инциденттеріне әрекет ету',
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
            ->where('name', 'АКЖ 3 курс (ОН)')
            ->first();

        if ($template) {
            DB::table('form_two_template_items')->where('template_id', $template->id)->delete();
            DB::table('form_two_templates')->where('id', $template->id)->delete();
        }
    }
};

