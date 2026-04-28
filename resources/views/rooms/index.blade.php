@extends('layouts.app')
@push('styles')
<style>
    .room-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 500;
        background: var(--c-surface-2);
        color: var(--c-text-2);
        border: 1px solid var(--c-border);
    }
    .room-badge.computer { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .room-badge.lab { background: #fef3c7; color: #92400e; border-color: #fde68a; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Кабинеты</h1>
        <p class="page-subtitle">Справочник кабинетов и типов</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('first.schedule.index') }}" class="btn btn-secondary">К расписанию</a>
        <a href="{{ route('teacher_absences.index') }}" class="btn btn-secondary">Отсутствия</a>
    </div>
</div>

@if($errors->any())
    <div class="app-alert app-alert-danger">
        <i class="bi bi-exclamation-circle"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
@endif

<div class="surface surface-p" style="margin-bottom:16px">
    <h2 class="section-title">Добавить кабинет</h2>
    <form method="POST" action="{{ route('rooms.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label" for="roomCode">Кабинет</label>
                    <input id="roomCode" name="code" class="field-input" required value="{{ old('code') }}" placeholder="301">
                </div>
            </div>
            <div class="form-field">
                <div class="field-group">
                    <label class="field-label" for="roomType">Тип</label>
                    <select id="roomType" name="type" class="field-input">
                        @foreach($roomTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', 'standard') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(!empty($hasIsActive))
                <div class="form-field">
                    <div class="field-group">
                        <label class="field-label">Статус</label>
                        <div class="form-check form-switch" style="margin-top:6px">
                            <input class="form-check-input" type="checkbox" role="switch" id="roomActive" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="roomActive" style="font-size:13px">Активный</label>
                        </div>
                    </div>
                </div>
            @endif
            <div class="form-field-auto" style="align-self:flex-end">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </form>
    <div style="margin-top:12px">
        <input type="search" id="roomSearch" class="field-input" placeholder="Поиск по кабинету">
    </div>
</div>

<div class="surface">
    <div class="surface-p" style="padding-bottom:12px">
        <h2 class="section-title" style="margin-bottom:0">Список кабинетов</h2>
    </div>
    <div style="overflow-x:auto">
        <table class="app-table" id="roomsTable">
            <thead>
                <tr>
                    <th>Кабинет</th>
                    <th>Тип</th>
                    @if(!empty($hasIsActive))
                        <th>Статус</th>
                    @endif
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr data-code="{{ mb_strtolower($room->code ?? '') }}">
                        <td style="font-weight:600">{{ $room->code }}</td>
                        <td><span class="room-badge {{ $room->type }}">{{ $roomTypes[$room->type] ?? $room->type }}</span></td>
                        @if(!empty($hasIsActive))
                            <td>
                                @php $isActive = (bool) ($room->is_active ?? true); @endphp
                                <span class="app-badge {{ $isActive ? 'app-badge-success' : 'app-badge-neutral' }}">
                                    {{ $isActive ? 'Активный' : 'Скрыт' }}
                                </span>
                            </td>
                        @endif
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoom{{ $room->id }}">Изменить</button>
                                <form method="POST" action="{{ route('rooms.destroy', $room->id) }}" onsubmit="return confirm('Удалить кабинет?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-building"></i>
                                <div class="empty-state-title">Пока нет кабинетов</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="roomActive{{ $room->id }}" name="is_active" value="1" @checked((bool) ($room->is_active ?? true))>
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
@endsection

@push('scripts')
<script>
    const roomSearch = document.getElementById('roomSearch');
    roomSearch?.addEventListener('input', () => {
        const term = roomSearch.value.trim().toLowerCase();
        document.querySelectorAll('#roomsTable tbody tr').forEach(row => {
            row.style.display = (row.dataset.code || '').includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
