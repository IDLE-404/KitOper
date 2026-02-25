@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Журнал изменений</h1>
            <div class="text-muted">Фиксируются все изменения по данным и расписанию</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Пользователь</label>
                    <select class="form-select" name="user_id">
                        <option value="">Все</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Метод</label>
                    <select class="form-select" name="method">
                        <option value="">Все</option>
                        @foreach(['POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                            <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Маршрут</label>
                    <input class="form-control" name="route" value="{{ $filters['route'] ?? '' }}" placeholder="route.name">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">От</label>
                    <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">До</label>
                    <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Поиск</label>
                    <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Путь, IP, маршрут">
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Фильтр</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                            <td class="text-nowrap">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->user->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $log->user->email ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $labels[$log->id] ?? '—' }}</div>
                                <div class="text-muted small">{{ $log->route_name ?? '—' }}</div>
                            </td>
                            <td class="text-muted">{{ $log->path }}</td>
                            <td>
                                <span class="badge bg-{{ $log->status_code < 400 ? 'success' : 'danger' }}">{{ $log->status_code }}</span>
                                <div class="text-muted small">{{ $log->duration_ms }} мс</div>
                            </td>
                            <td>
                                @if($log->payload)
                                    <details>
                                        <summary>Показать</summary>
                                        <pre class="small text-muted mb-0">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Записей нет</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if ($logs->hasPages())
                <nav aria-label="Пагинация журнала изменений">
                    <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">
                        <li class="page-item {{ $logs->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $logs->previousPageUrl() ?: '#' }}" tabindex="{{ $logs->onFirstPage() ? '-1' : '0' }}" aria-disabled="{{ $logs->onFirstPage() ? 'true' : 'false' }}">Назад</a>
                        </li>

                        @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $logs->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item {{ $logs->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $logs->nextPageUrl() ?: '#' }}" tabindex="{{ $logs->hasMorePages() ? '0' : '-1' }}" aria-disabled="{{ $logs->hasMorePages() ? 'false' : 'true' }}">Вперёд</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endsection
