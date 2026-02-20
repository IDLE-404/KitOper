@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Пользователи</h1>
            <div class="text-muted">Управление доступами</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label">Поиск</label>
                    <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Имя или email">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Роль</label>
                    <select class="form-select" name="role">
                        <option value="">Все</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
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
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'dispatcher' ? 'primary' : ($user->role === 'teacher' ? 'info' : 'secondary') }}">
                                    {{ $roles[$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('users.update_role', $user->id) }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PUT')
                                    <select class="form-select form-select-sm" name="role">
                                        @foreach($roles as $value => $label)
                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Сохранить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Нет пользователей</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
@endsection
