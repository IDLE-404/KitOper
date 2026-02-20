@extends('layouts.guest')

@section('content')
    @php($selectedRole = old('role', 'student'))
    <h1 class="auth-title">Вход</h1>
    <div class="auth-subtitle">Авторизуйтесь и выберите нужный тип аккаунта</div>
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label auth-label">Тип аккаунта</label>
            <div class="role-switch" role="group" aria-label="Тип аккаунта">
                <input type="radio" class="btn-check" name="role" id="login_role_student" value="student" autocomplete="off" @checked($selectedRole === 'student')>
                <label class="role-option" for="login_role_student">1 Ученик</label>

                <input type="radio" class="btn-check" name="role" id="login_role_teacher" value="teacher" autocomplete="off" @checked($selectedRole === 'teacher')>
                <label class="role-option" for="login_role_teacher">2 Учитель</label>

                <input type="radio" class="btn-check" name="role" id="login_role_dispatcher" value="dispatcher" autocomplete="off" @checked($selectedRole === 'dispatcher')>
                <label class="role-option" for="login_role_dispatcher">3 Диспетчер</label>
            </div>
            @error('role')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Email</label>
            <input type="email" name="email" class="form-control auth-input" value="{{ old('email') }}" required autofocus>
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
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Запомнить</label>
        </div>
        <button class="btn btn-primary w-100 auth-submit" type="submit">Войти</button>
    </form>
    <div class="text-center mt-3">
        <a class="auth-link" href="{{ route('register') }}">Нет аккаунта? Зарегистрироваться</a>
    </div>
@endsection
