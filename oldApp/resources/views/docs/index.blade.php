@extends('layouts.app')

@section('content')
<style>
.docs-toc { background: #f8f9fa; border-radius: 10px; padding: 20px 24px; margin-bottom: 28px; }
.docs-toc h6 { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #888; margin-bottom: 10px; }
.docs-toc a { display: block; color: #6941c6; font-size: 14px; padding: 2px 0; text-decoration: none; }
.docs-toc a:hover { text-decoration: underline; }
.docs-section { margin-bottom: 10px; }
.docs-section .accordion-button { font-weight: 700; font-size: 15px; }
.docs-section .accordion-button:not(.collapsed) { color: #6941c6; background: #f5f0ff; }
.docs-section .accordion-body { font-size: 14px; line-height: 1.7; }
.docs-section h5 { font-size: 14px; font-weight: 700; margin: 22px 0 6px; }
.docs-section h5:first-child { margin-top: 0; }
.docs-section table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 13px; }
.docs-section table th { background: #f3f4f6; padding: 6px 10px; text-align: left; font-weight: 600; }
.docs-section table td { padding: 6px 10px; border-top: 1px solid #e5e7eb; vertical-align: middle; }
.docs-section .tip { background: #f0fdf4; border-left: 3px solid #22c55e; padding: 10px 14px; border-radius: 4px; margin: 10px 0; font-size: 13px; }
.docs-section .warn { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 10px 14px; border-radius: 4px; margin: 10px 0; font-size: 13px; }
.step-badge { display: inline-block; background: #6941c6; color: #fff; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 11px; font-weight: 700; margin-right: 6px; }
/* Скриншоты */
.docs-screenshot {
    display: block;
    width: 100%;
    max-width: 860px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: 14px 0 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    cursor: zoom-in;
    transition: box-shadow .15s;
}
.docs-screenshot:hover { box-shadow: 0 4px 18px rgba(105,65,198,.18); }
.docs-screenshot-caption {
    font-size: 12px;
    color: #888;
    margin-bottom: 18px;
}
.docs-screenshot-sm {
    max-width: 340px;
}
/* Лайтбокс */
#docs-lightbox {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.82);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}
#docs-lightbox.active { display: flex; }
#docs-lightbox img {
    max-width: 92vw;
    max-height: 92vh;
    border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
}
/* Inline статус-бейджи */
.status-badge {
    display: inline-block;
    padding: 1px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.8;
    vertical-align: middle;
}
</style>

<div class="d-flex align-items-center mb-3 gap-3">
    <h1 class="page-title mb-0">Документация KitOper</h1>
    <span class="badge bg-secondary" style="font-size:11px;font-weight:500">v1.0</span>
</div>

{{-- TOC --}}
<div class="docs-toc">
    <h6>Содержание</h6>
    <div class="row">
        <div class="col-md-4">
            <a href="#section-start">1. Начало работы</a>
            <a href="#section-schedule-view">2. Расписание — просмотр</a>
            <a href="#section-schedule-edit">3. Расписание — редактирование</a>
            <a href="#section-form-two">4. Форма 2</a>
        </div>
        <div class="col-md-4">
            <a href="#section-dicts">5. Справочники</a>
            <a href="#section-special">6. Специальные периоды</a>
            <a href="#section-workload">7. Занятость преподавателей</a>
        </div>
        <div class="col-md-4">
            <a href="#section-ai">8. ИИ-Агент</a>
            <a href="#section-audit">9. Аудит</a>
            <a href="#section-faq">10. Частые вопросы</a>
        </div>
    </div>
</div>

<div class="accordion" id="docsAccordion">

    {{-- 1. Начало работы --}}
    <div class="accordion-item docs-section" id="section-start">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                1. Начало работы
            </button>
        </h2>
        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Страница входа</h5>
                <img src="{{ asset('img/docs/00-login.png') }}" class="docs-screenshot" alt="Страница входа">
                <p class="docs-screenshot-caption">Страница входа — выбор роли перед авторизацией</p>

                <p>Три роли в системе:</p>
                <ul>
                    <li><strong>Диспетчер</strong> — полный доступ: расписание, Форма 2, все справочники и инструменты</li>
                    <li><strong>Преподаватель</strong> — только свои пары на сегодня</li>
                    <li><strong>Ученик</strong> — просмотр расписания своей группы</li>
                </ul>

                <h5>Как войти</h5>
                <p>
                    <span class="step-badge">1</span>Перейдите по адресу системы &nbsp;
                    <span class="step-badge">2</span>Выберите тип аккаунта (Ученик / Учитель / Диспетчер) &nbsp;
                    <span class="step-badge">3</span>Введите email и пароль &nbsp;
                    <span class="step-badge">4</span>Нажмите «Войти»
                </p>

                <h5>Главный экран после входа</h5>
                <img src="{{ asset('img/docs/00-after-login.png') }}" class="docs-screenshot" alt="Главный экран расписания">
                <p class="docs-screenshot-caption">Расписание — главный экран. Диспетчер попадает сюда сразу после входа</p>

                <p>В левом меню — все разделы системы. На каждой странице в <strong>правом нижнем углу</strong> есть кнопка <strong>«? Помощь»</strong> — запускает интерактивный тур по элементам страницы.</p>
            </div>
        </div>
    </div>

    {{-- 2. Расписание — просмотр --}}
    <div class="accordion-item docs-section" id="section-schedule-view">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                2. Расписание — просмотр
            </button>
        </h2>
        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Режим «Неделя»</h5>
                <img src="{{ asset('img/docs/01-schedule-home.png') }}" class="docs-screenshot" alt="Расписание — режим Неделя">
                <p class="docs-screenshot-caption">Режим «Неделя» — все группы курса за 5 дней</p>

                <p>Над таблицей расписания:</p>
                <ul>
                    <li><strong>Числитель / знаменатель</strong> — строка «Сейчас показывается: неделя B (знаменатель)» под заголовком. Расписание чередуется еженедельно: A → B → A… При переходе к следующей/предыдущей неделе тип переключается автоматически.</li>
                    <li><strong>Выбор курса</strong> — дропдаун «Курс» переключает между 1–4 курсами.</li>
                    <li><strong>Поиск по группе или предмету</strong> — поле в правом верхнем углу.</li>
                    <li><strong>Выбор даты + «Показать неделю»</strong> — перейти к любой конкретной неделе семестра.</li>
                </ul>

                <p>Кнопки над таблицей:</p>
                <table>
                    <tr><th>Кнопка</th><th>Действие</th></tr>
                    <tr><td>Анализ недели</td><td>Проверяет конфликты: один преподаватель/кабинет в двух группах одновременно</td></tr>
                    <tr><td>Редактор недели</td><td>Открывает редактор шаблона расписания</td></tr>
                    <tr><td>Развернуть семестр</td><td>Копирует шаблон на все недели указанного периода</td></tr>
                    <tr><td>Дополнительно ▾</td><td>Меню дополнительных инструментов</td></tr>
                </table>

                <h5>Режим «День»</h5>
                <img src="{{ asset('img/docs/page-schedule-day.png') }}" class="docs-screenshot" alt="Расписание — режим День">
                <p class="docs-screenshot-caption">Режим «День» — один день с возможностью автоподстановки кабинетов</p>

                <p>Дополнительные возможности в режиме «День»:</p>
                <ul>
                    <li><strong>«Подставить кабинеты на день»</strong> — автоматически назначает свободные аудитории всем парам дня. Преподаватели с кабинетом по умолчанию получают свой кабинет.</li>
                    <li><strong>«Очистить кабинеты на день»</strong> — убрать все кабинеты дня и начать заново.</li>
                    <li>Навигация: <strong>«Предыдущий» / «Сегодня» / «Следующий»</strong> — по одному дню.</li>
                </ul>

                <div class="tip">Кнопка <strong>«? Помощь»</strong> в правом нижнем углу запускает пошаговый тур по элементам страницы. В режиме «День» кнопка отображается с подписью, в режиме «Неделя» — как иконка.</div>
            </div>
        </div>
    </div>

    {{-- 3. Расписание — редактирование --}}
    <div class="accordion-item docs-section" id="section-schedule-edit">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                3. Расписание — редактирование
            </button>
        </h2>
        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Как составить расписание с нуля</h5>
                <p>
                    <span class="step-badge">1</span>Откройте <strong>«Редактор недели»</strong><br>
                    <span class="step-badge">2</span>Выберите группу<br>
                    <span class="step-badge">3</span>Заполните пары по дням — числитель и знаменатель<br>
                    <span class="step-badge">4</span>Нажмите <strong>«Развернуть семестр»</strong> → укажите даты периода → подтвердите<br>
                    <span class="step-badge">5</span>Откройте расписание — пары появятся на реальных датах
                </p>

                <h5>Генератор расписания</h5>
                <img src="{{ asset('img/docs/page-generator.png') }}" class="docs-screenshot" alt="Генератор расписания">
                <p class="docs-screenshot-caption">Генератор — автоматически составляет шаблонную неделю по нормативам Формы 2</p>

                <p>Генератор считает нужное количество пар в неделю по нормативам Формы 2, затем расставляет их по слотам избегая конфликтов преподавателей.</p>
                <table>
                    <tr><th>Параметр</th><th>Значение</th></tr>
                    <tr><td>Курс / Группа / Семестр</td><td>Для какой группы строить</td></tr>
                    <tr><td>Неделя-шаблон</td><td>Дата недели, которая будет создана как шаблон</td></tr>
                    <tr><td>Недель в семестре</td><td>Сколько недель охватывает семестр (обычно 18)</td></tr>
                    <tr><td>Макс. пар в день</td><td>Ограничение пар на группу в день (обычно 4)</td></tr>
                    <tr><td>Разрешить субботу</td><td>Учитывать субботу при расстановке</td></tr>
                    <tr><td>Перезаписать расписание</td><td>Удалить текущее перед генерацией</td></tr>
                </table>
                <p>После генерации результат откроется в <strong>Редакторе недели</strong> — там можно поправить вручную, затем дублировать на весь семестр через «Дубликат недели».</p>

                <h5>Дубликат недели</h5>
                <img src="{{ asset('img/docs/page-week-duplicate.png') }}" class="docs-screenshot" alt="Дубликат недели">
                <p class="docs-screenshot-caption">Дубликат недели — копирует расписание одной недели на весь выбранный период</p>

                <p>Параметры:</p>
                <ul>
                    <li><strong>Неделя-шаблон</strong> — исходная неделя (выбирается отдельно от периода)</li>
                    <li><strong>Начало / Окончание периода</strong> — куда растянуть расписание</li>
                    <li><strong>«Пропускать недели, где уже есть расписание»</strong> — не перезаписывать заполненные недели</li>
                    <li><strong>«Синхронизировать Форму 2»</strong> ✅ — при дублировании автоматически обновить нормативы Ф2</li>
                </ul>

                <h5>Диалог редактирования пары (✏️)</h5>
                <ul>
                    <li>Выберите предмет, преподавателя, кабинет</li>
                    <li><strong>«Включить знаменатель»</strong> — если в неделю B другой предмет или преподаватель</li>
                    <li><strong>«Включить подгруппу 2»</strong> — если группа делится (две подгруппы в разных кабинетах одновременно)</li>
                    <li><strong>«Включить замену»</strong> — если основной преподаватель отсутствует, выберите заменяющего</li>
                </ul>
                <div class="tip">Преподаватели, занятые в это время или зафиксированные как отсутствующие, выделены красным/оранжевым в выпадающем списке.</div>

                <div class="warn">Повторный запуск «Развернуть семестр» перезаписывает существующие пары. В середине семестра лучше использовать «Дубликат недели» для точечных изменений.</div>
            </div>
        </div>
    </div>

    {{-- 4. Форма 2 --}}
    <div class="accordion-item docs-section" id="section-form-two">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                4. Форма 2
            </button>
        </h2>
        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Как выглядит Форма 2</h5>
                <img src="{{ asset('img/docs/03-form-two.png') }}" class="docs-screenshot" alt="Форма 2">
                <p class="docs-screenshot-caption">Форма 2 — журнал учёта фактических занятий группы за месяц. Строки = предметы, колонки = дни месяца</p>

                <h5>Что это</h5>
                <p>Официальный журнал учёта фактических занятий. Заполняется <strong>автоматически из расписания</strong>. Ручная правка — только при ошибках в расписании.</p>

                <h5>Как открыть нужный период</h5>
                <p>Курс → Группа → Семестр → Месяц → Год → нажмите <strong>«ОК»</strong>.</p>

                <h5>Статусы ячеек</h5>
                <table>
                    <tr><th>Вид</th><th>Значение</th></tr>
                    <tr><td><span class="status-badge" style="background:#ede9fe;color:#6941c6">2</span></td><td>Пара проведена нормально</td></tr>
                    <tr><td><span class="status-badge" style="background:#fef3c7;color:#92400e">■</span></td><td>Предмет заменён — у основного преподавателя пара не считается</td></tr>
                    <tr><td><span class="status-badge" style="background:#fee2e2;color:#b91c1c">2</span></td><td>Замена — бонусные часы заменяющему преподавателю</td></tr>
                    <tr><td><span class="status-badge" style="background:#f3f4f6;color:#9ca3af">•</span></td><td>Пары нет в этот день</td></tr>
                    <tr><td><span class="status-badge" style="background:#fef9c3;color:#854d0e">П</span></td><td>Государственный праздник</td></tr>
                    <tr><td><span class="status-badge" style="background:#fef08a;color:#78350f">Практика</span></td><td>День практики или полевых сборов</td></tr>
                    <tr><td><span style="font-size:12px;color:#6b7280">- - -</span></td><td>Прогноз (Ghost-режим) — в базу не записывается</td></tr>
                </table>
                <p>Выходные дни в таблице выделены <strong>зелёным фоном</strong>.</p>

                <h5>Ghost-режим (прогноз семестра)</h5>
                <p>Включите переключатель <strong>«Просмотр семестра»</strong>. Показывает сколько часов будет отработано к концу месяца по текущему шаблону расписания. Данные НЕ пишутся в базу — только для просмотра.</p>
                <div class="tip">Ghost-режим работает только если для группы заполнен шаблон недели (Редактор недели).</div>

                <h5>Режим коррекции</h5>
                <p>Включите тумблер <strong>«Режим коррекции»</strong> → ячейки станут редактируемыми. Используйте для исправления ошибок. После правок нажмите «Сохранить».</p>

                <h5>Экспорт</h5>
                <p>«Экспорт в Excel» — текущий месяц в .xlsx. «Экспорт 1 семестра» / «Экспорт 2 семестра» — сводная ведомость за весь семестр.</p>

                <h5>Шаблоны Формы 2</h5>
                <img src="{{ asset('img/docs/page-form-two-templates.png') }}" class="docs-screenshot" alt="Шаблоны Формы 2">
                <p class="docs-screenshot-caption">Шаблоны Ф2 — определяют какие предметы и нормативы попадают в Форму 2 для каждой группы</p>

                <p>Шаблон применяется к группе по <strong>токену</strong> — если токен «ПО, БКЕ, М», он применяется к любой группе, название которой содержит «ПО», «БКЕ» или «М». Один шаблон может покрывать несколько групп одновременно.</p>
            </div>
        </div>
    </div>

    {{-- 5. Справочники --}}
    <div class="accordion-item docs-section" id="section-dicts">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                5. Справочники
            </button>
        </h2>
        <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Преподаватели</h5>
                <img src="{{ asset('img/docs/04-teachers.png') }}" class="docs-screenshot" alt="Справочник преподавателей">
                <p class="docs-screenshot-caption">Справочник преподавателей — добавление с назначением предметов по курсам</p>

                <p>Добавьте всех преподавателей <strong>до составления расписания</strong>. При добавлении сразу назначьте предметы по курсам — только с назначенными предметами преподаватель появится в выпадающем списке при редактировании пары.</p>
                <div class="warn">Если преподаватель не появляется в списке при редактировании пары — откройте справочник и убедитесь, что у него отмечены нужные предметы.</div>

                <h5>Группы</h5>
                <img src="{{ asset('img/docs/05-groups.png') }}" class="docs-screenshot" alt="Список групп">
                <p class="docs-screenshot-caption">Список групп 1 курса. Тип (kz/ru), подвоение, кнопка «Завершить учебный год»</p>

                <p>При добавлении группы:</p>
                <ul>
                    <li><strong>Тип</strong> (Казахский / Русский) — определяет язык названий предметов в расписании</li>
                    <li><strong>«Есть подгруппа 2»</strong> — включает деление группы на пары. Включите один раз, потом отдельно в редакторе указывайте на каких парах группа делится.</li>
                </ul>
                <p>Кнопка <strong style="color:#dc2626">«Завершить учебный год»</strong> — переводит группу на следующий курс. Используется один раз в год.</p>

                <h5>Дисциплины</h5>
                <p>Список предметов по курсам. Изменение названия сразу применяется везде. Заполните казахское название для казахских групп.</p>

                <h5>Аудитории</h5>
                <p>Деактивируйте кабинет если он на ремонте — он пропадёт из списков выбора во всём расписании.</p>

                <h5>Пользователи</h5>
                <img src="{{ asset('img/docs/page-users.png') }}" class="docs-screenshot" alt="Пользователи системы">
                <p class="docs-screenshot-caption">Управление пользователями — только для диспетчера</p>

                <p>Раздел доступен только диспетчеру. Позволяет изменить роль любого пользователя или удалить его. Нельзя удалить или изменить роль себе.</p>
            </div>
        </div>
    </div>

    {{-- 6. Специальные периоды --}}
    <div class="accordion-item docs-section" id="section-special">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
                6. Специальные периоды
            </button>
        </h2>
        <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Праздники</h5>
                <p>Добавьте праздничные дни <strong>до разворачивания семестра</strong> — эти дни будут пропущены. В Форме 2 ячейки за праздники помечаются буквой «П».</p>

                <h5>Практика</h5>
                <p>Укажите группу и период практики — все пары группы в эти дни будут помечены в Форме 2 как практика. Удаление периода восстанавливает пары из расписания.</p>

                <h5>Полевые сборы</h5>
                <img src="{{ asset('img/docs/page-field-camps.png') }}" class="docs-screenshot" alt="Полевые сборы">
                <p class="docs-screenshot-caption">Полевые сборы — скрывают расписание группы и автоматически формируют часы в Форме 2</p>

                <p>Добавьте период сборов: группа, преподаватель, кабинет, даты начала и окончания, часов в день (по умолчанию 6). Система автоматически:</p>
                <ul>
                    <li>Скрывает обычное расписание группы за эти дни</li>
                    <li>Добавляет строки с часами сборов в Форму 2</li>
                </ul>

                <h5>Отсутствия преподавателей</h5>
                <p>Зафиксируйте болезнь, командировку или отпуск. После добавления:</p>
                <ol>
                    <li>Преподаватель выделяется оранжевым в списках при редактировании пар</li>
                    <li>«Анализ расписания» покажет его пары без назначенной замены</li>
                    <li>Откройте каждую пару → «Включить замену» → выберите заменяющего</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- 7. Занятость --}}
    <div class="accordion-item docs-section" id="section-workload">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7">
                7. Занятость преподавателей
            </button>
        </h2>
        <div id="collapse7" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <img src="{{ asset('img/docs/page-workload.png') }}" class="docs-screenshot" alt="Занятость преподавателей">
                <p class="docs-screenshot-caption">Матрица занятости — строки = пары по дням, столбцы = преподаватели</p>

                <p>Таблица показывает занятость всех преподавателей на выбранную неделю. Заполненная ячейка — пара назначена (показывает группу). «Свободно» — преподаватель доступен в это время.</p>

                <p><strong>Как использовать при назначении замены:</strong> откройте таблицу, найдите нужное время — сразу видно кто из преподавателей свободен. Поиск по фамилии сверху фильтрует столбцы.</p>
            </div>
        </div>
    </div>

    {{-- 8. AI-агент --}}
    <div class="accordion-item docs-section" id="section-ai">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8">
                8. ИИ-Агент
            </button>
        </h2>
        <div id="collapse8" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <p>Встроенный чат на локальной AI-модели (Ollama). Знает данные системы — может отвечать на вопросы о расписании, преподавателях и группах.</p>
                <h5>Примеры запросов</h5>
                <ul>
                    <li>«Какие пары у группы ПО-115 в среду?»</li>
                    <li>«Покажи нагрузку преподавателя Ахметова»</li>
                    <li>«Импортируй расписание из этого файла» (загрузите .xlsx)</li>
                </ul>
                <h5>Индикатор состояния модели</h5>
                <table>
                    <tr><th>Цвет</th><th>Значение</th></tr>
                    <tr><td>🟢 Зелёный</td><td>Модель готова к работе</td></tr>
                    <tr><td>🟡 Жёлтый</td><td>Загружается — подождите 1–2 минуты</td></tr>
                    <tr><td>🔴 Красный</td><td>Ollama недоступна — проверьте что сервис запущен</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- 9. Аудит --}}
    <div class="accordion-item docs-section" id="section-audit">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9">
                9. Аудит
            </button>
        </h2>
        <div id="collapse9" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <p>Журнал всех изменений в системе: кто, когда, что изменил. Используйте для расследования ошибок в расписании или Форме 2.</p>
                <div class="warn">Кнопка «Очистить» удаляет все записи безвозвратно. Используйте только в начале нового учебного года.</div>
            </div>
        </div>
    </div>

    {{-- 10. FAQ --}}
    <div class="accordion-item docs-section" id="section-faq">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10">
                10. Частые вопросы
            </button>
        </h2>
        <div id="collapse10" class="accordion-collapse collapse" data-bs-parent="#docsAccordion">
            <div class="accordion-body">
                <h5>Данные в Форме 2 не обновляются после изменения расписания?</h5>
                <p>Откройте нужный месяц заново — данные обновятся при загрузке страницы.</p>

                <h5>Преподаватель не появляется в списке при редактировании пары?</h5>
                <p>Откройте «Преподаватели» → найдите его → разверните предметы → отметьте нужные галочками. Без назначенных предметов преподаватель не показывается.</p>

                <h5>Как поставить разные предметы двум подгруппам одновременно?</h5>
                <p>
                    <span class="step-badge">1</span>В справочнике «Группы» включите «Есть подгруппа 2» для нужной группы<br>
                    <span class="step-badge">2</span>В диалоге пары → поставьте «Включить подгруппу 2»<br>
                    <span class="step-badge">3</span>Заполните поля для подгруппы 2 отдельно
                </p>

                <h5>Как посмотреть неделю A если сейчас показывается неделя B?</h5>
                <p>Введите дату конкретной недели A в поле выбора даты и нажмите «Показать».</p>

                <h5>Ghost-режим не показывает прогноз?</h5>
                <p>Убедитесь, что для группы заполнен шаблон недели (Редактор недели). Ghost строит прогноз именно по шаблону.</p>

                <h5>Таблица Формы 2 пустая — нет строк с предметами?</h5>
                <p>Нет нормативов для этой группы. Проверьте раздел «Шаблоны Ф2» — добавьте шаблон с подходящим токеном для группы.</p>

                <h5>Кнопка «Развернуть семестр» перезаписывает существующее расписание?</h5>
                <p>Да. В середине семестра лучше использовать «Дубликат недели» — он умеет пропускать недели где уже есть расписание.</p>

                <h5>Преподаватель выделен красным в выпадающем списке?</h5>
                <p>Он занят в это время у другой группы. Выбрать можно, но появится предупреждение о конфликте. Проверьте таблицу «Занятость».</p>
            </div>
        </div>
    </div>

</div>

{{-- Лайтбокс --}}
<div id="docs-lightbox">
    <img id="docs-lightbox-img" src="" alt="">
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tours/docs.js') }}"></script>
<script>
// Лайтбокс для скриншотов
document.querySelectorAll('.docs-screenshot').forEach(function(img) {
    img.addEventListener('click', function() {
        document.getElementById('docs-lightbox-img').src = this.src;
        document.getElementById('docs-lightbox').classList.add('active');
    });
});
document.getElementById('docs-lightbox').addEventListener('click', function() {
    this.classList.remove('active');
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('docs-lightbox').classList.remove('active');
});
</script>
@endpush
