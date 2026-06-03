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
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/sidebar/main.css">
    <link rel="stylesheet" href="/css/sidebar/ai-chat-widget.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css">
    @stack('styles')
</head>
<body class="ko-body">
    @php($currentUser = auth()->user())

    {{-- Mobile sidebar toggle --}}
    <button class="ko-sidebar-toggle" id="koSidebarToggle" aria-label="Открыть меню">
        <i class="bi bi-list"></i>
    </button>
    <div class="ko-sidebar-overlay" id="koSidebarOverlay"></div>

    <div class="ko-app">
        <aside class="ko-sidebar" id="koSidebar">

            {{-- Brand --}}
            <div class="ko-brand">
                <div class="ko-brand-icon">
                    <i class="bi bi-grid-fill"></i>
                </div>
                <div class="ko-brand-name">KitOper</div>
            </div>

            {{-- Navigation --}}
            <div class="ko-nav-body">
                <nav class="ko-nav">
                    @if($currentUser?->isDispatcher())
                        <a class="ko-nav-item {{ request()->routeIs('home', 'first.schedule.*') ? 'is-active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Расписание</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('first.schedule.week.duplicate', 'first.schedule.week.duplicate.store') ? 'is-active' : '' }}" href="{{ route('first.schedule.week.duplicate', ['course' => request()->integer('course') ?: 1]) }}">
                            <i class="bi bi-files"></i>
                            <span>Дубликат недели</span>
                        </a>

                        <a class="ko-nav-item {{ request()->routeIs('schedule.generate.*') ? 'is-active' : '' }}" href="{{ route('schedule.generate.index', ['course' => request()->integer('course') ?: 1]) }}">
                            <i class="bi bi-magic"></i>
                            <span>Генератор</span>
                        </a>

                        <div class="ko-divider"></div>

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

                        <div class="ko-divider"></div>

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

                        <div class="ko-divider"></div>

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
                        <a class="ko-nav-item {{ request()->routeIs('ai_agent.*') ? 'is-active' : '' }}" href="{{ route('ai_agent.index') }}">
                            <i class="bi bi-chat-dots"></i>
                            <span>ИИ-Агент</span>
                        </a>
                        <a class="ko-nav-item {{ request()->routeIs('docs.index') ? 'is-active' : '' }}" href="{{ route('docs.index') }}">
                            <i class="bi bi-book"></i>
                            <span>Документация</span>
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

                    <div class="ko-spacer"></div>

                    <div class="ko-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="ko-nav-item ko-logout" type="submit">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Выход</span>
                        </button>
                    </form>
                </nav>
            </div>

            {{-- User profile --}}
            <div class="ko-sidebar-footer">
                <div class="ko-user">
                    <div class="ko-user-avatar-wrap">
                        <div class="ko-user-avatar">{{ mb_substr($currentUser?->name ?? '?', 0, 1) }}</div>
                        <span class="ko-user-online"></span>
                    </div>
                    <div class="ko-user-info">
                        <div class="ko-user-name">{{ $currentUser?->name }}</div>
                        <div class="ko-user-role">
                            @if($currentUser?->role === 'dispatcher')
                                Диспетчер
                            @elseif($currentUser?->role === 'teacher')
                                Преподаватель
                            @else
                                Ученик
                            @endif
                        </div>
                    </div>
                    <i class="bi bi-chevron-expand ko-user-sort"></i>
                </div>
            </div>

        </aside>

        <div class="ko-content">
            <div class="ko-main">
                @if (session('success'))
                    <div class="app-alert app-alert-success" style="margin-bottom:16px">
                        <i class="bi bi-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="app-alert app-alert-danger" style="margin-bottom:16px">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
    <script>
    (function () {
        var btn = document.getElementById('koSidebarToggle');
        var sidebar = document.getElementById('koSidebar');
        var overlay = document.getElementById('koSidebarOverlay');
        if (!btn || !sidebar) return;

        function open() {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-active');
            btn.querySelector('i').className = 'bi bi-x-lg';
        }
        function close() {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-active');
            btn.querySelector('i').className = 'bi bi-list';
        }
        btn.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? close() : open();
        });
        if (overlay) overlay.addEventListener('click', close);
        sidebar.querySelectorAll('.ko-nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) close();
            });
        });
    })();
    </script>
    @stack('scripts')
    @if(auth()->user()?->isDispatcher())
        @include('components.ai-widget')
    @endif

    {{-- Tour help button (shown by each page's tour script) --}}
    <button id="tourHelpBtn" type="button" style="display:none" title="Интерактивная помощь">
        <i class="bi bi-question-circle"></i> Помощь
    </button>
    <style>
    #tourHelpBtn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9000;
        background: #7f56d9;
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(127,86,217,0.35);
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        display: none;
    }
    #tourHelpBtn:hover { background: #6941c6; transform: translateY(-1px); }
    #tourHelpBtn i { margin-right: 4px; }
    </style>
</body>
</html>
