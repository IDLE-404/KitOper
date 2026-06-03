<?php

/**
 * Улучшенный скрипт импорта нагрузки из Excel
 * Ищет предметы по индексу (РО/ОН/ПМ) для всех языков
 * Использование: php import_load_improved.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== УЛУЧШЕННЫЙ ИМПОРТ НАГРУЗКИ ИЗ EXCEL ===\n\n";

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
$subjectsCache = []; // Кэш предметов: "course_index" => id

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

// Загружаем ВСЕ предметы в кэш - по индексу + названию!
echo "✓ Загрузка предметов в кэш...\n";
$subjects2 = $db->table('second_course_subjects')->select('id', 'subject_name')->get();
$subjects3 = $db->table('third_course_subjects')->select('id', 'subject_name')->get();

// Функция нормализации для сравнения
$normalize = function($text) {
    $text = trim($text);
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = str_replace(['.', ',', ';', ':', '!', '?'], '', $text);
    return mb_strtolower($text);
};

// Извлекаем индекс из названия предмета
$extractIndexFunc = function($subjectName) {
    if (!$subjectName) return null;
    if (preg_match('/^(РО|ОН|ПМ)\s+([^\s]+)/u', $subjectName, $m)) {
        $num = rtrim($m[2], '.');
        return strtoupper($m[1]) . ' ' . $num;
    }
    return null;
};

foreach ($subjects2 as $s) {
    $index = $extractIndexFunc($s->subject_name);
    if ($index) {
        // Ключ: курс_индекс_нормализованное_название
        $key = '2_' . $index . '_' . $normalize($s->subject_name);
        $subjectsCache[$key] = $s->id;
        
        // Также сохраняем просто по индексу (для первого совпадения)
        $simpleKey = '2_' . $index;
        if (!isset($subjectsCache[$simpleKey])) {
            $subjectsCache[$simpleKey] = $s->id;
        }
    }
}

foreach ($subjects3 as $s) {
    $index = $extractIndexFunc($s->subject_name);
    if ($index) {
        $key = '3_' . $index . '_' . $normalize($s->subject_name);
        $subjectsCache[$key] = $s->id;
        
        $simpleKey = '3_' . $index;
        if (!isset($subjectsCache[$simpleKey])) {
            $subjectsCache[$simpleKey] = $s->id;
        }
    }
}
echo "  Найдено предметов: " . count($subjectsCache) . "\n";

// Статистика
$stats = [
    'processed' => 0,
    'updated' => 0,
    'inserted' => 0,
    'errors' => 0,
    'vacancies' => 0,
];

// Месяцы для импорта - ТОЛЬКО ФЕВРАЛЬ (начало семестра)
$monthsToImport = [2];  // Только февраль!
$year = 2026;

echo "\n✓ Начало импорта...\n\n";

// Читаем Excel построчно
for ($row = 4; $row <= $highestRow; $row++) {
    $groupName = trim((string) $sheet->getCell('A' . $row)->getValue());
    $resultIndex = trim((string) $sheet->getCell('D' . $row)->getValue());
    $subjectName = trim((string) $sheet->getCell('E' . $row)->getValue());
    $teacherName = trim((string) $sheet->getCell('F' . $row)->getValue());
    // БЕРЁМ ЧАСЫ ИЗ СТОЛБЦА T (итого за 2 семестр), а не L (всего часов)
    $totalHours = trim((string) $sheet->getCell('T' . $row)->getValue());
    
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
        echo "⚠️  Группа не найдена: $groupName\n";
        $stats['errors']++;
        continue;
    }
    
    // Ищем предмет по индексу + названию
    $subjectId = findSubjectByName($subjectName, $course, $subjectsCache, $normalize);
    if (!$subjectId) {
        echo "⚠️  Предмет не найден: $subjectName - курс $course, группа $groupName\n";
        $stats['errors']++;
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
    
    // Часы берём КАК ЕСТЬ из Excel (не делим!)
    $hoursPerMonth = (int) $totalHours;
    
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
echo "⚠️  Ошибок: {$stats['errors']}\n";
echo "\n=== ГОТОВО! ===\n";

// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========

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

/**
 * Извлекает индекс из названия предмета
 * "РО 1.1 Укреплять здоровье..." → "РО 1.1"
 * "ОН 4.3 Құқықтың негізгі..." → "ОН 4.3"
 */
function extractIndex($subjectName)
{
    if (!$subjectName) return null;
    
    // Ищем паттерн РО/ОН/ПМ с цифрами
    if (preg_match('/^(РО|ОН|ПМ)\s*([\d\.]+)/ui', $subjectName, $matches)) {
        return strtoupper($matches[1]) . ' ' . $matches[2];
    }
    
    return null;
}

/**
 * Ищет предмет по индексу + названию
 * Сначала ищем точное совпадение, потом просто по индексу
 */
function findSubjectByName($subjectName, $course, $cache, $normalize)
{
    global $extractIndexFunc;
    
    if (!$subjectName) return null;
    
    // Извлекаем индекс из названия
    $index = $extractIndexFunc($subjectName);
    if (!$index) return null;
    
    // Сначала ищем точное совпадение (индекс + название)
    $fullKey = $course . '_' . $index . '_' . $normalize($subjectName);
    if (isset($cache[$fullKey])) {
        return $cache[$fullKey];
    }
    
    // Если не найдено - ищем просто по индексу (первое совпадение)
    $simpleKey = $course . '_' . $index;
    return $cache[$simpleKey] ?? null;
}
