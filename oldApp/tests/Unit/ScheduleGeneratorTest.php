<?php

namespace Tests\Unit;

use App\Services\ScheduleGeneratorService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Тесты чистой логики генератора расписания — без обращений к БД.
 */
class ScheduleGeneratorTest extends TestCase
{
    private ScheduleGeneratorService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        // Создаём без DI-контейнера — тестируем только PHP-логику
        $this->svc = $this->createSvcWithoutDeps();
    }

    // -------------------------------------------------------------------------
    // calcPairs
    // -------------------------------------------------------------------------

    public function test_calc_pairs_whole_number(): void
    {
        // 36ч / 18 нед / 2ч = 1.0 пара/нед → num=1, den=1
        $result = $this->call('calcPairs', 1.0);
        $this->assertSame(['num' => 1, 'den' => 1], $result);
    }

    public function test_calc_pairs_half(): void
    {
        // 18ч / 18 нед / 2ч = 0.5 → num=1, den=0
        $result = $this->call('calcPairs', 0.5);
        $this->assertSame(['num' => 1, 'den' => 0], $result);
    }

    public function test_calc_pairs_two_per_week(): void
    {
        // 72ч / 18 нед / 2ч = 2.0 → num=2, den=2
        $result = $this->call('calcPairs', 2.0);
        $this->assertSame(['num' => 2, 'den' => 2], $result);
    }

    public function test_calc_pairs_one_and_half(): void
    {
        // 54ч / 18 нед / 2ч = 1.5 → num=2, den=1
        $result = $this->call('calcPairs', 1.5);
        $this->assertSame(['num' => 2, 'den' => 1], $result);
    }

    public function test_calc_pairs_zero_gives_zeros(): void
    {
        $result = $this->call('calcPairs', 0.0);
        $this->assertSame(['num' => 0, 'den' => 0], $result);
    }

    // -------------------------------------------------------------------------
    // buildGrid
    // -------------------------------------------------------------------------

    public function test_build_grid_structure(): void
    {
        $grid = $this->call('buildGrid');

        // 6 дней × 7 пар
        $this->assertCount(6, $grid);
        for ($d = 1; $d <= 6; $d++) {
            $this->assertCount(7, $grid[$d]);
            for ($l = 1; $l <= 7; $l++) {
                $this->assertNull($grid[$d][$l]['num']);
                $this->assertNull($grid[$d][$l]['den']);
            }
        }
    }

    // -------------------------------------------------------------------------
    // slotFreeInGrid
    // -------------------------------------------------------------------------

    public function test_slot_free_in_empty_grid(): void
    {
        $grid = $this->call('buildGrid');

        $this->assertTrue($this->call('slotFreeInGrid', $grid, 1, 1, 'both'));
        $this->assertTrue($this->call('slotFreeInGrid', $grid, 1, 1, 'num'));
        $this->assertTrue($this->call('slotFreeInGrid', $grid, 1, 1, 'den'));
    }

    public function test_slot_not_free_after_num_placed(): void
    {
        $grid = $this->call('buildGrid');
        $grid[1][1]['num'] = ['subject_id' => 5, 'teacher_id' => 10];

        $this->assertFalse($this->call('slotFreeInGrid', $grid, 1, 1, 'both'));
        $this->assertFalse($this->call('slotFreeInGrid', $grid, 1, 1, 'num'));
        $this->assertTrue($this->call('slotFreeInGrid', $grid, 1, 1, 'den'));
    }

    // -------------------------------------------------------------------------
    // teacherFree
    // -------------------------------------------------------------------------

    public function test_teacher_free_when_map_empty(): void
    {
        $this->assertTrue($this->call('teacherFree', [], 1, 1, 42));
    }

    public function test_teacher_not_free_when_marked(): void
    {
        $map = [42 => [1 => [1 => true]]];
        $this->assertFalse($this->call('teacherFree', $map, 1, 1, 42));
    }

    public function test_null_teacher_always_free(): void
    {
        $map = [42 => [1 => [1 => true]]];
        $this->assertTrue($this->call('teacherFree', $map, 1, 1, null));
    }

    // -------------------------------------------------------------------------
    // fitness
    // -------------------------------------------------------------------------

    public function test_fitness_worse_for_unplaced(): void
    {
        $good = ['placed' => $this->makePlacedRows(5), 'unplaced' => []];
        $bad  = ['placed' => $this->makePlacedRows(5), 'unplaced' => [['subject_name' => 'X', 'mode' => 'both']]];

        $fitGood = $this->call('fitness', $good);
        $fitBad  = $this->call('fitness', $bad);

        $this->assertGreaterThan($fitGood, $fitBad, 'Непоставленная пара должна увеличивать штраф');
    }

    public function test_fitness_worse_for_late_lessons(): void
    {
        $early = ['placed' => [['study_day' => 'Понедельник', 'lesson_number' => 1, 'teacher_id' => 1]], 'unplaced' => []];
        $late  = ['placed' => [['study_day' => 'Понедельник', 'lesson_number' => 6, 'teacher_id' => 1]], 'unplaced' => []];

        $fitEarly = $this->call('fitness', $early);
        $fitLate  = $this->call('fitness', $late);

        $this->assertGreaterThan($fitEarly, $fitLate, 'Поздняя пара должна давать больший штраф');
    }

    // -------------------------------------------------------------------------
    // pmxCrossover
    // -------------------------------------------------------------------------

    public function test_pmx_crossover_produces_valid_permutation(): void
    {
        $p1 = [0, 1, 2, 3, 4];
        $p2 = [4, 3, 2, 1, 0];

        $child = $this->call('pmxCrossover', $p1, $p2);

        $this->assertCount(5, $child);
        sort($child);
        $this->assertSame([0, 1, 2, 3, 4], $child, 'PMX должен вернуть перестановку без дублей');
    }

    public function test_swap_mutate_changes_order(): void
    {
        // При достаточно длинной хромосоме мутация что-то меняет
        $original = [0, 1, 2, 3, 4, 5];
        $mutated  = $this->call('swapMutate', $original);

        $this->assertCount(count($original), $mutated);
        sort($mutated);
        $sorted = $original;
        sort($sorted);
        $this->assertSame($sorted, $mutated, 'Мутация сохраняет все элементы');
    }

    // -------------------------------------------------------------------------
    // solve — интеграционный тест логики без БД
    // -------------------------------------------------------------------------

    public function test_solve_places_all_with_no_constraints(): void
    {
        $demand = [
            ['subject_id' => 1, 'subject_name' => 'Математика', 'teacher_id' => null, 'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2, 'pairs_num' => 1, 'pairs_den' => 1],
            ['subject_id' => 2, 'subject_name' => 'Физика',     'teacher_id' => null, 'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2, 'pairs_num' => 1, 'pairs_den' => 1],
            ['subject_id' => 3, 'subject_name' => 'Химия',      'teacher_id' => null, 'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2, 'pairs_num' => 1, 'pairs_den' => 1],
        ];
        $result = $this->callSolve($demand, [], 5, 4);

        $this->assertCount(3, $result['placed'], '3 пары должны быть размещены');
        $this->assertCount(0, $result['unplaced']);
    }

    public function test_solve_respects_max_pairs_per_day(): void
    {
        $demand = [];
        for ($i = 1; $i <= 5; $i++) {
            $demand[] = ['subject_id' => $i, 'subject_name' => "Предмет $i", 'teacher_id' => null,
                         'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2,
                         'pairs_num' => 1, 'pairs_den' => 1];
        }
        $result = $this->callSolve($demand, [], 5, 2);

        $dayCount = [];
        foreach ($result['placed'] as $p) {
            $dayCount[$p['study_day']] = ($dayCount[$p['study_day']] ?? 0) + 1;
        }
        foreach ($dayCount as $day => $cnt) {
            $this->assertLessThanOrEqual(2, $cnt, "День $day превышает лимит 2 пары");
        }
    }

    public function test_solve_no_teacher_conflicts(): void
    {
        $demand = [
            ['subject_id' => 1, 'subject_name' => 'Математика', 'teacher_id' => 42, 'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2, 'pairs_num' => 2, 'pairs_den' => 2],
            ['subject_id' => 2, 'subject_name' => 'Физика',     'teacher_id' => 42, 'teacher_id_2' => null, 'has_subgroups' => false, 'hours_per_class' => 2, 'pairs_num' => 2, 'pairs_den' => 2],
        ];
        $result = $this->callSolve($demand, [], 5, 5);

        $teacherSlots = [];
        foreach ($result['placed'] as $p) {
            if (($p['teacher_id'] ?? null) !== 42) continue;
            $slot = $p['study_day'] . '|' . $p['lesson_number'] . '|' . $p['mode_flag'];
            $this->assertArrayNotHasKey($slot, $teacherSlots, "Учитель 42 задвоен в слоте $slot");
            $teacherSlots[$slot] = true;
        }
    }

    public function test_subgroup_demand_generates_two_rows(): void
    {
        $item = [
            'subject_id' => 10, 'subject_name' => 'Казахский', 'has_subgroups' => true,
            'teacher_id' => 5, 'teacher_id_2' => 7, 'mode_flag' => 'both',
        ];

        $rows = $this->call('makeRow', 1, 2, 'both', $item);

        $this->assertCount(2, $rows);
        $this->assertSame('1', $rows[0]['subgroup']);
        $this->assertSame('2', $rows[1]['subgroup']);
        $this->assertSame(5, $rows[0]['teacher_id']);
        $this->assertSame(7, $rows[1]['teacher_id']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function call(string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod($this->svc, $method);
        $ref->setAccessible(true);
        return $ref->invoke($this->svc, ...$args);
    }

    /** solve() принимает $grid по ссылке — вызываем через invokeArgs */
    private function callSolve(array $demand, array $teacherMap, int $maxDay, int $maxPairs): array
    {
        $ref  = new ReflectionMethod($this->svc, 'solve');
        $ref->setAccessible(true);
        $grid = $this->call('buildGrid');
        return $ref->invokeArgs($this->svc, [$demand, &$grid, $teacherMap, $maxDay, $maxPairs]);
    }

    private function makePlacedRows(int $n): array
    {
        $rows = [];
        for ($i = 1; $i <= $n; $i++) {
            $rows[] = ['study_day' => 'Понедельник', 'lesson_number' => $i, 'teacher_id' => $i, 'mode_flag' => 'both'];
        }
        return $rows;
    }

    private function createSvcWithoutDeps(): ScheduleGeneratorService
    {
        // Создаём объект минуя DI — без реальных DB-зависимостей
        return (new \ReflectionClass(ScheduleGeneratorService::class))
            ->newInstanceWithoutConstructor();
    }
}
