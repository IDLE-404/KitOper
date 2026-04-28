<?php

namespace App\Services;

use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Проецирует двухнедельный шаблон расписания (числитель + знаменатель)
 * на заданный месяц и возвращает «призрачные» ячейки для отображения
 * в Форме 2. Данные в БД не записываются.
 */
class SemesterGhostService
{
    protected ScheduleToFormTwoSyncService $sync;

    public function __construct(ScheduleToFormTwoSyncService $sync)
    {
        $this->sync = $sync;
    }

    /**
     * Возвращает призрачные данные для месяца.
     *
     * Структура ответа:
     * [
     *   'cells'     => array<string, array>   // ключ "subjectId|teacherId|subgroup" => [day => [lesson_numbers]]
     *   'conflicts' => array<string>           // текстовые предупреждения о конфликтах
     * ]
     */
    public function ghostMonthData(
        int $groupId,
        int $year,
        int $month,
        int $course = 1
    ): array {
        $tables = CourseContext::tables($course);

        // Ищем эталонную неделю: самую раннюю неделю с данными для группы.
        $templateWeekStart = $this->findTemplateWeek($groupId, $tables['schedules']);
        if ($templateWeekStart === null) {
            return ['cells' => [], 'conflicts' => []];
        }

        $templateRows = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->whereDate('week_start', $templateWeekStart->toDateString())
            ->get();

        if ($templateRows->isEmpty()) {
            return ['cells' => [], 'conflicts' => []];
        }

        $dayOffset = [
            'Понедельник' => 0,
            'Вторник'     => 1,
            'Среда'       => 2,
            'Четверг'     => 3,
            'Пятница'     => 4,
            'Суббота'     => 5,
        ];

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Обходим все дни месяца и строим ghost-ячейки.
        $cells = [];

        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $dow = $cursor->dayOfWeek; // 0=вс, 1=пн … 6=сб
            if ($dow === 0) {           // воскресенье — нет пар
                $cursor->addDay();
                continue;
            }

            // Режим недели для этого конкретного дня.
            $weekStart = $cursor->copy()->startOfWeek(Carbon::MONDAY);
            $weekMode  = $this->sync->resolveWeekMode($weekStart, $course);

            // Имя дня по-русски (как хранится в study_day).
            $dayName = $this->dowToRussian($dow);
            if ($dayName === null) {
                $cursor->addDay();
                continue;
            }

            $day = $cursor->day;

            foreach ($templateRows as $row) {
                if ($row->study_day !== $dayName) {
                    continue;
                }

                // Числитель: subject_id / teacher_id
                // Знаменатель: subject_id_denominator / teacher_id_denominator (если задан)
                $hasDen = $row->subject_id_denominator || $row->teacher_id_denominator
                    || $row->subject_id_denominator_2 || $row->teacher_id_denominator_2;

                $subgroup = in_array($row->subgroup ?? null, ['2', 'B'], true) ? 2 : 1;

                // Подгруппа 1
                if ($subgroup === 1) {
                    $subjectNum = $row->subject_id ?? null;
                    $teacherNum = $row->teacher_id ?? null;
                    $subjectDen = $row->subject_id_denominator ?? $subjectNum;
                    $teacherDen = $row->teacher_id_denominator ?? $teacherNum;
                } else {
                    // Подгруппа 2: данные могут быть в _2 полях или в основных
                    $subjectNum = $row->subject_id_2 ?? $row->subject_id ?? null;
                    $teacherNum = $row->teacher_id_2 ?? $row->teacher_id ?? null;
                    $subjectDen = $row->subject_id_denominator_2 ?? $row->subject_id_denominator ?? $subjectNum;
                    $teacherDen = $row->teacher_id_denominator_2 ?? $row->teacher_id_denominator ?? $teacherNum;
                }

                if ($hasDen && $weekMode === 'denominator') {
                    $subjectId = $subjectDen;
                    $teacherId = $teacherDen;
                } else {
                    $subjectId = $subjectNum;
                    $teacherId = $teacherNum;
                }

                if (!$subjectId) {
                    continue;
                }

                $key = "{$subjectId}|{$teacherId}|{$subgroup}";
                $cells[$key][$day][] = (int) $row->lesson_number;
            }

            $cursor->addDay();
        }

        // Конфликты учителей в проекции месяца: один учитель в двух слотах одновременно.
        $conflicts = $this->detectConflicts($cells, $groupId, $year, $month, $course, $tables);

        return [
            'cells'     => $cells,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Проверяет teacher-конфликты между этой группой и остальными
     * на основе призрачных данных.
     */
    protected function detectConflicts(
        array $ghostCells,
        int $groupId,
        int $year,
        int $month,
        int $course,
        array $tables
    ): array {
        if (empty($ghostCells)) {
            return [];
        }

        // Собираем карту: teacherId => [day => [lesson_number => true]]
        $myTeacherSlots = [];
        foreach ($ghostCells as $key => $dayMap) {
            [, $teacherId] = explode('|', $key);
            if (!$teacherId) {
                continue;
            }
            foreach ($dayMap as $day => $lessons) {
                foreach ($lessons as $lesson) {
                    $myTeacherSlots[$teacherId][$day][$lesson] = true;
                }
            }
        }

        if (empty($myTeacherSlots)) {
            return [];
        }

        // Ищем те же учителя в записях Формы 2 других групп в этом месяце.
        $teacherIds = array_keys($myTeacherSlots);

        $monthStart = Carbon::create($year, $month, 1)->toDateString();
        $monthEnd   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $otherRecords = DB::table($tables['form_two_records'])
            ->whereIn('teacher_id', $teacherIds)
            ->where('group_id', '<>', $groupId)
            ->whereBetween('class_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['normal', 'replacement'])
            ->select('teacher_id', 'class_date', 'lesson_number', 'group_id')
            ->get();

        // Карта групп для имён.
        $groupNames = DB::table($tables['groups'])
            ->pluck('group_name', 'id');

        $teacherNames = DB::table($tables['teachers'])
            ->whereIn('id', $teacherIds)
            ->select('id', DB::raw('COALESCE(initials, teacher_name) as name'))
            ->pluck('name', 'id');

        $warnings = [];
        foreach ($otherRecords as $rec) {
            $day    = (int) Carbon::parse($rec->class_date)->day;
            $lesson = (int) $rec->lesson_number;
            $tid    = (int) $rec->teacher_id;

            if (isset($myTeacherSlots[$tid][$day][$lesson])) {
                $tName = $teacherNames[$tid] ?? "Препод #{$tid}";
                $gName = $groupNames[$rec->group_id] ?? "Группа #{$rec->group_id}";
                $warnings[] = "Конфликт: {$tName} — {$day} числа, пара {$lesson} (уже занят в {$gName})";
            }
        }

        return array_values(array_unique($warnings));
    }

    /**
     * Находит самую раннюю неделю с данными для группы.
     * Если данных нет — возвращает null.
     */
    protected function findTemplateWeek(int $groupId, string $schedulesTable): ?Carbon
    {
        $row = DB::table($schedulesTable)
            ->where('group_id', $groupId)
            ->whereNotNull('subject_id')
            ->orderBy('week_start')
            ->value('week_start');

        return $row ? Carbon::parse($row)->startOfWeek(Carbon::MONDAY) : null;
    }

    protected function dowToRussian(int $dow): ?string
    {
        return [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
        ][$dow] ?? null;
    }
}
