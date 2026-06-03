<?php

use App\Support\CourseContext;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = CourseContext::tables(1);
        $pivotTable = $tables['teacher_subjects'];

        if (!Schema::hasTable($pivotTable)) {
            throw new \RuntimeException("Table {$pivotTable} is missing. Run migrations first.");
        }

        $teacherSubjects = [
            'Ахменова А.Е.' => [
                'Русский язык',
                'Русская литература',
                'Орыс тілі және әдебиеті',
            ],
            'Ахмедьянова А.М.' => [
                'Қазақ тілі',
                'Казахский язык и литература',
                'Орыс тілі және әдебиеті',
            ],
            'Нурмагамбетова Н.С.' => [
                'Русский язык',
                'Русская литература',
            ],
            'Нурмагамбетова Л.Б.' => [
                'Казахский язык и литература',
                'Қазақ тілі',
            ],
            'Мынгышева А.А.' => [
                'Казахский язык и литература',
                'Қазақ әдебиеті',
            ],
            'Тауымова А.Е.' => [
                'Қазақ тілі',
                'Қазақ әдебиеті',
                'Казахский язык и литература',
            ],
            'Рахметова М.А.' => [
                'Қазақ әдебиеті',
                'Казахский язык и литература',
            ],
            'Карпаева Л.Б.' => [
                'Русский язык',
                'Русская литература',
                'Орыс тілі және әдебиеті',
            ],
            'Бралина М.Д.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Измайлова Е.В.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Мухамеджанова К.Б.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Жамбұл А.Қ.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Мадениятова Г.Д.' => [
                'Математика',
            ],
            'Султангазинова Д.С.' => [
                'Математика',
            ],
            'Иванова И.Н.' => [
                'Математика',
                'Математика (экзамен)',
            ],
            'Жагапарова Г.С.' => [
                'Математика',
                'Физика',
            ],
            'Нестеров И.Ю.' => [
                'Информатика',
                'Графика и проектирование',
            ],
            'Малгаждарова М.К.' => [
                'Информатика',
                'Графика и проектирование',
            ],
            'Курмангазина А.Ж.' => [
                'Информатика',
            ],
            'Канагатова М.С.' => [
                'Информатика',
                'Графика және жобалау',
            ],
            'Абенов Е.М.' => [
                'Информатика',
            ],
            'Солтанова А.М.' => [
                'История Казахстана',
                'Всемирная история',
                'География',
            ],
            'Аяпберген Н.Е.' => [
                'История Казахстана',
                'Всемирная история',
            ],
            'Ксембаева Д.М.' => [
                'География',
                'Қазақстан тарихы',
                'Дүниежүзі тарихы',
            ],
            'Табулдинов Б.К.' => [
                'География',
                'Всемирная история / Дүние жүзі тарихы',
            ],
            'Айнабекова Б.О.' => [
                'Физика',
            ],
            'Пилипенко А.А.' => [
                'Физика',
            ],
            'Трубецкая Т.Н.' => [
                'Химия',
            ],
            'Мухамедьярова А.И.' => [
                'Химия',
                'Биология',
            ],
            'Баймухамбетов Б.В.' => [
                'Биология',
            ],
            'Окенов Р.Н.' => [
                'Физическая культура',
            ],
            'Арыкова А.А.' => [
                'Физическая культура',
            ],
            'Жотеков А.Ш.' => [
                'Физическая культура',
            ],
            'Косбармаков А.Д.' => [
                'Физическая культура',
            ],
            'Бондарь В.Н.' => [
                'Физическая культура',
            ],
            'Серёгина Е.А.' => [
                'Физическая культура',
            ],
            'Альдекенов Т.С.' => [
                'Физическая культура',
            ],
            'Алданов Р.А.' => [
                'Физическая культура',
            ],
            'Кульмуратов А.К.' => [
                'Начальная военная и технологическая подготовка',
            ],
            'Нұрпеіс Н.Т.' => [
                'Бастапқы әскери және технологиялық дайындық',
            ],
        ];

        $subjectAliases = [
            'Математика (экзамен)' => ['Математика'],
            'Орыс тілі және әдебиеті' => ['Орыс тілі және әдебиеті', 'Орыс тілі және әдәбиеті'],
            'Дүниежүзі тарихы' => ['Дүниежүзі тарихы', 'Дүние жүзі тарихы'],
            'Всемирная история / Дүние жүзі тарихы' => [
                'Всемирная история',
                'Дүние жүзі тарихы',
                'Дүниежүзі тарихы',
            ],
        ];

        $now = now();
        $rows = [];

        foreach ($teacherSubjects as $teacherLabel => $subjects) {
            $teacherId = $this->resolveTeacherId($tables['teachers'], $teacherLabel);

            foreach ($subjects as $subjectLabel) {
                $subjectId = $this->resolveSubjectId($tables['subjects'], $subjectLabel, $subjectAliases);
                $rows[] = [
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table($pivotTable)->insertOrIgnore($rows);
    }

    public function down(): void
    {
        $tables = CourseContext::tables(1);
        $pivotTable = $tables['teacher_subjects'];

        if (!Schema::hasTable($pivotTable)) {
            return;
        }

        $teacherSubjects = [
            'Ахменова А.Е.' => [
                'Русский язык',
                'Русская литература',
                'Орыс тілі және әдебиеті',
            ],
            'Ахмедьянова А.М.' => [
                'Қазақ тілі',
                'Казахский язык и литература',
                'Орыс тілі және әдебиеті',
            ],
            'Нурмагамбетова Н.С.' => [
                'Русский язык',
                'Русская литература',
            ],
            'Нурмагамбетова Л.Б.' => [
                'Казахский язык и литература',
                'Қазақ тілі',
            ],
            'Мынгышева А.А.' => [
                'Казахский язык и литература',
                'Қазақ әдебиеті',
            ],
            'Тауымова А.Е.' => [
                'Қазақ тілі',
                'Қазақ әдебиеті',
                'Казахский язык и литература',
            ],
            'Рахметова М.А.' => [
                'Қазақ әдебиеті',
                'Казахский язык и литература',
            ],
            'Карпаева Л.Б.' => [
                'Русский язык',
                'Русская литература',
                'Орыс тілі және әдебиеті',
            ],
            'Бралина М.Д.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Измайлова Е.В.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Мухамеджанова К.Б.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Жамбұл А.Қ.' => [
                'Иностранный язык',
                'Шетел тілі',
            ],
            'Мадениятова Г.Д.' => [
                'Математика',
            ],
            'Султангазинова Д.С.' => [
                'Математика',
            ],
            'Иванова И.Н.' => [
                'Математика',
                'Математика (экзамен)',
            ],
            'Жагапарова Г.С.' => [
                'Математика',
                'Физика',
            ],
            'Нестеров И.Ю.' => [
                'Информатика',
                'Графика и проектирование',
            ],
            'Малгаждарова М.К.' => [
                'Информатика',
                'Графика и проектирование',
            ],
            'Курмангазина А.Ж.' => [
                'Информатика',
            ],
            'Канагатова М.С.' => [
                'Информатика',
                'Графика және жобалау',
            ],
            'Абенов Е.М.' => [
                'Информатика',
            ],
            'Солтанова А.М.' => [
                'История Казахстана',
                'Всемирная история',
                'География',
            ],
            'Аяпберген Н.Е.' => [
                'История Казахстана',
                'Всемирная история',
            ],
            'Ксембаева Д.М.' => [
                'География',
                'Қазақстан тарихы',
                'Дүниежүзі тарихы',
            ],
            'Табулдинов Б.К.' => [
                'География',
                'Всемирная история / Дүние жүзі тарихы',
            ],
            'Айнабекова Б.О.' => [
                'Физика',
            ],
            'Пилипенко А.А.' => [
                'Физика',
            ],
            'Трубецкая Т.Н.' => [
                'Химия',
            ],
            'Мухамедьярова А.И.' => [
                'Химия',
                'Биология',
            ],
            'Баймухамбетов Б.В.' => [
                'Биология',
            ],
            'Окенов Р.Н.' => [
                'Физическая культура',
            ],
            'Арыкова А.А.' => [
                'Физическая культура',
            ],
            'Жотеков А.Ш.' => [
                'Физическая культура',
            ],
            'Косбармаков А.Д.' => [
                'Физическая культура',
            ],
            'Бондарь В.Н.' => [
                'Физическая культура',
            ],
            'Серёгина Е.А.' => [
                'Физическая культура',
            ],
            'Альдекенов Т.С.' => [
                'Физическая культура',
            ],
            'Алданов Р.А.' => [
                'Физическая культура',
            ],
            'Кульмуратов А.К.' => [
                'Начальная военная и технологическая подготовка',
            ],
            'Нұрпеіс Н.Т.' => [
                'Бастапқы әскери және технологиялық дайындық',
            ],
        ];

        $subjectAliases = [
            'Математика (экзамен)' => ['Математика'],
            'Орыс тілі және әдебиеті' => ['Орыс тілі және әдебиеті', 'Орыс тілі және әдәбиеті'],
            'Дүниежүзі тарихы' => ['Дүниежүзі тарихы', 'Дүние жүзі тарихы'],
            'Всемирная история / Дүние жүзі тарихы' => [
                'Всемирная история',
                'Дүние жүзі тарихы',
                'Дүниежүзі тарихы',
            ],
        ];

        foreach ($teacherSubjects as $teacherLabel => $subjects) {
            $teacherId = $this->findTeacherId($tables['teachers'], $teacherLabel);
            if (!$teacherId) {
                continue;
            }

            foreach ($subjects as $subjectLabel) {
                $subjectId = $this->findSubjectId($tables['subjects'], $subjectLabel, $subjectAliases);
                if (!$subjectId) {
                    continue;
                }

                DB::table($pivotTable)
                    ->where('teacher_id', $teacherId)
                    ->where('subject_id', $subjectId)
                    ->delete();
            }
        }
    }

    private function resolveTeacherId(string $teachersTable, string $teacherLabel): int
    {
        $teacherLabel = $this->normalizeLabel($teacherLabel);
        $teacherId = $this->findTeacherId($teachersTable, $teacherLabel);

        if (!$teacherId) {
            $payload = [
                'teacher_name' => $teacherLabel,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn($teachersTable, 'initials')) {
                $payload['initials'] = $this->resolveInitials($teacherLabel);
            }

            $teacherId = (int) DB::table($teachersTable)->insertGetId($payload);
        }

        return $teacherId;
    }

    private function findTeacherId(string $teachersTable, string $teacherLabel): ?int
    {
        $teacherLabel = $this->normalizeLabel($teacherLabel);

        $teacherId = DB::table($teachersTable)
            ->where('teacher_name', $teacherLabel)
            ->orWhere('initials', $teacherLabel)
            ->value('id');

        return $teacherId ? (int) $teacherId : null;
    }

    private function resolveSubjectId(string $subjectsTable, string $subjectLabel, array $aliases): int
    {
        $subjectLabel = $this->normalizeLabel($subjectLabel);
        $subjectId = $this->findSubjectId($subjectsTable, $subjectLabel, $aliases);

        if (!$subjectId) {
            throw new \RuntimeException("Subject not found: {$subjectLabel}");
        }

        return $subjectId;
    }

    private function findSubjectId(string $subjectsTable, string $subjectLabel, array $aliases): ?int
    {
        $subjectLabel = $this->normalizeLabel($subjectLabel);
        $candidates = $aliases[$subjectLabel] ?? [$subjectLabel];

        if (str_contains($subjectLabel, '/')) {
            $parts = array_map('trim', explode('/', $subjectLabel));
            $candidates = array_merge($candidates, $parts);
        }

        $candidates = array_values(array_unique(array_filter(array_map(
            fn (string $label) => $this->normalizeLabel($label),
            $candidates
        ))));

        $subjectId = DB::table($subjectsTable)
            ->where(function ($query) use ($candidates) {
                foreach ($candidates as $candidate) {
                    $query->orWhere('subject_name', $candidate)
                        ->orWhere('name_ru', $candidate)
                        ->orWhere('name_kz', $candidate);
                }
            })
            ->value('id');

        return $subjectId ? (int) $subjectId : null;
    }

    private function normalizeLabel(string $label): string
    {
        $label = preg_replace('/\s+/u', ' ', trim($label));
        return $label ?? '';
    }

    private function resolveInitials(string $teacherName): ?string
    {
        $clean = $this->normalizeLabel($teacherName);
        if ($clean === '') {
            return null;
        }
        if (mb_strpos($clean, '.') !== false) {
            return $clean;
        }

        $parts = array_values(array_filter(explode(' ', $clean), fn($part) => $part !== ''));
        if (count($parts) < 2) {
            return $clean;
        }

        $surname = array_shift($parts);
        $initials = $surname . ' ';
        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1) . '.';
        }

        return trim($initials);
    }
};
