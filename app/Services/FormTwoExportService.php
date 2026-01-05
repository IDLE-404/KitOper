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
        $replacementTableRows = $report['replacement_table_rows'] ?? [];
        $subgroupTwoRows = $report['subgroup_two_rows'] ?? [];
        $totals = $report['totals'] ?? $formTwoService->calculateTotals($rows, $days);
        $dayTotals = $totals['day_totals'] ?? [];
        $columnTotals = $totals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        $replacementTotals = $formTwoService->calculateReplacementTotals($replacementTableRows, $days);
        $replacementDayTotals = $replacementTotals['day_totals'] ?? [];
        $replacementColumnTotals = $replacementTotals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        $subgroupTwoTotals = $report['subgroup_two_totals'] ?? $formTwoService->calculateTotals($subgroupTwoRows, $days);
        $subgroupTwoDayTotals = $subgroupTwoTotals['day_totals'] ?? [];
        $subgroupTwoColumnTotals = $subgroupTwoTotals['column_totals'] ?? [
            'normative' => 0,
            'used' => 0,
            'bonus' => 0,
            'left' => 0,
        ];
        
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

        $totalsRow = [
            '',
            'Итого',
            '',
            $columnTotals['normative'] ?? 0,
        ];
        foreach ($days as $day) {
            $totalsRow[] = $dayTotals[$day] ?? 0;
        }
        $totalsRow[] = $columnTotals['used'] ?? 0;
        $totalsRow[] = $columnTotals['bonus'] ?? 0;
        $totalsRow[] = $columnTotals['left'] ?? 0;
        fputcsv($file, $totalsRow, ';');

        if ($replacementTableRows) {
            fputcsv($file, []);
            fputcsv($file, ['Таблица замен (только учителя)'], ';');
            fputcsv($file, $headers, ';');

            $idx = 1;
            foreach ($replacementTableRows as $row) {
                $data = [
                    $idx++,
                    $row['subject_name'] ?? '—',
                    $row['teacher_name'] ?? '—',
                    $row['total_hours'] ?? 0,
                ];

                foreach ($days as $day) {
                    $cell = $row['days'][$day] ?? [];
                    if (($cell['status'] ?? 'empty') === 'replacement') {
                        $data[] = $cell['value'] ?? ($cell['bonus_hours'] ?? '2');
                    } else {
                        $data[] = '•';
                    }
                }

                $data[] = $row['used_hours_total'] ?? 0;
                $data[] = $row['bonus_hours_total'] ?? 0;
                $data[] = $row['hours_left'] ?? 0;

                fputcsv($file, $data, ';');
            }

            $totalsRow = [
                '',
                'Итого',
                '',
                $replacementColumnTotals['normative'] ?? 0,
            ];
            foreach ($days as $day) {
                $totalsRow[] = $replacementDayTotals[$day] ?? 0;
            }
            $totalsRow[] = $replacementColumnTotals['used'] ?? 0;
            $totalsRow[] = $replacementColumnTotals['bonus'] ?? 0;
            $totalsRow[] = $replacementColumnTotals['left'] ?? 0;
            fputcsv($file, $totalsRow, ';');
        }

        if ($subgroupTwoRows) {
            fputcsv($file, []);
            fputcsv($file, ['Подвоение (подгруппа 2)'], ';');
            fputcsv($file, []); // Пустая строка

            fputcsv($file, $headers, ';');

            $idx = 1;
            foreach ($subgroupTwoRows as $row) {
                $data = [
                    $idx++,
                    $row['subject_name'] ?? '—',
                    $row['teacher_name'] ?? '—',
                    $row['total_hours'] ?? 0,
                ];

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

            $totalsRow = [
                '',
                'Итого',
                '',
                $subgroupTwoColumnTotals['normative'] ?? 0,
            ];
            foreach ($days as $day) {
                $totalsRow[] = $subgroupTwoDayTotals[$day] ?? 0;
            }
            $totalsRow[] = $subgroupTwoColumnTotals['used'] ?? 0;
            $totalsRow[] = $subgroupTwoColumnTotals['bonus'] ?? 0;
            $totalsRow[] = $subgroupTwoColumnTotals['left'] ?? 0;
            fputcsv($file, $totalsRow, ';');
        }

        fclose($file);

        return $filename;
    }
}
