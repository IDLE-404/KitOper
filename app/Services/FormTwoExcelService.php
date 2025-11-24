<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Парсер Excel для Формы 2.
 *
 * Правила:
 * - normal: списываем часы у основного;
 * - sick (отсутствие): не списываем;
 * - replacement: не списываем основному, добавляем бонус заменяющему.
 *
 * Ожидаемый формат листа (при необходимости скорректируйте колонки/строки):
 *   A: группа (заголовок блока)
 *   B: предмет
 *   C: преподаватель
 *   D: норматив (total_hours)
 *   E..AI: дни 1..31
 *   AJ: «Дано часов» (опционально)
 */
class FormTwoExcelService
{
    private string $filePath;
    private array $monthNames;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? base_path('форма 2 1 курс 25-26 19.11.25г. (2).xlsx');
        $this->monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
        ];
    }

    /**
     * @return array{
     *   days:int[],
     *   groups: array<string,array{subjects:array<int,array{
     *      subject:string, teacher:string, total_hours:float, used_hours:float, hours_left:float,
     *      days: array<int,array{day:int,hours:float,type:string,color:string,replacement_teacher:?string,bonus_hours:?float}>
     *   }>}>, 
     *   teachers: array<string,array{total:float,given:float,bonus:float,remaining:float}>,
     *   replacements: array<string,array<int,array{day:int,subject:string,absent_teacher:string,replacement_teacher:?string,hours:float}>>
     * }
     */
    public function parse(int $month, int $year): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $days = range(1, $daysInMonth);

        if (!class_exists(IOFactory::class) || !is_file($this->filePath)) {
            return ['days' => $days, 'groups' => [], 'teachers' => [], 'replacements' => []];
        }

        $spreadsheet = IOFactory::load($this->filePath);
        $sheet = $this->pickSheet($spreadsheet, $month);

        $colSubject = 'B';
        $colTeacher = 'C';
        $colTotal = 'D';
        $dayColStart = 'E';
        $colGiven = 'AJ';

        $groups = [];
        $teachers = [];
        $replacements = [];

        $row = 2;
        $lastRow = $sheet->getHighestDataRow();
        $currentGroup = null;

        while ($row <= $lastRow) {
            $groupCandidate = trim((string) $sheet->getCell("A{$row}")->getValue());
            if ($groupCandidate !== '' && $this->looksLikeGroup($groupCandidate)) {
                $currentGroup = $groupCandidate;
                $row++;
                continue;
            }

            if (!$currentGroup) {
                $row++;
                continue;
            }

            $subjectName = trim((string) $sheet->getCell("{$colSubject}{$row}")->getValue());
            $teacherName = trim((string) $sheet->getCell("{$colTeacher}{$row}")->getValue());
            $totalHours = (float) $sheet->getCell("{$colTotal}{$row}")->getCalculatedValue();

            if ($subjectName === '') {
                $row++;
                continue;
            }

            $subjectDays = [];
            $usedHours = 0.0;

            foreach ($days as $day) {
                $col = $this->colByOffset($dayColStart, $day - 1);
                $cell = $sheet->getCell("{$col}{$row}");
                $style = $sheet->getStyle("{$col}{$row}");

                $rawVal = $cell->getCalculatedValue();
                $hours = $this->hoursFromValue($rawVal);

                $isYellow = $this->isYellow($style);
                $isRed = $this->isRed($style, $rawVal);
                $replacementTeacher = null;
                $bonus = null;

                $type = 'normal';
                $color = 'white';

                if ($isYellow && !$isRed) {
                    $type = 'sick';
                    $color = 'yellow';
                    $hours = 0;
                } elseif ($isYellow && $isRed) {
                    $type = 'replacement';
                    $color = 'yellow';
                    $replacementTeacher = $this->extractReplacementTeacher($cell);
                    $bonus = $hours > 0 ? $hours : 2.0;
                    $hours = 0;

                    if ($replacementTeacher) {
                        $this->addTeacherBonus($teachers, $replacementTeacher, $bonus);
                        $replacements[$currentGroup][] = [
                            'day' => $day,
                            'subject' => $subjectName,
                            'absent_teacher' => $teacherName,
                            'replacement_teacher' => $replacementTeacher,
                            'hours' => $bonus,
                        ];
                    }
                } else {
                    $usedHours += $hours;
                    $this->addTeacherGiven($teachers, $teacherName, $hours);
                }

                $subjectDays[] = [
                    'day' => $day,
                    'hours' => $hours,
                    'type' => $type,
                    'color' => $color,
                    'replacement_teacher' => $replacementTeacher,
                    'bonus_hours' => $bonus,
                ];
            }

            $givenVal = (float) $sheet->getCell("{$colGiven}{$row}")->getCalculatedValue();
            if ($givenVal > 0) {
                $this->addTeacherGiven($teachers, $teacherName, $givenVal);
            }

            $hoursLeft = max($totalHours - $usedHours, 0);

            $groups[$currentGroup]['subjects'][] = [
                'subject' => $subjectName,
                'teacher' => $teacherName ?: '—',
                'total_hours' => $totalHours,
                'used_hours' => $usedHours,
                'hours_left' => $hoursLeft,
                'days' => $subjectDays,
            ];

            $teachers[$teacherName]['total'] = ($teachers[$teacherName]['total'] ?? 0) + $totalHours;

            $row++;
        }

        foreach ($teachers as $name => $stat) {
            $given = $stat['given'] ?? 0;
            $bonus = $stat['bonus'] ?? 0;
            $total = $stat['total'] ?? 0;
            $teachers[$name]['remaining'] = $total - $given + $bonus;
        }

        return [
            'days' => $days,
            'groups' => $groups,
            'teachers' => $teachers,
            'replacements' => $replacements,
        ];
    }

    private function looksLikeGroup(string $value): bool
    {
        return preg_match('~^[A-ZА-ЯЁӘӨҮҚҒҢ][^ ]*[- ]?\d+~u', $value) === 1;
    }

    private function colByOffset(string $col, int $offset): string
    {
        $idx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col) + $offset;
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx);
    }

    private function hoursFromValue($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        return 0.0;
    }

    private function pickSheet($spreadsheet, int $month): Worksheet
    {
        $name = $this->monthNames[$month] ?? null;
        if ($name && $spreadsheet->sheetNameExists($name)) {
            return $spreadsheet->getSheetByName($name);
        }
        return $spreadsheet->getActiveSheet();
    }

    private function isYellow(\PhpOffice\PhpSpreadsheet\Style\Style $style): bool
    {
        $fill = strtoupper($style->getFill()->getStartColor()->getRGB());
        return in_array($fill, ['FFFF00', 'FFF200', 'FFD966', 'FFEB9C'], true);
    }

    private function isRed(\PhpOffice\PhpSpreadsheet\Style\Style $style, $value): bool
    {
        $fontColor = strtoupper($style->getFont()->getColor()->getRGB());
        return $value === '2' || $value === 2 || in_array($fontColor, ['FF0000', 'C00000', '9C0006'], true);
    }

    private function extractReplacementTeacher(\PhpOffice\PhpSpreadsheet\Cell\Cell $cell): ?string
    {
        $comment = $cell->getComment()?->getText()?->getPlainText();
        if ($comment) {
            return trim($comment);
        }
        return null;
    }

    private function addTeacherGiven(array &$teachers, string $name, float $hours): void
    {
        if ($name === '') {
            return;
        }
        $teachers[$name]['given'] = ($teachers[$name]['given'] ?? 0) + $hours;
    }

    private function addTeacherBonus(array &$teachers, string $name, float $hours): void
    {
        if ($name === '') {
            return;
        }
        $teachers[$name]['bonus'] = ($teachers[$name]['bonus'] ?? 0) + $hours;
    }
}
