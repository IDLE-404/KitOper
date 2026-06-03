<?php

/**
 * Упрощённый скрипт импорта нагрузки из Excel
 * Обновляет total_hours и teacher_id для существующих записей
 * Использование: php import_load_simple.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== УПРОЩЁННЫЙ ИМПОРТ НАГРУЗКИ ИЗ EXCEL ===\n\n";

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

// Кэш
$groupsCache = [];
$teachersCache = [];

// Загружаем группы
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
    'updated' => 0,
    'inserted' => 0,
    'errors' => 0,
    'vacancies' => 0,
];

// Месяцы для импорта
$monthsToImport = [2, 3, 4, 5];
$year = 2026;

echo "\n✓ Начало импорта...\n\n";

// Читаем Excel построчно
for ($row = 4; $row <= $highestRow; $row++) {
    $groupName = trim((string) $sheet->getCell('A' . $row)->getValue());
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
        continue;
    }
    
    $stats['processed']++;
    
    // Определяем курс
    $course = detectCourse($groupName);
    if (!$course) {
        continue;
    }
    
    // Ищем группу
    $groupId = $groupsCache[$groupName]['id'] ?? null;
    if (!$groupId) {
        continue;
    }
    
    // Ищем предмет по индексу (РО 1.1, ОН 4.3...)
    $subjectId = findSubjectByIndex($db, $resultIndex, $course);
    if (!$subjectId) {
        continue;
    }
    
    // Ищем преподавателя
    $teacherId = null;
    $isVacancy = false;
    
    if (strpos($teacherName, 'вакансия') !== false) {
        $isVacancy = true;
        $stats['vacancies']++;
        if (preg_match('/вакансия[\/\\\\](.+)/ui', $teacherName, $matches)) {
            $actualTeacher = trim($matches[1]);
            $teacherKey = normalizeTeacher($actualTeacher);
            if (isset($teachersCache[$teacherKey])) {
                $teacherId = $teachersCache[$teacherKey];
            }
        }
    } else {
        $cleanTeacher = preg_replace('/^практика[\/\\\\]*/ui', '', $teacherName);
        $teacherKey = normalizeTeacher($cleanTeacher);
        if (isset($teachersCache[$teacherKey])) {
            $teacherId = $teachersCache[$teacherKey];
        }
    }
    
    // Распределяем часы по месяцам
    $monthsCount = count($monthsToImport);
    $hoursPerMonth = (int) round((float) $totalHours / $monthsCount);
    
    // Обновляем/добавляем для каждого месяца
    foreach ($monthsToImport as $month) {
        $table = $course === 2 ? 'second_form_two_normatives' : 'third_form_two_normatives';
        
        $existing = $db->table($table)
            ->where('group_id', $groupId)
            ->where('subject_id', $subjectId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        
        if ($existing) {
            $db->table($table)->where('id', $existing->id)->update([
                'total_hours' => $hoursPerMonth,
                'teacher_id' => $teacherId,
                'updated_at' => now(),
            ]);
            $stats['updated']++;
        } else {
            // Добавляем новую запись только если есть группа и предмет
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

echo "\n=== РЕЗУЛЬТАТЫ ===\n";
echo "✓ Обработано: {$stats['processed']}\n";
echo "✓ Обновлено записей: {$stats['updated']}\n";
echo "✓ Добавлено записей: {$stats['inserted']}\n";
echo "⚠️  Вакансий: {$stats['vacancies']}\n";
echo "\n=== ГОТОВО! ===\n";

function normalizeTeacher($name)
{
    $name = trim($name);
    if (!$name) return '';
    $name = preg_replace('/[\/\\\\].*/u', '', $name);
    $name = preg_replace('/^практика\s*/ui', '', $name);
    return mb_strtolower(trim($name));
}

function detectCourse($groupName)
{
    if (preg_match('/-(3\d{2})/', $groupName)) return 3;
    if (preg_match('/-(2\d{2})/', $groupName)) return 2;
    return null;
}

function findSubjectByIndex($db, $index, $course)
{
    if (!$index) return null;
    
    // Нормализуем индекс: "РО 1.1." → "РО 1.1"
    $index = trim($index);
    $index = preg_replace('/^(РО|ОН|ПМ)\s*([\d\.]+)\.*/ui', '$1 $2', $index);
    $index = strtoupper($index);
    
    // Извлекаем тип и номер
    preg_match('/^(РО|ОН|ПМ)\s*([\d\.]+)/ui', $index, $matches);
    if (!$matches) return null;
    
    $type = strtoupper($matches[1]);
    $number = $matches[2];
    
    // Ищем предмет в БД
    $table = $course === 2 ? 'second_course_subjects' : 'third_course_subjects';
    $subject = $db->table($table)
        ->where('subject_name', 'LIKE', "$type $number%")
        ->first();
    
    return $subject ? $subject->id : null;
}
