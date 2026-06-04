<?php

namespace App\Support;

class TeacherAbsenceTypes
{
    public const TYPES = [
        'sick'         => 'Больничный',
        'order'        => 'По приказу',
        'dayoff'       => 'Отгул',
        'without_pay'  => 'Без содержания',
        'vacation'     => 'Отпуск',
        'travel'       => 'Командировка',
    ];

    // Типы, при которых нужна замена только преподавателя (предмет остаётся)
    public const TEACHER_ONLY_TYPES = ['sick', 'order', 'without_pay', 'vacation', 'travel'];

    // Типы, при которых нужна замена предмета (отгул — другой преп ведёт другой предмет)
    public const SUBJECT_REPLACE_TYPES = ['dayoff'];

    public static function labels(): array
    {
        return self::TYPES;
    }

    public static function values(): array
    {
        return array_keys(self::TYPES);
    }
}
