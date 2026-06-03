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

        $rawNames = [
            'Айнабекова Б.О.',
            'Айткенова А.М.',
            'Алдажуманов Темирлан Казбекович',
            'Алданов Рысбек Абдурасулович',
            'Альдекенов Талгат Сарсенбыевич',
            'Арыкова Алмагуль Аблаевна',
            'Асаимова Карлыгаш Сембековна',
            'Ахмедьянова Айгуль Мутаповна',
            'Ахменова Адия Ерталаповна-РиЛ',
            'Ашимова А.К.',
            'Аяперген Нұрдаулет  Ертайұлы',
            'Баймухамбетов Батырхан Валиханович',
            'Баширова Г.К.',
            'Бегембетов Дамир Мухтарович',
            'Беккер Эрик Эдуардович',
            'Бондарь Виктор Николаевич',
            'Бралина Макпал Достанқызы',
            'Брусенко Владислав Сергеевич',
            'Волочаева А.А.',
            'Григорьев Борис Вячеславович',
            'Жагапарова Галия Саматовна',
            'Жадрин А.Е.',
            'Жалпаков Талгат Темиржанович',
            'Жамбұл Альбина Қинаятқызы - анг',
            'Жуматаева Роза Капышевна',
            'Зейнолла Асылбек Арманұлы-ИС',
            'Иванова И.Н.',
            'Измайлова Елена Валерьевна',
            'Исаханова Жанар Газизовна',
            'Исканова Г.Ш.',
            'Канагатова Макпал Серикжановна',
            'Кекина Елена Александровна-ИС',
            'Косбармаков Адиль Дюсенбаевич',
            'Крыжановский Станислав Александрович',
            'Ксембаева Динара Магмуровна',
            'Кульмуратов А.К.',
            'Курмангазина Асем Жумашевна',
            'Қимадиден Гүлайым Ақихатқызы',
            'Льясова Айгуль Ауталиповна',
            'Мадениятова Гульназ Дарханкызы-мат',
            'Мирбеков Бауыржан Сайдуалиулы',
            'Мухамеджанова Карина Бауржановна',
            'Мухамедьярова Анар Иматаевна',
            'Мынгышева Акжаркын Амангельдиновна',
            'Нестеров Илья Юрьевич',
            'Нурмагамбетова Ляззат Бейбитовна',
            'Нурмагамбетова Назымгуль Сагындыковна',
            'Окенов Руслан Нариманович',
            'Олейник Светлана Александровна',
            'Рахметова  Майя Агыбаевна',
            'Серёгина Екатерина Анатольевна',
            'Смурыгин Антон Михайлович',
            'Солтанова Алмагуль Мергеновна',
            'Сулейменова Камила Муратовна-ИС',
            'Султангазинова Диана Сериковна',
            'Табулдинов Байтас Кайрбаевич',
            'Тауымова Айдана Ерболовна',
            'Ташимов Даурен Кабдешович',
            'Тетерина Светлана Владимировна',
            'Трубецкая Татьяна Николаевна',
            'Малгаждарова  Мира Кошербаевна',
            'Хаипергина Айгерим Юрьевна',
            'Шамгунова Алия Ермековна',
            'Шандыбасова Аружан Саятовна',
            'фирук 1 (пары Жотекова)',
        ];

        $now = now();
        $formatted = [];

        foreach ($rawNames as $raw) {
            $normalized = $this->normalizeName($raw);
            $fullName = $this->normalizeFullName($normalized);
            if ($fullName === '') {
                continue;
            }

            $initials = $this->formatInitials($fullName);
            $formatted[] = $fullName;

            $candidates = array_values(array_unique(array_filter([
                $raw,
                $normalized,
                $this->stripAnnotations($raw),
                $this->stripAnnotations($normalized),
                $initials,
            ])));

            DB::table('teachers')
                ->whereIn('teacher_name', $candidates)
                ->update([
                    'teacher_name' => $fullName,
                    'initials' => $initials,
                    'updated_at' => $now,
                ]);
        }

        $formatted = array_values(array_unique($formatted));

        foreach ($formatted as $name) {
            $exists = DB::table('teachers')
                ->where('teacher_name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('teachers')->insert([
                'teacher_name' => $name,
                'initials' => $this->formatInitials($name),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // no-op
    }

    private function normalizeName(string $raw): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $raw));
        return $this->stripAnnotations($clean);
    }

    private function stripAnnotations(string $raw): string
    {
        return trim(preg_replace('/\s*-\s*.*$/u', '', $raw));
    }

    private function normalizeFullName(string $raw): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $raw));
        if ($clean === '') {
            return '';
        }

        $lower = mb_strtolower($clean);
        if (preg_match('/\b(физ|фирук)\b/u', $lower)) {
            return 'Физкультура вакансия';
        }

        return $this->stripAnnotations($clean);
    }

    private function formatInitials(string $raw): ?string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $raw));
        if ($clean === '') {
            return null;
        }

        $lower = mb_strtolower($clean);
        if (preg_match('/\b(физ|фирук)\b/u', $lower)) {
            return 'Физкультура вакансия';
        }

        if (mb_strpos($clean, '.') !== false) {
            return $clean;
        }

        $parts = array_values(array_filter(explode(' ', $clean), fn($part) => $part !== ''));
        if (count($parts) < 2) {
            return $clean;
        }

        $surname = array_shift($parts);
        $initials = '';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1) . '.';
        }

        return trim($surname . ' ' . $initials);
    }
};
