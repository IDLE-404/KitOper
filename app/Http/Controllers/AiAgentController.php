<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\SchedulePlanningService;

class AiAgentController extends Controller
{
    private const SESSION_PENDING_ACTION = 'ai_agent_pending_action';
    private const SESSION_PENDING_CLARIFICATION = 'ai_agent_pending_clarification';
    private const SESSION_CONTEXT = 'ai_agent_context';
    private const PENDING_TTL_SECONDS = 600;

    private string $ollamaHost;
    private string $ollamaModel;
    private SchedulePlanningService $planningService;

    public function __construct()
    {
        $this->ollamaHost = env('OLLAMA_HOST', 'http://ollama:11434');
        $this->ollamaModel = env('OLLAMA_MODEL', 'qwen2.5:3b');
        $this->planningService = new SchedulePlanningService();
    }

    public function index()
    {
        $ollamaStatus = $this->checkOllamaStatus();
        $ollamaModels = $ollamaStatus ? $this->getOllamaModels() : [];

        return view('ai_agent.index', compact('ollamaStatus', 'ollamaModels'));
    }

    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:50000']);

        $message = trim($request->input('message'));
        $history = $request->input('history', []);

        if (!$this->checkOllamaStatus()) {
            return response()->json([
                'success' => false,
                'error'   => 'Ollama недоступна. Проверьте что контейнер it-ollama запущен.',
            ], 503);
        }

        try {
            $systemPrompt = $this->buildSystemPromptWithContext();
            $result       = $this->processMessage($message, $history, $systemPrompt);

            return response()->json(['success' => true, 'reply' => $result]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error'   => 'Не удалось обработать запрос. Попробуйте еще раз.',
            ], 500);
        }
    }

    private function processMessage(string $message, array $history, string $systemPrompt): string
    {
        file_put_contents('/tmp/ai_debug.log', date('Y-m-d H:i:s') . " processMessage: $message\n", FILE_APPEND);
        
        $normalizedMessage = $this->normalizeUserMessage($message);
        $this->rememberConversationContext($normalizedMessage);

        // Только подтверждение ожидающих действий (update/delete) обрабатывается отдельно
        $pendingReply = $this->handlePendingActionFlow($normalizedMessage);
        if ($pendingReply !== null) {
            return $pendingReply;
        }

        // Всё остальное — через LLM
        $context = '';
        foreach (array_slice($history, -6) as $msg) {
            $role     = $msg['role'] === 'user' ? 'Пользователь' : 'Ассистент';
            $context .= "{$role}: {$msg['content']}\n";
        }

        $workContext = $this->formatContextForPrompt($this->getConversationContext());
        $fullPrompt  = $systemPrompt . "\n\n";
        $fullPrompt .= "Текущий контекст: {$workContext}\n";
        if ($context) {
            $fullPrompt .= "История диалога:\n{$context}\n";
        }
        $fullPrompt .= "Пользователь: {$message}\nАссистент:";

        // Не кэшируем сообщения - всегда делаем свежий запрос к БД
        $hasTableContext = str_contains($message, '[ТАБЛИЦА]');

        $raw = $this->callOllama($fullPrompt);

        file_put_contents('/tmp/ai_debug.log', date('Y-m-d H:i:s') . " RAW response: " . substr($raw, 0, 1000) . "\n", FILE_APPEND);

        $action = $this->extractActionFromRaw($raw);
        if ($action !== null) {
            $reply = $this->handleActionWithConfirmation($action, $raw);
            return $reply;
        }

        // Fallback: try to parse natural language if AI didn't produce JSON
        file_put_contents('/tmp/ai_debug.log', date('Y-m-d H:i:s') . " Testing fallback for: $message\n", FILE_APPEND);
        $fallbackAction = $this->tryParseNaturalLanguageAction($message);
        file_put_contents('/tmp/ai_debug.log', date('Y-m-d H:i:s') . " Fallback result: " . json_encode($fallbackAction) . "\n", FILE_APPEND);
        if ($fallbackAction !== null) {
            $reply = $this->handleActionWithConfirmation($fallbackAction, $raw);
            return $reply;
        }

        $reply = $this->formatFreeTextReply($this->sanitizeAssistantText($raw));
        return $reply;
    }

    private function extractActionFromRaw(string $raw): ?array
    {
        $candidates = [];
        $trimmed = trim($raw);
        if ($trimmed !== '') {
            $candidates[] = $trimmed;
        }

        if (preg_match_all('/```json\s*([\s\S]*?)```/iu', $raw, $matches)) {
            foreach ($matches[1] as $jsonBlock) {
                $candidate = trim((string) $jsonBlock);
                if ($candidate !== '') {
                    $candidates[] = $candidate;
                }
            }
        }

        $inline = $this->extractFirstJsonObject($raw);
        if ($inline !== null) {
            $candidates[] = $inline;
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                continue;
            }
            if (!isset($decoded['action']) || !is_string($decoded['action'])) {
                continue;
            }
            return $decoded;
        }

        return null;
    }

    private function extractFirstJsonObject(string $text): ?string
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($text);

        for ($i = $start; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }
                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
                continue;
            }

            if ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    private function handleActionWithConfirmation(array $action, string $rawResponse = ''): string
    {
        $normalizedAction = $this->normalizeActionPayload($action);
        $act = $normalizedAction['action'];

        if (in_array($act, ['update', 'delete'], true) && $this->isUnsafeMutation($normalizedAction['where'])) {
            return "Не могу выполнить — нужно точнее указать запись (например, полное имя или ID).";
        }

        // Деструктивные операции требуют подтверждения пользователя
        if (in_array($act, ['update', 'delete'], true)) {
            try {
                request()->session()->put(self::SESSION_PENDING_ACTION, [
                    'action' => $normalizedAction,
                    'ts'     => time(),
                ]);
            } catch (\Throwable) {
                // В тестах сессия может быть недоступна — продолжаем без сохранения
            }
            return $this->buildConfirmationMessage($normalizedAction);
        }

        return $this->executeDbAction($normalizedAction, $rawResponse);
    }

    private function normalizeActionPayload(array $action): array
    {
        $normalized = [
            'action' => strtolower((string) ($action['action'] ?? '')),
            'table'  => (string) ($action['table'] ?? ''),
            'where'  => is_array($action['where'] ?? null) ? $action['where'] : [],
            'data'   => is_array($action['data'] ?? null) ? $action['data'] : [],
            'limit'  => (int) ($action['limit'] ?? 200),
        ];

        if ($normalized['limit'] <= 0) {
            $normalized['limit'] = 200;
        }

        return $normalized;
    }

    private function handlePendingActionFlow(string $normalizedMessage): ?string
    {
        // Подтверждение отключено — сразу выполняем действия
        request()->session()->forget(self::SESSION_PENDING_ACTION);
        return null;
    }

    private function executeFileImport(array $action): string
    {
        $fileType = $action['file_type'] ?? '';
        $mappings = $action['mappings'] ?? [];
        
        return "Функция импорта файла типа '{$fileType}' с сопоставлением " . json_encode($mappings, JSON_UNESCAPED_UNICODE) . " временно недоступна. Для импорта используйте кнопку загрузки файла в интерфейсе.";
    }

    private function buildConfirmationMessage(array $action): string
    {
        $sectionName = $this->tableDisplayName((string) ($action['table'] ?? ''));
        $act = (string) ($action['action'] ?? '');
        $whereText = $this->formatWhereForHuman((array) ($action['where'] ?? []));
        $dataText = $this->formatDataForHuman((array) ($action['data'] ?? []));

        if ($act === 'update') {
            return "Понял, хочу изменить {$whereText} в разделе «{$sectionName}» — {$dataText}. Всё верно? Напиши **подтверждаю** или **отмена**.";
        }

        return "Хочу удалить {$whereText} из раздела «{$sectionName}». Это необратимо — точно удаляем? Напиши **подтверждаю** или **отмена**.";
    }

    private function formatWhereForHuman(array $where): string
    {
        if (empty($where)) {
            return 'неуточнённую запись';
        }

        $parts = [];
        foreach ($where as $col => $value) {
            $parts[] = $this->formatDisplayValue((string) $col, $value);
        }

        return implode(', ', $parts);
    }

    private function formatDataForHuman(array $data): string
    {
        if (empty($data)) {
            return 'без изменений';
        }

        $parts = [];
        foreach ($data as $col => $value) {
            $parts[] = $this->columnDisplayName((string) $col) . ' → ' . $this->formatDisplayValue((string) $col, $value);
        }

        return implode(', ', $parts);
    }

    private function isConfirmIntent(string $message): bool
    {
        return $this->containsAny($message, ['подтверждаю', 'подтвердить', 'выполняй', 'выполнить', 'да, подтверждаю']);
    }

    private function isCancelIntent(string $message): bool
    {
        return $this->containsAny($message, ['отмена', 'отменить', 'не надо', 'не выполняй', 'стоп']);
    }

    private function isUnsafeMutation(array $where): bool
    {
        if (empty($where)) {
            return true;
        }

        // Allow delete with any unique identifier or multiple fields
        if (array_key_exists('id', $where)) {
            return false;
        }

        // Allow if at least 2 fields (more specific delete)
        if (count($where) >= 2) {
            return false;
        }

        // Allow single field if it's a specific identifier like group_id, teacher_id, etc.
        $safeSingleFields = ['group_id', 'teacher_id', 'subject_id', 'room_id', 'course', 'year', 'month'];
        if (count($where) === 1) {
            $key = array_key_first($where);
            if (in_array($key, $safeSingleFields, true)) {
                return false;
            }
        }

        // Allow single text field that can be looked up (for update/delete by name)
        if (count($where) === 1) {
            $key = array_key_first($where);
            $value = $where[$key];
            if (is_string($value) && strlen($value) > 2) {
                return false; // Allow - will look up by name
            }
        }

        return true;
    }

    private function buildClarificationQuestion(string $message): ?string
    {
        $context = $this->getConversationContext();
        $course = $this->detectCourseFromText($message) ?? ($context['course'] ?? null);

        if ($this->containsAny($message, ['дисциплин', 'предмет']) && !$course) {
            $this->setPendingClarification(['type' => 'course', 'scenario' => 'subjects_list']);
            return "Уточните, пожалуйста, для какого курса показать дисциплины: 1, 2, 3 или 4?";
        }

        if ($this->containsAny($message, ['групп']) && $this->containsAny($message, ['покажи', 'список', 'все']) && !$course) {
            $this->setPendingClarification(['type' => 'course', 'scenario' => 'groups_list']);
            return "Уточните курс, чтобы показать группы: 1, 2, 3 или 4?";
        }

        if ($this->containsAny($message, ['нагрузк', 'час']) && !$this->detectMonthFromText($message) && !isset($context['month'])) {
            return "Уточните месяц, за который нужна нагрузка.";
        }

        if ($this->containsAny($message, ['удали', 'переимен', 'измени']) && !$this->containsId($message)) {
            return "Уточните ID записи, которую нужно изменить или удалить.";
        }

        return null;
    }

    private function setPendingClarification(array $payload): void
    {
        request()->session()->put(self::SESSION_PENDING_CLARIFICATION, [
            'payload' => $payload,
            'ts'      => time(),
        ]);
    }

    private function clearPendingClarification(): void
    {
        request()->session()->forget(self::SESSION_PENDING_CLARIFICATION);
    }

    private function handlePendingClarificationFlow(string $message): ?string
    {
        $session = request()->session();
        $pending = $session->get(self::SESSION_PENDING_CLARIFICATION);
        if (!is_array($pending) || !isset($pending['payload']) || !is_array($pending['payload'])) {
            return null;
        }

        $ts = (int) ($pending['ts'] ?? 0);
        if ($ts > 0 && (time() - $ts) > self::PENDING_TTL_SECONDS) {
            $this->clearPendingClarification();
            return null;
        }

        if ($this->isCancelIntent($message)) {
            $this->clearPendingClarification();
            return "Хорошо, уточнение отменено.";
        }

        $payload = $pending['payload'];
        $type = (string) ($payload['type'] ?? '');
        if ($type !== 'course') {
            $this->clearPendingClarification();
            return null;
        }

        $course = $this->detectCourseFromText($message);
        if ($course === null) {
            $trimmed = trim($message);
            if (mb_strlen($trimmed) <= 3) {
                return "Нужен номер курса: 1, 2, 3 или 4.";
            }

            $this->clearPendingClarification();
            return null;
        }

        $context = $this->getConversationContext();
        $context['course'] = $course;
        $session->put(self::SESSION_CONTEXT, $context);

        $scenarioKey = (string) ($payload['scenario'] ?? '');
        $this->clearPendingClarification();

        $action = $this->buildScenarioActionFromConfigKey($scenarioKey, $course);
        if ($action === null) {
            return null;
        }

        return $this->handleActionWithConfirmation($action);
    }

    private function buildScenarioAction(string $normalizedMessage, string $originalMessage): ?array
    {
        $context = $this->getConversationContext();
        $course = $this->detectCourseFromText($normalizedMessage) ?? ($context['course'] ?? null);

        $configured = $this->buildConfiguredScenarioAction($normalizedMessage, $course);
        if ($configured !== null) {
            return $configured;
        }

        if (preg_match('/(?:найди|поищи|поиск)\s+(?:преподавател[яь]\s+)?([а-яa-z\-]{3,}(?:\s+[а-яa-z\-]{2,}){0,2})/iu', $normalizedMessage, $m)) {
            $query = trim((string) ($m[1] ?? ''));
            if ($query !== '') {
                return [
                    'action' => 'select',
                    'table'  => 'teachers',
                    'where'  => ['teacher_name' => '%' . $query . '%'],
                    'data'   => ['id', 'teacher_name', 'initials'],
                    'limit'  => 200,
                ];
            }
        }

        if (preg_match('/(?:удали|убери)\s+.*преподавател[яь].*?(?:id\s*)?(\d+)/iu', $normalizedMessage, $m)) {
            $id = (int) ($m[1] ?? 0);
            if ($id > 0) {
                return [
                    'action' => 'delete',
                    'table'  => 'teachers',
                    'where'  => ['id' => $id],
                    'data'   => [],
                    'limit'  => 1,
                ];
            }
        }

        if (preg_match('/(?:переименуй|измени)\s+.*преподавател[яь].*?(?:id\s*)?(\d+).*?(?:в|на)\s+(.+)$/iu', $originalMessage, $m)) {
            $id = (int) ($m[1] ?? 0);
            $newName = trim((string) ($m[2] ?? ''));
            if ($id > 0 && $newName !== '') {
                return [
                    'action' => 'update',
                    'table'  => 'teachers',
                    'where'  => ['id' => $id],
                    'data'   => ['teacher_name' => $newName],
                    'limit'  => 1,
                ];
            }
        }

        return null;
    }

    private function buildConfiguredScenarioAction(string $message, ?int $course): ?array
    {
        $scenarios = $this->aiConfig('scenarios', []);
        if (!is_array($scenarios)) {
            return null;
        }

        foreach ($scenarios as $scenarioKey => $scenario) {
            if (!is_array($scenario)) {
                continue;
            }

            if (($scenario['enabled'] ?? true) === false) {
                continue;
            }

            $intentAll = is_array($scenario['intent_all'] ?? null) ? $scenario['intent_all'] : [];
            $intentAny = is_array($scenario['intent_any'] ?? null) ? $scenario['intent_any'] : [];
            $requiresAny = is_array($scenario['requires_any'] ?? null) ? $scenario['requires_any'] : [];

            if (!empty($intentAll) && !$this->containsAll($message, $intentAll)) {
                continue;
            }
            if (!empty($intentAny) && !$this->containsAny($message, $intentAny)) {
                continue;
            }
            if (!empty($requiresAny) && !$this->containsAny($message, $requiresAny)) {
                continue;
            }

            $requiresCourse = (bool) ($scenario['requires_course'] ?? false);
            if ($requiresCourse && !$course) {
                continue;
            }

            $action = $this->buildScenarioActionFromConfigKey((string) $scenarioKey, $course);
            if ($action !== null) {
                return $action;
            }
        }

        return null;
    }

    private function buildScenarioActionFromConfigKey(string $scenarioKey, ?int $course): ?array
    {
        $scenario = $this->aiConfig("scenarios.{$scenarioKey}", []);
        if (!is_array($scenario) || (($scenario['enabled'] ?? true) === false)) {
            return null;
        }

        $actionConfig = is_array($scenario['action'] ?? null) ? $scenario['action'] : [];
        $table = $actionConfig['table'] ?? null;
        if (!is_string($table) || $table === '') {
            $tableByCourse = is_array($actionConfig['table_by_course'] ?? null) ? $actionConfig['table_by_course'] : [];
            if ($course && isset($tableByCourse[$course]) && is_string($tableByCourse[$course])) {
                $table = $tableByCourse[$course];
            }
        }

        if (!is_string($table) || $table === '') {
            return null;
        }

        $action = (string) ($actionConfig['action'] ?? 'select');
        $columns = is_array($actionConfig['data'] ?? null) ? $actionConfig['data'] : [];
        $limit = (int) ($actionConfig['limit'] ?? 200);
        if ($limit <= 0) {
            $limit = 200;
        }

        return [
            'action' => $action,
            'table'  => $table,
            'where'  => [],
            'data'   => $columns,
            'limit'  => $limit,
        ];
    }

    private function buildCapabilitiesReply(string $message): ?string
    {
        $intentPhrases = $this->aiConfig('capabilities.intent_phrases', ['что ты умеешь', 'что умеешь', 'твои возможности', 'что можешь']);
        if (!is_array($intentPhrases) || empty($intentPhrases)) {
            $intentPhrases = ['что ты умеешь', 'что умеешь', 'твои возможности', 'что можешь'];
        }

        $isCapabilitiesIntent = $this->containsAny($message, $intentPhrases);
        if (!$isCapabilitiesIntent) {
            return null;
        }

        $wantsShort = $this->containsAny($message, ['кратко', 'коротко', 'в двух словах']);
        if ($wantsShort) {
            return (string) $this->aiConfig(
                'capabilities.short_reply',
                'Я могу показывать данные по преподавателям, группам, дисциплинам, аудиториям, праздникам и отсутствиям, а также изменять записи по вашей команде.'
            );
        }

        $intro = (string) $this->aiConfig('capabilities.detailed_intro', 'Я помогаю диспетчеру работать с данными учебной части простыми командами.');
        $items = $this->aiConfig('capabilities.detailed_items', []);
        $examples = $this->aiConfig('capabilities.examples', []);
        $outro = (string) $this->aiConfig('capabilities.detailed_outro', 'Если хотите, могу сразу начать с любой команды из примеров.');

        $lines = [];
        if ($intro !== '') {
            $lines[] = $intro;
        }

        if (is_array($items) && !empty($items)) {
            $lines[] = 'Что я умею:';
            foreach (array_values($items) as $i => $item) {
                if (!is_string($item) || trim($item) === '') {
                    continue;
                }
                $lines[] = ($i + 1) . '. ' . trim($item);
            }
        }

        if (is_array($examples) && !empty($examples)) {
            $lines[] = '';
            $lines[] = 'Примеры команд:';
            foreach ($examples as $example) {
                if (!is_string($example) || trim($example) === '') {
                    continue;
                }
                $lines[] = '• «' . trim($example) . '»';
            }
        }

        if ($outro !== '') {
            $lines[] = '';
            $lines[] = $outro;
        }

        return trim(implode("\n", $lines));
    }

    private function formatContextForPrompt(array $context): string
    {
        $parts = [];
        if (isset($context['course'])) {
            $parts[] = 'курс: ' . $context['course'];
        }
        if (isset($context['month'])) {
            $parts[] = 'месяц: ' . $this->formatDisplayValue('month', $context['month']);
        }
        if (isset($context['year'])) {
            $parts[] = 'год: ' . $context['year'];
        }

        if (!$parts) {
            return 'не задан';
        }

        return implode(', ', $parts);
    }

    private function rememberConversationContext(string $message): void
    {
        $session = request()->session();
        $context = $this->getConversationContext();

        $course = $this->detectCourseFromText($message);
        $month = $this->detectMonthFromText($message);
        $year = $this->detectYearFromText($message);

        if ($course !== null) {
            $context['course'] = $course;
        }
        if ($month !== null) {
            $context['month'] = $month;
        }
        if ($year !== null) {
            $context['year'] = $year;
        }

        $session->put(self::SESSION_CONTEXT, $context);
    }

    private function getConversationContext(): array
    {
        $ctx = request()->session()->get(self::SESSION_CONTEXT, []);
        return is_array($ctx) ? $ctx : [];
    }

    private function normalizeUserMessage(string $message): string
    {
        $text = mb_strtolower(trim($message));
        $text = str_replace('ё', 'е', $text);

        $replacements = [
            'деспетчер'   => 'диспетчер',
            'преокт'      => 'проект',
            'порабоатать' => 'поработать',
            'дисцыплин'   => 'дисциплин',
            'предметы'    => 'дисциплины',
            'кабинеты'    => 'аудитории',
            'препод'      => 'преподаватель',
        ];

        $text = strtr($text, $replacements);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    private function detectCourseFromText(string $text): ?int
    {
        if (preg_match('/^\s*([1-4])\s*$/u', $text, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/\b([1-4])\s*(?:-| )?(?:й|го)?\s*курс/u', $text, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/\b(перв|втор|трет|четвер)ого\s+курс/u', $text, $m)) {
            return match (true) {
                str_starts_with($m[1], 'перв') => 1,
                str_starts_with($m[1], 'втор') => 2,
                str_starts_with($m[1], 'трет') => 3,
                str_starts_with($m[1], 'четвер') => 4,
                default => null,
            };
        }

        return null;
    }

    private function detectMonthFromText(string $text): ?int
    {
        $monthMap = [
            'январ' => 1, 'феврал' => 2, 'март' => 3, 'апрел' => 4, 'май' => 5, 'июн' => 6,
            'июл' => 7, 'август' => 8, 'сентябр' => 9, 'октябр' => 10, 'ноябр' => 11, 'декабр' => 12,
        ];

        foreach ($monthMap as $needle => $month) {
            if (mb_strpos($text, $needle) !== false) {
                return $month;
            }
        }

        if (preg_match('/\b(1[0-2]|[1-9])\s*месяц/u', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function detectYearFromText(string $text): ?int
    {
        if (preg_match('/\b(20\d{2})\b/u', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function containsId(string $text): bool
    {
        return preg_match('/(?:\bid\b|№|номер)\s*\d{1,6}\b/iu', $text) === 1;
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (!is_string($needle) || $needle === '') {
                continue;
            }
            if (mb_strpos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function containsAll(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (!is_string($needle) || $needle === '') {
                continue;
            }
            if (mb_strpos($text, $needle) === false) {
                return false;
            }
        }

        return true;
    }

    private function aiConfig(string $key, mixed $default = null): mixed
    {
        return config('ai_agent.' . $key, $default);
    }

    private function formatFreeTextReply(string $text): string
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return "Что сделать: уточните задачу в свободной форме.";
        }

        return $trimmed;
    }

    private function executeDbAction(array $action, string $rawResponse): string
    {
        $act   = $action['action'] ?? '';
        $table = $action['table'] ?? '';
        $where = $action['where'] ?? [];
        $data  = $action['data'] ?? [];
        $limit = $action['limit'] ?? 200;

        // Проверка новых действий планирования
        if (in_array($act, ['check_conflicts', 'find_replacement', 'suggest_placement', 'week_stats', 'free_teachers', 'free_rooms', 'plan_schedule'], true)) {
            return $this->handlePlanningAction($action);
        }

        // Импорт из файла
        if ($act === 'import_file') {
            return $this->handleFileImport($action);
        }

        $allowedTables = [
            'teachers', 'first_course_group', 'second_course_group', 'third_course_group', 'fourth_course_group',
            'first_course_subjects', 'second_course_subjects', 'third_course_subjects', 'fourth_course_subjects',
            'form_two_normatives', 'second_form_two_normatives', 'third_form_two_normatives', 'fourth_form_two_normatives',
            'holidays', 'rooms', 'teacher_absences', 'practice_periods', 'users',
        ];

        if (!in_array($table, $allowedTables)) {
            return "Не могу выполнить запрос: раздел «{$table}» недоступен.";
        }

        try {
            $sectionName = $this->tableDisplayName($table);

            switch ($act) {
                case 'select':
                    return $this->dbSelect($table, $where, $data, (int) $limit);

                case 'insert':
                    $data['created_at'] = $data['updated_at'] = now();
                    DB::table($table)->insert($data);
                    return "Что сделал: добавил запись в раздел «{$sectionName}».\nЧто дальше: при необходимости напишите, что именно показать или изменить.";

                case 'update':
                    if (empty($where)) {
                        return "Для изменения нужно уточнение: что именно менять.";
                    }
                    
                    // If updating by name (teacher_name, group_name, subject_name), first find the record
                    $q = DB::table($table);
                    $whereOriginal = $where;
                    
                    // Check if where contains name field and we need to look up ID
                    $nameFields = ['teacher_name', 'group_name', 'subject_name', 'name', 'title', 'code'];
                    $whereKey = array_key_first($where);
                    $whereVal = $where[$whereKey];
                    
                    if (in_array($whereKey, $nameFields, true) && is_string($whereVal) && strlen($whereVal) > 2) {
                        // Try to find the record by name first
                        $record = DB::table($table)->where($whereKey, 'LIKE', '%' . $whereVal . '%')->first();
                        if ($record && isset($record->id)) {
                            $where = ['id' => $record->id];
                        }
                    }
                    
                    foreach ($where as $col => $val) {
                        $q->where($col, $val);
                    }
                    // Strip LIKE wildcards from actual values being written to DB
                    $data = array_map(fn($v) => is_string($v) ? trim($v, '%') : $v, $data);
                    $data['updated_at'] = now();
                    $count = $q->update($data);
                    if ($count === 0) {
                        return "Записи для изменения не найдены. Уточните параметры поиска.";
                    }
                    return "Что сделал: обновил записей — {$count}.\nЧто дальше: могу показать обновленные данные для проверки.";

                case 'delete':
                    if (empty($where)) {
                        return "Для удаления нужно уточнение: какую запись удалить.";
                    }
                    
                    // If deleting by name, first find the record
                    $nameFields = ['teacher_name', 'group_name', 'subject_name', 'name', 'title', 'code'];
                    $whereKey = array_key_first($where);
                    $whereVal = $where[$whereKey];
                    
                    if (in_array($whereKey, $nameFields, true) && is_string($whereVal) && strlen($whereVal) > 2) {
                        $record = DB::table($table)->where($whereKey, 'LIKE', '%' . $whereVal . '%')->first();
                        if ($record && isset($record->id)) {
                            $where = ['id' => $record->id];
                        }
                    }
                    
                    $q = DB::table($table);
                    foreach ($where as $col => $val) {
                        $q->where($col, $val);
                    }
                    $count = $q->delete();
                    if ($count === 0) {
                        return "Записи для удаления не найдены. Проверьте данные запроса.";
                    }
                    return "Что сделал: удалил записей — {$count}.\nЧто дальше: могу показать текущий список после удаления.";

                default:
                    return $this->sanitizeAssistantText(preg_replace('/```json.*?```/s', '', $rawResponse));
            }
        } catch (\Throwable $e) {
            report($e);
            return "Не удалось выполнить запрос. Попробуйте переформулировать его проще.";
        }
    }

    private function handlePlanningAction(array $action): string
    {
        $act = $action['action'];
        $course = (int) ($action['course'] ?? 1);
        $weekStart = $action['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
        $day = (int) ($action['day'] ?? 1);
        $lesson = (int) ($action['lesson'] ?? 1);

        $dayNames = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота'];

        switch ($act) {
            case 'check_conflicts':
                $conflicts = $this->planningService->analyzeConflicts($course, $weekStart);
                if (isset($conflicts['error'])) {
                    return "Ошибка: {$conflicts['error']}";
                }

                $lines = ["**Анализ конфликтов** для {$course} курса на неделю {$weekStart}", ""];

                if (empty($conflicts['room_conflicts']) && empty($conflicts['teacher_conflicts'])) {
                    $lines[] = "✓ **Конфликтов не обнаружено!** Расписание чистое.";
                } else {
                    if (!empty($conflicts['room_conflicts'])) {
                        $lines[] = "⚠️ **Конфликты аудиторий:** " . count($conflicts['room_conflicts']);
                        foreach (array_slice($conflicts['room_conflicts'], 0, 5) as $c) {
                            $lines[] = "- {$dayNames[$c['day']]}, {$c['lesson']} пара: аудитория **{$c['room_code']}**";
                        }
                        if (count($conflicts['room_conflicts']) > 5) {
                            $lines[] = "...и ещё " . (count($conflicts['room_conflicts']) - 5) . " конфликтов";
                        }
                    }

                    if (!empty($conflicts['teacher_conflicts'])) {
                        $lines[] = "";
                        $lines[] = "⚠️ **Конфликты преподавателей:** " . count($conflicts['teacher_conflicts']);
                        foreach (array_slice($conflicts['teacher_conflicts'], 0, 5) as $c) {
                            $lines[] = "- {$dayNames[$c['day']]}, {$c['lesson']} пара: **{$c['teacher_name']}**";
                        }
                        if (count($conflicts['teacher_conflicts']) > 5) {
                            $lines[] = "...и ещё " . (count($conflicts['teacher_conflicts']) - 5) . " конфликтов";
                        }
                    }
                }

                $lines[] = "";
                $lines[] = "Могу предложить варианты замен или оптимизации расписания.";
                return implode("\n", $lines);

            case 'find_replacement':
                $teacherId = (int) ($action['teacher_id'] ?? 0);
                if (!$teacherId) {
                    return "Укажите ID преподавателя для поиска замены.";
                }
                $replacements = $this->planningService->findReplacements($course, $teacherId, $day, $lesson);
                if (isset($replacements['error'])) {
                    return "Ошибка: {$replacements['error']}";
                }

                $teacherName = $this->resolveTeacherNameById($teacherId);
                $lines = ["**Замены для {$teacherName}**", ""];
                $lines[] = "📅 {$dayNames[$day]}, {$lesson} пара";
                $lines[] = "";

                if (empty($replacements)) {
                    $lines[] = "К сожалению, свободных преподавателей для этой пары не нашлось.";
                } else {
                    $lines[] = "Доступные кандидаты:";
                    foreach (array_slice($replacements, 0, 8) as $r) {
                        $status = $r['availability'] === 'свободен' ? '✓' : '○';
                        $subjects = implode(', ', array_slice($r['subjects'], 0, 2));
                        $lines[] = "{$status} **{$r['teacher_name']}** ({$r['initials']}) — {$subjects}";
                    }
                    if (count($replacements) > 8) {
                        $lines[] = "...и ещё " . (count($replacements) - 8) . " кандидатов";
                    }
                }

                $lines[] = "";
                $lines[] = "Чтобы назначить замену, скажите: «Назначь замену для {$teacherName} на {$dayNames[$day]}, {$lesson} пара».";
                return implode("\n", $lines);

            case 'free_teachers':
                $subjectId = (int) ($action['subject_id'] ?? 0);
                $teachers = $this->planningService->getAvailableTeachers($course, $day, $lesson, $subjectId ?: null);

                $lines = ["**Свободные преподаватели**", ""];
                $lines[] = "📅 {$dayNames[$day]}, {$lesson} пара";
                if ($subjectId) {
                    $subjectName = $this->resolveSubjectNameById($course, $subjectId);
                    $lines[] = "📚 По предмету: {$subjectName}";
                }
                $lines[] = "";

                if (empty($teachers)) {
                    $lines[] = "Свободных преподавателей не найдено.";
                } else {
                    $lines[] = "Доступны:";
                    foreach ($teachers as $t) {
                        $lines[] = "- **{$t['name']}** ({$t['initials']})";
                    }
                }
                return implode("\n", $lines);

            case 'free_rooms':
                $rooms = $this->planningService->getAvailableRooms($course, $day, $lesson);

                $lines = ["**Свободные аудитории**", ""];
                $lines[] = "📅 {$dayNames[$day]}, {$lesson} пара";
                $lines[] = "";

                if (empty($rooms)) {
                    $lines[] = "Свободных аудиторий не найдено. Все заняты.";
                } else {
                    $standard = array_filter($rooms, fn($r) => $r['type'] === 'standard');
                    $computer = array_filter($rooms, fn($r) => $r['type'] === 'computer');

                    if (!empty($standard)) {
                        $lines[] = "📖 Обычные: " . implode(', ', array_map(fn($r) => "**{$r['code']}**", $standard));
                    }
                    if (!empty($computer)) {
                        $lines[] = "💻 Компьютерные: " . implode(', ', array_map(fn($r) => "**{$r['code']}**", $computer));
                    }
                }
                return implode("\n", $lines);

            case 'suggest_placement':
                $groupId = (int) ($action['group_id'] ?? 0);
                $subjectId = (int) ($action['subject_id'] ?? 0);
                $teacherId = (int) ($action['teacher_id'] ?? 0);

                if (!$groupId || !$subjectId) {
                    return "Укажите ID группы и предмета для поиска места.";
                }

                $placements = $this->planningService->suggestSchedulePlacement($course, $groupId, $subjectId, $teacherId ?: null);
                $groupName = $this->resolveGroupNameById($course, $groupId);
                $subjectName = $this->resolveSubjectNameById($course, $subjectId);

                $lines = ["**Где поставить «{$subjectName}» для {$groupName}?**", ""];

                $goodSlots = array_filter($placements, fn($p) => empty($p['teacher_conflict']) && !empty($p['available_rooms']));
                $limitedSlots = array_filter($placements, fn($p) => !empty($p['teacher_conflict']) && !empty($p['available_rooms']));
                $noRoomsSlots = array_filter($placements, fn($p) => empty($p['available_rooms']));

                if (!empty($goodSlots)) {
                    $lines[] = "✓ **Оптимальные слоты** (свободен и преподаватель, и аудитория):";
                    foreach (array_slice($goodSlots, 0, 4) as $slot) {
                        $rooms = implode(', ', array_slice(array_map(fn($r) => $r['code'], $slot['available_rooms']), 0, 2));
                        $lines[] = "- {$dayNames[$slot['day']]}, {$slot['lesson']} пара → {$rooms}";
                    }
                }

                if (!empty($limitedSlots)) {
                    $lines[] = "";
                    $lines[] = "○ **Свободны только аудитории** (преподаватель занят):";
                    foreach (array_slice($limitedSlots, 0, 3) as $slot) {
                        $rooms = implode(', ', array_slice(array_map(fn($r) => $r['code'], $slot['available_rooms']), 0, 2));
                        $lines[] = "- {$dayNames[$slot['day']]}, {$slot['lesson']} пара → {$rooms}";
                    }
                }

                if (empty($goodSlots) && empty($limitedSlots)) {
                    $lines[] = "Свободных слотов не найдено. Попробуйте другой день или предмет.";
                }

                return implode("\n", $lines);

            case 'week_stats':
                $stats = $this->planningService->getWeekStats($course, $weekStart);
                $lines = ["**Статистика недели** {$weekStart}", ""];
                $lines[] = "📊 {$course} курс";
                $lines[] = "";

                $lines[] = "**Всего пар:** =={$stats['total_pairs']}==";
                $lines[] = "";
                $lines[] = "**По дням:**";
                foreach ($stats['by_day'] as $d => $count) {
                    if ($count > 0) {
                        $lines[] = "- {$dayNames[$d]}: {$count} пар";
                    }
                }

                if (!empty($stats['by_teacher'])) {
                    $lines[] = "";
                    $lines[] = "**Нагрузка преподавателей (топ-5):**";
                    foreach (array_slice($stats['by_teacher'], 0, 5) as $t) {
                        $lines[] = "- {$t['name']}: =={$t['pairs']}== пар";
                    }
                }

                if ($stats['unassigned_rooms'] > 0) {
                    $lines[] = "";
                    $lines[] = "> ⚠️ {$stats['unassigned_rooms']} пар без назначенной аудитории";
                }

                return implode("\n", $lines);

            case 'plan_schedule':
                $groupIds = $action['group_ids'] ?? [];
                $subjectId = (int) ($action['subject_id'] ?? 0);
                $hoursPerWeek = (int) ($action['hours_per_week'] ?? 4);

                if (empty($groupIds) || !$subjectId) {
                    return "Укажите группы (group_ids), предмет (subject_id) и часы в неделю (hours_per_week).";
                }

                $suggestions = $this->planningService->generateScheduleSuggestion($course, $groupIds, $subjectId, $hoursPerWeek);
                if (isset($suggestions['error'])) {
                    return "Ошибка: {$suggestions['error']}";
                }

                $lines = ["**📋 Предложение расписания**", ""];

                foreach ($suggestions as $groupData) {
                    $lines[] = "**{$groupData['group']['name']}** — {$groupData['subject']['name']} ({$groupData['planned_hours']}ч/нед)";
                    if (empty($groupData['slots'])) {
                        $lines[] = "  Нет свободных слотов для этой группы";
                    } else {
                        foreach ($groupData['slots'] as $slot) {
                            $room = $slot['room']['code'] ?? '?';
                            $teacher = $slot['teacher']['name'] ?? '?';
                            $lines[] = "  ✓ {$dayNames[$slot['day']]}, {$slot['lesson']} пара → {$room}, {$teacher}";
                        }
                    }
                    $lines[] = "";
                }

                $lines[] = "Это предложение. Скажите «Сохрани» чтобы добавить в расписание или «Измени» чтобы скорректировать.";
                return implode("\n", $lines);

            default:
                return "Неизвестное действие планирования: {$act}";
        }
    }

    private function handleFileImport(array $action): string
    {
        $fileType = $action['file_type'] ?? '';
        $mappings = $action['mappings'] ?? [];
        $previewRows = $action['preview_rows'] ?? 5;

        if (empty($fileType)) {
            return "Укажите тип файла: нагрузка, преподаватели, график, расписание";
        }

        $typeNames = [
            'нагрузка' => 'нагрузка преподавателей (Форма 2)',
            'преподаватели' => 'список преподавателей',
            'график' => 'график учебного процесса',
            'расписание' => 'расписание занятий',
        ];

        $typeName = $typeNames[$fileType] ?? $fileType;
        
        $mappingDesc = [];
        foreach ($mappings as $fileCol => $dbField) {
            $mappingDesc[] = "{$fileCol} → {$dbField}";
        }

        $reply = "**📁 Импорт: {$typeName}**\n\n";
        $reply .= "Сопоставление колонок:\n" . implode("\n", $mappingDesc) . "\n\n";
        $reply .= "Показано {$previewRows} строк для проверки.\n";
        $reply .= "Подтвердить импорт? (Да/Нет)";

        request()->session()->put(self::SESSION_PENDING_ACTION, [
            'action' => ['type' => 'file_import', 'file_type' => $fileType, 'mappings' => $mappings],
            'ts'     => time(),
        ]);

        return $reply;
    }

    private function dbSelect(string $table, array $where, array $columns, int $limit): string
    {
        $q = DB::table($table);

        foreach ($where as $col => $val) {
            if (is_string($val) && str_contains($val, '%')) {
                $q->where($col, 'LIKE', $val);
            } else {
                $q->where($col, $val);
            }
        }

        // Ignore columns if model returned only ['id'] — select everything instead
        if ($columns && !(count($columns) === 1 && $columns[0] === 'id')) {
            $q->select($columns);
        }

        $totalCount = (clone $q)->count();

        $maxRows = max(1, min($limit, 500));
        $rows = $q->limit($maxRows)->get();

        if ($rows->isEmpty()) {
            return "Ничего не найдено. Уточните запрос: фамилию, группу, курс или период.";
        }

        $hasMore = $totalCount > $maxRows;

        $sectionName = $this->tableDisplayName($table);
        $skipCols    = ['created_at', 'updated_at', 'password', 'remember_token'];

        // Build display rows with human-readable values
        $displayRows = [];
        foreach ($rows as $row) {
            $rowArr  = (array) $row;
            $cells   = [];
            foreach ($rowArr as $col => $value) {
                if (in_array($col, $skipCols, true)) {
                    continue;
                }
                $cells[$col] = $this->resolveHumanValue($table, $rowArr, (string) $col, $value);
            }
            if (!empty($cells)) {
                $displayRows[] = $cells;
            }
        }

        if (empty($displayRows)) {
            return "Данные найдены, но нечего отобразить.";
        }

        $colKeys     = array_keys($displayRows[0]);
        $headerCells = array_map(fn($k) => $this->columnDisplayName($k), $colKeys);
        $separator   = array_fill(0, count($colKeys), '---');

        $lines = ["**{$sectionName}** — всего в базе: **{$totalCount}**", ''];
        $lines[] = '| ' . implode(' | ', $headerCells) . ' |';
        $lines[] = '| ' . implode(' | ', $separator) . ' |';

        foreach ($displayRows as $row) {
            $cells   = array_map(
                fn($k) => str_replace(['|', "\n", "\r"], [' ', ' ', ''], (string) ($row[$k] ?? '—')),
                $colKeys
            );
            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        if ($hasMore) {
            $lines[] = '';
            $lines[] = "*Показаны первые {$maxRows} записей из {$totalCount}. Уточните запрос для сужения.*";
        }

        $lines[] = '';
        $lines[] = "Могу отфильтровать точнее или показать другой раздел.";

        return implode("\n", $lines);
    }

    private function loadDbContext(): string
    {
        return Cache::remember('ai_db_context_v2', 300, function () {
            $lines = [];

            $teacherCount = DB::table('teachers')->count();
            if ($teacherCount) {
                $lines[] = "Преподавателей в системе: {$teacherCount}";
            }

            foreach ([1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth'] as $num => $prefix) {
                try {
                    $cnt = DB::table("{$prefix}_course_group")->count();
                    if ($cnt) $lines[] = "Групп {$num} курса: {$cnt}";
                } catch (\Exception $e) {}
            }

            foreach ([1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth'] as $num => $prefix) {
                try {
                    $cnt = DB::table("{$prefix}_course_subjects")->count();
                    if ($cnt) $lines[] = "Дисциплин {$num} курса: {$cnt}";
                } catch (\Exception $e) {}
            }

            try {
                $cnt = DB::table('rooms')->count();
                if ($cnt) $lines[] = "Аудиторий: {$cnt}";
            } catch (\Exception $e) {}

            return implode("\n", $lines);
        });
    }

    private function buildSystemPrompt(): string
    {
        return Cache::remember('ai_system_prompt', 600, function () {
            $teachers = $this->safeTableCount('teachers');
            $groups1  = $this->safeTableCount('first_course_group');
            $groups2  = $this->safeTableCount('second_course_group');
            $groups3  = $this->safeTableCount('third_course_group');
            $groups4  = $this->safeTableCount('fourth_course_group');
            $rooms    = $this->safeTableCount('rooms');
            $holidays = $this->safeTableCount('holidays');

            return <<<PROMPT
Ты — ИИ-ассистент диспетчера учебной части колледжа.

═══════════════════════════════════════════
О ПРОЕКТЕ
═══════════════════════════════════════════
Система "KitOper" — автоматизированная система управления учебным процессом колледжа. Основные функции:
• Планирование и управление расписанием занятий
• Учёт нагрузки преподавателей (Форма 2)
• Управление группами и дисциплинами по курсам (1-4 курс)
• Контроль посещаемости и отсутствий преподавателей
• Управление аудиторным фондом
• Планирование практик и лагерных периодов

═══════════════════════════════════════════
СТРУКТУРА БАЗЫ ДАННЫХ
═══════════════════════════════════════════

ТАБЛИЦЫ ПРЕПОДАВАТЕЛЕЙ:
• teachers — преподаватели (id, teacher_name, initials, и др.)
• teacher_absences — отсутствия преподавателей (teacher_id, date, reason, type)

ТАБЛИЦЫ ГРУПП (по курсам):
• first_course_group — группы 1 курса ({$groups1} групп, поля: id, group_name, group_number, course)
• second_course_group — группы 2 курса ({$groups2} групп)
• third_course_group — группы 3 курса ({$groups3} групп)
• fourth_course_group — группы 4 курса ({$groups4} групп)

ТАБЛИЦЫ ДИСЦИПЛИН (по курсам):
• first_course_subjects — дисциплины 1 курса
• second_course_subjects — дисциплины 2 курса
• third_course_subjects — дисциплины 3 курса
• fourth_course_subjects — дисциплины 4 курса

ТАБЛИЦЫ НАГРУЗКИ (Форма 2, по курсам):
• form_two_normatives — нагрузка 1 курса (group_id, subject_id, teacher_id, month, year, total_hours, hours_per_class)
• second_form_two_normatives — нагрузка 2 курса
• third_form_two_normatives — нагрузка 3 курса
• fourth_form_two_normatives — нагрузка 4 курса

ТАБЛИЦЫ РАСПИСАНИЯ (по курсам):
• first_course_schedules — расписание 1 курса
• second_course_schedules — расписание 2 курса
• third_course_schedules — расписание 3 курса
• fourth_course_schedules — расписание 4 курса
Поля: week_start, study_day (1-6 = пн-сб), lesson_number (1-6), group_id, subject_id, teacher_id, room_id, mode

ПРОЧИЕ ТАБЛИЦЫ:
• rooms — аудитории ({$rooms}, поля: id, code, title, room_type)
• holidays — праздники/выходные дни ({$holidays}, поля: name, start_date, end_date)
• practice_periods — периоды практик
• field_camp_periods — лагерные периоды
• users — пользователи системы (id, name, email, role)
• audit_logs — журнал действий пользователей

═══════════════════════════════════════════
ПРАВИЛА РАБОТЫ
═══════════════════════════════════════════

1. Отвечай на РУССКОМ языке естественно и по-человечески
2. Будь краток для простых запросов, но информативен для сложных
3. Используй **жирный** текст для имён, чисел, дат — это важно для быстрого сканирования
4. Для списков используй маркеры • или -
5. НИКОГДА не говори пользователю про ID, SQL, таблицы, JSON — это внутренняя кухня
6. Всегда показывай общее количество записей в ответе
7. Если данных много — показывай первые 200-500 записей с пометкой "всего X"
8. НИКОГДА НЕ ГАЛЛЮЦИНИРУЙ — всегда делай РЕАЛЬНЫЙ запрос к БД для получения данных!
   НЕ придумывай числа, имена, названия — только реальные данные из системы!
9. Для ЛЮБЫХ вопросов типа "сколько", "количество", "число" — ОБЯЗАТЕЛЬНО делай SELECT запрос!
   Пример: "сколько учителей" → {"action":"select","table":"teachers","where":{},"data":["id"],"limit":1}
   Потом посчитай количество записей и скажи пользователю реальное число!

═══════════════════════════════════════════
КАК РАБОТАТЬ С ЗАПРОСАМИ ПОЛЬЗОВАТЕЛЯ
═══════════════════════════════════════════

ВАЖНО: Диспетчер НЕ знает про ID записей — не спрашивай их никогда!

ПОИСК ЗАПИСЕЙ:
• Пользователь говорит "Иванов" → ищи в teachers где teacher_name LIKE '%Иванов%'
• Пользователь говорит "группа ТЭ-311" → ищи в appropriate_course_group где group_name = 'ТИ-311'
• Найдя запись — используй её id для дальнейших действий (UPDATE/DELETE)

ПРИМЕРЫ:
• "Покажи всех преподавателей" → SELECT teachers, limit 200
• "Найди Петрова" → SELECT teachers WHERE teacher_name LIKE '%Петров%'
• "Измени Смурыгин на Смурыгин А." → SELECT где teacher_name LIKE '%Смурыгин%', получи id → UPDATE
• "Удали группу ТЭ-311" → SELECT где group_name='ТЭ-311', получи id → DELETE
• "Какая нагрузка у Иванова?" → SELECT form_two_normatives WHERE teacher_id = (SELECT id FROM teachers WHERE ...)

═══════════════════════════════════════════
ФОРМАТ КОМАНД (внутренний)
═══════════════════════════════════════════

Для работы с данными возвращай JSON:

ВЫБОРКА (select):
{"action":"select","table":"teachers","where":{},"data":["id","teacher_name","initials"],"limit":200}
{"action":"select","table":"first_course_group","where":{"group_name":"ТЭ-311"},"data":["id","group_name","group_number"]}

ДОБАВЛЕНИЕ (insert):
{"action":"insert","table":"teachers","data":{"teacher_name":"Иванов И.И.","initials":"Иванов И.И."}}

ИЗМЕНЕНИЕ (update):
{"action":"update","table":"teachers","where":{"id":5},"data":{"teacher_name":"Петров П.П."}}

УДАЛЕНИЕ (delete):
{"action":"delete","table":"form_two_normatives","where":{"group_id":3,"subject_id":5}}

РАСПИСАНИЕ (специальные действия):
• check_conflicts — проверить конфликты (дубли аудиторий и преподавателей)
• find_replacement — найти замену преподавателю
• suggest_placement — предложить место для новой пары
• week_stats — статистика недели (пар по дням, нагрузка преподавателей)
• free_teachers — свободные преподаватели на время
• free_rooms — свободные аудитории на время
• plan_schedule — сгенерировать предложение по расписанию

═══════════════════════════════════════════
ИМПОРТ ИЗ ФАЙЛОВ
═══════════════════════════════════════════

Пользователь может загрузить Excel/Word файл. Твоя задача:
1. Проанализировать структуру файла (колонки, листы, данные)
2. Определить тип данных (нагрузка, преподаватели, график, расписание)
3. Сопоставить колонки файла с полями БД
4. Показать план импорта и запросить подтверждение
5. Вернуть: {"action":"import_file","file_type":"нагрузка|преподаватели|график|расписание","mappings":{"колонка":"поле_бд"},"preview_rows":5}

═══════════════════════════════════════════
ПРИМЕРЫ ДИАЛОГОВ
═══════════════════════════════════════════

Пользователь: "Покажи всех преподавателей"
AI: Возвращает JSON select для teachers

Пользователь: "Сколько групп на 2 курсе?"
AI: Возвращает JSON select для second_course_group, показывает count

Пользователь: "Проверь конфликты на этой неделе"
AI: Возвращает check_conflicts с параметрами course и week_start

Пользователь: "Привет! Чем можешь помочь?"
AI: Человеческим языком: "Привет! Я помогаю работать с учебными данными: покажу списки преподавателей, групп, дисциплин, проверю расписание на конфликты или предложу замену. Просто спроси!"

═══════════════════════════════════════════
ВАЖНЫЕ НАПОМИНАНИЯ
═══════════════════════════════════════════
• Показывай ВСЕГДА общее количество записей в базе
• Используй limit 200 по умолчанию для select
• При отображении таблиц — первая строка это заголовки
• Избегай технических терминов в ответах пользователю
• Будь дружелюбным и полезным!
PROMPT;
        });
    }

    private function buildSystemPromptWithContext(): string
    {
        $base    = $this->buildSystemPrompt();
        $context = $this->loadDbContext();

        if (!$context) {
            return $base;
        }

        return $base . "\n\n═══════════════════════════════════════════\nТЕКУЩИЕ ДАННЫЕ В СИСТЕМЕ\n═══════════════════════════════════════════\n" . $context;
    }

    private function sanitizeAssistantText(string $text): string
    {
        // Убираем только маркеры ```json и ``` без контента внутри (артефакты модели)
        $text = preg_replace('/^```json\s*$/mu', '', $text);
        $text = preg_replace('/^```\s*$/mu', '', $text);
        // Убираем префикс "Ассистент:" если модель его добавила
        $text = preg_replace('/^\s*Ассистент:\s*/u', '', (string) $text);
        $text = trim((string) $text);

        if ($text === '') {
            return 'Не могу ответить на этот вопрос. Попробуйте переформулировать.';
        }

        return $text;
    }

    // Fallback: parse natural language into DB action when AI didn't produce JSON
    private function tryParseNaturalLanguageAction(string $message): ?array
    {
        $msg = mb_strtolower($message);
        
        // Pattern: "сколько учителей" / "сколько групп" / "сколько аудиторий" / "сколько праздников"
        if (preg_match('/^сколько\s+(учител|преподавател|групп|аудитор|кабинет|праздник|дисциплин|предмет)/iu', $msg, $m)) {
            $entity = mb_strtolower($m[1]);
            
            $tableMap = [
                'учител' => 'teachers',
                'преподавател' => 'teachers',
                'групп' => null, // depends on course context
                'аудитор' => 'rooms',
                'кабинет' => 'rooms',
                'праздник' => 'holidays',
                'дисциплин' => null,
                'предмет' => null,
            ];
            
            foreach ($tableMap as $key => $table) {
                if (strpos($entity, $key) === 0) {
                    if ($table !== null) {
                        return [
                            'action' => 'select',
                            'table' => $table,
                            'where' => [],
                            'data' => ['id'],
                            'limit' => 1,
                        ];
                    }
                    break;
                }
            }
        }
        
        // Pattern 1: "замени X на Y" (verb at start)
        if (preg_match('/^замени\s+(.+)\s+на\s+(.+)$/iu', $msg, $m)) {
            $oldName = trim($m[1]);
            $newName = trim($m[2]);
            
            if (strlen($oldName) > 2) {
                return $this->buildUpdateAction('teachers', $oldName, $newName, $msg);
            }
        }
        
        // Pattern 2: "X замени на Y" (verb in middle)
        if (preg_match('/(.+)\s+замени\s+на\s+(.+)$/iu', $msg, $m)) {
            $oldName = trim($m[1]);
            $newName = trim($m[2]);
            
            if (strlen($oldName) > 2 && strlen($newName) > 2) {
                return $this->buildUpdateAction('teachers', $oldName, $newName, $msg);
            }
        }
        
        // Pattern 3: "X измени на Y"
        if (preg_match('/(.+)\s+измени\s+на\s+(.+)$/iu', $msg, $m)) {
            $oldName = trim($m[1]);
            $newName = trim($m[2]);
            
            if (strlen($oldName) > 2 && strlen($newName) > 2) {
                return $this->buildUpdateAction('teachers', $oldName, $newName, $msg);
            }
        }
        
        return null;
    }
    
    private function buildUpdateAction(string $table, string $oldValue, string $newValue, string $msg): array
    {
        if (strpos($msg, 'группа') !== false) $table = 'first_course_group';
        if (strpos($msg, 'предмет') !== false || strpos($msg, 'дисциплин') !== false) $table = 'first_course_subjects';
        if (strpos($msg, 'аудиторий') !== false) $table = 'rooms';
        
        return [
            'action' => 'update',
            'table'  => $table,
            'where'  => ['teacher_name' => $oldValue],
            'data'   => ['teacher_name' => $newValue],
            'limit'  => 1,
        ];
    }

    private function safeTableCount(string $table): int
    {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->count();
    }

    private function tableDisplayName(string $table): string
    {
        $map = [
            'teachers'                   => 'Преподаватели',
            'first_course_group'         => 'Группы 1 курса',
            'second_course_group'        => 'Группы 2 курса',
            'third_course_group'         => 'Группы 3 курса',
            'fourth_course_group'        => 'Группы 4 курса',
            'first_course_subjects'      => 'Дисциплины 1 курса',
            'second_course_subjects'     => 'Дисциплины 2 курса',
            'third_course_subjects'      => 'Дисциплины 3 курса',
            'fourth_course_subjects'     => 'Дисциплины 4 курса',
            'form_two_normatives'        => 'Нагрузка 1 курса',
            'second_form_two_normatives' => 'Нагрузка 2 курса',
            'third_form_two_normatives'  => 'Нагрузка 3 курса',
            'fourth_form_two_normatives' => 'Нагрузка 4 курса',
            'holidays'                   => 'Праздники',
            'rooms'                      => 'Аудитории',
            'teacher_absences'           => 'Отсутствия преподавателей',
            'practice_periods'           => 'Периоды практики',
            'users'                      => 'Пользователи',
        ];

        return $map[$table] ?? $table;
    }

    private function columnDisplayName(string $column): string
    {
        $map = [
            'id'            => 'ID',
            'teacher_name'  => 'Преподаватель',
            'initials'      => 'Инициалы',
            'group_name'    => 'Группа',
            'group_number'  => 'Номер группы',
            'subject_name'  => 'Предмет',
            'group_id'      => 'ID группы',
            'subject_id'    => 'ID предмета',
            'teacher_id'    => 'ID преподавателя',
            'month'         => 'Месяц',
            'year'          => 'Год',
            'total_hours'   => 'Часы',
            'hours_per_class' => 'Часов на занятие',
            'name'          => 'Название',
            'start_date'    => 'Дата начала',
            'end_date'      => 'Дата окончания',
            'code'          => 'Код',
            'title'         => 'Наименование',
            'type'          => 'Тип аудитории',
            'room_id'       => 'Аудитория',
            'absence_type'  => 'Тип отсутствия',
            'course'        => 'Курс',
            'room_type'     => 'Тип',
            'email'         => 'Email',
            'role'          => 'Роль',
        ];

        return $map[$column] ?? $column;
    }

    private function formatDisplayValue(string $column, mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'не указано';
        }

        if ($column === 'role') {
            return match ((string) $value) {
                'dispatcher' => 'диспетчер',
                'teacher'    => 'преподаватель',
                'student'    => 'студент',
                default      => (string) $value,
            };
        }

        if ($column === 'month' && is_numeric($value)) {
            $months = [
                1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель',
                5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август',
                9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь',
            ];
            $monthNum = (int) $value;
            return $months[$monthNum] ?? (string) $value;
        }

        if (str_ends_with($column, '_date')) {
            $ts = strtotime((string) $value);
            if ($ts !== false) {
                return date('d.m.Y', $ts);
            }
        }

        return (string) $value;
    }

    private function resolveHumanValue(string $table, array $row, string $column, mixed $value): string
    {
        if ($column === 'teacher_id' && is_numeric($value)) {
            $id = (int) $value;
            $name = $this->resolveTeacherNameById($id);
            if ($name) {
                return "{$name} (ID {$id})";
            }
        }

        if ($column === 'room_id' && is_numeric($value)) {
            $id = (int) $value;
            $room = $this->resolveRoomById($id);
            if ($room) {
                return "{$room} (ID {$id})";
            }
        }

        if ($column === 'group_id' && is_numeric($value)) {
            $id = (int) $value;
            $course = $this->detectCourseByTableOrRow($table, $row);
            if ($course) {
                $groupName = $this->resolveGroupNameById($course, $id);
                if ($groupName) {
                    return "{$groupName} (ID {$id})";
                }
            }
        }

        if ($column === 'subject_id' && is_numeric($value)) {
            $id = (int) $value;
            $course = $this->detectCourseByTableOrRow($table, $row);
            if ($course) {
                $subjectName = $this->resolveSubjectNameById($course, $id);
                if ($subjectName) {
                    return "{$subjectName} (ID {$id})";
                }
            }
        }

        return $this->formatDisplayValue($column, $value);
    }

    private function detectCourseByTableOrRow(string $table, array $row): ?int
    {
        if (isset($row['course']) && is_numeric($row['course'])) {
            $course = (int) $row['course'];
            if ($course >= 1 && $course <= 4) {
                return $course;
            }
        }

        return match ($table) {
            'form_two_normatives', 'first_course_group', 'first_course_subjects' => 1,
            'second_form_two_normatives', 'second_course_group', 'second_course_subjects' => 2,
            'third_form_two_normatives', 'third_course_group', 'third_course_subjects' => 3,
            'fourth_form_two_normatives', 'fourth_course_group', 'fourth_course_subjects' => 4,
            default => null,
        };
    }

    private function resolveTeacherNameById(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        static $cache = [];
        if (array_key_exists($id, $cache)) {
            return $cache[$id];
        }

        if (!DB::getSchemaBuilder()->hasTable('teachers')) {
            $cache[$id] = null;
            return null;
        }

        $cache[$id] = DB::table('teachers')->where('id', $id)->value('teacher_name');
        return $cache[$id];
    }

    private function resolveRoomById(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        static $cache = [];
        if (array_key_exists($id, $cache)) {
            return $cache[$id];
        }

        if (!DB::getSchemaBuilder()->hasTable('rooms')) {
            $cache[$id] = null;
            return null;
        }

        $room = DB::table('rooms')->select('code', 'title')->where('id', $id)->first();
        if (!$room) {
            $cache[$id] = null;
            return null;
        }

        $label = trim((string) ($room->code ?? ''));
        $title = trim((string) ($room->title ?? ''));
        if ($label !== '' && $title !== '') {
            $cache[$id] = "{$label} — {$title}";
        } else {
            $cache[$id] = $label !== '' ? $label : ($title !== '' ? $title : null);
        }

        return $cache[$id];
    }

    private function resolveGroupNameById(int $course, int $id): ?string
    {
        if ($id <= 0 || $course < 1 || $course > 4) {
            return null;
        }

        static $cache = [];
        $cacheKey = "{$course}:{$id}";
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $table = match ($course) {
            1 => 'first_course_group',
            2 => 'second_course_group',
            3 => 'third_course_group',
            4 => 'fourth_course_group',
            default => 'first_course_group',
        };

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            $cache[$cacheKey] = null;
            return null;
        }

        $cache[$cacheKey] = DB::table($table)->where('id', $id)->value('group_name');
        return $cache[$cacheKey];
    }

    private function resolveSubjectNameById(int $course, int $id): ?string
    {
        if ($id <= 0 || $course < 1 || $course > 4) {
            return null;
        }

        static $cache = [];
        $cacheKey = "{$course}:{$id}";
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $table = $this->subjectsTable($course);
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            $cache[$cacheKey] = null;
            return null;
        }

        $cache[$cacheKey] = DB::table($table)->where('id', $id)->value('subject_name');
        return $cache[$cacheKey];
    }

    public function parseFile(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,docx,doc|max:10240']);

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $filename = $file->getClientOriginalName();

        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                $content = $this->excelToText($file->getRealPath());
            } else {
                $zip     = new \ZipArchive();
                $content = '';
                if ($zip->open($file->getRealPath()) === true) {
                    $xml     = $zip->getFromName('word/document.xml');
                    $content = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));
                    $zip->close();
                }
                if (!$content) {
                    throw new \Exception('Не удалось прочитать документ Word');
                }
            }

            return response()->json(['success' => true, 'content' => $content, 'filename' => $filename]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    private function excelToText(string $path): string
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getSheet(0);
        $highestRow  = $sheet->getHighestDataRow();
        $highestCol  = $sheet->getHighestDataColumn();
        $lines       = [];

        for ($row = 1; $row <= min($highestRow, 300); $row++) {
            $cells = [];
            for ($col = 'A'; $col <= $highestCol; $col++) {
                $val     = trim((string) $sheet->getCell($col . $row)->getFormattedValue());
                $cells[] = $val;
            }
            $line = implode(' | ', array_filter($cells, fn($c) => $c !== ''));
            if ($line) {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|mimes:xlsx,xls,docx,doc|max:10240',
            'import_type' => 'required|in:workload,teachers',
            'months'      => 'nullable|string',
            'year'        => 'nullable|integer|min:2020|max:2030',
        ]);

        $file       = $request->file('file');
        $importType = $request->input('import_type');
        $months     = $request->input('months') ? array_map('intval', explode(',', $request->input('months'))) : [date('n')];
        $year       = (int) $request->input('year', date('Y'));
        $ext        = strtolower($file->getClientOriginalExtension());

        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                $rows = $this->parseExcel($file->getRealPath());
            } else {
                $rows = $this->parseWordWithOllama($file->getRealPath(), $importType);
            }

            if ($importType === 'workload') {
                $preview = $this->buildWorkloadPreview($rows, $months, $year);
            } else {
                $preview = $this->buildTeachersPreview($rows);
            }

            return response()->json([
                'success'     => true,
                'import_type' => $importType,
                'months'      => $months,
                'year'        => $year,
                'preview'     => $preview,
                'total'       => count($preview),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function importData(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:workload,teachers',
            'rows'        => 'required|array',
            'months'      => 'nullable|array',
            'year'        => 'nullable|integer',
        ]);

        $importType = $request->input('import_type');
        $rows       = $request->input('rows');
        $months     = $request->input('months', [date('n')]);
        $year       = (int) $request->input('year', date('Y'));
        $log        = [];
        $stats      = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        try {
            DB::beginTransaction();

            if ($importType === 'workload') {
                $this->importWorkload($rows, $months, $year, $log, $stats);
            } else {
                $this->importTeachers($rows, $log, $stats);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'stats' => $stats, 'log' => $log]);
    }

    public function ollamaStatus()
    {
        return response()->json([
            'running' => $this->checkOllamaStatus(),
            'models'  => $this->getOllamaModels(),
        ]);
    }

    public function pullModel(Request $request)
    {
        $model = $request->input('model', $this->ollamaModel);

        $ch = curl_init("{$this->ollamaHost}/api/pull");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['name' => $model, 'stream' => false]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 300,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return response()->json(['success' => false, 'error' => $error], 500);
        }

        return response()->json(['success' => true, 'message' => "Модель $model загружена"]);
    }

    // ─── Parsing ────────────────────────────────────────────────────────────────

    private function parseExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getSheet(0);
        $highestRow  = $sheet->getHighestDataRow();
        $rows        = [];

        for ($row = 4; $row <= $highestRow; $row++) {
            $groupName   = trim((string) $sheet->getCell('A' . $row)->getValue());
            $resultIndex = trim((string) $sheet->getCell('D' . $row)->getValue());
            $subjectName = trim((string) $sheet->getCell('E' . $row)->getValue());
            $teacherName = trim((string) $sheet->getCell('F' . $row)->getValue());
            $totalHours  = trim((string) $sheet->getCell('Q' . $row)->getValue());

            if (!$groupName || !$subjectName || str_contains($groupName, '_итог')) {
                continue;
            }
            if ($totalHours === '' || $totalHours === '0') {
                continue;
            }

            $rows[] = [
                'group_name'   => $groupName,
                'result_index' => $resultIndex,
                'subject_name' => $subjectName,
                'teacher_name' => $teacherName,
                'total_hours'  => (int) $totalHours,
            ];
        }

        return $rows;
    }

    private function parseWordWithOllama(string $path, string $importType): array
    {
        $zip     = new \ZipArchive();
        $content = '';

        if ($zip->open($path) === true) {
            $xml     = $zip->getFromName('word/document.xml');
            $content = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));
            $zip->close();
        }

        if (!$content) {
            throw new \Exception('Не удалось прочитать документ Word');
        }

        $prompt = $importType === 'teachers'
            ? $this->buildTeachersPrompt($content)
            : $this->buildWorkloadPrompt($content);

        $result = $this->callOllama($prompt);

        preg_match('/\[.*\]/s', $result, $matches);
        if (!$matches) {
            throw new \Exception('Ollama не вернул корректный JSON массив');
        }

        $decoded = json_decode($matches[0], true);
        if (!is_array($decoded)) {
            throw new \Exception('Ollama вернул некорректный JSON');
        }

        return $decoded;
    }

    // ─── Preview builders ────────────────────────────────────────────────────────

    private function buildWorkloadPreview(array $rows, array $months, int $year): array
    {
        $groupsCache   = $this->loadGroupsCache();
        $teachersCache = $this->loadTeachersCache();
        $preview       = [];

        foreach ($rows as $row) {
            $groupName   = $row['group_name'];
            $resultIndex = $row['result_index'] ?? '';
            $subjectName = $row['subject_name'];
            $teacherName = $row['teacher_name'];
            $totalHours  = (int) ($row['total_hours'] ?? 0);

            $course = $this->detectCourse($groupName);
            if (!$course) {
                continue;
            }

            $groupData = $groupsCache[$groupName] ?? null;
            $groupId   = $groupData ? $groupData['id'] : null;

            $subjectId = null;
            $subjectFound = '';
            if ($resultIndex) {
                $subjectId = $this->findSubjectByIndex($resultIndex, $course);
                if ($subjectId) {
                    $subjectFound = $this->getSubjectName($subjectId, $course);
                }
            }
            if (!$subjectId) {
                $subjectId = $this->findSubjectByName($subjectName, $course);
                if ($subjectId) {
                    $subjectFound = $this->getSubjectName($subjectId, $course);
                }
            }

            $teacherId    = null;
            $teacherFound = '';
            $isVacancy    = str_contains(mb_strtolower($teacherName), 'вакансия');
            if (!$isVacancy && $teacherName) {
                $key       = $this->normalizeTeacher($teacherName);
                $teacherId = $teachersCache[$key] ?? null;
                if ($teacherId) {
                    $teacherFound = $this->getTeacherName($teacherId);
                }
            }

            $normTable = $this->normativesTable($course);
            $status    = 'new';
            if ($groupId && $subjectId) {
                $existing = DB::table($normTable)
                    ->where('group_id', $groupId)
                    ->where('subject_id', $subjectId)
                    ->whereIn('month', $months)
                    ->where('year', $year)
                    ->exists();
                $status = $existing ? 'update' : 'new';
            }

            $preview[] = [
                'group_name'    => $groupName,
                'group_id'      => $groupId,
                'subject_name'  => $subjectFound ?: $subjectName,
                'subject_id'    => $subjectId,
                'teacher_name'  => $teacherFound ?: ($isVacancy ? 'Вакансия' : $teacherName),
                'teacher_id'    => $teacherId,
                'total_hours'   => $totalHours,
                'course'        => $course,
                'status'        => $status,
                'result_index'  => $resultIndex,
                'warnings'      => array_filter([
                    !$groupId    ? "Группа «{$groupName}» не найдена в БД" : null,
                    !$subjectId  ? "Предмет «{$subjectName}» не найден" : null,
                    !$teacherId && !$isVacancy && $teacherName ? "Преподаватель «{$teacherName}» не найден" : null,
                ]),
            ];
        }

        return $preview;
    }

    private function buildTeachersPreview(array $rows): array
    {
        $preview = [];
        foreach ($rows as $row) {
            $name = trim($row['teacher_name'] ?? $row['name'] ?? '');
            if (!$name) {
                continue;
            }
            $existing = DB::table('teachers')
                ->whereRaw('LOWER(teacher_name) = ?', [mb_strtolower($name)])
                ->first();

            $preview[] = [
                'teacher_name' => $name,
                'initials'     => $row['initials'] ?? null,
                'status'       => $existing ? 'exists' : 'new',
            ];
        }

        return $preview;
    }

    // ─── Import ──────────────────────────────────────────────────────────────────

    private function importWorkload(array $rows, array $months, int $year, array &$log, array &$stats): void
    {
        $now = now();

        foreach ($rows as $row) {
            $groupId   = $row['group_id'] ?? null;
            $subjectId = $row['subject_id'] ?? null;
            $teacherId = $row['teacher_id'] ?? null;
            $hours     = (int) ($row['total_hours'] ?? 0);
            $course    = (int) ($row['course'] ?? 0);

            if (!$groupId || !$subjectId) {
                $log[] = "⚠ Пропущено: {$row['group_name']} — {$row['subject_name']} (нет ID)";
                $stats['skipped']++;
                continue;
            }

            $table = $this->normativesTable($course);

            foreach ($months as $month) {
                $existing = DB::table($table)
                    ->where('group_id', $groupId)
                    ->where('subject_id', $subjectId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                if ($existing) {
                    DB::table($table)->where('id', $existing->id)->update([
                        'total_hours' => $hours,
                        'teacher_id'  => $teacherId,
                        'updated_at'  => $now,
                    ]);
                    $stats['updated']++;
                } else {
                    DB::table($table)->insert([
                        'group_id'      => $groupId,
                        'subject_id'    => $subjectId,
                        'teacher_id'    => $teacherId,
                        'month'         => $month,
                        'year'          => $year,
                        'total_hours'   => $hours,
                        'hours_per_class' => 2,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);
                    $stats['inserted']++;
                }
            }

            $log[] = "✓ {$row['group_name']} — {$row['subject_name']}: {$hours}ч";
        }
    }

    private function importTeachers(array $rows, array &$log, array &$stats): void
    {
        $now = now();

        foreach ($rows as $row) {
            $name = trim($row['teacher_name'] ?? '');
            if (!$name) {
                continue;
            }

            $existing = DB::table('teachers')
                ->whereRaw('LOWER(teacher_name) = ?', [mb_strtolower($name)])
                ->first();

            if ($existing) {
                $log[] = "= Уже есть: {$name}";
                $stats['skipped']++;
            } else {
                DB::table('teachers')->insert([
                    'teacher_name' => $name,
                    'initials'     => $row['initials'] ?? null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $log[] = "✓ Добавлен: {$name}";
                $stats['inserted']++;
            }
        }
    }

    // ─── Ollama helpers ───────────────────────────────────────────────────────────

    private function checkOllamaStatus(): bool
    {
        $ch = curl_init("{$this->ollamaHost}/api/tags");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }

    private function getOllamaModels(): array
    {
        $ch = curl_init("{$this->ollamaHost}/api/tags");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return array_column($data['models'] ?? [], 'name');
    }

    private function callOllama(string $prompt): string
    {
        $model = request()->input('model', $this->ollamaModel);

        $ch = curl_init("{$this->ollamaHost}/api/generate");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'model'  => $model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature'    => 0.7,
                    'top_p'          => 0.9,
                    'top_k'          => 40,
                    'repeat_penalty' => 1.1,
                    'num_ctx'        => 3072,
                    'num_predict'    => 512,
                ],
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 600,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Ollama недоступна: {$error}");
        }

        $data = json_decode($response, true);
        return $data['response'] ?? '';
    }

    private function buildTeachersPrompt(string $text): string
    {
        return <<<PROMPT
Из текста ниже извлеки список преподавателей. Верни ТОЛЬКО JSON массив без пояснений.
Формат: [{"teacher_name": "Иванов Иван Иванович", "initials": "Иванов И.И."}]
Если инициалы неизвестны — null.

Текст:
{$text}

JSON:
PROMPT;
    }

    private function buildWorkloadPrompt(string $text): string
    {
        return <<<PROMPT
Из текста ниже извлеки учебную нагрузку. Верни ТОЛЬКО JSON массив без пояснений.
Формат: [{"group_name": "ИС-201", "subject_name": "Математика", "teacher_name": "Иванов И.И.", "total_hours": 72, "result_index": ""}]

Текст:
{$text}

JSON:
PROMPT;
    }

    // ─── DB helpers ───────────────────────────────────────────────────────────────

    private function loadGroupsCache(): array
    {
        $cache = [];
        $courseMap = [1 => 'first_course_group', 2 => 'second_course_group', 3 => 'third_course_group', 4 => 'fourth_course_group'];

        foreach ($courseMap as $course => $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }
            $groups = DB::table($table)->select('id', 'group_name')->get();
            foreach ($groups as $g) {
                $cache[$g->group_name] = ['id' => $g->id, 'course' => $course];
            }
        }

        return $cache;
    }

    private function loadTeachersCache(): array
    {
        $cache    = [];
        $teachers = DB::table('teachers')->select('id', 'teacher_name', 'initials')->get();
        foreach ($teachers as $t) {
            $key = $this->normalizeTeacher($t->teacher_name);
            if ($key) {
                $cache[$key] = $t->id;
            }
            if ($t->initials) {
                $cache[$this->normalizeTeacher($t->initials)] = $t->id;
            }
        }

        return $cache;
    }

    private function findSubjectByIndex(string $index, int $course): ?int
    {
        $index = trim($index);
        if (!preg_match('/^(РО|ОН|ПМ)\s+([^\s]+)/u', $index, $m)) {
            return null;
        }
        $normalized = strtoupper($m[1]) . ' ' . rtrim($m[2], '.');
        $table      = $this->subjectsTable($course);

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        $subject = DB::table($table)
            ->where('subject_name', 'LIKE', "{$normalized}%")
            ->first();

        return $subject?->id;
    }

    private function findSubjectByName(string $name, int $course): ?int
    {
        if (!$name) {
            return null;
        }
        $table = $this->subjectsTable($course);

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        $subject = DB::table($table)
            ->whereRaw('LOWER(subject_name) LIKE ?', ['%' . mb_strtolower($name) . '%'])
            ->first();

        return $subject?->id;
    }

    private function getSubjectName(int $id, int $course): string
    {
        $table = $this->subjectsTable($course);
        return DB::table($table)->where('id', $id)->value('subject_name') ?? '';
    }

    private function getTeacherName(int $id): string
    {
        return DB::table('teachers')->where('id', $id)->value('teacher_name') ?? '';
    }

    private function normalizeTeacher(string $name): string
    {
        $name = preg_replace('/[\/\\\\].*/u', '', $name);
        $name = preg_replace('/^практика\s*/ui', '', $name);
        return mb_strtolower(trim($name));
    }

    private function detectCourse(string $groupName): ?int
    {
        if (preg_match('/-(1\d{2})/', $groupName)) {
            return 1;
        }
        if (preg_match('/-(2\d{2})/', $groupName)) {
            return 2;
        }
        if (preg_match('/-(3\d{2})/', $groupName)) {
            return 3;
        }
        if (preg_match('/-(4\d{2})/', $groupName)) {
            return 4;
        }

        return null;
    }

    private function subjectsTable(int $course): string
    {
        return match ($course) {
            1 => 'first_course_subjects',
            2 => 'second_course_subjects',
            3 => 'third_course_subjects',
            4 => 'fourth_course_subjects',
            default => 'first_course_subjects',
        };
    }

    private function normativesTable(int $course): string
    {
        return match ($course) {
            1 => 'form_two_normatives',
            2 => 'second_form_two_normatives',
            3 => 'third_form_two_normatives',
            4 => 'fourth_form_two_normatives',
            default => 'form_two_normatives',
        };
    }
}
