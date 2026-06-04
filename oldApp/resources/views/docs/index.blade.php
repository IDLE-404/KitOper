<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Документация — KitOper</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/css/docs/main.css">
</head>
<body class="docs-root" style="margin:0;background:#fff">

{{-- Progress bar --}}
<div class="docs-progress"><div class="docs-progress-bar" id="progressBar"></div></div>

{{-- Header --}}
<header class="docs-header">
    <a href="{{ route('home') }}" class="docs-header-brand">
        <i class="bi bi-grid-fill"></i> KitOper
    </a>
    <div class="docs-header-divider"></div>
    <span class="docs-header-title">Документация</span>
    <div class="docs-header-spacer"></div>
    <div class="docs-header-search">
        <i class="bi bi-search"></i>
        <input type="text" id="docsSearch" placeholder="Поиск по документации...">
    </div>
    <a href="{{ route('home') }}" class="docs-header-back">
        <i class="bi bi-arrow-left"></i> В приложение
    </a>
</header>

{{-- Layout --}}
<div class="docs-layout">

    {{-- Sidebar --}}
    <aside class="docs-sidebar">
        <nav>
            <div class="docs-nav-group">
                <span class="docs-nav-group-label">Начало</span>
                <a href="#start" class="docs-nav-link active"><i class="bi bi-rocket-takeoff"></i> Быстрый старт</a>
                <a href="#roles" class="docs-nav-link"><i class="bi bi-people"></i> Роли и доступ</a>
            </div>
            <div class="docs-nav-divider"></div>
            <div class="docs-nav-group">
                <span class="docs-nav-group-label">Расписание</span>
                <a href="#schedule-view" class="docs-nav-link"><i class="bi bi-calendar3"></i> Просмотр</a>
                <a href="#schedule-edit" class="docs-nav-link"><i class="bi bi-pencil-square"></i> Редактирование</a>
                <a href="#schedule-week" class="docs-nav-link"><i class="bi bi-grid"></i> Шаблон недели</a>
                <a href="#schedule-day" class="docs-nav-link"><i class="bi bi-calendar-day"></i> Режим «День»</a>
                <a href="#replacements" class="docs-nav-link"><i class="bi bi-arrow-left-right"></i> Замены</a>
            </div>
            <div class="docs-nav-divider"></div>
            <div class="docs-nav-group">
                <span class="docs-nav-group-label">Форма 2</span>
                <a href="#form-two" class="docs-nav-link"><i class="bi bi-file-earmark-text"></i> Учёт часов</a>
                <a href="#form-two-colors" class="docs-nav-link"><i class="bi bi-palette"></i> Цвета ячеек</a>
                <a href="#form-two-ghost" class="docs-nav-link"><i class="bi bi-eye"></i> Прогноз (Ghost)</a>
                <a href="#form-two-correction" class="docs-nav-link"><i class="bi bi-pen"></i> Коррекция</a>
            </div>
            <div class="docs-nav-divider"></div>
            <div class="docs-nav-group">
                <span class="docs-nav-group-label">Справочники</span>
                <a href="#teachers" class="docs-nav-link"><i class="bi bi-mortarboard"></i> Преподаватели</a>
                <a href="#groups" class="docs-nav-link"><i class="bi bi-people-fill"></i> Группы</a>
                <a href="#subjects" class="docs-nav-link"><i class="bi bi-journal-bookmark"></i> Дисциплины</a>
                <a href="#rooms" class="docs-nav-link"><i class="bi bi-building"></i> Аудитории</a>
            </div>
            <div class="docs-nav-divider"></div>
            <div class="docs-nav-group">
                <span class="docs-nav-group-label">Прочее</span>
                <a href="#absences" class="docs-nav-link"><i class="bi bi-clipboard-x"></i> Отсутствия</a>
                <a href="#special" class="docs-nav-link"><i class="bi bi-calendar-event"></i> Периоды</a>
                <a href="#workload" class="docs-nav-link"><i class="bi bi-table"></i> Занятость</a>
                <a href="#ai" class="docs-nav-link"><i class="bi bi-chat-dots"></i> ИИ-агент</a>
                <a href="#faq" class="docs-nav-link"><i class="bi bi-question-circle"></i> Вопросы и ответы</a>
            </div>
        </nav>
    </aside>

    {{-- Content --}}
    <main class="docs-content">

        {{-- ════════════════════════════════════════════
             БЫСТРЫЙ СТАРТ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="start" data-search="старт начало вход войти">
            <span class="docs-section-tag"><i class="bi bi-rocket-takeoff"></i> Начало работы</span>
            <h1 class="docs-h1">Добро пожаловать в KitOper</h1>
            <p class="docs-lead">KitOper — система управления расписанием колледжа. Здесь вы составляете расписание, отслеживаете фактические занятия и формируете отчёты.</p>

            <div class="docs-callout info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Кнопка «? Помощь»</div>
                    На каждой странице системы есть фиолетовая кнопка в правом нижнем углу. Нажмите её — запустится интерактивный тур, который покажет и объяснит все элементы страницы.
                </div>
            </div>

            <h2 class="docs-h2">Как начать работу</h2>
            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Войдите в систему</div>
                        <div class="docs-step-desc">Перейдите по адресу системы → выберите тип аккаунта «Диспетчер» → введите email и пароль → нажмите «Войти».</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Заполните справочники</div>
                        <div class="docs-step-desc">Добавьте <a href="#teachers">преподавателей</a>, <a href="#groups">группы</a>, <a href="#subjects">дисциплины</a> и <a href="#rooms">аудитории</a>. Без этого составить расписание не получится.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Добавьте праздники и каникулы</div>
                        <div class="docs-step-desc">Перейдите в <a href="#special">Праздники</a> и добавьте все нерабочие дни семестра — система их пропустит при разворачивании расписания.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">4</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Составьте шаблон недели</div>
                        <div class="docs-step-desc">Откройте <a href="#schedule-week">Редактор недели</a>, выберите группу и заполните пары по дням. Это ваш шаблон — он будет скопирован на все недели семестра.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">5</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Разверните семестр</div>
                        <div class="docs-step-desc">В редакторе недели прокрутите вниз → «Развернуть на семестр» → укажите даты → нажмите кнопку. Расписание появится на всех неделях автоматически.</div>
                    </div>
                </div>
            </div>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-login.png" alt="Экран входа" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Экран входа в KitOper. Выберите тип аккаунта перед вводом пароля.</div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             РОЛИ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="roles" data-search="роли доступ диспетчер преподаватель студент права">
            <span class="docs-section-tag"><i class="bi bi-people"></i> Доступ</span>
            <h1 class="docs-h1">Роли и права доступа</h1>
            <p class="docs-lead">В системе три типа пользователей с разным уровнем доступа.</p>

            <div class="docs-role-grid">
                <div class="docs-role-card">
                    <div class="docs-role-icon purple"><i class="bi bi-person-gear"></i></div>
                    <div class="docs-role-name">Диспетчер</div>
                    <div class="docs-role-desc">Составляет расписание, управляет справочниками, смотрит Форму 2, управляет пользователями.</div>
                    <span class="docs-role-badge full">Полный доступ</span>
                </div>
                <div class="docs-role-card">
                    <div class="docs-role-icon green"><i class="bi bi-mortarboard"></i></div>
                    <div class="docs-role-name">Преподаватель</div>
                    <div class="docs-role-desc">Видит только свои пары на текущий день. Редактировать ничего не может.</div>
                    <span class="docs-role-badge limited">Только просмотр</span>
                </div>
                <div class="docs-role-card">
                    <div class="docs-role-icon blue"><i class="bi bi-person"></i></div>
                    <div class="docs-role-name">Студент</div>
                    <div class="docs-role-desc">Просматривает расписание своей группы. Нет доступа к Форме 2 и справочникам.</div>
                    <span class="docs-role-badge view">Только расписание</span>
                </div>
            </div>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Управление пользователями</div>
                    Добавить нового пользователя и назначить ему роль можно в разделе <strong>«Пользователи»</strong> бокового меню (доступно только диспетчеру).
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             РАСПИСАНИЕ — ПРОСМОТР
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="schedule-view" data-search="расписание просмотр неделя день числитель знаменатель ячейка пара курс">
            <span class="docs-section-tag"><i class="bi bi-calendar3"></i> Расписание</span>
            <h1 class="docs-h1">Просмотр расписания</h1>
            <p class="docs-lead">Главная страница системы. Показывает все группы выбранного курса на текущей неделе.</p>

            <div class="docs-img-wrap">
                <div class="docs-annotated">
                    <img src="/img/docs/doc-schedule-main.png" alt="Расписание" loading="lazy" style="width:100%">
                    <div class="docs-ann purple" style="top:3%;left:1%;width:12%;height:7%">
                        <span class="docs-ann-badge">1</span>
                    </div>
                    <div class="docs-ann green" style="top:3%;left:22%;width:18%;height:7%">
                        <span class="docs-ann-badge">2</span>
                    </div>
                    <div class="docs-ann yellow" style="top:3%;right:2%;width:22%;height:7%">
                        <span class="docs-ann-badge">3</span>
                    </div>
                </div>
                <div class="docs-img-caption" style="flex-direction:column;align-items:flex-start;gap:4px;">
                    <div class="docs-ann-list">
                        <div class="docs-ann-item"><span class="docs-ann-item-num" style="background:var(--docs-accent)">1</span> <span>Выбор курса — переключает между 1–4 курсами</span></div>
                        <div class="docs-ann-item"><span class="docs-ann-item-num" style="background:var(--docs-green)">2</span> <span>Метка A/B — показывает числитель или знаменатель текущей недели</span></div>
                        <div class="docs-ann-item"><span class="docs-ann-item-num" style="background:var(--docs-yellow)">3</span> <span>Навигация по неделям и выбор конкретной даты</span></div>
                    </div>
                </div>
            </div>

            <h2 class="docs-h2">Числитель (A) и знаменатель (B)</h2>
            <p class="docs-p">Расписание в колледже чередуется еженедельно — это называется числитель/знаменатель. Одна неделя идёт вариант A, следующая — вариант B, потом снова A и так далее.</p>

            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-alphabet"></i> Неделя A (числитель)</div>
                    <div class="docs-feature-card-desc">Первый вариант расписания. Например, понедельник — математика у группы ИС-12.</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-alphabet-uppercase"></i> Неделя B (знаменатель)</div>
                    <div class="docs-feature-card-desc">Второй вариант. Следующую неделю у той же группы в понедельник может быть физика.</div>
                </div>
            </div>

            <div class="docs-callout info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="docs-callout-body">Система автоматически определяет, какая сейчас неделя — A или B. Метка отображается в заголовке страницы. При переходе на другую неделю метка меняется сама.</div>
            </div>

            <h2 class="docs-h2">Режимы просмотра</h2>
            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-grid-3x3"></i> Режим «Неделя»</div>
                    <div class="docs-feature-card-desc">Все 5 дней сразу для всех групп. Удобно для общего контроля расписания.</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-calendar-day"></i> Режим «День»</div>
                    <div class="docs-feature-card-desc">Только один день. Есть кнопка автоподстановки кабинетов. Переключается кнопками «‹» / «›».</div>
                </div>
            </div>

            <h2 class="docs-h2">Как читать ячейку пары</h2>
            <p class="docs-p">Каждая ячейка в таблице — одна пара. Нажмите <span class="docs-ui-btn"><i class="bi bi-pencil"></i> ✏️</span> чтобы открыть её на редактирование.</p>
            <table class="docs-table">
                <thead><tr><th>Вид ячейки</th><th>Что означает</th><th>Что делать</th></tr></thead>
                <tbody>
                    <tr><td>Обычная белая</td><td>Пара запланирована нормально</td><td>Ничего не нужно</td></tr>
                    <tr><td><span class="docs-ui-tag yellow">Жёлтая</span></td><td>Назначена замена преподавателя</td><td>Проверьте корректность замены</td></tr>
                    <tr><td><span class="docs-ui-tag red">Красная рамка</span></td><td>Конфликт — кабинет занят дважды</td><td>Исправьте через ✏️</td></tr>
                    <tr><td><span class="docs-ui-tag gray">Пустая</span></td><td>Пары нет в этот день</td><td>—</td></tr>
                </tbody>
            </table>

            <div class="docs-related">
                <span class="docs-related-label">Связанные разделы</span>
                <a href="#schedule-edit" class="docs-related-link"><i class="bi bi-pencil-square"></i> Редактирование пары</a>
                <a href="#schedule-day" class="docs-related-link"><i class="bi bi-calendar-day"></i> Режим «День»</a>
                <a href="#replacements" class="docs-related-link"><i class="bi bi-arrow-left-right"></i> Замены</a>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             РЕДАКТИРОВАНИЕ ПАРЫ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="schedule-edit" data-search="редактировать пара предмет преподаватель кабинет знаменатель подгруппа замена диалог">
            <span class="docs-section-tag"><i class="bi bi-pencil-square"></i> Расписание</span>
            <h1 class="docs-h1">Редактирование пары</h1>
            <p class="docs-lead">Нажмите <span class="docs-ui-btn"><i class="bi bi-pencil"></i> ✏️</span> на любой ячейке — откроется диалог редактирования.</p>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-pair-modal.png" alt="Диалог редактирования пары" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Диалог редактирования пары. Здесь настраивается всё для одного конкретного урока.</div>
            </div>

            <h2 class="docs-h2">Что можно настроить</h2>

            <div class="docs-faq">
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Предмет, преподаватель и кабинет <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Базовые поля пары. Выберите предмет из списка, затем преподавателя (список фильтруется по тем, у кого назначен этот предмет), затем кабинет. Система покажет красным занятых преподавателей.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">«Включить знаменатель» — зачем? <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">
                        По умолчанию пара одинакова в обе недели (A и B). Если в неделю B нужен <strong>другой предмет или другой преподаватель</strong> — поставьте галочку «Включить знаменатель». Появится второй блок полей для недели B.
                        <div class="docs-callout tip" style="margin-top:10px">
                            <i class="bi bi-lightbulb-fill"></i>
                            <div class="docs-callout-body">Если знаменатель не включён — в обе недели будет одна и та же пара. Это нормально для большинства предметов.</div>
                        </div>
                    </div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">«Включить подгруппу 2» — зачем? <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">
                        Если группа делится пополам <strong>на этой конкретной паре</strong> — поставьте галочку. Появятся отдельные поля для подгруппы 2. Обе подгруппы занимаются одновременно в разных кабинетах.
                        <div class="docs-callout warning" style="margin-top:10px">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div class="docs-callout-body">Сначала убедитесь, что у группы включена опция «Есть подгруппа 2» в разделе <a href="#groups">Группы</a>. Иначе галочка не появится.</div>
                        </div>
                    </div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">«Включить замену» — когда использовать? <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">
                        Когда основной преподаватель отсутствует (болезнь, командировка). Поставьте галочку → выберите заменяющего преподавателя. В <a href="#form-two">Форме 2</a> у основного эта пара будет пустой, а у заменяющего прибавятся бонусные часы.
                        <div class="docs-callout info" style="margin-top:10px">
                            <i class="bi bi-info-circle-fill"></i>
                            <div class="docs-callout-body">Преподаватели из списка отсутствий помечены <span class="docs-ui-tag yellow">оранжевым</span> — это сигнал что нужна замена.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="docs-related">
                <span class="docs-related-label">Связанные разделы</span>
                <a href="#replacements" class="docs-related-link"><i class="bi bi-arrow-left-right"></i> Замены подробнее</a>
                <a href="#groups" class="docs-related-link"><i class="bi bi-people-fill"></i> Настройка подгрупп</a>
                <a href="#absences" class="docs-related-link"><i class="bi bi-clipboard-x"></i> Отсутствия</a>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ШАБЛОН НЕДЕЛИ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="schedule-week" data-search="шаблон неделя редактор создать расписание подгруппа числитель знаменатель развернуть семестр">
            <span class="docs-section-tag"><i class="bi bi-grid"></i> Расписание</span>
            <h1 class="docs-h1">Шаблон недели</h1>
            <p class="docs-lead">Здесь вы создаёте «типичную неделю» для группы. После настройки система разворачивает шаблон на весь семестр автоматически.</p>

            <div class="docs-callout info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Логика работы</div>
                    Шаблон не привязан к датам — это просто набор пар по дням недели. Кнопка «Развернуть на семестр» копирует шаблон на все реальные недели, чередуя A и B и пропуская праздники.
                </div>
            </div>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-schedule-week.png" alt="Редактор шаблона недели" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Редактор шаблона недели. Каждая строка — одна пара, столбцы A и B — числитель и знаменатель.</div>
            </div>

            <h2 class="docs-h2">Как составить расписание с нуля</h2>
            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Выберите группу</div>
                        <div class="docs-step-desc">Нажмите на выпадающий список вверху → выберите группу. Таблица пар загрузится для этой группы.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Выберите день</div>
                        <div class="docs-step-desc">Нажмите на вкладку нужного дня (Пн, Вт, Ср, Чт, Пт) — таблица покажет пары этого дня.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Заполните пары</div>
                        <div class="docs-step-desc">В каждой строке — столбцы для числителя (A) и знаменателя (B). Выберите предмет, преподавателя и кабинет. Если обе недели одинаковы — заполните только A, B оставьте пустым.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">4</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Добавьте пары кнопкой «+ Добавить пару»</div>
                        <div class="docs-step-desc">Под таблицей каждого дня есть кнопка добавления новой пары. Нажмите, чтобы добавить 3-ю, 4-ю и т.д. пары.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">5</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Разверните на семестр</div>
                        <div class="docs-step-desc">Прокрутите страницу вниз → найдите раздел «Развернуть на семестр» → укажите дату начала и конца семестра → нажмите кнопку.</div>
                    </div>
                </div>
            </div>

            <div class="docs-callout warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Внимание: перезапись</div>
                    Повторный запуск «Развернуть на семестр» <strong>перезаписывает</strong> все существующие пары за указанный период. В середине семестра используйте <a href="#schedule-day">«Дубликат недели»</a> для точечных изменений.
                </div>
            </div>

            <div class="docs-related">
                <span class="docs-related-label">Связанные разделы</span>
                <a href="#schedule-edit" class="docs-related-link"><i class="bi bi-pencil-square"></i> Редактирование пары</a>
                <a href="#schedule-day" class="docs-related-link"><i class="bi bi-calendar-day"></i> Дубликат недели</a>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             РЕЖИМ ДЕНЬ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="schedule-day" data-search="день кабинет автоподстановка подставить очистить дубликат">
            <span class="docs-section-tag"><i class="bi bi-calendar-day"></i> Расписание</span>
            <h1 class="docs-h1">Режим «День» и автоподстановка</h1>
            <p class="docs-lead">Переключитесь в режим «День» — здесь есть особая возможность: автоматически расставить кабинеты за один клик.</p>

            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Переключитесь в «День»</div>
                        <div class="docs-step-desc">Нажмите кнопку «День» в переключателе режимов. Выберите нужный день кнопками навигации.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Нажмите «Подставить кабинеты»</div>
                        <div class="docs-step-desc">Система автоматически назначит свободные кабинеты всем парам дня. Преподаватели с кабинетом по умолчанию получат свой кабинет, остальные — любой свободный.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Проверьте и скорректируйте</div>
                        <div class="docs-step-desc">Просмотрите результат. Если что-то не так — нажмите <span class="docs-ui-btn"><i class="bi bi-pencil"></i> ✏️</span> на нужной паре и поменяйте кабинет вручную. Кнопка «Очистить кабинеты» сбросит все назначения дня.</div>
                    </div>
                </div>
            </div>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Совет</div>
                    Настройте кабинет по умолчанию у каждого преподавателя в разделе <a href="#teachers">Преподаватели</a> — тогда автоподстановка будет работать точнее.
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ЗАМЕНЫ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="replacements" data-search="замена больничный отсутствие преподаватель бонус часы форма 2">
            <span class="docs-section-tag"><i class="bi bi-arrow-left-right"></i> Расписание</span>
            <h1 class="docs-h1">Замены преподавателей</h1>
            <p class="docs-lead">Когда преподаватель заболел или уехал — нужно назначить замену. Это влияет на расписание и Форму 2.</p>

            <h2 class="docs-h2">Полный процесс замены</h2>
            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Зафиксируйте отсутствие</div>
                        <div class="docs-step-desc">Перейдите в <a href="#absences">«Отсутствия»</a> → выберите преподавателя → добавьте период с датами. После этого его ФИО окрасится <span class="docs-ui-tag yellow">оранжевым</span> во всех выпадающих списках расписания.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Нажмите «Анализ расписания»</div>
                        <div class="docs-step-desc">На странице расписания нажмите кнопку «Анализ». Система покажет список пар этого преподавателя без замены — это ваш список задач.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Назначьте замену на каждой паре</div>
                        <div class="docs-step-desc">Нажмите ✏️ на паре → поставьте галочку «Включить замену» → выберите заменяющего преподавателя → сохраните.</div>
                    </div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">4</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body">
                        <div class="docs-step-title">Проверьте в Форме 2</div>
                        <div class="docs-step-desc">Откройте Форму 2 для этой группы. Пары с заменой будут отмечены <span class="docs-ui-tag yellow">жёлтым</span> у основного и <span class="docs-ui-tag red">красным</span> у заменяющего — это бонусные часы.</div>
                    </div>
                </div>
            </div>

            <div class="docs-callout warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Не забудьте назначить замену</div>
                    Если зафиксировали отсутствие, но замену не назначили — в Форме 2 у группы будут пустые ячейки за эти дни. Анализ расписания покажет эти пары как проблемные.
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ФОРМА 2
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="form-two" data-search="форма 2 учёт часов журнал месяц семестр группа экспорт excel">
            <span class="docs-section-tag"><i class="bi bi-file-earmark-text"></i> Форма 2</span>
            <h1 class="docs-h1">Форма 2 — учёт занятий</h1>
            <p class="docs-lead">Официальный журнал фактически проведённых часов. Заполняется автоматически из расписания. Ручное редактирование — только при ошибках.</p>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-form-two.png" alt="Форма 2" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Форма 2 за выбранный месяц. Каждая строка — предмет, каждый столбец — день месяца.</div>
            </div>

            <h2 class="docs-h2">Как открыть Форму 2</h2>
            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Выберите курс</div><div class="docs-step-desc">В выпадающем списке «Курс» выберите нужный курс (1–4).</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Выберите группу</div><div class="docs-step-desc">В списке «Группа» выберите нужную группу.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Укажите семестр и месяц</div><div class="docs-step-desc">Нажмите «1» или «2» для выбора семестра — список месяцев обновится. Выберите нужный месяц.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">4</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Нажмите «ОК»</div><div class="docs-step-desc">Таблица загрузится с данными за выбранный период.</div></div>
                </div>
            </div>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Экспорт в Excel</div>
                    Кнопка <span class="docs-ui-btn primary"><i class="bi bi-file-earmark-excel"></i> Экспорт</span> скачает Форму 2 за текущий месяц. «Экспорт 1 семестр» и «Экспорт 2 семестр» — сводная ведомость за весь семестр.
                </div>
            </div>
        </section>

        {{-- ЦВЕТА --}}
        <section class="docs-section" id="form-two-colors" data-search="цвета ячейки форма 2 замена практика праздник">
            <span class="docs-section-tag"><i class="bi bi-palette"></i> Форма 2</span>
            <h1 class="docs-h1">Что означают цвета ячеек</h1>
            <p class="docs-lead">Каждый цвет ячейки несёт смысл. Вот полная расшифровка.</p>

            <div class="docs-legend">
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#ede9fe;color:#6941c6;font-size:14px;font-weight:800;">2</div>
                    <div class="docs-legend-text"><strong>Фиолетовый «2»</strong> — пара проведена нормально</div>
                    <div class="docs-legend-when">Норма</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#fef9c3;color:#854d0e;font-size:16px;">■</div>
                    <div class="docs-legend-text"><strong>Жёлтый «■»</strong> — предмет заменён на другой. Эта пара не засчитывается основному преподавателю.</div>
                    <div class="docs-legend-when">Замена предмета</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#fee2e2;color:#991b1b;font-size:14px;font-weight:800;">2</div>
                    <div class="docs-legend-text"><strong>Красный «2»</strong> — этот преподаватель провёл пару вместо другого. Бонусные часы.</div>
                    <div class="docs-legend-when">Замена (бонус)</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#f3f4f6;color:#6b7280;font-size:18px;">•</div>
                    <div class="docs-legend-text"><strong>Серый «•»</strong> — в этот день пары по данному предмету не было</div>
                    <div class="docs-legend-when">Нет пары</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#fef9c3;color:#854d0e;font-size:12px;font-weight:800;">П</div>
                    <div class="docs-legend-text"><strong>«П»</strong> — государственный праздник, занятий нет</div>
                    <div class="docs-legend-when">Праздник</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#fef9c3;color:#854d0e;font-size:10px;font-weight:700;">Пр</div>
                    <div class="docs-legend-text"><strong>Жёлтый фон «Практика»</strong> — группа на производственной практике или полевых сборах</div>
                    <div class="docs-legend-when">Практика</div>
                </div>
                <div class="docs-legend-item">
                    <div class="docs-legend-swatch" style="background:#fff;border:2px dashed #9ca3af;color:#9ca3af;font-size:12px;">~</div>
                    <div class="docs-legend-text"><strong>Пунктирная рамка</strong> — прогноз (Ghost-режим). В базу данных не записывается.</div>
                    <div class="docs-legend-when">Прогноз</div>
                </div>
            </div>
        </section>

        {{-- GHOST --}}
        <section class="docs-section" id="form-two-ghost" data-search="ghost прогноз призрак будущее план часов норма">
            <span class="docs-section-tag"><i class="bi bi-eye"></i> Форма 2</span>
            <h1 class="docs-h1">Прогноз — Ghost-режим 👻</h1>
            <p class="docs-lead">Хотите заранее проверить, успеет ли группа выработать норму часов до конца месяца? Включите Ghost-режим.</p>

            <div class="docs-callout info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Как это работает</div>
                    Ghost-режим смотрит на шаблон расписания группы и проецирует пары на оставшиеся дни месяца. Результат — прогноз с пунктирными ячейками. В базу данных ничего не записывается.
                </div>
            </div>

            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Откройте Форму 2 за нужный месяц</div><div class="docs-step-desc">Выберите курс, группу, семестр, месяц и нажмите «ОК».</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Нажмите переключатель «👻 Просмотр семестра»</div><div class="docs-step-desc">Он находится над таблицей. После включения в таблице появятся пунктирные ячейки — это прогноз до конца месяца.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Проверьте итоговые колонки</div><div class="docs-step-desc">Колонка «Остаток» покажет сколько часов ещё будет проведено до конца семестра. Если остаток уходит в минус — норма будет перевыполнена.</div></div>
                </div>
            </div>

            <div class="docs-callout warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="docs-callout-body">Ghost-режим работает только если для группы заполнен <a href="#schedule-week">шаблон недели</a>. Если шаблон пустой — прогноз не появится.</div>
            </div>
        </section>

        {{-- КОРРЕКЦИЯ --}}
        <section class="docs-section" id="form-two-correction" data-search="коррекция исправить добавить предмет сохранить ручное редактирование">
            <span class="docs-section-tag"><i class="bi bi-pen"></i> Форма 2</span>
            <h1 class="docs-h1">Режим коррекции</h1>
            <p class="docs-lead">Если данные в таблице неверны из-за ошибки в расписании — используйте режим коррекции для ручного исправления.</p>

            <div class="docs-callout danger">
                <i class="bi bi-shield-exclamation"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Используйте только при необходимости</div>
                    Данные Формы 2 должны поступать из расписания автоматически. Коррекция — только если в расписании была техническая ошибка. Не исправляйте данные вручную без веской причины.
                </div>
            </div>

            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Включите тумблер «Режим коррекции»</div><div class="docs-step-desc">Он находится над таблицей. После включения ячейки таблицы станут кликабельными.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Внесите изменения</div><div class="docs-step-desc">Нажмите на нужную ячейку — она станет редактируемой. Если нужно добавить строку предмета — нажмите кнопку «Добавить предмет».</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Нажмите «Сохранить коррекцию»</div><div class="docs-step-desc">Изменения запишутся в базу данных. Страница перезагрузится с обновлёнными данными.</div></div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ПРЕПОДАВАТЕЛИ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="teachers" data-search="преподаватель добавить предметы кабинет инициалы дубликат назначить">
            <span class="docs-section-tag"><i class="bi bi-mortarboard"></i> Справочники</span>
            <h1 class="docs-h1">Справочник преподавателей</h1>
            <p class="docs-lead">Добавьте всех преподавателей до составления расписания. Без преподавателя в справочнике его невозможно поставить на пару.</p>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-teachers.png" alt="Справочник преподавателей" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Список преподавателей с возможностью развернуть назначенные предметы.</div>
            </div>

            <div class="docs-callout danger">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Обязательно назначьте предметы</div>
                    Нажмите «Развернуть предметы» рядом с каждым преподавателем и отметьте галочками все предметы, которые он ведёт. <strong>Если галочка не стоит — преподаватель не появится в выпадающем списке при составлении расписания.</strong>
                </div>
            </div>

            <h2 class="docs-h2">Как добавить преподавателя</h2>
            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Заполните ФИО и инициалы</div><div class="docs-step-desc">Инициалы (например, «А.Б.В.») отображаются в ячейках расписания — важно заполнить точно.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Укажите кабинет по умолчанию</div><div class="docs-step-desc">Это кабинет, в который система поставит преподавателя при автоподстановке кабинетов. Выберите из списка аудиторий.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Нажмите «Добавить»</div><div class="docs-step-desc">Преподаватель появится в списке.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">4</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Назначьте предметы</div><div class="docs-step-desc">Нажмите «Развернуть предметы» → отметьте галочками все дисциплины, которые он ведёт по каждому курсу.</div></div>
                </div>
            </div>

            <div class="docs-callout warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Дубликаты</div>
                    Если видите <span class="docs-ui-tag yellow">оранжевую строку</span> — два преподавателя с одинаковым именем. Это путает систему при подборе замен. Проверьте инициалы и удалите лишнюю запись.
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ГРУППЫ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="groups" data-search="группа добавить подгруппа тип рус каз завершить год перевести">
            <span class="docs-section-tag"><i class="bi bi-people-fill"></i> Справочники</span>
            <h1 class="docs-h1">Справочник групп</h1>
            <p class="docs-lead">Добавьте все учебные группы. Каждая группа получает своё расписание и свою Форму 2.</p>

            <div class="docs-img-wrap">
                <img src="/img/docs/doc-groups.png" alt="Справочник групп" loading="lazy">
                <div class="docs-img-caption"><i class="bi bi-image"></i> Список групп с фильтром по курсам.</div>
            </div>

            <h2 class="docs-h2">Параметры группы</h2>
            <table class="docs-table">
                <thead><tr><th>Параметр</th><th>Что означает</th><th>Когда важно</th></tr></thead>
                <tbody>
                    <tr>
                        <td><strong>Название</strong></td>
                        <td>Например, «ПО-115» или «АҚЖ-125»</td>
                        <td>Отображается в расписании и Форме 2</td>
                    </tr>
                    <tr>
                        <td><strong>Тип: рус / каз</strong></td>
                        <td>Язык обучения группы</td>
                        <td>Казахские группы видят казахские названия предметов</td>
                    </tr>
                    <tr>
                        <td><strong>Есть подгруппа 2</strong></td>
                        <td>Группа делится на две части на некоторых предметах</td>
                        <td>Нужно для информатики, иностранного, лабораторных</td>
                    </tr>
                </tbody>
            </table>

            <div class="docs-callout info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Галочка «Есть подгруппа 2»</div>
                    После включения в редакторе расписания появится кнопка «Добавить подгруппу 2» на нужных парах. Это позволяет ставить двум половинам группы разные предметы в одно время.
                </div>
            </div>

            <div class="docs-callout danger">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">«Завершить учебный год» — необратимо</div>
                    Нажимайте эту кнопку <strong>один раз в год</strong> — когда группа переходит на следующий курс. Система переведёт группу и очистит шаблон расписания. Отменить нельзя.
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ДИСЦИПЛИНЫ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="subjects" data-search="дисциплина предмет добавить казахский русский модуль">
            <span class="docs-section-tag"><i class="bi bi-journal-bookmark"></i> Справочники</span>
            <h1 class="docs-h1">Справочник дисциплин</h1>
            <p class="docs-lead">Список предметов по каждому курсу. Только из этого списка можно выбирать предмет при составлении расписания.</p>

            <h2 class="docs-h2">Как добавить дисциплину</h2>
            <p class="docs-p">Выберите курс → заполните название на русском и казахском → укажите модуль → выберите тип групп (рус / каз / оба) → нажмите «Добавить».</p>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">Изменение названия предмета сразу обновляет его везде — в расписании, Форме 2 и всех выпадающих списках. Не нужно исправлять вручную в каждом месте.</div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             АУДИТОРИИ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="rooms" data-search="аудитория кабинет добавить тип активный неактивный">
            <span class="docs-section-tag"><i class="bi bi-building"></i> Справочники</span>
            <h1 class="docs-h1">Справочник аудиторий</h1>
            <p class="docs-lead">Список учебных кабинетов. Система следит за занятостью кабинетов и предупреждает о конфликтах.</p>

            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-check-circle"></i> Активный кабинет</div>
                    <div class="docs-feature-card-desc">Доступен для выбора при составлении расписания и автоподстановке.</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-x-circle"></i> Неактивный кабинет</div>
                    <div class="docs-feature-card-desc">Не предлагается в новых парах. Используйте при ремонте или закрытии кабинета.</div>
                </div>
            </div>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">Деактивация кабинета не удаляет старые пары — только убирает его из новых назначений. Историческое расписание остаётся нетронутым.</div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ОТСУТСТВИЯ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="absences" data-search="отсутствие больничный командировка отпуск добавить период">
            <span class="docs-section-tag"><i class="bi bi-clipboard-x"></i> Периоды</span>
            <h1 class="docs-h1">Отсутствия преподавателей</h1>
            <p class="docs-lead">Зафиксируйте дни, когда преподаватель не работает. Система отметит его пары в расписании как требующие замены.</p>

            <div class="docs-steps">
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">1</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Выберите преподавателя из списка</div><div class="docs-step-desc">Используйте поиск если преподавателей много.</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">2</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Укажите период и причину</div><div class="docs-step-desc">Выберите даты начала и конца отсутствия. Добавьте комментарий (болезнь, командировка и т.д.).</div></div>
                </div>
                <div class="docs-step">
                    <div class="docs-step-left"><div class="docs-step-num">3</div><div class="docs-step-line"></div></div>
                    <div class="docs-step-body"><div class="docs-step-title">Нажмите «Добавить»</div><div class="docs-step-desc">После этого перейдите в расписание и назначьте замену на каждой паре. Подробнее — в разделе <a href="#replacements">Замены</a>.</div></div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             СПЕЦИАЛЬНЫЕ ПЕРИОДЫ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="special" data-search="праздник практика полевые сборы каникулы период добавить">
            <span class="docs-section-tag"><i class="bi bi-calendar-event"></i> Периоды</span>
            <h1 class="docs-h1">Праздники, практика, полевые сборы</h1>
            <p class="docs-lead">Специальные периоды влияют на то, как отображается расписание и Форма 2 в эти дни.</p>

            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-calendar-x"></i> Праздники</div>
                    <div class="docs-feature-card-desc">Добавьте до разворачивания семестра. Система пропустит эти дни. В Форме 2 — буква «П».</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-briefcase"></i> Практика</div>
                    <div class="docs-feature-card-desc">Производственная или учебная практика группы. Пары в Форме 2 отмечаются жёлтым «Практика».</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-compass"></i> Полевые сборы</div>
                    <div class="docs-feature-card-desc">Военные или спортивные сборы. Аналогично практике — пары помечаются в Форме 2.</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-arrow-counterclockwise"></i> Удаление периода</div>
                    <div class="docs-feature-card-desc">После удаления пары восстанавливаются из шаблона расписания автоматически.</div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ЗАНЯТОСТЬ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="workload" data-search="занятость нагрузка преподаватель свободен таблица неделя">
            <span class="docs-section-tag"><i class="bi bi-table"></i> Инструменты</span>
            <h1 class="docs-h1">Занятость преподавателей</h1>
            <p class="docs-lead">Таблица показывает кто из преподавателей свободен в конкретное время. Незаменима при подборе замены.</p>

            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-grid-3x3"></i> Как читать таблицу</div>
                    <div class="docs-feature-card-desc">Строки — пары по дням, столбцы — преподаватели. Заполненная ячейка — занят, пустая — свободен.</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-search"></i> Поиск</div>
                    <div class="docs-feature-card-desc">Введите фамилию в поле поиска — таблица отфильтруется по нужному преподавателю.</div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             ИИ-АГЕНТ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="ai" data-search="ии агент чат искусственный интеллект вопрос импорт excel ollama">
            <span class="docs-section-tag"><i class="bi bi-chat-dots"></i> Инструменты</span>
            <h1 class="docs-h1">ИИ-Агент</h1>
            <p class="docs-lead">Встроенный чат на локальной нейросети. Понимает вопросы о расписании на русском языке.</p>

            <h2 class="docs-h2">Что можно спросить</h2>
            <div class="docs-feature-grid">
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-calendar-check"></i> О расписании</div>
                    <div class="docs-feature-card-desc">«Какие пары у группы ПО-115 в среду?», «Покажи расписание на завтра»</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-person-check"></i> О преподавателях</div>
                    <div class="docs-feature-card-desc">«Покажи нагрузку Ахметова», «Кто свободен во вторник в 10:00?»</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-file-earmark-excel"></i> Импорт из Excel</div>
                    <div class="docs-feature-card-desc">Загрузите .xlsx файл с расписанием — агент проанализирует и поможет импортировать данные</div>
                </div>
                <div class="docs-feature-card">
                    <div class="docs-feature-card-title"><i class="bi bi-cpu"></i> Статус модели</div>
                    <div class="docs-feature-card-desc">Зелёный — готов, жёлтый — загружается (1–2 мин), красный — недоступен</div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════
             FAQ
        ════════════════════════════════════════════ --}}
        <section class="docs-section" id="faq" data-search="вопрос ответ проблема ошибка не работает не появляется">
            <span class="docs-section-tag"><i class="bi bi-question-circle"></i> Помощь</span>
            <h1 class="docs-h1">Вопросы и ответы</h1>
            <p class="docs-lead">Самые частые ситуации и как их решить.</p>

            <div class="docs-faq">
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Преподаватель не появляется в списке при редактировании пары <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">У преподавателя не назначен нужный предмет. Перейдите в <a href="#teachers">Преподаватели</a> → нажмите «Развернуть предметы» рядом с его именем → поставьте галочку на нужном предмете.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Форма 2 пустая — нет строк с предметами <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Для этой группы не созданы нормативы. Проверьте, заполнен ли шаблон Формы 2 для данного курса в разделе «Шаблоны Ф2». Если шаблон есть, но нормативы не создались — обратитесь к администратору.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Данные в Форме 2 не обновились после изменения расписания <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Откройте Форму 2 заново — данные пересчитываются при каждой загрузке. Если данные по-прежнему неверные, используйте <a href="#form-two-correction">режим коррекции</a>.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Ghost-режим не показывает прогноз <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Прогноз строится по шаблону расписания. Если шаблон не заполнен — прогноза не будет. Перейдите в <a href="#schedule-week">Редактор недели</a> и заполните пары для группы.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Как посмотреть неделю A если сейчас показывается неделя B? <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Введите конкретную дату в поле выбора недели и нажмите «Показать». Выберите дату недели, которая была неделей A.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">«Развернуть семестр» случайно перезаписал расписание <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Проверьте раздел «Журнал изменений» — там зафиксированы все действия с временными метками. В критической ситуации можно попробовать откат через журнал, но лучше обратиться к разработчику.</div>
                </div>
                <div class="docs-faq-item">
                    <button class="docs-faq-q">Нужно изменить расписание только на одну неделю, не трогая остальные <i class="bi bi-chevron-down"></i></button>
                    <div class="docs-faq-a">Используйте раздел «Дубликат недели» в боковом меню. Можно скопировать расписание одной недели на другую, либо вручную отредактировать нужные ячейки через ✏️ прямо в просмотре расписания.</div>
                </div>
            </div>

            <div class="docs-callout tip">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="docs-callout-body">
                    <div class="docs-callout-title">Не нашли ответ?</div>
                    Нажмите кнопку <strong>«? Помощь»</strong> на нужной странице — запустится интерактивный тур, который покажет каждый элемент и объяснит как с ним работать.
                </div>
            </div>
        </section>

    </main>
</div>

{{-- Back to top --}}
<button class="docs-back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="bi bi-chevron-up"></i>
</button>

<script>
// ── FAQ toggle
document.querySelectorAll('.docs-faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.docs-faq-item');
        item.classList.toggle('open');
    });
});

// ── Search
const searchInput = document.getElementById('docsSearch');
const sections = document.querySelectorAll('.docs-section');
searchInput?.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase().trim();
    sections.forEach(sec => {
        if (!q) { sec.classList.remove('search-hidden', 'search-dim'); return; }
        const text = (sec.textContent + ' ' + (sec.dataset.search || '')).toLowerCase();
        if (text.includes(q)) sec.classList.remove('search-hidden', 'search-dim');
        else sec.classList.add('search-hidden');
    });
});

// ── Active nav on scroll
const navLinks = document.querySelectorAll('.docs-nav-link');
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.id;
            navLinks.forEach(l => l.classList.remove('active'));
            const active = document.querySelector(`.docs-nav-link[href="#${id}"]`);
            if (active) active.classList.add('active');
        }
    });
}, { threshold: 0.2, rootMargin: '-56px 0px -60% 0px' });
sections.forEach(s => observer.observe(s));

// ── Progress bar
const progressBar = document.getElementById('progressBar');
window.addEventListener('scroll', () => {
    const total = document.body.scrollHeight - window.innerHeight;
    const pct = total > 0 ? (window.scrollY / total) * 100 : 0;
    if (progressBar) progressBar.style.width = pct + '%';
    const backTop = document.getElementById('backTop');
    if (backTop) backTop.classList.toggle('visible', window.scrollY > 400);
});

// ── Smooth scroll for nav links
navLinks.forEach(link => {
    link.addEventListener('click', e => {
        const href = link.getAttribute('href');
        if (href.startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

</body>
</html>
