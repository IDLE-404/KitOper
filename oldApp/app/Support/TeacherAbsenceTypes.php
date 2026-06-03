<?php

namespace App\Support;

class TeacherAbsenceTypes
{
    public const TYPES = [
        'sick' => 'Больничный',
        'order' => 'По приказу',
        'vacation' => 'Отпуск',
        'travel' => 'Командировка',
    ];

    public static function labels(): array
    {
        return self::TYPES;
    }

    public static function values(): array
    {
        return array_keys(self::TYPES);
    }
}
