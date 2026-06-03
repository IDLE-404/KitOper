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
.docs-section h5 { font-size: 14px; font-weight: 700; margin: 18px 0 6px; }
.docs-section table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 13px; }
.docs-section table th { background: #f3f4f6; padding: 6px 10px; text-align: left; font-weight: 600; }
.docs-section table td { padding: 6px 10px; border-top: 1px solid #e5e7eb; }
.docs-section .tip { background: #f0fdf4; border-left: 3px solid #22c55e; padding: 10px 14px; border-radius: 4px; margin: 10px 0; font-size: 13px; }
.docs-section .warn { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 10px 14px; border-radius: 4px; margin: 10px 0; font-size: 13px; }
.step-badge { display: inline-block; background: #6941c6; color: #fff; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; font-size: 11px; font-weight: 700; margin-right: 6px; }
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
                <h5>Что такое KitOper</h5>
                <p>Система управления расписанием колледжа. Роли пользователей:</p>
                <ul>
                    <li><strong>Диспетчер</strong> — полный доступ: расписание, Форма 2, все справочники</li>
                    <li><strong>Преподаватель</strong> — только свои пары на сегодня</li>
                    <li><strong>Студент</strong> — просмотр расписания своей группы</li>
                </ul>

                <h5>Как войти</h5>
                <p><span class="step-badge">1</span>Перейдите по адресу системы &nbsp;
                   <span class="step-badge">2</span>Выберите тип аккаунта &nbsp;
                   <span class="step-badge">3</span>Введите email и пароль &nbsp;
                   <span class="step-badge">4</span>Нажмите «Войти»</p>

                <h5>Боковое меню</h5>
                <p>Навигация по всем разделам. На каждой странице есть кнопка <strong>«? Помощь»</strong> (правый нижний угол) — запускает интерактивный тур по элементам страницы.</p>
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
                <h5>Режимы просмотра</h5>
                <p><strong>Неделя</strong> — все группы курса за 5 дней. <strong>День</strong> — один день, удобно для автоподстановки кабинетов.</p>

                <h5>Числитель (A) и знаменатель (B)</h5>
                <p>Расписание чередуется еженедельно: неделя A → неделя B → неделя A... Метка в заголовке страницы показывает текущий режим. При навигации по неделям система переключает режим автоматически.</p>

                <h5>Навигация</h5>
                <p>Кнопки «‹» / «›» — предыдущая/следующая неделя. Поле даты + кнопка «Показать» — перейти к конкретной неделе.</p>

                <h5>Ячейка пары</h5>
                <p>Показывает предмет, преподавателя, кабинет. Цвета:</p>
                <table>
                    <tr><th>Цвет</th><th>Значение</th></tr>
                    <tr><td>Обычная</td><td>Нормальная пара</td></tr>
                    <tr><td>Жёлтая</td><td>Назначена замена преподавателя</td></tr>
                    <tr><td>Красная рамка</td><td>Конфликт кабинета или преподавателя</td></tr>
                </table>
                <p>Нажмите <strong>✏️</strong> на ячейке — откроется диалог редактирования.</p>

                <h5>Анализ расписания</h5>
                <p>Кнопка «Анализ» проверяет конфликты: один кабинет у двух групп одновременно, преподаватель отсутствует без замены. Красный значок — найдены проблемы.</p>

                <h5>Режим «День»: автоподстановка кабинетов</h5>
                <p>Переключитесь в режим «День» → нажмите <strong>«Подставить кабинеты»</strong>. Система назначит свободные кабинеты всем парам дня (преподаватели с кабинетом по умолчанию — в свой кабинет). Кнопка «Очистить кабинеты» — убрать все и начать заново.</p>
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
                    <span class="step-badge">3</span>Заполните пары по дням: числитель и знаменатель<br>
                    <span class="step-badge">4</span>Прокрутите вниз → <strong>«Развернуть на семестр»</strong> → укажите даты → нажмите кнопку<br>
                    <span class="step-badge">5</span>Откройте расписание — пары появятся на реальных датах
                </p>

                <h5>Диалог редактирования пары (✏️)</h5>
                <ul>
                    <li>Выберите предмет, преподавателя, кабинет</li>
                    <li><strong>«Включить знаменатель»</strong> — если в неделю B другой предмет или преподаватель</li>
                    <li><strong>«Включить подгруппу 2»</strong> — если группа делится на эту пару (две подгруппы занимаются одновременно в разных кабинетах)</li>
                    <li><strong>«Включить замену»</strong> — если основной преподаватель отсутствует, выберите заменяющего</li>
                </ul>

                <div class="tip">Преподаватели, занятые в это время или отсутствующие, выделены красным/оранжевым в выпадающем списке.</div>

                <h5>Подгруппа 2</h5>
                <p>Включается в двух местах:</p>
                <ul>
                    <li><strong>В справочнике групп</strong> — галочка «Есть подгруппа 2» на самой группе. Включите один раз.</li>
                    <li><strong>В редакторе недели / диалоге пары</strong> — галочка «Включить подгруппу 2» на конкретной паре. Включайте только на парах, где группа реально делится.</li>
                </ul>

                <h5>Назначить замену</h5>
                <p>В диалоге пары → «Включить замену» → выберите заменяющего преподавателя. В Форме 2: у основного эта пара будет помечена как не проведённая, у заменяющего добавятся бонусные часы.</p>

                <h5>Дубликат недели</h5>
                <p>Копирует расписание одной недели на другой период. Используйте если нужно изменить одну неделю без перестройки всего шаблона.</p>

                <h5>Развернуть семестр</h5>
                <p>Копирует шаблон на все недели периода, чередуя A/B и пропуская праздники и каникулы.</p>

                <div class="warn">Повторный запуск «Развернуть семестр» перезаписывает существующие пары. Используйте с осторожностью в середине семестра.</div>
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
                <h5>Что это</h5>
                <p>Официальный журнал учёта фактических занятий. Заполняется автоматически из расписания. Ручная правка — только при ошибках в расписании.</p>

                <h5>Как открыть нужный период</h5>
                <p>Курс → Группа → Семестр → Месяц → Год → нажмите <strong>«ОК»</strong>.</p>

                <h5>Цвета ячеек</h5>
                <table>
                    <tr><th>Вид</th><th>Значение</th></tr>
                    <tr><td>Фиолетовый «2»</td><td>Пара проведена нормально</td></tr>
                    <tr><td>Жёлтый «■»</td><td>Предмет заменён — эта пара не считается у преподавателя</td></tr>
                    <tr><td>Красный «2»</td><td>Замена — бонусные часы заменяющему преподавателю</td></tr>
                    <tr><td>Серый «•»</td><td>Пары нет в этот день</td></tr>
                    <tr><td>«П»</td><td>Государственный праздник</td></tr>
                    <tr><td>Жёлтый фон</td><td>Практика или полевые сборы</td></tr>
                    <tr><td>Пунктир</td><td>Прогноз (Ghost-режим) — в базу не записывается</td></tr>
                </table>

                <h5>Ghost-режим (прогноз)</h5>
                <p>Включите переключатель «👻 Просмотр семестра». Показывает сколько часов будет отработано к концу месяца по текущему шаблону. Удобно проверить выработку нормы до конца месяца.</p>

                <h5>Режим коррекции</h5>
                <p>Включите тумблер → ячейки станут редактируемыми. Используйте для исправления ошибок расписания. После правок нажмите <strong>«Сохранить коррекцию»</strong>. Кнопка «Добавить предмет» — если нужная строка отсутствует в таблице.</p>

                <h5>Таблица замен</h5>
                <p>Вторая таблица внизу — только строки с заменами. Показывает кто кого замещал и сколько бонусных часов начислено. Используется для расчёта доплаты.</p>

                <h5>Экспорт</h5>
                <p>«Экспорт» — текущий месяц в .xlsx. «Экспорт 1 семестр» / «Экспорт 2 семестр» — сводная ведомость за весь семестр.</p>
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
                <p>Добавьте всех до составления расписания. <strong>Обязательно</strong> назначьте предметы каждому преподавателю (кнопка «Развернуть предметы» → галочки) — иначе он не будет появляться в выпадающем списке при редактировании пары. Кабинет по умолчанию используется при автоподстановке кабинетов. Оранжевая строка = дубликат, нужно разобраться.</p>

                <h5>Группы</h5>
                <p>Добавьте все группы. Тип (рус/каз) определяет язык названий предметов. Галочка «Есть подгруппа 2» — включает деление группы. Кнопка <strong>«Завершить учебный год»</strong> — переводит группу на следующий курс, используется один раз в год.</p>

                <h5>Дисциплины</h5>
                <p>Список предметов по курсам. Заполните казахское название если нужно для казахских групп. Изменение названия сразу применяется везде.</p>

                <h5>Аудитории</h5>
                <p>Деактивируйте кабинет если он на ремонте — он пропадёт из списков выбора. Кабинет по умолчанию у преподавателя ссылается сюда.</p>
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
                <p>Добавьте до разворачивания семестра — эти дни будут пропущены. В Форме 2 помечаются буквой «П».</p>

                <h5>Практика и полевые сборы</h5>
                <p>Укажите группу и период — все пары группы в эти дни будут помечены в Форме 2 как практика/сборы. Удаление периода восстанавливает пары из расписания.</p>

                <h5>Отсутствия преподавателей</h5>
                <p>Зафиксируйте болезнь / командировку / отпуск. После этого:</p>
                <ol>
                    <li>Преподаватель выделяется оранжевым в списках при редактировании пар</li>
                    <li>Кнопка «Анализ расписания» покажет его пары без замены</li>
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
                <p>Таблица: строки — пары по дням, столбцы — преподаватели. Заполненная ячейка — пара назначена. Используйте при подборе замены чтобы сразу видеть кто свободен в нужное время. Поиск сверху фильтрует по фамилии.</p>
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
                <p>Встроенный чат на локальной AI-модели (Ollama). Знает данные системы.</p>
                <h5>Примеры запросов</h5>
                <ul>
                    <li>«Какие пары у группы ПО-115 в среду?»</li>
                    <li>«Покажи нагрузку преподавателя Ахметова»</li>
                    <li>«Импортируй расписание из этого файла» (загрузите .xlsx)</li>
                </ul>
                <p>Статус модели: зелёный — готов, жёлтый — загружается (подождите 1–2 мин), красный — недоступен.</p>
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
                <p>Журнал всех изменений в системе: кто, когда, что изменил. Используйте для расследования ошибок. Кнопка «Очистить» — удаляет все записи безвозвратно, используйте только в начале нового учебного года.</p>
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
                <p>Откройте нужный месяц заново — данные обновятся при загрузке.</p>

                <h5>Преподаватель не появляется в списке при редактировании пары?</h5>
                <p>Проверьте в справочнике преподавателей: у него должны быть отмечены галочками нужные предметы. Без них он не показывается.</p>

                <h5>Как поставить разные предметы подгруппам в одно время?</h5>
                <p>В диалоге пары поставьте галочку «Включить подгруппу 2». Появятся отдельные поля для подгруппы 2. Убедитесь, что у группы включена опция «Есть подгруппа 2» в справочнике групп.</p>

                <h5>Как посмотреть неделю A если сейчас показывается неделя B?</h5>
                <p>Введите дату конкретной недели A в поле выбора и нажмите «Показать».</p>

                <h5>Кнопка «Развернуть семестр» перезаписывает существующее расписание?</h5>
                <p>Да. Повторный запуск перезаписывает пары за указанный период. В середине семестра лучше использовать «Дубликат недели» для точечных изменений.</p>

                <h5>Ghost-режим не показывает прогноз?</h5>
                <p>Убедитесь, что для группы заполнен шаблон недели (редактор недели). Ghost строит прогноз именно по шаблону.</p>

                <h5>Преподаватель выделен красным в списке?</h5>
                <p>Он занят в это время у другой группы. Выбрать можно, но система покажет предупреждение о конфликте. Проверьте таблицу занятости.</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tours/docs.js') }}"></script>
@endpush
