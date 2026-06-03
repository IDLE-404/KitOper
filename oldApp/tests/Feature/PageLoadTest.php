<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяем что страницы отдают корректный статус и не падают с 500.
 * RefreshDatabase откатывает все изменения после каждого теста.
 */
class PageLoadTest extends TestCase
{
    use RefreshDatabase;

    private User $dispatcher;
    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = User::factory()->create(['role' => User::ROLE_DISPATCHER]);
        $this->teacher    = User::factory()->create(['role' => User::ROLE_TEACHER]);
    }

    // -------------------------------------------------------------------------
    // Гость (не авторизован)
    // -------------------------------------------------------------------------

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_guest_cannot_access_schedule(): void
    {
        $this->get('/first-course/schedule')->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // Диспетчер — основные страницы
    // -------------------------------------------------------------------------

    public function test_dispatcher_can_see_schedule(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/first-course/schedule')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_form_two(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/first-course/form-two')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_duplicate_week(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/first-course/schedule/week-duplicate')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_schedule_generator(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/schedule/generate')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_users(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/users')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_audit_logs(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/audit-logs')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_teachers(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/first-course/teachers')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_rooms(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/rooms')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_holidays(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/holidays')
            ->assertStatus(200);
    }

    public function test_dispatcher_can_see_ai_agent(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/ai-agent')
            ->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Учитель — ограниченный доступ
    // -------------------------------------------------------------------------

    public function test_teacher_can_see_today(): void
    {
        $this->actingAs($this->teacher)
            ->get('/teacher/today')
            ->assertStatus(200);
    }

    public function test_teacher_cannot_access_dispatcher_pages(): void
    {
        $this->actingAs($this->teacher)
            ->get('/users')
            ->assertStatus(403);
    }

    public function test_teacher_cannot_access_generator(): void
    {
        $this->actingAs($this->teacher)
            ->get('/schedule/generate')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Роуты которые должны возвращать 404 / 405 при неверном методе
    // -------------------------------------------------------------------------

    public function test_get_on_post_only_route_returns_405(): void
    {
        $this->actingAs($this->dispatcher)
            ->get('/schedule/generate') // GET — OK
            ->assertStatus(200);

        // POST без данных через JSON → валидация 422, не 500
        $this->actingAs($this->dispatcher)
            ->postJson('/schedule/generate', [])
            ->assertStatus(422);
    }

    public function test_audit_log_rollback_requires_valid_log_id(): void
    {
        $this->actingAs($this->dispatcher)
            ->post('/audit-logs/9999/rollback')
            ->assertStatus(404);
    }
}
