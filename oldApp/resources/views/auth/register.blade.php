@extends('layouts.guest')

@section('content')
    <h1 class="auth-title">Регистрация</h1>
    <div class="auth-subtitle">Создайте аккаунт ученика</div>
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label auth-label">Имя</label>
            <input type="text" name="name" class="form-control auth-input" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Группа</label>
            <select name="group_key" class="form-control auth-input" required>
                <option value="">— Выберите группу —</option>
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
            @error('group_key')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Email</label>
            <input type="email" name="email" class="form-control auth-input" value="{{ old('email') }}" required>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Пароль</label>
            <input type="password" name="password" class="form-control auth-input" required>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Подтверждение пароля</label>
            <input type="password" name="password_confirmation" class="form-control auth-input" required>
        </div>
        <button class="btn btn-primary w-100 auth-submit" type="submit">Создать аккаунт</button>
    </form>
    <div class="text-center mt-3">
        <a class="auth-link" href="{{ route('login') }}">Уже есть аккаунт? Войти</a>
    </div>
@endsection
