<?php

namespace App\Support;

use Carbon\Carbon;

class CourseContext
{
    public static function normalize(int|string|null $course): int
    {
        $val = (int) ($course ?: 1);
        if ($val < 1 || $val > 4) {
            return 1;
        }
        return $val;
    }

    public static function prefix(int $course): string
    {
        return match (self::normalize($course)) {
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
            default => 'first',
        };
    }

    /**
     * Возвращает набор таблиц для курса.
     *
     * @return array{groups:string,subjects:string,teachers:string,schedules:string,form_two_normatives:string,form_two_records:string,form_two_practice_records:string,teacher_subjects:string}
     */
    public static function tables(int $course): array
    {
        $course = self::normalize($course);
        $prefix = self::prefix($course);

        return [
            'groups' => "{$prefix}_course_group",
            'subjects' => "{$prefix}_course_subjects",
            'teachers' => 'teachers',
            'schedules' => "{$prefix}_course_schedules",
            'form_two_normatives' => $course === 1 ? 'form_two_normatives' : "{$prefix}_form_two_normatives",
            'form_two_records' => $course === 1 ? 'form_two_records' : "{$prefix}_form_two_records",
            'form_two_practice_records' => $course === 1 ? 'form_two_practice_records' : "{$prefix}_form_two_practice_records",
            'teacher_subjects' => "{$prefix}_course_teacher_subjects",
        ];
    }

    public static function semesterStart(int $course, ?Carbon $reference = null): Carbon
    {
        $course = self::normalize($course);
        $configured = config('schedule.semester_start');
        $date = null;
        if (is_array($configured)) {
            $date = $configured[$course] ?? ($configured['default'] ?? null);
        } elseif (is_string($configured) && $configured !== '') {
            $date = $configured;
        }

        if ($date) {
            return Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
        }

        $ref = $reference ? $reference->copy() : Carbon::now();
        $year = $ref->month >= 9 ? $ref->year : $ref->year - 1;

        return Carbon::create($year, 9, 1)->startOfWeek(Carbon::MONDAY);
    }
}
