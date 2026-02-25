<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }
        if ($request->filled('route')) {
            $query->where('route_name', $request->input('route'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }
        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('path', 'like', "%{$term}%")
                    ->orWhere('route_name', 'like', "%{$term}%")
                    ->orWhere('ip', 'like', "%{$term}%");
            });
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        $labels = [];
        foreach ($logs as $log) {
            $labels[$log->id] = $this->humanLabel($log->route_name, $log->method, $log->payload ?? []);
        }

        return view('audit_logs.index', [
            'logs' => $logs,
            'users' => $users,
            'labels' => $labels,
            'filters' => $request->only(['user_id', 'method', 'route', 'from', 'to', 'q']),
        ]);
    }

    private function humanLabel(?string $routeName, string $method, array $payload): string
    {
        $map = [
            'practice.store' => 'Добавлена практика',
            'practice.destroy' => 'Удалена практика',
            'field_camps.store' => 'Добавлены полевые сборы',
            'field_camps.destroy' => 'Удалены полевые сборы',
            'holidays.store' => 'Добавлен праздник',
            'holidays.update' => 'Изменён праздник',
            'holidays.destroy' => 'Удалён праздник',
            'rooms.store' => 'Добавлена аудитория',
            'rooms.update' => 'Изменена аудитория',
            'rooms.destroy' => 'Удалена аудитория',
            'teacher_absences.store' => 'Добавлено отсутствие преподавателя',
            'teacher_absences.update' => 'Изменено отсутствие преподавателя',
            'teacher_absences.destroy' => 'Удалено отсутствие преподавателя',
            'first.schedule.week.save' => 'Сохранена неделя расписания',
            'first.schedule.semester.expand' => 'Расписание развернуто на семестр',
            'first.schedule.week.duplicate.store' => 'Выполнен дубликат недели',
            'first.schedule.pair.update' => 'Изменена пара',
            'first.schedule.pair.delete' => 'Удалена пара',
            'first.schedule.auto_assign_rooms_day' => 'Автоназначение кабинетов',
            'first.schedule.clear_rooms_day' => 'Очистка кабинетов',
            'first.schedule.form_two.save' => 'Сохранены корректировки формы 2',
            'form_two_templates.store' => 'Создан шаблон Ф2',
            'form_two_templates.update' => 'Изменён шаблон Ф2',
            'form_two_templates.destroy' => 'Удалён шаблон Ф2',
            'form_two_templates.items.store' => 'Добавлен пункт шаблона Ф2',
            'form_two_templates.items.update' => 'Изменён пункт шаблона Ф2',
            'form_two_templates.items.destroy' => 'Удалён пункт шаблона Ф2',
            'teachers.store' => 'Добавлен преподаватель',
            'teachers.update' => 'Изменён преподаватель',
            'teachers.destroy' => 'Удалён преподаватель',
            'groups.store' => 'Добавлена группа',
            'groups.update' => 'Изменена группа',
            'groups.destroy' => 'Удалена группа',
            'groups.finish_year' => 'Завершён учебный год группы',
            'subjects.store' => 'Добавлена дисциплина',
            'subjects.update' => 'Изменена дисциплина',
            'subjects.destroy' => 'Удалена дисциплина',
            'users.update_role' => 'Изменена роль пользователя',
        ];

        $label = $map[$routeName] ?? null;
        if (!$label) {
            $label = $method . ' ' . ($routeName ?: 'route');
        }

        $detail = $this->payloadDetail($payload);
        if ($detail) {
            $label .= ' (' . $detail . ')';
        }

        return $label;
    }

    private function payloadDetail(array $payload): ?string
    {
        $keys = ['name', 'title', 'subject_name', 'name_ru', 'group_name', 'room_id', 'teacher_id', 'email'];
        foreach ($keys as $key) {
            if (!empty($payload[$key])) {
                return (string) $payload[$key];
            }
        }
        if (!empty($payload['start_date']) || !empty($payload['end_date'])) {
            $start = $payload['start_date'] ?? '';
            $end = $payload['end_date'] ?? '';
            return trim("{$start} - {$end}");
        }

        return null;
    }
}
