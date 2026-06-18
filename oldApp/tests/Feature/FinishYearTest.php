<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Проверяем логику завершения учебного года:
 *   - группы переводятся из таблицы текущего курса в таблицу следующего;
 *   - имена групп обновляются (ПО-233 → ПО-333);
 *   - выпускники (последний курс) удаляются;
 *   - учителя и дисциплины остаются нетронутыми.
 */
class FinishYearTest extends TestCase
{
    use RefreshDatabase;

    private User $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = User::factory()->create(['role' => User::ROLE_DISPATCHER]);
    }

    // -------------------------------------------------------------------------
    // Доступ
    // -------------------------------------------------------------------------

    public function test_guest_cannot_call_finish_year_global(): void
    {
        $this->post(route('groups.finish_year_global'))
            ->assertRedirect('/login');
    }

    public function test_teacher_cannot_call_finish_year_global(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->post(route('groups.finish_year_global'))
            ->assertStatus(403);
    }

    public function test_dispatcher_can_call_finish_year_global(): void
    {
        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'))
            ->assertRedirect(route('groups.index'));
    }

    // -------------------------------------------------------------------------
    // Перевод с 1-го на 2-й курс
    // -------------------------------------------------------------------------

    public function test_first_course_group_moves_to_second_course_table(): void
    {
        DB::table('first_course_group')->insert([
            'group_name'   => 'ПО-133',
            'group_number' => 133,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseMissing('first_course_group', ['group_name' => 'ПО-133']);
        $this->assertDatabaseHas('second_course_group', [
            'group_name'   => 'ПО-233',
            'group_number' => 233,
        ]);
    }

    // -------------------------------------------------------------------------
    // Перевод со 2-го на 3-й курс
    // -------------------------------------------------------------------------

    public function test_second_course_group_moves_to_third_course_table(): void
    {
        DB::table('second_course_group')->insert([
            'group_name'   => 'ПО-233',
            'group_number' => 233,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseMissing('second_course_group', ['group_name' => 'ПО-233']);
        $this->assertDatabaseHas('third_course_group', [
            'group_name'   => 'ПО-333',
            'group_number' => 333,
        ]);
    }

    // -------------------------------------------------------------------------
    // Выпуск: 3-й курс (не-ТЭ)
    // -------------------------------------------------------------------------

    public function test_third_course_non_te_group_is_graduated(): void
    {
        DB::table('third_course_group')->insert([
            'group_name'   => 'ПО-333',
            'group_number' => 333,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseMissing('third_course_group', ['group_name' => 'ПО-333']);
        $this->assertDatabaseMissing('fourth_course_group', ['group_name' => 'ПО-433']);
    }

    // -------------------------------------------------------------------------
    // ТЭ: 3-й курс переходит на 4-й (maxYear = 4)
    // -------------------------------------------------------------------------

    public function test_third_course_te_group_moves_to_fourth_course_table(): void
    {
        DB::table('third_course_group')->insert([
            'group_name'   => 'ТЭ-333',
            'group_number' => 333,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseMissing('third_course_group', ['group_name' => 'ТЭ-333']);
        $this->assertDatabaseHas('fourth_course_group', [
            'group_name'   => 'ТЭ-433',
            'group_number' => 433,
        ]);
    }

    // -------------------------------------------------------------------------
    // Выпуск: 4-й курс (все, включая ТЭ)
    // -------------------------------------------------------------------------

    public function test_fourth_course_groups_are_always_graduated(): void
    {
        DB::table('fourth_course_group')->insert([
            ['group_name' => 'ТЭ-433', 'group_number' => 433, 'created_at' => now(), 'updated_at' => now()],
            ['group_name' => 'ПО-433', 'group_number' => 433, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseMissing('fourth_course_group', ['group_name' => 'ТЭ-433']);
        $this->assertDatabaseMissing('fourth_course_group', ['group_name' => 'ПО-433']);
    }

    // -------------------------------------------------------------------------
    // Все курсы одновременно
    // -------------------------------------------------------------------------

    public function test_all_courses_transition_in_single_request(): void
    {
        DB::table('first_course_group')->insert(['group_name' => 'М-133', 'group_number' => 133, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('second_course_group')->insert(['group_name' => 'М-233', 'group_number' => 233, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('third_course_group')->insert(['group_name' => 'М-333', 'group_number' => 333, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('fourth_course_group')->insert(['group_name' => 'ТЭ-433', 'group_number' => 433, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        // Первый курс → второй
        $this->assertDatabaseMissing('first_course_group', ['group_name' => 'М-133']);
        $this->assertDatabaseHas('second_course_group', ['group_name' => 'М-233', 'group_number' => 233]);

        // Второй курс → третий
        $this->assertDatabaseHas('third_course_group', ['group_name' => 'М-333', 'group_number' => 333]);

        // Третий курс (не-ТЭ М) → выпуск
        $this->assertDatabaseMissing('fourth_course_group', ['group_name' => 'М-433']);

        // Четвёртый курс ТЭ → выпуск
        $this->assertDatabaseMissing('fourth_course_group', ['group_name' => 'ТЭ-433']);
    }

    // -------------------------------------------------------------------------
    // Атрибуты группы сохраняются
    // -------------------------------------------------------------------------

    public function test_group_attributes_are_preserved_on_promotion(): void
    {
        DB::table('first_course_group')->insert([
            'group_name'    => 'СИБ-133',
            'group_number'  => 133,
            'group_type'    => 'kz',
            'has_subgroups' => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $row = DB::table('second_course_group')->where('group_name', 'СИБ-233')->first();
        $this->assertNotNull($row, 'Группа СИБ-233 должна появиться в second_course_group');
        $this->assertSame('kz', $row->group_type);
        $this->assertEquals(1, $row->has_subgroups);
    }

    // -------------------------------------------------------------------------
    // Учителя и дисциплины не затрагиваются
    // -------------------------------------------------------------------------

    public function test_teachers_are_not_affected_by_finish_year(): void
    {
        DB::table('teachers')->insert([
            'teacher_name' => 'Иванов Иван Иванович',
            'initials'     => 'Иванов И.И.',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseHas('teachers', ['teacher_name' => 'Иванов Иван Иванович']);
    }

    public function test_subjects_are_not_affected_by_finish_year(): void
    {
        DB::table('second_course_subjects')->insert([
            'subject_name' => 'Математика',
            'name_ru'      => 'Математика',
            'name_kz'      => 'Математика (кз)',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Добавим группу, чтобы было что переводить
        DB::table('second_course_group')->insert([
            'group_name'   => 'ПО-233',
            'group_number' => 233,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        $this->assertDatabaseHas('second_course_subjects', ['subject_name' => 'Математика']);
    }

    // -------------------------------------------------------------------------
    // Группы с номером < 100 не обрабатываются
    // -------------------------------------------------------------------------

    public function test_groups_without_course_prefix_in_number_are_skipped(): void
    {
        DB::table('first_course_group')->insert([
            'group_name'   => 'Доп-33',
            'group_number' => 33,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'));

        // Группа должна остаться нетронутой
        $this->assertDatabaseHas('first_course_group', ['group_name' => 'Доп-33']);
    }

    // -------------------------------------------------------------------------
    // Flash-сообщение об успехе
    // -------------------------------------------------------------------------

    public function test_finish_year_returns_success_flash(): void
    {
        DB::table('second_course_group')->insert([
            'group_name'   => 'БКЕ-233',
            'group_number' => 233,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->actingAs($this->dispatcher)
            ->post(route('groups.finish_year_global'))
            ->assertSessionHas('success');
    }
}
