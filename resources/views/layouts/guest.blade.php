<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KitOper') }} — Вход</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@500;600&display=swap">
    <style>
        :root {
            --auth-bg-a: #e7efff;
            --auth-bg-b: #f5f8ff;
            --auth-bg-c: #f0faf5;
            --auth-primary: #315be8;
            --auth-primary-soft: #dfe8ff;
            --auth-text: #1c2638;
            --auth-muted: #687892;
            --auth-border: #d8e1f0;
            --auth-shadow: 0 28px 70px rgba(19, 34, 66, 0.16);
            --auth-radius: 22px;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--auth-text);
            background:
                radial-gradient(900px 500px at 8% -5%, rgba(49, 91, 232, 0.18), transparent 70%),
                radial-gradient(900px 520px at 96% 110%, rgba(39, 176, 117, 0.12), transparent 72%),
                linear-gradient(145deg, var(--auth-bg-a) 0%, var(--auth-bg-b) 55%, var(--auth-bg-c) 100%);
            min-height: 100vh;
        }

        .auth-shell {
            max-width: 1180px;
        }

        .auth-intro {
            border-radius: var(--auth-radius);
            background: linear-gradient(155deg, #2d59e8 0%, #4c77ff 62%, #77a0ff 100%);
            color: #fff;
            box-shadow: var(--auth-shadow);
            padding: 34px 30px;
            min-height: 100%;
            position: relative;
            overflow: hidden;
        }

        .auth-intro::after {
            content: "";
            position: absolute;
            right: -44px;
            bottom: -44px;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }

        .auth-intro::before {
            content: "";
            position: absolute;
            right: 62px;
            top: 24px;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .auth-intro h2 {
            font-family: 'Unbounded', sans-serif;
            font-size: 1.35rem;
            margin-bottom: 10px;
            line-height: 1.35;
            position: relative;
            z-index: 1;
        }

        .auth-intro p {
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 0;
            max-width: 360px;
            position: relative;
            z-index: 1;
        }

        .auth-intro-list {
            list-style: none;
            margin: 20px 0 0;
            padding: 0;
            display: grid;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .auth-intro-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.94);
            font-weight: 500;
        }

        .auth-intro-list li::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
        }

        .auth-card {
            border: 1px solid rgba(255, 255, 255, 0.86);
            border-radius: var(--auth-radius);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            box-shadow: var(--auth-shadow);
            padding: 28px;
        }

        .brand {
            font-family: 'Unbounded', sans-serif;
            letter-spacing: 0.5px;
        }

        .auth-title {
            font-size: 1.35rem;
            margin-bottom: 4px;
        }

        .auth-subtitle {
            color: var(--auth-muted);
            font-size: 0.95rem;
            margin-bottom: 18px;
        }

        .auth-label {
            font-weight: 600;
            color: #324057;
            margin-bottom: 7px;
        }

        .auth-input,
        .auth-card .form-select {
            border-radius: 12px;
            border-color: var(--auth-border);
            padding: 10px 12px;
            font-size: 0.95rem;
        }

        .auth-input:focus,
        .auth-card .form-select:focus,
        .auth-card .form-check-input:focus {
            border-color: #6f90ff;
            box-shadow: 0 0 0 0.25rem rgba(68, 107, 235, 0.18);
        }

        .role-switch {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .role-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--auth-border);
            border-radius: 12px;
            padding: 10px 8px;
            background: #fff;
            color: #344256;
            font-size: 0.92rem;
            font-weight: 600;
            transition: all 0.18s ease;
        }

        .btn-check:checked + .role-option {
            background: var(--auth-primary-soft);
            border-color: #8ca7ff;
            color: #203a8f;
            box-shadow: 0 8px 18px rgba(60, 99, 226, 0.18);
        }

        .auth-submit {
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #2e5ae9 0%, #4472ff 100%);
            font-weight: 600;
            padding: 10px 14px;
            box-shadow: 0 10px 22px rgba(48, 89, 229, 0.28);
        }

        .auth-submit:hover {
            filter: brightness(1.03);
        }

        .auth-link {
            color: #3059db;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .teacher-field-hint {
            font-size: 0.82rem;
            color: var(--auth-muted);
        }

        @media (max-width: 991px) {
            .auth-intro {
                margin-bottom: 14px;
            }
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 22px 18px;
            }

            .role-switch {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container auth-shell py-4 py-md-5">
        <div class="row justify-content-center align-items-stretch g-3 g-lg-4">
            <div class="col-12 col-lg-5 d-none d-lg-block">
                <div class="auth-intro">
                    <h2>Платформа расписания KitOper</h2>
                    <p>Выберите роль, войдите в аккаунт и работайте только с теми разделами, которые нужны именно вам.</p>
                    <ul class="auth-intro-list">
                        <li>Преподавателю показываются пары на сегодня</li>
                        <li>Диспетчер управляет расписанием и заменами</li>
                        <li>Ученик быстро просматривает свое расписание</li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-md-8 col-lg-5">
                <div class="text-center mb-3">
                    <div class="brand h3 mb-1">KitOper</div>
                    <div class="text-muted">Система расписания колледжа</div>
                </div>
                <div class="card auth-card">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
