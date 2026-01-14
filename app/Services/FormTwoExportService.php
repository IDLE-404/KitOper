<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\KazakhstanHolidayService;
use App\Support\CourseContext;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FormTwoExportService
{
    public function exportXlsx(int $groupId, int $year, int $month, int $course = 1): string
    {
        $formTwoService = new FormTwoService();
        $holidayService = new KazakhstanHolidayService();
        $holidayDays = $holidayService->getMonthHolidays($year, $month);
        $report = $formTwoService->buildMonthReport($groupId, $year, $month, $course, $holidayDays);

        $rows = $report['rows'] ?? [];
        $days = $report['days'] ?? [];
        $replacementTableRows = $report['replacement_table_rows'] ?? [];
        $subgroupTwoRows = $report['subgroup_two_rows'] ?? [];

        $totals = $report['totals'] ?? $formTwoService->calculateTotals($rows, $days);
        $replacementTotals = $formTwoService->calculateReplacementTotals($replacementTableRows, $days);
        $subgroupTwoTotals = $report['subgroup_two_totals'] ?? $formTwoService->calculateTotals($subgroupTwoRows, $days);

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Форма 2');

        $row = 1;
        $sheet->setCellValue("A{$row}", "Форма 2 — {$course} курс");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;
        $sheet->setCellValue("A{$row}", "Группа: {$groupName}");
        $row++;
        $sheet->setCellValue("A{$row}", "Период: {$monthName} {$year}");
        $row += 2;

        $row = $this->writeGridTable(
            $sheet,
            $row,
            'Основная таблица',
            $rows,
            $days,
            $totals,
            $holidayDays,
            $month,
            $year,
            false
        );

        if (!empty($replacementTableRows)) {
            $row += 2;
            $row = $this->writeGridTable(
                $sheet,
                $row,
                'Таблица замен (только учителя)',
                $replacementTableRows,
                $days,
                $replacementTotals,
                $holidayDays,
                $month,
                $year,
                true
            );
        }

        if (!empty($subgroupTwoRows)) {
            $row += 2;
            $row = $this->writeGridTable(
                $sheet,
                $row,
                'Подвоение (подгруппа 2)',
                $subgroupTwoRows,
                $days,
                $subgroupTwoTotals,
                $holidayDays,
                $month,
                $year,
                false
            );
        }

        $filename = storage_path('app/temp/form_two_' . $groupId . '_' . $year . '_' . $month . '_' . time() . '.xlsx');
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return $filename;
    }

    public function exportSemesterXlsx(int $groupId, int $semester, int $year, int $course = 1): string
    {
        if (!in_array($semester, [1, 2], true)) {
            throw new \InvalidArgumentException('Некорректный семестр');
        }

        $formTwoService = new FormTwoService();
        $holidayService = new KazakhstanHolidayService();

        if ($semester === 1) {
            $start = Carbon::create($year, 9, 1);
            $end = Carbon::create($year + 1, 1, 18);
        } else {
            $start = Carbon::create($year, 2, 2);
            $end = Carbon::create($year, 6, 28);
        }

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Форма 2');

        $row = 1;
        $sheet->setCellValue("A{$row}", "Форма 2 — {$course} курс");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;
        $sheet->setCellValue("A{$row}", "Группа: {$groupName}");
        $row++;
        $sheet->setCellValue(
            "A{$row}",
            'Период: ' . $this->semesterLabel($semester, $start, $end)
        );
        $row += 2;

        $cursor = $start->copy()->startOfMonth();
        while ($cursor->lte($end)) {
            $month = (int) $cursor->month;
            $yearValue = (int) $cursor->year;
            $monthName = $months[$month] ?? "Месяц {$month}";
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $minDay = $monthStart->isSameMonth($start) ? (int) $start->day : 1;
            $maxDay = $monthEnd->isSameMonth($end) ? (int) $end->day : (int) $monthEnd->day;

            $holidayDays = $holidayService->getMonthHolidays($yearValue, $month);
            $report = $formTwoService->buildMonthReport($groupId, $yearValue, $month, $course, $holidayDays);

            [$report, $holidayDays] = $this->sliceReportByDays(
                $report,
                $holidayDays,
                $minDay,
                $maxDay,
                $formTwoService
            );

            $rows = $report['rows'] ?? [];
            $days = $report['days'] ?? [];
            $replacementTableRows = $report['replacement_table_rows'] ?? [];
            $subgroupTwoRows = $report['subgroup_two_rows'] ?? [];
            $totals = $report['totals'] ?? $formTwoService->calculateTotals($rows, $days);
            $replacementTotals = $formTwoService->calculateReplacementTotals($replacementTableRows, $days);
            $subgroupTwoTotals = $report['subgroup_two_totals'] ?? $formTwoService->calculateTotals($subgroupTwoRows, $days);

            $sheet->setCellValue("A{$row}", "{$monthName} {$yearValue}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row += 2;

            $row = $this->writeGridTable(
                $sheet,
                $row,
                'Основная таблица',
                $rows,
                $days,
                $totals,
                $holidayDays,
                $month,
                $yearValue,
                false
            );

            if (!empty($replacementTableRows)) {
                $row += 2;
                $row = $this->writeGridTable(
                    $sheet,
                    $row,
                    'Таблица замен (только учителя)',
                    $replacementTableRows,
                    $days,
                    $replacementTotals,
                    $holidayDays,
                    $month,
                    $yearValue,
                    true
                );
            }

            if (!empty($subgroupTwoRows)) {
                $row += 2;
                $row = $this->writeGridTable(
                    $sheet,
                    $row,
                    'Подвоение (подгруппа 2)',
                    $subgroupTwoRows,
                    $days,
                    $subgroupTwoTotals,
                    $holidayDays,
                    $month,
                    $yearValue,
                    false
                );
            }

            $row += 3;
            $cursor->addMonth();
        }

        $filename = storage_path('app/temp/form_two_semester_' . $semester . '_' . $groupId . '_' . time() . '.xlsx');
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return $filename;
    }

    private function writeGridTable(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $startRow,
        string $title,
        array $rows,
        array $days,
        array $totals,
        array $holidayDays,
        int $month,
        int $year,
        bool $onlyReplacements
    ): int {
        $sheet->setCellValue("A{$startRow}", $title);
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $startRow++;

        $headerRow = $startRow;
        $colIndex = 1;
        $header = ['#', 'Предмет', 'Преподаватель', 'Норматив'];
        foreach ($header as $label) {
            $this->setCell($sheet, $colIndex, $headerRow, $label);
            $colIndex++;
        }

        foreach ($days as $day) {
            $this->setCell($sheet, $colIndex, $headerRow, (int) $day);
            $colIndex++;
        }

        $tail = ['Использовано', 'Бонус', 'Остаток'];
        foreach ($tail as $label) {
            $this->setCell($sheet, $colIndex, $headerRow, $label);
            $colIndex++;
        }

        $lastCol = $colIndex - 1;
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);
        $headerRange = "A{$headerRow}:{$lastColLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray($this->headerStyle());

        $this->applyDayHeaderStyles($sheet, $headerRow, $days, $holidayDays, $month, $year);

        $row = $headerRow + 1;
        $idx = 1;
        foreach ($rows as $data) {
            $col = 1;
            $this->setCell($sheet, $col++, $row, $idx++);
            $this->setCell($sheet, $col++, $row, $data['subject_name'] ?? '—');
            $this->setCell($sheet, $col++, $row, $data['teacher_name'] ?? '—');
            $this->setCell($sheet, $col++, $row, $data['total_hours'] ?? 0);

            foreach ($days as $day) {
                $cell = $data['days'][$day] ?? [];
                $status = $cell['status'] ?? 'empty';
                $value = '•';
                $date = Carbon::create($year, $month, (int) $day);
                $isWeekend = $date->dayOfWeek === 0 || $date->dayOfWeek === 6;
                $isHoliday = isset($holidayDays[$day]);

                if ($onlyReplacements) {
                    if ($status === 'replacement') {
                        $value = $cell['value'] ?? ($cell['bonus_hours'] ?? '2');
                    }
                } else {
                    if ($status === 'normal') {
                        $value = $cell['used_hours'] ?? ($data['hours_per_class'] ?? 2);
                    } elseif ($status === 'replacement') {
                        $value = $cell['bonus_hours'] ?? ($data['hours_per_class'] ?? 2);
                    } elseif ($status === 'replaced') {
                        $value = '■';
                    } elseif ($status === 'empty') {
                        $value = '•';
                    }
                }

                $this->setCell($sheet, $col, $row, $value);
                $sheet->getStyle($this->cellRef($col, $row))
                    ->applyFromArray($this->statusStyle($status, $isWeekend, $isHoliday));
                $col++;
            }

            $this->setCell($sheet, $col++, $row, $data['used_hours_total'] ?? 0);
            $this->setCell($sheet, $col++, $row, $data['bonus_hours_total'] ?? 0);
            $this->setCell($sheet, $col++, $row, $data['hours_left'] ?? 0);
            $row++;
        }

        $totalsRow = $row;
        $sheet->setCellValue("B{$totalsRow}", 'Итого');
        $sheet->getStyle("B{$totalsRow}")->getFont()->setBold(true);
        $this->setCell($sheet, 4, $totalsRow, $totals['column_totals']['normative'] ?? 0);

        $dayTotals = $totals['day_totals'] ?? [];
        $col = 5;
        foreach ($days as $day) {
            $this->setCell($sheet, $col++, $totalsRow, $dayTotals[$day] ?? 0);
        }

        $this->setCell($sheet, $col++, $totalsRow, $totals['column_totals']['used'] ?? 0);
        $this->setCell($sheet, $col++, $totalsRow, $totals['column_totals']['bonus'] ?? 0);
        $this->setCell($sheet, $col++, $totalsRow, $totals['column_totals']['left'] ?? 0);

        $tableRange = "A{$headerRow}:{$lastColLetter}{$totalsRow}";
        $sheet->getStyle($tableRange)->applyFromArray($this->tableBorderStyle());
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$totalsRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$totalsRow}")
            ->getAlignment()
            ->setWrapText(true);

        $this->setColumnWidths($sheet, count($days));

        return $totalsRow + 1;
    }

    private function statusStyle(string $status, bool $isWeekend, bool $isHoliday): array
    {
        $colors = [
            'normal' => '8DBBFF',
            'replaced' => 'FFD966',
            'replacement' => 'FF8C8C',
            'empty' => 'F8FAFC',
        ];
        if ($status === 'empty') {
            if ($isHoliday) {
                $color = 'FFF2CC';
            } elseif ($isWeekend) {
                $color = 'C6F6D5';
            } else {
                $color = $colors['empty'];
            }
        } else {
            $color = $colors[$status] ?? $colors['empty'];
        }

        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
        ];
    }

    private function headerStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'],
            ],
        ];
    }

    private function tableBorderStyle(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];
    }

    private function applyDayHeaderStyles(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $headerRow,
        array $days,
        array $holidayDays,
        int $month,
        int $year
    ): void {
        $col = 5;
        foreach ($days as $day) {
            $date = Carbon::create($year, $month, (int) $day);
            $dayOfWeek = $date->dayOfWeek;
            $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;
            $isHoliday = isset($holidayDays[$day]);
            if ($isWeekend || $isHoliday) {
                $color = $isHoliday ? 'FFF7D6' : 'D1FAE5';
                $sheet->getStyle($this->cellRef($col, $headerRow))
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($color);
            }
            $col++;
        }
    }

    private function setColumnWidths(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $dayCount): void
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(14);

        $dayStart = 5;
        for ($i = 0; $i < $dayCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($dayStart + $i);
            $sheet->getColumnDimension($col)->setWidth(4);
        }

        $afterDays = $dayStart + $dayCount;
        foreach ([0, 1, 2] as $offset) {
            $col = Coordinate::stringFromColumnIndex($afterDays + $offset);
            $sheet->getColumnDimension($col)->setWidth(12);
        }
    }

    private function setCell(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $column, int $row, $value): void
    {
        $sheet->setCellValue($this->cellRef($column, $row), $value);
    }

    private function cellRef(int $column, int $row): string
    {
        return Coordinate::stringFromColumnIndex($column) . $row;
    }

    private function semesterLabel(int $semester, Carbon $start, Carbon $end): string
    {
        return sprintf(
            '%d семестр (%s — %s)',
            $semester,
            $start->format('d.m.Y'),
            $end->format('d.m.Y')
        );
    }

    private function sliceReportByDays(
        array $report,
        array $holidayDays,
        int $minDay,
        int $maxDay,
        FormTwoService $formTwoService
    ): array {
        $days = array_values(array_filter(
            $report['days'] ?? [],
            fn ($day) => $day >= $minDay && $day <= $maxDay
        ));
        $dayKeys = array_flip($days);

        $filterRows = function (array $rows) use ($days, $dayKeys): array {
            foreach ($rows as &$row) {
                $rowDays = $row['days'] ?? [];
                $row['days'] = array_intersect_key($rowDays, $dayKeys);
                $used = 0;
                $bonus = 0;
                foreach ($days as $day) {
                    $cell = $row['days'][$day] ?? [];
                    $used += (int) ($cell['used_hours'] ?? 0);
                    $bonus += (int) ($cell['bonus_hours'] ?? 0);
                }
                $row['used_hours_total'] = $used;
                $row['bonus_hours_total'] = $bonus;
                $startLeft = (int) ($row['hours_left_start'] ?? $row['total_hours'] ?? 0);
                $row['hours_left'] = max(0, $startLeft - ($used - $bonus));
            }
            unset($row);
            return $rows;
        };

        $rows = $filterRows($report['rows'] ?? []);
        $replacementRows = $filterRows($report['replacement_table_rows'] ?? []);
        $subgroupTwoRows = $filterRows($report['subgroup_two_rows'] ?? []);

        $holidayDays = array_intersect_key($holidayDays, $dayKeys);

        $report['rows'] = $rows;
        $report['replacement_table_rows'] = $replacementRows;
        $report['subgroup_two_rows'] = $subgroupTwoRows;
        $report['days'] = $days;
        $report['totals'] = $formTwoService->calculateTotals($rows, $days);
        $report['subgroup_two_totals'] = $formTwoService->calculateTotals($subgroupTwoRows, $days);

        return [$report, $holidayDays];
    }
}
