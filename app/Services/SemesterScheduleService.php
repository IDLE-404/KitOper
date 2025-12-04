<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\CourseContext;

class SemesterScheduleService
{
    public function __construct(
        protected ScheduleToFormTwoSyncService $syncService
    ) {
    }

    /**
     * Разворачивает расписание выбранной недели на диапазон недель семестра.
     *
     * @return array{inserted_weeks:int,inserted_rows:int,skipped_weeks:array}
     */
    public function expandFromTemplate(
        int $groupId,
        Carbon $templateWeekStart,
        Carbon $semesterStart,
        Carbon $semesterEnd,
        string $firstWeekMode = 'numerator',
        bool $skipExistingWeeks = false,
        bool $syncFormTwo = true,
        int $course = 1
    ): array {
        $tables = CourseContext::tables($course);
        $templateWeekStart = $templateWeekStart->copy()->startOfWeek(Carbon::MONDAY);
        $semesterStart = $semesterStart->copy()->startOfWeek(Carbon::MONDAY);
        $semesterEnd = $semesterEnd->copy()->startOfWeek(Carbon::MONDAY);

        $templateRows = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->whereDate('week_start', $templateWeekStart->toDateString())
            ->get();

        if ($templateRows->isEmpty()) {
            return ['inserted_weeks' => 0, 'inserted_rows' => 0, 'skipped_weeks' => []];
        }

        $now = now();
        $insertedWeeks = 0;
        $insertedRows = 0;
        $skippedWeeks = [];
        $weekIndex = 0;

        $nextId = (int) DB::table($tables['schedules'])->max('id') + 1;

        $weekPointer = $semesterStart->copy();
        while ($weekPointer->lte($semesterEnd)) {
            $weekDate = $weekPointer->toDateString();

            if ($skipExistingWeeks && DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $weekDate)
                ->exists()) {
                $skippedWeeks[] = $weekDate;
                $weekPointer->addWeek();
                $weekIndex++;
                continue;
            }

            DB::table($tables['schedules'])
                ->where('group_id', $groupId)
                ->whereDate('week_start', $weekDate)
                ->delete();

            $rowsToInsert = [];
            foreach ($templateRows as $row) {
                $rowData = $this->prepareRow((array) $row, $weekDate, $now);
                $rowData['id'] = $nextId++;
                $rowsToInsert[] = $rowData;
            }

            if ($rowsToInsert) {
                DB::table($tables['schedules'])->insert($rowsToInsert);
                $insertedWeeks++;
                $insertedRows += count($rowsToInsert);

                if ($syncFormTwo) {
                    $mode = $this->modeForIndex($firstWeekMode, $weekIndex);
                    $this->syncService->syncWeek($groupId, $weekPointer->copy(), $mode, null, null, $course);
                }
            }

            $weekPointer->addWeek();
            $weekIndex++;
        }

        return [
            'inserted_weeks' => $insertedWeeks,
            'inserted_rows' => $insertedRows,
            'skipped_weeks' => $skippedWeeks,
        ];
    }

    protected function prepareRow(array $row, string $weekDate, $now): array
    {
        unset($row['id'], $row['mode']);
        $row['week_start'] = $weekDate;
        $row['created_at'] = $now;
        $row['updated_at'] = $now;

        foreach ($this->flagFields() as $field => $default) {
            $row[$field] = $default;
        }

        return $row;
    }

    protected function modeForIndex(string $firstWeekMode, int $weekIndex): string
    {
        $firstWeekMode = in_array($firstWeekMode, ['numerator', 'denominator'], true) ? $firstWeekMode : 'numerator';
        if ($weekIndex % 2 === 0) {
            return $firstWeekMode;
        }

        return $firstWeekMode === 'numerator' ? 'denominator' : 'numerator';
    }

    /**
     * Флаги/столбцы, которые нужно сбросить при копировании недели (отмены, замены).
     */
    protected function flagFields(): array
    {
        return [
            'is_absent_1_num' => false,
            'is_replacement_1_num' => false,
            'replacement_teacher_id_1_num' => null,
            'replacement_subject_id_1_num' => null,
            'replacement_comment_1_num' => null,
            'is_absent_1_den' => false,
            'is_replacement_1_den' => false,
            'replacement_teacher_id_1_den' => null,
            'replacement_subject_id_1_den' => null,
            'replacement_comment_1_den' => null,
            'is_absent_2_num' => false,
            'is_replacement_2_num' => false,
            'replacement_teacher_id_2_num' => null,
            'replacement_subject_id_2_num' => null,
            'replacement_comment_2_num' => null,
            'is_absent_2_den' => false,
            'is_replacement_2_den' => false,
            'replacement_teacher_id_2_den' => null,
            'replacement_subject_id_2_den' => null,
            'replacement_comment_2_den' => null,
        ];
    }
}
