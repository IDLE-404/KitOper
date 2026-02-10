@extends('layouts.guest')

@section('content')
    <h1 class="h4 mb-3">Вход</h1>
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Пароль</label>
            <input type="password" name="password" class="form-control" required>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Запомнить</label>
        </div>
        <button class="btn btn-primary w-100" type="submit">Войти</button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('register') }}">Нет аккаунта? Зарегистрироваться</a>
    </div>
@endsection
