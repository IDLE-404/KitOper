<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяем что API-эндпоинты правильно валидируют данные
 * и не допускают конфликтных ситуаций.
 */
class ScheduleValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = User::factory()->create(['role' => User::ROLE_DISPATCHER]);
    }

    // -------------------------------------------------------------------------
    // Генератор расписания — валидация входных данных
    // -------------------------------------------------------------------------

    public function test_generator_requires_group_id(): void
    {
        $this->actingAs($this->dispatcher)
            ->postJson('/schedule/generate', [
                'course'   => 1,
                'semester' => 2,
                'template_week' => '2026-09-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['group_id']);
    }

    public function test_generator_requires_valid_semester(): void
    {
        $this->actingAs($this->dispatcher)
            ->postJson('/schedule/generate', [
                'group_id'      => 1,
                'course'        => 1,
                'semester'      => 5, // невалидный
                'template_week' => '2026-09-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['semester']);
    }

    public function test_generator_requires_template_week(): void
    {
        $this->actingAs($this->dispatcher)
            ->postJson('/schedule/generate', [
                'group_id' => 1,
                'course'   => 1,
                'semester' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['template_week']);
    }

    public function test_generator_rejects_invalid_course(): void
    {
        $this->actingAs($this->dispatcher)
            ->postJson('/schedule/generate', [
                'group_id'      => 1,
                'course'        => 9,   // нет такого курса
                'semester'      => 1,
                'template_week' => '2026-09-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course']);
    }

    // -------------------------------------------------------------------------
    // Управление пользователями
    // -------------------------------------------------------------------------

    public function test_update_role_requires_valid_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($this->dispatcher)
            ->putJson("/users/{$user->id}/role", ['role' => 'superadmin'])
            ->assertStatus(422);
    }

    public function test_can_update_user_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($this->dispatcher)
            ->put("/users/{$user->id}/role", ['role' => User::ROLE_DISPATCHER])
            ->assertRedirect();

        $this->assertSame(User::ROLE_DISPATCHER, $user->fresh()->role);
    }

    public function test_cannot_delete_last_dispatcher(): void
    {
        // dispatcher — единственный диспетчер, удалять нельзя
        $this->actingAs($this->dispatcher)
            ->delete("/users/{$this->dispatcher->id}")
            ->assertRedirect(); // редирект с ошибкой

        $this->assertDatabaseHas('users', ['id' => $this->dispatcher->id]);
    }

    public function test_can_delete_non_dispatcher_user(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($this->dispatcher)
            ->delete("/users/{$student->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_cannot_delete_self(): void
    {
        $this->actingAs($this->dispatcher)
            ->delete("/users/{$this->dispatcher->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->dispatcher->id]);
    }

    // -------------------------------------------------------------------------
    // Журнал изменений
    // -------------------------------------------------------------------------

    public function test_audit_log_clear_requires_dispatcher(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->post('/audit-logs/clear', ['days' => 30])
            ->assertStatus(403);
    }

    public function test_audit_log_clear_accepts_valid_days(): void
    {
        $this->actingAs($this->dispatcher)
            ->post('/audit-logs/clear', ['days' => 30])
            ->assertRedirect('/audit-logs');
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('correct')]);

        // Неверный пароль → редирект обратно (может быть / или /login)
        $this->post('/login', ['email' => 'test@example.com', 'password' => 'wrong'])
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_logout_redirects_to_login(): void
    {
        $this->actingAs($this->dispatcher)
            ->post('/logout')
            ->assertRedirect('/login');
    }
}
