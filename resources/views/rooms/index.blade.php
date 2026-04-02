@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="{{ asset('css/schedule/main.css') }}">
<style>
    .panel-card {
        background: var(--panel);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid #ecf0f6;
        padding: 18px 20px;
    }
    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 12px;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    .form-field {
        flex: 1 1 200px;
        min-width: 180px;
    }
    .form-field label {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
        display: inline-block;
    }
    .form-field--actions {
        flex: 0 0 auto;
    }
    .table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }
    .table thead th {
        color: var(--muted);
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #e6ebf2;
    }
    .empty-note {
        color: var(--muted);
        font-size: 14px;
        padding: 16px 0;
        text-align: center;
    }
    .room-type {
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 12px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .room-type.computer {
        background: #e0f2fe;
        color: #0369a1;
    }
    .room-type.lab {
        background: #fef3c7;
        color: #92400e;
    }
    .room-type.standard {
        background: #e2e8f0;
        color: #334155;
    }
</style>
@endpush

@section('content')
<div class="schedule-shell compact">
    <div class="header-row">
        <div>
            <h1 class="page-title">Кабинеты</h1>
            <p class="page-subtitle">Справочник кабинетов и типов</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('first.schedule.index') }}" class="btn-pill ghost">К расписанию</a>
            <a href="{{ route('teacher_absences.index') }}" class="btn-pill ghost">Отсутствия преподавателей</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-card mb-4">
        <div class="panel-title">Добавить кабинет</div>
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-field">
                    <label for="roomCode">Кабинет</label>
                    <input id="roomCode" name="code" class="search-input w-100" required value="{{ old('code') }}" placeholder="Например: 301">
                </div>
                <div class="form-field">
                    <label for="roomType">Тип</label>
                    <select id="roomType" name="type" class="search-input w-100">
                        @foreach($roomTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', 'standard') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!empty($hasIsActive))
                    <div class="form-field">
                        <label class="d-block">Статус</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="roomActive" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="roomActive">Активный</label>
                        </div>
                    </div>
                @endif
                <div class="form-field form-field--actions">
                    <button type="submit" class="btn-pill primary">Добавить</button>
                </div>
            </div>
        </form>
        <div class="mt-3">
            <input type="search" id="roomSearch" class="search-input w-100" placeholder="Поиск по кабинету">
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Список кабинетов</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="roomsTable">
                <thead>
                    <tr>
                        <th>Кабинет</th>
                        <th>Тип</th>
                        @if(!empty($hasIsActive))
                            <th>Статус</th>
                        @endif
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                        <tr data-code="{{ mb_strtolower($room->code ?? '') }}">
                            <td class="fw-semibold">{{ $room->code }}</td>
                            <td>
                                <span class="room-type {{ $room->type }}">{{ $roomTypes[$room->type] ?? $room->type }}</span>
                            </td>
                            @if(!empty($hasIsActive))
                                <td>
                                    @php $isActive = (bool) ($room->is_active ?? true); @endphp
                                    <span class="badge {{ $isActive ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $isActive ? 'Активный' : 'Скрыт' }}
                                    </span>
                                </td>
                            @endif
                            <td class="text-end">
                                <div class="table-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoom{{ $room->id }}">Редактировать</button>
                                    <form method="POST" action="{{ route('rooms.destroy', $room->id) }}" onsubmit="return confirm('Удалить кабинет?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-note">Пока нет кабинетов.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($rooms as $room)
    <div class="modal fade" id="editRoom{{ $room->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('rooms.update', $room->id) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать кабинет</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Кабинет</label>
                            <input name="code" class="form-control" value="{{ $room->code }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Тип</label>
                            <select name="type" class="form-select">
                                @foreach($roomTypes as $key => $label)
                                    <option value="{{ $key }}" @selected($room->type === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(!empty($hasIsActive))
                            <div class="col-md-4">
                                <label class="form-label d-block">Статус</label>
                                <div class="form-check form-switch mt-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="roomActive{{ $room->id }}"
                                        name="is_active"
                                        value="1"
                                        @checked((bool) ($room->is_active ?? true))
                                    >
                                    <label class="form-check-label" for="roomActive{{ $room->id }}">Активный</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    const roomSearch = document.getElementById('roomSearch');
    const roomsTable = document.getElementById('roomsTable');

    roomSearch?.addEventListener('input', () => {
        const term = roomSearch.value.trim().toLowerCase();
        roomsTable?.querySelectorAll('tbody tr').forEach((row) => {
            const code = row.dataset.code || '';
            row.style.display = code.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection
