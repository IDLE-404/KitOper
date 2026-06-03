<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KazakhstanHolidayService
{
    private array $cache = [];

    public function getMonthHolidays(int $year, int $month): array
    {
        $cacheKey = $year . ':' . $month;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $holidays = Schema::hasTable('holidays')
            ? $this->getDatabaseHolidays($year, $month)
            : $this->getConfigHolidays($year, $month);

        return $this->cache[$cacheKey] = $holidays;
    }

    protected function getConfigHolidays(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $holidays = [];
        $default = config('kazakhstan_holidays.default', []);
        $perYear = config("kazakhstan_holidays.by_year.{$year}", []);

        foreach (array_merge($default, $perYear) as $definition => $name) {
            if (!preg_match('/^(?<month>\\d{2})-(?<day>\\d{2})$/', $definition, $matches)) {
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

        $this->appendWinterVacation($holidays, $year, $month);

        return $holidays;
    }

    private function getDatabaseHolidays(int $year, int $month): array
    {
        $holidays = [];
        $rows = DB::table('holidays')
            ->where(function ($query) use ($year) {
                $query->whereNull('year')
                    ->orWhere('year', $year);
            })
            ->get([
                'name',
                'start_month',
                'start_day',
                'end_month',
                'end_day',
                'year',
            ]);

        foreach ($rows as $row) {
            $start = Carbon::create($year, (int) $row->start_month, (int) $row->start_day);
            $end = Carbon::create($year, (int) $row->end_month, (int) $row->end_day);

            if ($end->lt($start)) {
                continue;
            }

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->month !== $month) {
                    continue;
                }
                $day = $date->day;
                $holidays[$day] = [
                    'name' => $row->name,
                    'date' => $date->toDateString(),
                ];
            }
        }

        return $holidays;
    }

    private function appendWinterVacation(array &$holidays, int $year, int $month): void
    {
        $start = Carbon::create($year, 1, 19);
        $end = Carbon::create($year, 2, 1);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->month !== $month) {
                continue;
            }
            $day = $date->day;
            $holidays[$day] = [
                'name' => 'Каникулы',
                'date' => $date->toDateString(),
            ];
        }
    }
}
