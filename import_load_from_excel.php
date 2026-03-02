<?php

/**
 * Скрипт импорта нагрузки из Excel в form_two_normatives
 * Использование: php import_load_from_excel.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== ИМПОРТ НАГРУЗКИ ИЗ EXCEL ===\n\n";

// Инициализация Laravel DB
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'db',
    'database'  => 'KitOper',
    'username'  => 'laravel',
    'password'  => 'laravel',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$db = $capsule->getConnection();

// Загружаем Excel
$file = __DIR__ . '/нагрузка_для расписания(2).xlsx';
if (!file_exists($file)) {
    echo "❌ Файл не найден: $file\n";
    exit(1);
}

echo "✓ Загрузка Excel файла...\n";
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getSheet(0);
$highestRow = $sheet->getHighestDataRow();

// Кэш данных
$groupsCache = [];
$subjectsCache = []; // Ключ: "РО X.X" или "ОН X.X" → id
$teachersCache = [];

// Загружаем группы для 2 и 3 курсов
echo "✓ Загрузка групп...\n";
$groups2 = $db->table('second_course_group')->select('id', 'group_name')->get();
$groups3 = $db->table('third_course_group')->select('id', 'group_name')->get();

foreach ($groups2 as $g) {
    $groupsCache[$g->group_name] = ['id' => $g->id, 'course' => 2];
}
foreach ($groups3 as $g) {
    $groupsCache[$g->group_name] = ['id' => $g->id, 'course' => 3];
}
echo "  Найдено групп: " . count($groupsCache) . "\n";

// Загружаем предметы - ищем по индексу результата обучения (РО X.X, ОН X.X)
echo "✓ Загрузка предметов (по индексам РО/ОН)...\n";

// Для 3 курса
$subjects3 = $db->table('third_course_subjects')
    ->select('id', 'subject_name', 'module_title')
    ->get();

foreach ($subjects3 as $s) {
    // Извлекаем все индексы РО/ОН/ПМ из названия
    $name = $s->subject_name;
    preg_match_all('/(РО|ОН|ПМ)\s*([\d\.]+)/ui', $name, $matches);
    for ($i = 0; $i < count($matches[0]); $i++) {
        $index = strtoupper($matches[1][$i]) . ' ' . $matches[2][$i];
        $subjectsCache[$index . '_3'] = ['id' => $s->id, 'course' => 3, 'name' => $name];
    }
}

// Для 2 курса
$subjects2 = $db->table('second_course_subjects')
    ->select('id', 'subject_name', 'module_title')
    ->get();

foreach ($subjects2 as $s) {
    $name = $s->subject_name;
    preg_match_all('/(РО|ОН|ПМ)\s*([\d\.]+)/ui', $name, $matches);
    for ($i = 0; $i < count($matches[0]); $i++) {
        $index = strtoupper($matches[1][$i]) . ' ' . $matches[2][$i];
        // Приоритет у 2 курса если дублируется
        $subjectsCache[$index . '_2'] = ['id' => $s->id, 'course' => 2, 'name' => $name];
    }
}

echo "  Найдено предметов по индексам: " . count($subjectsCache) . "\n";

// Загружаем преподавателей
echo "✓ Загрузка преподавателей...\n";
$teachers = $db->table('teachers')->select('id', 'teacher_name', 'initials')->get();
foreach ($teachers as $t) {
    $key = normalizeTeacher($t->teacher_name);
    if ($key) {
        $teachersCache[$key] = $t->id;
    }
    if ($t->initials) {
        $teachersCache[normalizeTeacher($t->initials)] = $t->id;
    }
}
echo "  Найдено преподавателей: " . count($teachersCache) . "\n";

// Статистика
$stats = [
    'processed' => 0,
    'inserted' => 0,
    'updated' => 0,
    'errors' => 0,
    'vacancies' => 0,
    'skipped' => 0,
];

// Месяцы для импорта (февраль, март, апрель, май)
$monthsToImport = [2, 3, 4, 5];
$year = 2026;

echo "\n✓ Начало импорта...\n\n";

// Читаем Excel построчно
for ($row = 4; $row <= $highestRow; $row++) {
    $groupName = trim((string) $sheet->getCell('A' . $row)->getValue());
    $module = trim((string) $sheet->getCell('C' . $row)->getValue());
    $resultIndex = trim((string) $sheet->getCell('D' . $row)->getValue());
    $subjectName = trim((string) $sheet->getCell('E' . $row)->getValue());
    $teacherName = trim((string) $sheet->getCell('F' . $row)->getValue());
    $totalHours = trim((string) $sheet->getCell('L' . $row)->getValue());
    
    // Пропускаем пустые строки и итоги
    if (!$groupName || !$subjectName || strpos($groupName, '_итог') !== false) {
        continue;
    }
    
    // Пропускаем, если нет часов
    if ($totalHours === '' || $totalHours === '0') {
        $stats['skipped']++;
        continue;
    }
    
    $stats['processed']++;
    
    // Определяем курс по названию группы
    $course = detectCourse($groupName);
    if (!$course) {
        $stats['skipped']++;
        continue;
    }
    
    // Ищем группу
    $groupId = $groupsCache[$groupName]['id'] ?? null;
    if (!$groupId) {
        echo "⚠️  Группа не найдена: $groupName\n";
        $stats['errors']++;
        continue;
    }
    
    // Ищем предмет по индексу (РО 1.1, ОН 4.3...)
    $subjectId = null;
    if ($resultIndex) {
        // Нормализуем индекс: "РО 1.1." → "РО 1.1"
        $normalizedIndex = normalizeIndex($resultIndex);
        $cacheKey = $normalizedIndex . '_' . $course;
        
        if (isset($subjectsCache[$cacheKey])) {
            $subjectId = $subjectsCache[$cacheKey]['id'];
        }
    }
    
    if (!$subjectId) {
        echo "⚠️  Предмет не найден: $resultIndex ($subjectName) - курс $course, группа $groupName\n";
        $stats['errors']++;
        continue;
    }
    
    // Ищем преподавателя
    $teacherId = null;
    $isVacancy = false;
    
    if (strpos($teacherName, 'вакансия') !== false || stripos($teacherName, 'vacancy') !== false) {
        $isVacancy = true;
        $stats['vacancies']++;
        // Пытаемся найти второго преподавателя если есть (вакансия/Иванов)
        if (preg_match('/вакансия[\/\\\\](.+)/ui', $teacherName, $matches)) {
            $actualTeacher = trim($matches[1]);
            $teacherKey = normalizeTeacher($actualTeacher);
            if (isset($teachersCache[$teacherKey])) {
                $teacherId = $teachersCache[$teacherKey];
            }
        }
    } else {
        // Убираем "практика/" префикс
        $cleanTeacher = preg_replace('/^практика[\/\\\\]*/ui', '', $teacherName);
        $teacherKey = normalizeTeacher($cleanTeacher);
        if (isset($teachersCache[$teacherKey])) {
            $teacherId = $teachersCache[$teacherKey];
        }
    }
    
    if (!$teacherId && !$isVacancy) {
        echo "⚠️  Преподаватель не найден: '$teacherName' (группа: $groupName, предмет: $resultIndex)\n";
        $stats['errors']++;
        continue;
    }
    
    // Распределяем часы по месяцам
    $monthsCount = count($monthsToImport);
    $hoursPerMonth = (int) round((float) $totalHours / $monthsCount);
    
    // Обновляем/добавляем нормативы для каждого месяца
    foreach ($monthsToImport as $month) {
        $table = $course === 2 ? 'second_form_two_normatives' : 'third_form_two_normatives';
        
        $existing = $db->table($table)
            ->where('group_id', $groupId)
            ->where('subject_id', $subjectId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        
        if ($existing) {
            // Обновляем существующую запись
            $db->table($table)->where('id', $existing->id)->update([
                'total_hours' => $hoursPerMonth,
                'teacher_id' => $teacherId,
                'updated_at' => now(),
            ]);
            $stats['updated']++;
        } else {
            // Добавляем новую запись
            $db->table($table)->insert([
                'group_id' => $groupId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacherId,
                'month' => $month,
                'year' => $year,
                'total_hours' => $hoursPerMonth,
                'hours_per_class' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $stats['inserted']++;
        }
    }
}

echo "\n=== РЕЗУЛЬТАТЫ ИМПОРТА ===\n";
echo "✓ Обработано строк: {$stats['processed']}\n";
echo "✓ Добавлено записей: {$stats['inserted']}\n";
echo "✓ Обновлено записей: {$stats['updated']}\n";
echo "⚠️  Пропущено (нет часов): {$stats['skipped']}\n";
echo "⚠️  Вакансий (без преподавателя): {$stats['vacancies']}\n";
echo "⚠️  Ошибок: {$stats['errors']}\n";
echo "\n=== ГОТОВО! ===\n";

// Вспомогательные функции

function normalizeTeacher($name)
{
    $name = trim($name);
    if (!$name) return '';
    // Убираем всё после / или \
    $name = preg_replace('/[\/\\\\].*/u', '', $name);
    // Убираем префикс "практика"
    $name = preg_replace('/^практика\s*/ui', '', $name);
    $name = trim($name);
    return mb_strtolower($name);
}

function normalizeIndex($index)
{
    $index = trim($index);
    // "РО 1.1." → "РО 1.1" (уббираем точку в конце)
    $index = preg_replace('/^(РО|ОН|ПМ)\s*([\d\.]+)\.*/ui', '$1 $2', $index);
    return strtoupper($index);
}

function detectCourse($groupName)
{
    if (preg_match('/-(3\d{2})/', $groupName)) {
        return 3;
    }
    if (preg_match('/-(2\d{2})/', $groupName)) {
        return 2;
    }
    return null;
}
