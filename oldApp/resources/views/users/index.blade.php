@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Пользователи</h1>
        <p class="page-subtitle">Управление доступами</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modalCreateUser').showModal()">
        <i class="bi bi-person-plus"></i> Создать пользователя
    </button>
</div>

@if(session('success'))
    <div style="margin-bottom:16px;padding:12px 16px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:10px;font-size:14px">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if($errors->has('delete'))
    <div style="margin-bottom:16px;padding:12px 16px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:10px;font-size:14px">
        <i class="bi bi-exclamation-circle"></i> {{ $errors->first('delete') }}
    </div>
@endif

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

<div class="surface" style="width:100%">
    <div style="overflow-x:auto;width:100%">
        <table class="app-table" style="width:100%">
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Группа</th>
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
                                    'teacher'    => 'app-badge-success',
                                    default      => 'app-badge-neutral'
                                };
                            @endphp
                            <span class="app-badge {{ $roleClass }}">{{ $roles[$user->role] ?? $user->role }}</span>
                        </td>
                        <td class="td-muted">
                            @if($user->role === 'student' && $user->group_id && $user->group_course)
                                @php
                                    $gTable = match((int)$user->group_course) {
                                        1 => 'first_course_group', 2 => 'second_course_group',
                                        3 => 'third_course_group', 4 => 'fourth_course_group',
                                        default => null
                                    };
                                    $gName = $gTable ? \DB::table($gTable)->where('id',$user->group_id)->value('group_name') : null;
                                @endphp
                                {{ $gName ?? '—' }} ({{ $user->group_course }} курс)
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('users.update_role', $user->id) }}"
                                  style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                @csrf
                                @method('PUT')
                                <select class="field-input" name="role" style="width:auto" onchange="toggleGroupSelect(this)">
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select class="field-input group-select" name="group_key" style="width:auto;{{ $user->role !== 'student' ? 'display:none' : '' }}">
                                    <option value="">— Группа —</option>
                                    @foreach($groups as $course => $courseGroups)
                                        <optgroup label="{{ $course }} курс">
                                            @foreach($courseGroups as $group)
                                                <option value="{{ $course }}:{{ $group->id }}"
                                                    @selected($user->group_id == $group->id && $user->group_course == $course)>
                                                    {{ $group->group_name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button class="btn btn-secondary btn-sm" type="submit">Сохранить</button>

                                @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-sm"
                                        style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5"
                                        onclick="
                                            if(confirm('Удалить пользователя {{ addslashes($user->name) }}?')) {
                                                this.closest('form').querySelector('[name=_method]').value='DELETE';
                                                this.closest('form').action='{{ route('users.destroy', $user->id) }}';
                                                this.closest('form').submit();
                                            }
                                        ">Удалить</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
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

{{-- Модалка создания пользователя --}}
<dialog id="modalCreateUser" style="border:none;border-radius:16px;padding:0;box-shadow:0 20px 60px rgba(0,0,0,0.18);width:100%;max-width:460px;background:#fff">
    <div style="padding:24px 28px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h2 style="font-size:18px;font-weight:700;margin:0">Создать пользователя</h2>
            <button type="button" onclick="document.getElementById('modalCreateUser').close()"
                style="background:none;border:none;cursor:pointer;color:var(--c-text-2);font-size:20px;line-height:1;padding:0">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="field-group">
                    <label class="field-label">Имя</label>
                    <input class="field-input" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Иван Иванов">
                    @error('name')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input class="field-input" type="email" name="email" value="{{ old('email') }}" required placeholder="ivan@example.com">
                    @error('email')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label class="field-label">Роль</label>
                    <select class="field-input" name="role" id="modalRole" required onchange="document.getElementById('modalGroupWrap').style.display=this.value==='student'?'block':'none'">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', 'student') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field-group" id="modalGroupWrap">
                    <label class="field-label">Группа</label>
                    <select class="field-input" name="group_key">
                        <option value="">— Группа —</option>
                        @foreach($groups as $course => $courseGroups)
                            <optgroup label="{{ $course }} курс">
                                @foreach($courseGroups as $group)
                                    <option value="{{ $course }}:{{ $group->id }}"
                                        @selected(old('group_key') === "{$course}:{$group->id}")>
                                        {{ $group->group_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('group_key')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label class="field-label">Пароль</label>
                    <input class="field-input" type="password" name="password" required placeholder="Минимум 8 символов">
                    @error('password')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field-group">
                    <label class="field-label">Подтверждение пароля</label>
                    <input class="field-input" type="password" name="password_confirmation" required>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px">
                <button class="btn btn-primary" type="submit" style="flex:1">Создать</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalCreateUser').close()" style="flex:1">Отмена</button>
            </div>
        </form>
    </div>
</dialog>

<script>
function toggleGroupSelect(roleSelect) {
    const row = roleSelect.closest('form');
    const groupSelect = row.querySelector('.group-select');
    if (groupSelect) {
        groupSelect.style.display = roleSelect.value === 'student' ? '' : 'none';
    }
}

// Открыть модалку если были ошибки валидации
@if($errors->hasAny(['name','email','role','password','group_id']))
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('modalCreateUser').showModal();
    });
@endif

document.getElementById('modalCreateUser').addEventListener('click', function(e) {
    if (e.target === this) this.close();
});

// Показать/скрыть поле группы при смене роли в модалке
document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('modalRole');
    const groupWrap  = document.getElementById('modalGroupWrap');
    if (roleSelect && groupWrap) {
        groupWrap.style.display = roleSelect.value === 'student' ? 'block' : 'none';
        roleSelect.addEventListener('change', () => {
            groupWrap.style.display = roleSelect.value === 'student' ? 'block' : 'none';
        });
    }
});
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/tours/users.js') }}"></script>
@endpush
