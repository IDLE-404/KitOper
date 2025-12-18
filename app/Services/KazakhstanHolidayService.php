<?php

namespace App\Services;

use Carbon\Carbon;

class KazakhstanHolidayService
{
    private array $cache = [];

    public function getMonthHolidays(int $year, int $month): array
    {
        $cacheKey = $year . ':' . $month;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $holidays = [];

        foreach ($this->getDefinitions($year) as $definition => $name) {
            if (!preg_match('/^(?<month>\d{2})-(?<day>\d{2})$/', $definition, $matches)) {
                continue;
            }

            if ((int) $matches['month'] !== $month) {
                continue;
            }

            $day = (int) $matches['day'];
            if ($day < 1 || $day > $daysInMonth) {
                continue;
            }

            $holidays[$day] = [
                'name' => $name,
                'date' => Carbon::create($year, $month, $day)->toDateString(),
            ];
        }

        return $this->cache[$cacheKey] = $holidays;
    }

    protected function getDefinitions(int $year): array
    {
        $default = config('kazakhstan_holidays.default', []);
        $perYear = config("kazakhstan_holidays.by_year.{$year}", []);

        return array_merge($default, $perYear);
    }
}
