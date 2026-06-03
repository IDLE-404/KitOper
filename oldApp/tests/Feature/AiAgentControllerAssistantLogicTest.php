<?php

namespace Tests\Feature;

use App\Http\Controllers\AiAgentController;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AiAgentControllerAssistantLogicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $session = new Store('testing', new ArraySessionHandler(120));
        $session->start();

        $request = Request::create('/', 'GET');
        $request->setLaravelSession($session);

        $this->app->instance('request', $request);
    }

    private function invokePrivate(object $object, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionMethod($object, $method);
        $ref->setAccessible(true);
        return $ref->invoke($object, ...$args);
    }

    public function test_extracts_nested_json_action(): void
    {
        $controller = app(AiAgentController::class);
        $raw = '{"action":"select","table":"first_course_subjects","where":{},"data":["id","subject_name"],"limit":50}';

        $action = $this->invokePrivate($controller, 'extractActionFromRaw', $raw);

        $this->assertIsArray($action);
        $this->assertSame('select', $action['action']);
        $this->assertSame('first_course_subjects', $action['table']);
        $this->assertSame([], $action['where']);
    }

    public function test_builds_subjects_scenario_for_first_course(): void
    {
        $controller = app(AiAgentController::class);
        $message = 'Покажи дисциплины 1 курса';
        $normalized = $this->invokePrivate($controller, 'normalizeUserMessage', $message);

        $action = $this->invokePrivate($controller, 'buildScenarioAction', $normalized, $message);

        $this->assertIsArray($action);
        $this->assertSame('select', $action['action']);
        $this->assertSame('first_course_subjects', $action['table']);
        $this->assertSame(['id', 'subject_name'], $action['data']);
    }

    public function test_subjects_scenario_can_be_disabled_in_config(): void
    {
        Config::set('ai_agent.scenarios.subjects_list.enabled', false);

        $controller = app(AiAgentController::class);
        $message = 'Покажи дисциплины 1 курса';
        $normalized = $this->invokePrivate($controller, 'normalizeUserMessage', $message);

        $action = $this->invokePrivate($controller, 'buildScenarioAction', $normalized, $message);

        $this->assertNull($action);
    }

    public function test_asks_for_course_when_subjects_without_course(): void
    {
        $controller = app(AiAgentController::class);
        $message = 'покажи дисциплины';
        $normalized = $this->invokePrivate($controller, 'normalizeUserMessage', $message);

        $question = $this->invokePrivate($controller, 'buildClarificationQuestion', $normalized);

        $this->assertIsString($question);
        $this->assertStringContainsString('для какого курса', mb_strtolower($question));
    }

    public function test_delete_action_requires_confirmation_message(): void
    {
        $controller = app(AiAgentController::class);
        $action = [
            'action' => 'delete',
            'table'  => 'teachers',
            'where'  => ['id' => 15],
            'data'   => [],
            'limit'  => 1,
        ];

        $reply = $this->invokePrivate($controller, 'handleActionWithConfirmation', $action, '');

        $this->assertIsString($reply);
        $this->assertStringContainsString('подтверждаю', mb_strtolower($reply));
        $this->assertStringContainsString('отмена', mb_strtolower($reply));
    }

    public function test_capabilities_request_returns_detailed_reply(): void
    {
        $controller = app(AiAgentController::class);

        $reply = $this->invokePrivate($controller, 'buildCapabilitiesReply', 'что ты умеешь');

        $this->assertIsString($reply);
        $this->assertStringContainsString('Что я умею', $reply);
        $this->assertStringContainsString('Примеры команд', $reply);
    }

    public function test_capabilities_request_can_be_short_on_demand(): void
    {
        $controller = app(AiAgentController::class);

        $reply = $this->invokePrivate($controller, 'buildCapabilitiesReply', 'что ты умеешь кратко');

        $this->assertIsString($reply);
        $this->assertStringNotContainsString('Примеры команд', $reply);
    }

    public function test_detects_course_from_single_digit_reply(): void
    {
        $controller = app(AiAgentController::class);

        $course = $this->invokePrivate($controller, 'detectCourseFromText', '1');

        $this->assertSame(1, $course);
    }

    public function test_pending_clarification_accepts_course_number(): void
    {
        $controller = app(AiAgentController::class);

        request()->session()->put('ai_agent_pending_clarification', [
            'payload' => ['type' => 'course', 'scenario' => 'missing_scenario'],
            'ts' => time(),
        ]);

        $reply = $this->invokePrivate($controller, 'handlePendingClarificationFlow', '1');

        $this->assertNull($reply);
        $this->assertNull(request()->session()->get('ai_agent_pending_clarification'));
        $this->assertSame(1, request()->session()->get('ai_agent_context.course'));
    }
}
