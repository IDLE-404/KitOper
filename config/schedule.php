<?php

return [
    /*
     * Дата начала семестра (неделя-числитель).
     * Можно задавать отдельно для курсов 1–4 через переменные окружения.
     */
    'semester_start' => [
        'default' => env('SEMESTER_START'),
        1 => env('SEMESTER_START_COURSE_1'),
        2 => env('SEMESTER_START_COURSE_2'),
        3 => env('SEMESTER_START_COURSE_3'),
        4 => env('SEMESTER_START_COURSE_4'),
    ],
];
