<?php

namespace App\Support;

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
     * @return array{groups:string,subjects:string,teachers:string,schedules:string,form_two_normatives:string,form_two_records:string}
     */
    public static function tables(int $course): array
    {
        $course = self::normalize($course);
        $prefix = self::prefix($course);
        $teacherTable = $course === 1 ? 'frist_course_teachers' : "{$prefix}_course_teachers";

        return [
            'groups' => "{$prefix}_course_group",
            'subjects' => "{$prefix}_course_subjects",
            'teachers' => $teacherTable,
            'schedules' => "{$prefix}_course_schedules",
            'form_two_normatives' => $course === 1 ? 'form_two_normatives' : "{$prefix}_form_two_normatives",
            'form_two_records' => $course === 1 ? 'form_two_records' : "{$prefix}_form_two_records",
        ];
    }
}
