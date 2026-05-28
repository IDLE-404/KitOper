<?php

namespace App\Services;

use App\Support\CourseContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleGeneratorService
{
    private const DAY_NAMES = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
    ];

    private const DAY_INTS = [
        'Понедельник' => 1,
        'Вторник'     => 2,
        'Среда'       => 3,
        'Четверг'     => 4,
        'Пятница'     => 5,
        'Суббота'     => 6,
    ];

    // -------------------------------------------------------------------------
    // Точка входа
    // -------------------------------------------------------------------------

    public function generate(
        int    $groupId,
        int    $course,
        int    $semester,
        Carbon $templateWeek,
        array  $params
    ): array {
        $tables   = CourseContext::tables($course);
        $weeks    = (int) ($params['weeks_in_semester'] ?? 18);
        $maxDay   = ($params['allow_saturday'] ?? false) ? 6 : 5;
        $maxPairs = (int) ($params['max_pairs_per_day'] ?? 4);

        $demand     = $this->loadDemand($groupId, $semester, $tables, $weeks);
        $teacherMap = $this->buildTeacherMap($templateWeek, $groupId, $course);

        // "both" пара покрывает числитель И знаменатель одной строкой → max, не сумма
        $totalDemand = array_sum(array_map(fn($i) => max($i['pairs_num'], $i['pairs_den']), $demand));

        if (empty($demand)) {
            return [
                'placed' => [], 'unplaced' => [],
                'stats'  => ['total_demand' => 0, 'placed' => 0, 'skipped' => 0, 'weeks_count' => $weeks, 'inserted_rows' => 0],
            ];
        }

        $result   = $this->evolve($demand, $teacherMap, $maxDay, $maxPairs, $params);
        $inserted = $this->persist($groupId, $templateWeek, $result['placed'], $tables);

        // Считаем уникальные слоты (не строки): subgroup-пара = 1 слот, 2 строки
        $uniqueSlots = count(array_unique(array_map(
            fn ($p) => $p['study_day'] . '|' . $p['lesson_number'] . '|' . $p['mode_flag'],
            $result['placed']
        )));

        return [
            'placed'   => $result['placed'],
            'unplaced' => $result['unplaced'],
            'stats'    => [
                'total_demand'  => $totalDemand,
                'placed'        => $uniqueSlots,
                'skipped'       => count($result['unplaced']),
                'weeks_count'   => $weeks,
                'inserted_rows' => $inserted,
                'generations'   => $result['generations'] ?? 0,
                'algorithm'     => 'genetic',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Генетический алгоритм
    // Хромосома = порядок расстановки предметов (indirect encoding)
    // Декодер   = greedy solver
    // -------------------------------------------------------------------------

    private function evolve(array $demand, array $teacherMap, int $maxDay, int $maxPairs, array $params): array
    {
        $popSize      = (int) ($params['ga_population'] ?? 40);
        $maxGen       = (int) ($params['ga_generations'] ?? 150);
        $mutRate      = (float) ($params['ga_mutation_rate'] ?? 0.15);
        $eliteCount   = 2;
        $tournamentK  = 3;
        $maxSeconds   = (int) ($params['ga_max_seconds'] ?? 8);
        $startTime    = microtime(true);

        // Индексы хромосомы: перестановка 0..N-1
        $n    = count($demand);
        $base = range(0, $n - 1);

        // Генерируем начальную популяцию
        // Первая особь — порядок от самого трудного к лёгкому
        $sortedIndices = range(0, $n - 1);
        usort($sortedIndices, function ($a, $b) use ($demand, $teacherMap, $maxDay, $maxPairs) {
            $fA = $this->countFreeTeacherSlots($demand[$a]['teacher_id'], $teacherMap, $maxDay, $maxPairs);
            $fB = $this->countFreeTeacherSlots($demand[$b]['teacher_id'], $teacherMap, $maxDay, $maxPairs);
            return $fA <=> $fB;
        });
        $population    = [];

        $population[] = $this->decodeChromosome($sortedIndices, $demand, $teacherMap, $maxDay, $maxPairs);

        for ($i = 1; $i < $popSize; $i++) {
            $perm = $base;
            shuffle($perm);
            $population[] = $this->decodeChromosome($perm, $demand, $teacherMap, $maxDay, $maxPairs);
        }

        usort($population, fn ($a, $b) => $a['fitness'] <=> $b['fitness']);

        $genDone = 0;
        for ($gen = 0; $gen < $maxGen; $gen++) {
            if ((microtime(true) - $startTime) >= $maxSeconds) {
                break;
            }

            // Идеальное решение — ранний выход
            if ($population[0]['fitness'] === 0) {
                break;
            }

            $newPop = array_slice($population, 0, $eliteCount);

            while (count($newPop) < $popSize) {
                $p1    = $this->tournamentSelect($population, $tournamentK);
                $p2    = $this->tournamentSelect($population, $tournamentK);
                $child = $this->pmxCrossover($p1['chromosome'], $p2['chromosome']);

                if (mt_rand(1, 100) <= ($mutRate * 100)) {
                    $child = $this->swapMutate($child);
                }

                $newPop[] = $this->decodeChromosome($child, $demand, $teacherMap, $maxDay, $maxPairs);
            }

            usort($newPop, fn ($a, $b) => $a['fitness'] <=> $b['fitness']);
            $population = $newPop;
            $genDone++;
        }

        $best = $population[0];
        return [
            'placed'      => $best['result']['placed'],
            'unplaced'    => $best['result']['unplaced'],
            'fitness'     => $best['fitness'],
            'generations' => $genDone,
        ];
    }

    // Декодирует хромосому (порядок индексов) в расписание через greedy
    private function decodeChromosome(array $chromosome, array $demand, array $teacherMap, int $maxDay, int $maxPairs): array
    {
        $ordered = array_map(fn ($i) => $demand[$i], $chromosome);
        $grid    = $this->buildGrid();
        $result  = $this->solve($ordered, $grid, $teacherMap, $maxDay, $maxPairs);

        return [
            'chromosome' => $chromosome,
            'result'     => $result,
            'fitness'    => $this->fitness($result),
        ];
    }

    // Фитнес: меньше = лучше
    private function fitness(array $result): int
    {
        $score = count($result['unplaced']) * 1000;

        foreach ($result['placed'] as $p) {
            $lesson = (int) $p['lesson_number'];
            $day    = self::DAY_INTS[$p['study_day']] ?? 1;

            $score += $lesson * 3;
            if ($day === 6) {
                $score += 15;
            }
        }

        // Штраф за перегруженные дни (> 4 пар)
        $dayCount = [];
        foreach ($result['placed'] as $p) {
            $dayCount[$p['study_day']] = ($dayCount[$p['study_day']] ?? 0) + 1;
        }
        foreach ($dayCount as $cnt) {
            if ($cnt > 4) {
                $score += ($cnt - 4) * 20;
            }
        }

        return $score;
    }

    // Турнирный отбор
    private function tournamentSelect(array $population, int $k): array
    {
        $best = null;
        $size = count($population);
        for ($i = 0; $i < $k; $i++) {
            $candidate = $population[mt_rand(0, $size - 1)];
            if ($best === null || $candidate['fitness'] < $best['fitness']) {
                $best = $candidate;
            }
        }
        return $best;
    }

    // PMX (Partially Matched Crossover) для перестановок
    private function pmxCrossover(array $p1, array $p2): array
    {
        $n      = count($p1);
        $child  = array_fill(0, $n, -1);
        $a      = mt_rand(0, $n - 2);
        $b      = mt_rand($a + 1, $n - 1);

        // Копируем отрезок из p2
        for ($i = $a; $i <= $b; $i++) {
            $child[$i] = $p2[$i];
        }

        // Заполняем остальные позиции из p1 без дублей
        $used = array_flip(array_slice($child, $a, $b - $a + 1));
        $pos  = 0;
        foreach ($p1 as $val) {
            if (isset($used[$val])) {
                continue;
            }
            while ($pos >= $a && $pos <= $b) {
                $pos++;
            }
            if ($pos < $n) {
                $child[$pos] = $val;
                $pos++;
            }
        }

        return $child;
    }

    // Мутация: меняем 2 случайных позиции местами
    private function swapMutate(array $chromosome): array
    {
        $n  = count($chromosome);
        if ($n < 2) {
            return $chromosome;
        }
        $i = mt_rand(0, $n - 1);
        $j = mt_rand(0, $n - 1);
        while ($j === $i) {
            $j = mt_rand(0, $n - 1);
        }
        [$chromosome[$i], $chromosome[$j]] = [$chromosome[$j], $chromosome[$i]];
        return $chromosome;
    }

    // -------------------------------------------------------------------------
    // Загрузка нормативов → demand[]
    // -------------------------------------------------------------------------

    private function loadDemand(int $groupId, int $semester, array $tables, int $weeks): array
    {
        $normatives = DB::table($tables['form_two_normatives'])
            ->where('group_id', $groupId)
            ->where('semester', $semester)
            ->whereNotNull('subject_id')
            ->get(['subject_id', 'teacher_id', 'total_hours', 'hours_per_class']);

        // Если нормативов для семестра нет — пробуем без фильтра по семестру
        if ($normatives->isEmpty()) {
            $normatives = DB::table($tables['form_two_normatives'])
                ->where('group_id', $groupId)
                ->whereNotNull('subject_id')
                ->get(['subject_id', 'teacher_id', 'total_hours', 'hours_per_class']);
        }

        $subjectNames = DB::table($tables['subjects'])
            ->pluck('subject_name', 'id');

        // Fallback 1: teacher_subjects — глобальная привязка предмет→преподаватель
        $teacherBySubjectGlobal = DB::table($tables['teacher_subjects'])
            ->pluck('teacher_id', 'subject_id');

        // Fallback 2: последнее расписание группы — самый актуальный источник
        $schedRows = DB::table($tables['schedules'])
            ->where('group_id', $groupId)
            ->whereNotNull('subject_id')
            ->orderByDesc('week_start')
            ->get(['subject_id', 'teacher_id', 'subgroup']);

        $teacherBySubjectSchedule = $schedRows
            ->filter(fn ($r) => !empty($r->teacher_id) && empty($r->subgroup))
            ->unique('subject_id')
            ->pluck('teacher_id', 'subject_id');

        // Подгруппы: предметы которые делятся (subgroup=1 и subgroup=2 в расписании)
        // Структура: [subject_id => ['teacher1' => tid, 'teacher2' => tid]]
        $subgroupMap = [];
        $sub1 = $schedRows->filter(fn ($r) => (string) ($r->subgroup ?? '') === '1')
            ->unique('subject_id');
        $sub2 = $schedRows->filter(fn ($r) => (string) ($r->subgroup ?? '') === '2')
            ->unique('subject_id');
        foreach ($sub1 as $r) {
            $subgroupMap[(int) $r->subject_id]['teacher1'] = $r->teacher_id ? (int) $r->teacher_id : null;
        }
        foreach ($sub2 as $r) {
            $subgroupMap[(int) $r->subject_id]['teacher2'] = $r->teacher_id ? (int) $r->teacher_id : null;
        }

        $demand = [];
        foreach ($normatives as $norm) {
            $hpc          = max(1, (int) ($norm->hours_per_class ?: 2));
            $pairsPerWeek = ($norm->total_hours > 0 && $weeks > 0)
                ? $norm->total_hours / $weeks / $hpc
                : 0;

            if ($pairsPerWeek <= 0) {
                continue;
            }

            ['num' => $pairsNum, 'den' => $pairsDen] = $this->calcPairs($pairsPerWeek);

            if ($pairsNum === 0 && $pairsDen === 0) {
                continue;
            }

            $sid = (int) $norm->subject_id;

            // Подгруппный предмет?
            if (isset($subgroupMap[$sid])) {
                $t1 = $subgroupMap[$sid]['teacher1'] ?? null;
                $t2 = $subgroupMap[$sid]['teacher2'] ?? null;

                // Если оба учителя известны и разные → реальный подгрупповой предмет
                if ($t1 && $t2 && $t1 !== $t2) {
                    $demand[] = [
                        'subject_id'      => $sid,
                        'subject_name'    => $subjectNames[$sid] ?? "Предмет #{$sid}",
                        'teacher_id'      => $t1,
                        'teacher_id_2'    => $t2,
                        'has_subgroups'   => true,
                        'hours_per_class' => $hpc,
                        'pairs_num'       => $pairsNum,
                        'pairs_den'       => $pairsDen,
                    ];
                    continue;
                }

                // Один учитель для обоих подгрупп → ставим как обычный предмет
                $demand[] = [
                    'subject_id'      => $sid,
                    'subject_name'    => $subjectNames[$sid] ?? "Предмет #{$sid}",
                    'teacher_id'      => $t1 ?? $t2,
                    'teacher_id_2'    => null,
                    'has_subgroups'   => false,
                    'hours_per_class' => $hpc,
                    'pairs_num'       => $pairsNum,
                    'pairs_den'       => $pairsDen,
                ];
                continue;
            }

            // Обычный предмет — приоритет: норматив → расписание → teacher_subjects
            $teacherId = $norm->teacher_id
                ? (int) $norm->teacher_id
                : ($teacherBySubjectSchedule[$sid] ?? $teacherBySubjectGlobal[$sid] ?? null);

            $demand[] = [
                'subject_id'      => $sid,
                'subject_name'    => $subjectNames[$sid] ?? "Предмет #{$sid}",
                'teacher_id'      => $teacherId ? (int) $teacherId : null,
                'teacher_id_2'    => null,
                'has_subgroups'   => false,
                'hours_per_class' => $hpc,
                'pairs_num'       => $pairsNum,
                'pairs_den'       => $pairsDen,
            ];
        }

        return $demand;
    }

    private function calcPairs(float $pairsPerWeek): array
    {
        $floor = (int) floor($pairsPerWeek);
        $frac  = $pairsPerWeek - $floor;

        if ($frac < 0.15) {
            return ['num' => $floor, 'den' => $floor];
        }
        if (abs($frac - 0.5) < 0.15) {
            return ['num' => $floor + 1, 'den' => $floor];
        }
        // Нестандартный дроб — округляем вверх для числителя
        return ['num' => $floor + 1, 'den' => $floor];
    }

    // -------------------------------------------------------------------------
    // Карта занятости преподавателей (все курсы, кроме нашей группы)
    // -------------------------------------------------------------------------

    private function buildTeacherMap(Carbon $templateWeek, int $excludeGroupId, int $course): array
    {
        $teacherMap = [];

        for ($c = 1; $c <= 4; $c++) {
            $tables = CourseContext::tables($c);
            $rows   = DB::table($tables['schedules'])
                ->whereDate('week_start', $templateWeek->toDateString())
                ->where('group_id', '!=', $excludeGroupId)
                ->get(['teacher_id', 'teacher_id_denominator', 'teacher_id_2', 'teacher_id_denominator_2', 'study_day', 'lesson_number']);

            foreach ($rows as $row) {
                $day    = self::DAY_INTS[$row->study_day] ?? 0;
                $lesson = (int) $row->lesson_number;
                if (!$day || !$lesson) {
                    continue;
                }
                foreach (['teacher_id', 'teacher_id_denominator', 'teacher_id_2', 'teacher_id_denominator_2'] as $field) {
                    $tid = $row->$field ? (int) $row->$field : null;
                    if ($tid) {
                        $teacherMap[$tid][$day][$lesson] = true;
                    }
                }
            }
        }

        return $teacherMap;
    }

    // -------------------------------------------------------------------------
    // Матрица слотов группы 6 дней × 7 пар × 2 режима
    // -------------------------------------------------------------------------

    private function buildGrid(): array
    {
        $grid = [];
        for ($d = 1; $d <= 6; $d++) {
            for ($l = 1; $l <= 7; $l++) {
                $grid[$d][$l]['num'] = null;
                $grid[$d][$l]['den'] = null;
            }
        }
        return $grid;
    }

    // -------------------------------------------------------------------------
    // Основной алгоритм
    // -------------------------------------------------------------------------

    private function solve(array $demand, array &$grid, array $teacherMap, int $maxDay, int $maxPairs): array
    {
        $demand   = $this->orderByDifficulty($demand, $teacherMap, $maxDay, $maxPairs);
        $placed   = [];
        $unplaced = [];

        foreach ($demand as $item) {
            $bothCount = min($item['pairs_num'], $item['pairs_den']);

            for ($i = 0; $i < $bothCount; $i++) {
                $slot = $this->pickBestSlot($item, 'both', $grid, $teacherMap, $maxDay, $maxPairs);
                if ($slot) {
                    $this->placeInGrid($grid, $slot['day'], $slot['lesson'], 'both', $item);
                    $this->markTeachers($teacherMap, $slot['day'], $slot['lesson'], $item);
                    foreach ($this->makeRow($slot['day'], $slot['lesson'], 'both', $item) as $r) {
                        $placed[] = $r;
                    }
                } else {
                    $unplaced[] = ['subject_name' => $item['subject_name'], 'mode' => 'both'];
                }
            }

            for ($i = $bothCount; $i < $item['pairs_num']; $i++) {
                $slot = $this->pickBestSlot($item, 'num', $grid, $teacherMap, $maxDay, $maxPairs);
                if ($slot) {
                    $this->placeInGrid($grid, $slot['day'], $slot['lesson'], 'num', $item);
                    $this->markTeachers($teacherMap, $slot['day'], $slot['lesson'], $item);
                    foreach ($this->makeRow($slot['day'], $slot['lesson'], 'num', $item) as $r) {
                        $placed[] = $r;
                    }
                } else {
                    $unplaced[] = ['subject_name' => $item['subject_name'], 'mode' => 'num'];
                }
            }

            for ($i = $bothCount; $i < $item['pairs_den']; $i++) {
                $slot = $this->pickBestSlot($item, 'den', $grid, $teacherMap, $maxDay, $maxPairs);
                if ($slot) {
                    $this->placeInGrid($grid, $slot['day'], $slot['lesson'], 'den', $item);
                    $this->markTeachers($teacherMap, $slot['day'], $slot['lesson'], $item);
                    foreach ($this->makeRow($slot['day'], $slot['lesson'], 'den', $item) as $r) {
                        $placed[] = $r;
                    }
                } else {
                    $unplaced[] = ['subject_name' => $item['subject_name'], 'mode' => 'den'];
                }
            }
        }

        return ['placed' => $placed, 'unplaced' => $unplaced];
    }

    // Обновляем teacherMap после каждого размещения — предотвращает конфликты внутри генерации
    private function markTeachers(array &$teacherMap, int $day, int $lesson, array $item): void
    {
        if ($item['teacher_id']) {
            $teacherMap[$item['teacher_id']][$day][$lesson] = true;
        }
        if (!empty($item['has_subgroups']) && !empty($item['teacher_id_2'])
            && $item['teacher_id_2'] !== $item['teacher_id']) {
            $teacherMap[$item['teacher_id_2']][$day][$lesson] = true;
        }
    }

    // Сначала размещаем предметы у преподавателей с наименьшим кол-вом свободных слотов
    private function orderByDifficulty(array $demand, array $teacherMap, int $maxDay, int $maxPairs): array
    {
        usort($demand, function ($a, $b) use ($teacherMap, $maxDay, $maxPairs) {
            $freeA = $this->countFreeTeacherSlots($a['teacher_id'], $teacherMap, $maxDay, $maxPairs);
            $freeB = $this->countFreeTeacherSlots($b['teacher_id'], $teacherMap, $maxDay, $maxPairs);
            return $freeA <=> $freeB;
        });
        return $demand;
    }

    private function countFreeTeacherSlots(?int $tid, array $teacherMap, int $maxDay, int $maxPairs): int
    {
        if (!$tid) {
            return 999; // без преподавателя — самые свободные
        }
        $free = 0;
        for ($d = 1; $d <= $maxDay; $d++) {
            for ($l = 1; $l <= $maxPairs; $l++) {
                if (empty($teacherMap[$tid][$d][$l])) {
                    $free++;
                }
            }
        }
        return $free;
    }

    private function pickBestSlot(array $item, string $mode, array $grid, array $teacherMap, int $maxDay, int $maxPairs): ?array
    {
        $candidates  = [];
        $hasSubgroup = $item['has_subgroups'] ?? false;
        $tid2        = $hasSubgroup ? ($item['teacher_id_2'] ?? null) : null;

        for ($d = 1; $d <= $maxDay; $d++) {
            for ($l = 1; $l <= $maxPairs; $l++) {
                if (!$this->slotFreeInGrid($grid, $d, $l, $mode)) {
                    continue;
                }
                if (!$this->teacherFree($teacherMap, $d, $l, $item['teacher_id'])) {
                    continue;
                }
                // Для подгрупп: второй учитель тоже должен быть свободен
                if ($hasSubgroup && !$this->teacherFree($teacherMap, $d, $l, $tid2)) {
                    continue;
                }
                $candidates[] = [
                    'day'    => $d,
                    'lesson' => $l,
                    'score'  => $this->scoreSlot($d, $l, $mode, $grid, $item),
                ];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);
        return $candidates[0];
    }

    private function slotFreeInGrid(array $grid, int $day, int $lesson, string $mode): bool
    {
        return match ($mode) {
            'both' => $grid[$day][$lesson]['num'] === null && $grid[$day][$lesson]['den'] === null,
            'num'  => $grid[$day][$lesson]['num'] === null,
            'den'  => $grid[$day][$lesson]['den'] === null,
            default => false,
        };
    }

    private function teacherFree(array $teacherMap, int $day, int $lesson, ?int $tid): bool
    {
        if (!$tid) {
            return true;
        }
        return empty($teacherMap[$tid][$day][$lesson]);
    }

    private function scoreSlot(int $day, int $lesson, string $mode, array $grid, array $item): int
    {
        $score = 100;

        // Предпочитаем утренние пары (1-3)
        $score -= $lesson * 8;

        // Штраф за субботу
        if ($day === 6) {
            $score -= 20;
        }

        // Штраф если этот предмет уже стоит в этот день
        $pairsOnDay = $this->countGroupPairsOnDay($grid, $day);
        $score -= $pairsOnDay * 12;

        // Штраф за "окно" между парами
        if ($this->createsGap($grid, $day, $lesson, $mode)) {
            $score -= 35;
        }

        // Бонус за равномерность — поощряем дни без пар
        $activeDays = $this->countActiveDays($grid);
        if ($activeDays < 3 && $pairsOnDay === 0) {
            $score += 25;
        }

        // Штраф если этот предмет уже есть в этот день
        if ($this->subjectAlreadyOnDay($grid, $day, $item['subject_id'], $mode)) {
            $score -= 40;
        }

        return $score;
    }

    private function countGroupPairsOnDay(array $grid, int $day): int
    {
        $count = 0;
        foreach ($grid[$day] as $slots) {
            if ($slots['num'] !== null || $slots['den'] !== null) {
                $count++;
            }
        }
        return $count;
    }

    private function createsGap(array $grid, int $day, int $lesson, string $mode): bool
    {
        // Проверяем есть ли занятые слоты ПОСЛЕ пустого промежутка перед $lesson
        $hasBelow = false;
        $hasGap   = false;
        for ($l = 1; $l < $lesson; $l++) {
            $occupied = ($grid[$day][$l]['num'] !== null || $grid[$day][$l]['den'] !== null);
            if ($hasBelow && !$occupied) {
                $hasGap = true;
            }
            if ($occupied) {
                $hasBelow = true;
            }
        }
        return $hasGap;
    }

    private function countActiveDays(array $grid): int
    {
        $active = 0;
        for ($d = 1; $d <= 6; $d++) {
            foreach ($grid[$d] as $slots) {
                if ($slots['num'] !== null || $slots['den'] !== null) {
                    $active++;
                    break;
                }
            }
        }
        return $active;
    }

    private function subjectAlreadyOnDay(array $grid, int $day, int $subjectId, string $mode): bool
    {
        foreach ($grid[$day] as $slots) {
            $sid = $mode === 'den' ? ($slots['den'] ?? null) : ($slots['num'] ?? null);
            if ($sid && ($sid['subject_id'] ?? null) === $subjectId) {
                return true;
            }
        }
        return false;
    }

    private function placeInGrid(array &$grid, int $day, int $lesson, string $mode, array $item): void
    {
        // Для подгрупп помечаем оба учителя занятыми в этом слоте
        $data = ['subject_id' => $item['subject_id'], 'teacher_id' => $item['teacher_id']];
        if ($mode === 'both' || $mode === 'num') {
            $grid[$day][$lesson]['num'] = $data;
        }
        if ($mode === 'both' || $mode === 'den') {
            $grid[$day][$lesson]['den'] = $data;
        }
    }

    /**
     * Для подгрупп возвращает МАССИВ из двух строк (subgroup 1 и 2).
     * Для обычных предметов — массив из одной строки.
     */
    private function makeRow(int $day, int $lesson, string $mode, array $item): array
    {
        $base = [
            'study_day'     => self::DAY_NAMES[$day],
            'lesson_number' => $lesson,
            'subject_id'    => $item['subject_id'],
            'teacher_id'    => $item['teacher_id'],
            'mode_flag'     => $mode,
            'subgroup'      => null,
            'teacher_id_2'  => null,
        ];

        if (!empty($item['has_subgroups'])) {
            $base['subgroup']     = '1';
            $row2                 = $base;
            $row2['subgroup']     = '2';
            $row2['teacher_id']   = $item['teacher_id_2'] ?? $item['teacher_id'];
            return [$base, $row2];
        }

        return [$base];
    }

    // -------------------------------------------------------------------------
    // Запись в БД
    // -------------------------------------------------------------------------

    private function persist(int $groupId, Carbon $templateWeek, array $placed, array $tables): int
    {
        if (empty($placed)) {
            return 0;
        }

        $rows = [];
        $now  = now();

        foreach ($placed as $p) {
            $modeFlag = $p['mode_flag'];
            $subgroup = $p['subgroup'] ?? null;

            $rows[] = [
                'week_start'             => $templateWeek->toDateString(),
                'study_day'              => $p['study_day'],
                'lesson_number'          => $p['lesson_number'],
                'group_id'               => $groupId,
                'subgroup'               => $subgroup ?: null,
                // Числитель
                'subject_id'             => $modeFlag !== 'den' ? $p['subject_id'] : null,
                'teacher_id'             => $modeFlag !== 'den' ? $p['teacher_id'] : null,
                // Знаменатель
                'subject_id_denominator' => $modeFlag !== 'num' ? $p['subject_id'] : null,
                'teacher_id_denominator' => $modeFlag !== 'num' ? $p['teacher_id'] : null,
                'room_id'                => null,
                'room_id_denominator'    => null,
                'is_replacement'         => 0,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        // Вставляем чанками по 50
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table($tables['schedules'])->insert($chunk);
        }

        return count($rows);
    }
}
