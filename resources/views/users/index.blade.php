@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Пользователи</h1>
        <p class="page-subtitle">Управление доступами</p>
    </div>
</div>

<div class="surface surface-p" style="margin-bottom:16px">
    <form method="GET" class="form-row">
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Поиск</label>
                <input class="field-input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Имя или email">
            </div>
        </div>
        <div class="form-field">
            <div class="field-group">
                <label class="field-label">Роль</label>
                <select class="field-input" name="role">
                    <option value="">Все</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
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
                    <th>Пользователь</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="font-weight:600">{{ $user->name }}</td>
                        <td class="td-muted">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleClass = match($user->role) {
                                    'dispatcher' => 'app-badge-primary',
                                    'teacher' => 'app-badge-success',
                                    default => 'app-badge-neutral'
                                };
                            @endphp
                            <span class="app-badge {{ $roleClass }}">{{ $roles[$user->role] ?? $user->role }}</span>
                        </td>
                        <td>
                            <div style="display:flex;gap:8px;align-items:center">
                                <form method="POST" action="{{ route('users.update_role', $user->id) }}" style="display:flex;gap:8px;align-items:center">
                                    @csrf
                                    @method('PUT')
                                    <select class="field-input" name="role" style="width:auto">
                                        @foreach($roles as $value => $label)
                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-secondary btn-sm" type="submit">Сохранить</button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                                      onsubmit="return confirm('Удалить пользователя {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5" type="submit">Удалить</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-person-gear"></i>
                                <div class="empty-state-title">Нет пользователей</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="surface-p" style="padding-top:12px;border-top:1px solid var(--c-border)">
        {{ $users->links() }}
    </div>
</div>
@endsection
