<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KitOper') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Unbounded:wght@500;600&display=swap">
    <link rel="stylesheet" href="/css/sidebar.css">
    @stack('styles')
</head>
<body class="ko-body">
    @php($currentUser = auth()->user())
    <div class="ko-app">
        <aside class="ko-sidebar">
            <div class="ko-brand">
                <div class="ko-brand-icon">
                    <i class="bi bi-grid"></i>
                </div>
                <div class="ko-brand-name">KitOper</div>
            </div>

            <div>
                <div class="ko-section-title">Меню</div>
                <nav class="ko-nav">
                    @if($currentUser?->isDispatcher())
                        <a class="ko-nav-item {{ request()->routeIs('home', 'first.schedule.*') ? 'is-active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Расписание</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('groups.*') ? 'is-active' : '' }}" href="{{ route('groups.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Группы</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('teachers.*') ? 'is-active' : '' }}" href="{{ route('teachers.index') }}">
                            <i class="bi bi-mortarboard"></i>
                            <span>Преподаватели</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('subjects.*') ? 'is-active' : '' }}" href="{{ route('subjects.index') }}">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Дисциплины</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('rooms.*') ? 'is-active' : '' }}" href="{{ route('rooms.index') }}">
                            <i class="bi bi-building"></i>
                            <span>Аудитории</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('holidays.*') ? 'is-active' : '' }}" href="{{ route('holidays.index') }}">
                            <i class="bi bi-calendar-event"></i>
                            <span>Праздники</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('practice.*') ? 'is-active' : '' }}" href="{{ route('practice.index') }}">
                            <i class="bi bi-briefcase"></i>
                            <span>Практика</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('field_camps.*') ? 'is-active' : '' }}" href="{{ route('field_camps.index') }}">
                            <i class="bi bi-compass"></i>
                            <span>Полевые сборы</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('teacher_absences.*') ? 'is-active' : '' }}" href="{{ route('teacher_absences.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Отсутствия</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('teachers.workload') ? 'is-active' : '' }}" href="{{ route('teachers.workload') }}">
                            <i class="bi bi-table"></i>
                            <span>Занятость</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('first.schedule.form_two') ? 'is-active' : '' }}" href="{{ route('first.schedule.form_two') }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Форма 2</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('form_two_templates.*') ? 'is-active' : '' }}" href="{{ route('form_two_templates.index') }}">
                            <i class="bi bi-list-check"></i>
                            <span>Шаблоны Ф2</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Пользователи</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('audit_logs.*') ? 'is-active' : '' }}" href="{{ route('audit_logs.index') }}">
                            <i class="bi bi-clipboard-data"></i>
                            <span>Журнал изменений</span>
                        </a>
                    @elseif($currentUser?->isTeacher())
                        <a class="ko-nav-item {{ request()->routeIs('teacher.today') ? 'is-active' : '' }}" href="{{ route('teacher.today') }}">
                            <i class="bi bi-calendar3"></i>
                            <span>Сегодняшние пары</span>
                        </a>
                    @else
                        <a class="ko-nav-item {{ request()->routeIs('home', 'first.schedule.*') ? 'is-active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Расписание</span>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="ko-spacer"></div>

            <div class="px-3 pb-2 text-muted small">
                {{ $currentUser?->name }}
                <div>
                    @if($currentUser?->role === 'dispatcher')
                        Диспетчер
                    @elseif($currentUser?->role === 'teacher')
                        Преподаватель
                    @else
                        Ученик
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="ko-nav">
                @csrf
                <button class="ko-nav-item ko-logout" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Выход</span>
                </button>
            </form>

        </aside>

        <div class="ko-content">
            <main class="ko-main">
                <div class="ko-main-inner">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
