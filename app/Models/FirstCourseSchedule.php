<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FirstCourseSchedule extends Model
{
    protected $table = 'first_course_schedules';

    protected static function field(object|array $row, string $key)
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }

        return $row->{$key} ?? null;
    }

    /**
     * Проверка занятости кабинета для указанного слота.
     */
    public static function roomConflictExists(
        int $groupId,
        string $studyDay,
        int $lessonNumber,
        $roomId,
        string $mode,
        ?\Carbon\Carbon $weekStart = null
    ): bool {
        if ($roomId === null || $roomId === '') {
            return false;
        }

        $roomId = (string) $roomId;

        $rows = DB::table('first_course_schedules')
            ->where('study_day', $studyDay)
            ->where('lesson_number', $lessonNumber)
            ->where('group_id', '<>', $groupId)
            ->when($weekStart, fn ($q) => $q->whereDate('week_start', $weekStart->toDateString()))
            ->get([
                'group_id',
                'study_day',
                'lesson_number',
                'subgroup',
                'room_id',
                'room_id_denominator',
                'room_id_denominator_2',
                'room_id_2',
                'subject_id_denominator',
                'teacher_id_denominator',
                'subject_id_denominator_2',
                'teacher_id_denominator_2',
            ]);

        foreach ($rows as $row) {
            $modes = self::numeratorModesForRow($row);
            $slots = self::roomSlotsForRow($row, $modes);

            foreach ($slots as $slot) {
                if ((string) $slot['room'] !== $roomId || $slot['mode'] !== $mode) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Собрать карту конфликтов для уже загруженных строк расписания.
     *
     * Возвращает массив [$groupId][$day][$lesson][$mode][$subgroup] => true
     */
    public static function detectRoomConflicts(Collection $rows): array
    {
        $slots = [];

        foreach ($rows as $row) {
            $modes = self::numeratorModesForRow($row);
            $subgroup = self::field($row, 'subgroup') === '2' ? '2' : '1';

            foreach (self::roomSlotsForRow($row, $modes, $subgroup) as $slot) {
                $key = implode('|', [$slot['room'], $slot['day'], $slot['lesson'], $slot['mode']]);
                $slots[$key]['groups'][$slot['group_id']] = true;
                $slots[$key]['items'][] = $slot;
            }
        }

        $conflicts = [];
        foreach ($slots as $slot) {
            if (count($slot['groups'] ?? []) < 2) {
                continue;
            }

            foreach ($slot['items'] as $item) {
                $conflicts[$item['group_id']][$item['day']][$item['lesson']][$item['mode']][$item['subgroup']] = true;
            }
        }

        return $conflicts;
    }

    /**
     * Определяем набор режимов недели для поля room_id/room_id_2 (числитель или все недели).
     */
    protected static function numeratorModesForRow(object $row): array
    {
        $hasDenominator = self::field($row, 'subject_id_denominator')
            || self::field($row, 'teacher_id_denominator')
            || self::field($row, 'room_id_denominator')
            || self::field($row, 'subject_id_denominator_2')
            || self::field($row, 'teacher_id_denominator_2')
            || self::field($row, 'room_id_denominator_2');

        // Если нет знаменателя — слот действует на обе недели.
        return $hasDenominator ? ['numerator'] : ['numerator', 'denominator'];
    }

    /**
     * Возвращает набор слотов (кабинет + режим недели) для строки расписания.
     */
    protected static function roomSlotsForRow(object $row, array $modes, string $subgroup = '1'): array
    {
        $result = [];
        $day = self::field($row, 'study_day');
        $lesson = self::field($row, 'lesson_number');

        if ($day === null || $lesson === null) {
            return $result;
        }

        $subgroupFlag = self::field($row, 'subgroup') === '2' ? '2' : '1';

        $roomNum1 = $subgroupFlag === '1' ? self::field($row, 'room_id') : null;
        $roomNum2 = self::field($row, 'room_id_2') ?: ($subgroupFlag === '2' ? self::field($row, 'room_id') : null);

        $roomDen1 = $subgroupFlag === '1' ? self::field($row, 'room_id_denominator') : null;
        $roomDen2 = self::field($row, 'room_id_denominator_2') ?: ($subgroupFlag === '2' ? self::field($row, 'room_id_denominator') : null);

        $append = function ($room, string $mode, string $subgroupKey) use (&$result, $row, $day, $lesson) {
            if ($room === null || $room === '') {
                return;
            }

            $result[] = [
                'group_id' => self::field($row, 'group_id'),
                'day' => $day,
                'lesson' => $lesson,
                'subgroup' => $subgroupKey,
                'mode' => $mode,
                'room' => (string) $room,
            ];
        };

        foreach ($modes as $mode) {
            $append($roomNum1, $mode, '1');
            $append($roomNum2, $mode, '2');
        }

        // Знаменатель — только для второй половины недели.
        $append($roomDen1, 'denominator', '1');
        $append($roomDen2, 'denominator', '2');

        return $result;
    }
}
