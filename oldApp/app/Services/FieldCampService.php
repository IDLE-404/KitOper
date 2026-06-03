<?php

namespace App\Services;

use App\Models\FieldCampPeriod;
use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FieldCampService
{
    public function periodsForRange(int $course, array $groupIds, Carbon $start, Carbon $end): Collection
    {
        if ($course !== 1 || !$groupIds) {
            return collect();
        }

        return FieldCampPeriod::query()
            ->where('course', $course)
            ->whereIn('group_id', $groupIds)
            ->whereDate('end_date', '>=', $start->toDateString())
            ->whereDate('start_date', '<=', $end->toDateString())
            ->get();
    }

    public function campDatesForRange(int $course, int $groupId, Carbon $start, Carbon $end): array
    {
        if ($course !== 1) {
            return [];
        }

        $periods = FieldCampPeriod::query()
            ->where('course', $course)
            ->where('group_id', $groupId)
            ->whereDate('end_date', '>=', $start->toDateString())
            ->whereDate('start_date', '<=', $end->toDateString())
            ->get();

        $dates = [];
        $holidayService = app(KazakhstanHolidayService::class);
        $holidayCache = [];
        foreach ($periods as $period) {
            $rangeStart = Carbon::parse($period->start_date)->max($start);
            $rangeEnd = Carbon::parse($period->end_date)->min($end);
            for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                if ($cursor->isWeekend()) {
                    continue;
                }
                $monthKey = $cursor->format('Y-m');
                if (!isset($holidayCache[$monthKey])) {
                    $holidayCache[$monthKey] = $holidayService->getMonthHolidays($cursor->year, $cursor->month);
                }
                if (isset($holidayCache[$monthKey][$cursor->day])) {
                    continue;
                }
                $dates[$cursor->toDateString()] = true;
            }
        }

        return array_keys($dates);
    }

    public function applyPeriod(FieldCampPeriod $period): void
    {
        $course = (int) $period->course;
        if ($course !== 1) {
            return;
        }
        $tables = CourseContext::tables($course);
        $subjectId = $this->ensureFieldCampSubjectId($course);

        $start = Carbon::parse($period->start_date);
        $end = Carbon::parse($period->end_date);
        $holidayService = app(KazakhstanHolidayService::class);

        $payload = [];
        $holidayCache = [];
        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            if ($cursor->isWeekend()) {
                continue;
            }
            $monthKey = $cursor->format('Y-m');
            if (!isset($holidayCache[$monthKey])) {
                $holidayCache[$monthKey] = $holidayService->getMonthHolidays($cursor->year, $cursor->month);
            }
            if (isset($holidayCache[$monthKey][$cursor->day])) {
                continue;
            }

            $payload[] = [
                'group_id' => $period->group_id,
                'subject_id' => $subjectId,
                'teacher_id' => $period->teacher_id,
                'class_date' => $cursor->toDateString(),
                'year' => (int) $cursor->year,
                'month' => (int) $cursor->month,
                'day' => (int) $cursor->day,
                'lesson_number' => null,
                'subgroup' => 1,
                'total_hours' => 0,
                'hours_per_class' => (int) $period->hours_per_day,
                'status' => 'normal',
                'replacement_teacher_id' => null,
                'replacement_subject_id' => null,
                'bonus_hours' => null,
                'used_hours' => (int) $period->hours_per_day,
                'absent_reason' => null,
                'replacement_comment' => null,
                'mode' => 'single',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table($tables['form_two_records'])
            ->where('group_id', $period->group_id)
            ->whereBetween('class_date', [$start->toDateString(), $end->toDateString()])
            ->delete();

        if ($payload) {
            DB::table($tables['form_two_records'])->insert($payload);
        }
    }

    public function removePeriod(FieldCampPeriod $period): void
    {
        $course = (int) $period->course;
        if ($course !== 1) {
            return;
        }
        $tables = CourseContext::tables($course);
        $subjectIds = $this->fieldCampSubjectIds($course);

        $start = Carbon::parse($period->start_date);
        $end = Carbon::parse($period->end_date);

        if ($subjectIds) {
            DB::table($tables['form_two_records'])
                ->where('group_id', $period->group_id)
                ->whereBetween('class_date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('subject_id', $subjectIds)
                ->delete();
        }

        /** @var ScheduleToFormTwoSyncService $sync */
        $sync = app(ScheduleToFormTwoSyncService::class);
        $weekStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $lastWeek = $end->copy()->startOfWeek(Carbon::MONDAY);
        while ($weekStart->lte($lastWeek)) {
            $sync->syncWeek((int) $period->group_id, $weekStart, $weekStart, null, $course);
            $weekStart->addWeek();
        }
    }

    protected function ensureFieldCampSubjectId(int $course): int
    {
        $tables = CourseContext::tables($course);
        $table = $tables['subjects'];
        $subjectName = 'Полевые сборы';

        $existing = DB::table($table)
            ->where('name_ru', $subjectName)
            ->orWhere('subject_name', $subjectName)
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        DB::table($table)->insert([
            'module_title' => 'Практика',
            'module_index' => null,
            'subject_name' => $subjectName,
            'name_ru' => $subjectName,
            'name_kz' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table($table)
            ->where('name_ru', $subjectName)
            ->orWhere('subject_name', $subjectName)
            ->value('id');
    }

    protected function fieldCampSubjectIds(int $course): array
    {
        $tables = CourseContext::tables($course);
        $table = $tables['subjects'];
        $names = ['Полевые сборы'];

        return DB::table($table)
            ->whereIn('name_ru', $names)
            ->orWhereIn('subject_name', $names)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
