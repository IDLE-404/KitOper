<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\KazakhstanHolidayService;
use App\Support\CourseContext;
use Carbon\Carbon;

class FormTwoExportService
{
    public function export(int $groupId, int $year, int $month, int $course = 1): string
    {
        $formTwoService = new FormTwoService();
        $holidayService = new KazakhstanHolidayService();
        $holidayDays = $holidayService->getMonthHolidays($year, $month);
        $report = $formTwoService->buildMonthReport($groupId, $year, $month, $course, $holidayDays);
        $rows = $report['rows'] ?? [];
        $days = $report['days'] ?? [];
        $replacementRows = $report['replacement_rows'] ?? [];
        
        $tables = CourseContext::tables($course);
        $group = \Illuminate\Support\Facades\DB::table($tables['groups'])
            ->where('id', $groupId)
            ->first();
        $groupName = $group->group_name ?? "Группа #{$groupId}";
        
        $months = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
        ];
        $monthName = $months[$month] ?? "Месяц {$month}";

        // Открываем файл для записи
        $filename = storage_path('app/temp/form_two_' . $groupId . '_' . $year . '_' . $month . '_' . time() . '.csv');
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = fopen($filename, 'w');
        // BOM для корректного отображения кириллицы в Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Заголовок
        fputcsv($file, ["Форма 2 — {$course} курс"], ';');
        fputcsv($file, ["Группа: {$groupName}"], ';');
        fputcsv($file, ["Период: {$monthName} {$year}"], ';');
        fputcsv($file, []); // Пустая строка

        // Заголовки таблицы
        $headers = ['#', 'Предмет', 'Преподаватель', 'Норматив'];
        foreach ($days as $day) {
            $headers[] = (string) $day;
        }
        $headers[] = 'Использовано';
        $headers[] = 'Бонус';
        $headers[] = 'Остаток';
        fputcsv($file, $headers, ';');

        // Данные
        $idx = 1;
        foreach ($rows as $row) {
            $data = [
                $idx++,
                $row['subject_name'] ?? '—',
                $row['teacher_name'] ?? '—',
                $row['total_hours'] ?? 0,
            ];

            // Ячейки дней
            foreach ($days as $day) {
                $cell = $row['days'][$day] ?? [];
                $status = $cell['status'] ?? 'empty';
                $value = '';

                if ($status === 'normal') {
                    $value = $cell['used_hours'] ?? $row['hours_per_class'] ?? '2';
                } elseif ($status === 'replacement') {
                    $value = $cell['bonus_hours'] ?? $row['hours_per_class'] ?? '2';
                } elseif ($status === 'replaced') {
                    $value = '■';
                } else {
                    $value = '•';
                }

                $data[] = $value;
            }

            $data[] = $row['used_hours_total'] ?? 0;
            $data[] = $row['bonus_hours_total'] ?? 0;
            $data[] = $row['hours_left'] ?? 0;

            fputcsv($file, $data, ';');
        }

        if ($replacementRows) {
            fputcsv($file, []);
            fputcsv($file, ['Таблица замен (только учителя)'], ';');
            fputcsv($file, ['Дата', 'Пара', 'Подгр.', 'Предмет', 'Преподаватель', 'Замещающий', 'Комментарий'], ';');
            foreach ($replacementRows as $replacement) {
                $formattedDate = $replacement['class_date_label']
                    ?? ($replacement['class_date'] ? Carbon::parse($replacement['class_date'])->format('d.m.Y') : '');
                fputcsv($file, [
                    $formattedDate,
                    $replacement['lesson_number'] ?? '',
                    $replacement['subgroup'] ?? '',
                    $replacement['subject_name'] ?? '—',
                    $replacement['teacher_name'] ?? '—',
                    $replacement['replacement_teacher_name'] ?? '—',
                    $replacement['comment'] ?? '',
                ], ';');
            }
        }

        fclose($file);

        return $filename;
    }
}
