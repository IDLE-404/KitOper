@extends('layouts.guest')

@section('content')
    @php($selectedRole = old('role', 'student'))
    <h1 class="auth-title">Регистрация</h1>
    <div class="auth-subtitle">Создайте аккаунт и сразу назначьте роль</div>
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label auth-label">Тип аккаунта</label>
            <div class="role-switch" role="group" aria-label="Тип аккаунта">
                <input type="radio" class="btn-check" name="role" id="register_role_student" value="student" autocomplete="off" @checked($selectedRole === 'student')>
                <label class="role-option" for="register_role_student">
                    <i class="bi bi-mortarboard"></i>
                    <span>1 Ученик</span>
                </label>

                <input type="radio" class="btn-check" name="role" id="register_role_teacher" value="teacher" autocomplete="off" @checked($selectedRole === 'teacher')>
                <label class="role-option" for="register_role_teacher">
                    <i class="bi bi-person-workspace"></i>
                    <span>2 Учитель</span>
                </label>

                <input type="radio" class="btn-check" name="role" id="register_role_dispatcher" value="dispatcher" autocomplete="off" @checked($selectedRole === 'dispatcher')>
                <label class="role-option" for="register_role_dispatcher">
                    <i class="bi bi-diagram-3"></i>
                    <span>3 Диспетчер</span>
                </label>
            </div>
            @error('role')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label auth-label">Имя</label>
            <input type="text" name="name" class="form-control auth-input" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 teacher-field-wrap {{ $selectedRole !== 'teacher' ? 'is-hidden' : '' }}" id="teacherSurnameWrap">
            <label class="form-label auth-label">Фамилия преподавателя</label>
            <input type="text" name="teacher_surname" id="teacherSurnameInput" class="form-control auth-input" value="{{ old('teacher_surname') }}" placeholder="Например: Сулейменова">
            <div class="teacher-field-hint mt-1">Поле обязательно только для роли преподавателя. Фамилия должна совпадать со справочником преподавателей.</div>
            @error('teacher_surname')
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

    <script>
        (() => {
            const roleInputs = Array.from(document.querySelectorAll('input[name="role"]'));
            const wrap = document.getElementById('teacherSurnameWrap');
            const input = document.getElementById('teacherSurnameInput');
            if (!wrap || !input || roleInputs.length === 0) return;

            const syncTeacherField = () => {
                const selected = roleInputs.find((el) => el.checked)?.value || 'student';
                const isTeacher = selected === 'teacher';
                wrap.classList.toggle('is-hidden', !isTeacher);
                input.required = isTeacher;
            };

            roleInputs.forEach((el) => el.addEventListener('change', syncTeacherField));
            syncTeacherField();
        })();
    </script>
@endsection
