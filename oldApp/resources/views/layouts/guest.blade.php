<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KitOper') }} — Вход</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@500;600&display=swap">
    <style>
        :root {
            --auth-primary: #7f56d9;
            --auth-primary-2: #6941c6;
            --auth-primary-soft: #f0ebff;
            --auth-text: #0f172a;
            --auth-muted: #6b7280;
            --auth-border: #e5e7eb;
            --auth-shadow: 0 12px 40px rgba(16, 24, 40, 0.12);
            --auth-radius: 16px;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--auth-text);
            background:
                radial-gradient(900px 500px at 8% -5%, rgba(127, 86, 217, 0.1), transparent 70%),
                radial-gradient(900px 520px at 96% 110%, rgba(18, 183, 106, 0.08), transparent 72%),
                #f7f7f8;
            min-height: 100vh;
        }

        .auth-shell {
            max-width: 1180px;
        }

        .auth-intro {
            border-radius: var(--auth-radius);
            background: linear-gradient(155deg, #6941c6 0%, #7f56d9 62%, #9e77ed 100%);
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
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 3px rgba(127, 86, 217, 0.15);
        }

        .role-switch {
            --active-index: 0;
            position: relative;
            display: grid;
            grid-template-columns: repeat(var(--role-count, 3), minmax(0, 1fr));
            gap: 0;
            border: 1px solid var(--auth-border);
            border-radius: 14px;
            padding: 4px;
            background: #fff;
            isolation: isolate;
        }

        .role-switch::before {
            content: "";
            position: absolute;
            z-index: 0;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc((100% - 8px) / var(--role-count, 3));
            border-radius: 10px;
            background: linear-gradient(135deg, #7f56d9 0%, #6941c6 100%);
            box-shadow: 0 6px 16px rgba(127, 86, 217, 0.3);
            transform: translateX(calc(var(--active-index, 0) * 100%));
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .role-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 0;
            border-radius: 12px;
            padding: 10px 4px;
            background: transparent;
            color: #344256;
            font-size: 0.92rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
            transition: color 0.22s ease, transform 0.22s ease;
        }

        .role-option i {
            font-size: 0.9rem;
        }

        .btn-check:checked + .role-option {
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-check:not(:checked) + .role-option:hover {
            color: var(--auth-primary);
        }

        .auth-submit {
            border: 0;
            border-radius: 8px;
            background: var(--auth-primary);
            font-weight: 600;
            padding: 10px 14px;
            box-shadow: 0 4px 12px rgba(127, 86, 217, 0.28);
        }

        .auth-submit:hover {
            background: var(--auth-primary-2);
        }

        .auth-link {
            color: var(--auth-primary);
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

        .teacher-field-wrap {
            overflow: hidden;
            max-height: 180px;
            opacity: 1;
            transform: translateY(0);
            transition: max-height 0.28s ease, opacity 0.24s ease, transform 0.24s ease, margin 0.24s ease;
        }

        .teacher-field-wrap.is-hidden {
            max-height: 0;
            opacity: 0;
            transform: translateY(-4px);
            margin-bottom: 0 !important;
            pointer-events: none;
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

            .role-option {
                font-size: 0.78rem;
                gap: 4px;
                padding: 9px 2px;
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
    <script>
        (() => {
            const roleValueToIndex = { student: 0, teacher: 1, dispatcher: 2 };
            document.querySelectorAll('.role-switch').forEach((switchEl) => {
                const radios = Array.from(switchEl.querySelectorAll('input[type="radio"][name="role"]'));
                if (!radios.length) return;

                const sync = () => {
                    const selected = radios.find((el) => el.checked)?.value || 'student';
                    const index = roleValueToIndex[selected] ?? 0;
                    switchEl.style.setProperty('--active-index', String(index));
                };

                radios.forEach((radio) => radio.addEventListener('change', sync));
                sync();
            });
        })();
    </script>
</body>
</html>
