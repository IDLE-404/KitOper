@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Журнал изменений</h1>
        <p class="page-subtitle">Фиксируются все изменения по данным и расписанию</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <form method="POST" action="{{ route('audit_logs.clear') }}"
              onsubmit="return confirm('Удалить старые записи журнала?')">
            @csrf
            <select name="days" class="field-input" style="width:auto;display:inline-block">
                <option value="30">Старше 30 дней</option>
                <option value="90">Старше 90 дней</option>
                <option value="180">Старше 180 дней</option>
                <option value="0">Весь журнал</option>
            </select>
            <button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;margin-left:6px" type="submit">
                Очистить
            </button>
        </form>
    </div>
</div>

<div class="surface surface-p" style="margin-bottom:16px">
    <form method="GET" class="form-row">
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Пользователь</label>
                <select class="field-input" name="user_id">
                    <option value="">Все</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Метод</label>
                <select class="field-input" name="method">
                    <option value="">Все</option>
                    @foreach(['POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Маршрут</label>
                <input class="field-input" name="route" value="{{ $filters['route'] ?? '' }}" placeholder="route.name">
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">От</label>
                <input type="date" class="field-input" name="from" value="{{ $filters['from'] ?? '' }}">
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">До</label>
                <input type="date" class="field-input" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Поиск</label>
                <input class="field-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Путь, IP, маршрут">
            </div>
        </div>
        <div class="form-field-auto" style="align-self:flex-end">
            <button class="btn btn-primary" type="submit">Найти</button>
        </div>
    </form>
</div>

<div class="surface">
    <div style="overflow-x:auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пользователь</th>
                    <th>Событие</th>
                    <th>Путь</th>
                    <th>Статус</th>
                    <th>Детали</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="td-muted" style="white-space:nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <div style="font-weight:600">{{ $log->user->name ?? '—' }}</div>
                            <div class="td-muted">{{ $log->user->email ?? '' }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600">{{ $labels[$log->id] ?? '—' }}</div>
                            <div class="td-muted">{{ $log->route_name ?? '—' }}</div>
                        </td>
                        <td class="td-muted">{{ $log->path }}</td>
                        <td>
                            <span class="app-badge {{ $log->status_code < 400 ? 'app-badge-success' : 'app-badge-danger' }}">{{ $log->status_code }}</span>
                            <div class="td-muted" style="margin-top:2px">{{ $log->duration_ms }} мс</div>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
                                @if($log->payload)
                                    <details>
                                        <summary style="cursor:pointer;color:var(--c-primary);font-size:12px">► Показать</summary>
                                        <pre style="font-size:11px;color:var(--c-text-2);margin:6px 0 0;white-space:pre-wrap;word-break:break-all">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    <span class="td-muted">—</span>
                                @endif

                                @if($log->route_name === 'schedule.generate.store' && $log->status_code < 400)
                                    <form method="POST"
                                          action="{{ route('audit_logs.rollback', $log->id) }}"
                                          onsubmit="return confirm('Удалить сгенерированное расписание для этой записи?')">
                                        @csrf
                                        <button type="submit"
                                                style="font-size:11px;padding:2px 8px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;cursor:pointer">
                                            ↩ Откатить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-clipboard-data"></i>
                                <div class="empty-state-title">Записей нет</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="surface-p" style="padding-top:12px;border-top:1px solid var(--c-border)">
            <nav aria-label="Пагинация">
                <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">
                    <li class="page-item {{ $logs->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $logs->previousPageUrl() ?: '#' }}">Назад</a>
                    </li>
                    @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                        <li class="page-item {{ $page === $logs->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ $logs->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $logs->nextPageUrl() ?: '#' }}">Вперёд</a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>
@endsection
