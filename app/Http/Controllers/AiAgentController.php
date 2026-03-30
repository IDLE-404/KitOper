<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AiAgentController extends Controller
{
    private string $ollamaHost;
    private string $ollamaModel;

    public function __construct()
    {
        $this->ollamaHost = env('OLLAMA_HOST', 'http://ollama:11434');
        $this->ollamaModel = env('OLLAMA_MODEL', 'llama3.2:3b');
    }

    public function index()
    {
        $ollamaStatus = $this->checkOllamaStatus();
        $ollamaModels = $ollamaStatus ? $this->getOllamaModels() : [];

        return view('ai_agent.index', compact('ollamaStatus', 'ollamaModels'));
    }

    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $message = trim($request->input('message'));
        $history = $request->input('history', []);

        if (!$this->checkOllamaStatus()) {
            return response()->json([
                'success' => false,
                'error'   => 'Ollama недоступна. Проверьте что контейнер it-ollama запущен.',
            ], 503);
        }

        try {
            $systemPrompt = $this->buildSystemPrompt();
            $result       = $this->processMessage($message, $history, $systemPrompt);

            return response()->json(['success' => true, 'reply' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function processMessage(string $message, array $history, string $systemPrompt): string
    {
        // Build conversation context
        $context = '';
        foreach (array_slice($history, -6) as $msg) {
            $role     = $msg['role'] === 'user' ? 'Пользователь' : 'Ассистент';
            $context .= "{$role}: {$msg['content']}\n";
        }

        $fullPrompt = $systemPrompt . "\n\n";
        if ($context) {
            $fullPrompt .= "История диалога:\n{$context}\n";
        }
        $fullPrompt .= "Пользователь: {$message}\nАссистент:";

        $raw = $this->callOllama($fullPrompt);

        // Check if the response contains a DB action
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $raw, $m)) {
            return $this->executeDbAction(json_decode($m[1], true), $raw);
        }
        if (preg_match('/\{"action".*?\}/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            if ($decoded && isset($decoded['action'])) {
                return $this->executeDbAction($decoded, $raw);
            }
        }

        return trim($raw);
    }

    private function executeDbAction(array $action, string $rawResponse): string
    {
        $act   = $action['action'] ?? '';
        $table = $action['table'] ?? '';
        $where = $action['where'] ?? [];
        $data  = $action['data'] ?? [];
        $limit = $action['limit'] ?? 50;

        $allowedTables = [
            'teachers', 'first_course_group', 'second_course_group', 'third_course_group', 'fourth_course_group',
            'first_course_subjects', 'second_course_subjects', 'third_course_subjects', 'fourth_course_subjects',
            'form_two_normatives', 'second_form_two_normatives', 'third_form_two_normatives', 'fourth_form_two_normatives',
            'holidays', 'rooms', 'teacher_absences', 'practice_periods', 'users',
        ];

        if (!in_array($table, $allowedTables)) {
            return "Таблица «{$table}» не разрешена для изменений.";
        }

        try {
            switch ($act) {
                case 'select':
                    return $this->dbSelect($table, $where, $data, (int) $limit);

                case 'insert':
                    $data['created_at'] = $data['updated_at'] = now();
                    DB::table($table)->insert($data);
                    return "✓ Запись добавлена в таблицу «{$table}».";

                case 'update':
                    if (empty($where)) {
                        return "Ошибка: для обновления нужно указать условие (where).";
                    }
                    $q = DB::table($table);
                    foreach ($where as $col => $val) {
                        $q->where($col, $val);
                    }
                    $data['updated_at'] = now();
                    $count = $q->update($data);
                    return "✓ Обновлено записей: {$count}.";

                case 'delete':
                    if (empty($where)) {
                        return "Ошибка: для удаления нужно условие (where).";
                    }
                    $q = DB::table($table);
                    foreach ($where as $col => $val) {
                        $q->where($col, $val);
                    }
                    $count = $q->delete();
                    return "✓ Удалено записей: {$count}.";

                default:
                    return trim(preg_replace('/```json.*?```/s', '', $rawResponse));
            }
        } catch (\Exception $e) {
            return "Ошибка при работе с БД: " . $e->getMessage();
        }
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

        if ($columns) {
            $q->select($columns);
        }

        $rows = $q->limit(min($limit, 100))->get();

        if ($rows->isEmpty()) {
            return "Записей не найдено.";
        }

        // Format as markdown table
        $cols    = array_keys((array) $rows->first());
        $header  = '| ' . implode(' | ', $cols) . ' |';
        $divider = '| ' . implode(' | ', array_fill(0, count($cols), '---')) . ' |';
        $lines   = [$header, $divider];

        foreach ($rows as $row) {
            $cells   = array_map(fn($v) => $v ?? '—', array_values((array) $row));
            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        return implode("\n", $lines) . "\n\nВсего записей: " . $rows->count();
    }

    private function buildSystemPrompt(): string
    {
        $teachers = DB::table('teachers')->count();
        $groups1  = DB::table('first_course_group')->count();
        $groups2  = DB::table('second_course_group')->count();
        $groups3  = DB::table('third_course_group')->count();

        return <<<PROMPT
Ты — умный ассистент системы KitOper (управление учебным расписанием). Ты разговариваешь на русском языке.

У тебя есть доступ к базе данных. Когда пользователь просит выполнить действие с данными, ты ДОЛЖЕН ответить JSON-блоком в формате:

```json
{"action": "select|insert|update|delete", "table": "имя_таблицы", "where": {}, "data": [], "limit": 20}
```

Доступные таблицы:
- teachers: id, teacher_name, initials (преподаватели, сейчас {$teachers} записей)
- first_course_group: id, group_name, group_number (группы 1 курса, {$groups1} записей)
- second_course_group: id, group_name, group_number (группы 2 курса, {$groups2} записей)
- third_course_group: id, group_name, group_number (группы 3 курса, {$groups3} записей)
- fourth_course_group: id, group_name, group_number (группы 4 курса)
- first_course_subjects: id, subject_name (дисциплины 1 курса)
- second_course_subjects: id, subject_name (дисциплины 2 курса)
- third_course_subjects: id, subject_name (дисциплины 3 курса)
- form_two_normatives: id, group_id, subject_id, teacher_id, month, year, total_hours (нагрузка 1 курс)
- second_form_two_normatives: (нагрузка 2 курс, те же поля)
- third_form_two_normatives: (нагрузка 3 курс, те же поля)
- holidays: id, name, start_date, end_date (праздники)
- rooms: id, code, title, room_type (аудитории)
- teacher_absences: id, teacher_id, absence_type, start_date, end_date (отсутствия)

Примеры:
- "покажи всех преподавателей" → {"action":"select","table":"teachers","where":{},"data":["id","teacher_name","initials"],"limit":50}
- "найди учителя Иванов" → {"action":"select","table":"teachers","where":{"teacher_name":"%Иванов%"},"data":["id","teacher_name","initials"],"limit":10}
- "переименуй учителя с id 5 в Петров Иван" → {"action":"update","table":"teachers","where":{"id":5},"data":{"teacher_name":"Петров Иван"},"limit":1}
- "удали учителя с id 99" → {"action":"delete","table":"teachers","where":{"id":99},"limit":1}

Если пользователь просто задаёт вопрос без операций с БД — отвечай текстом без JSON.
Всегда отвечай кратко и по-русски.
PROMPT;
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
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
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
