(function () {
    'use strict';

    var STEPS = [
        {
            element: '.page-header',
            popover: {
                title: 'Форма 2',
                description: 'Официальный журнал учёта фактических занятий. Заполняется автоматически из расписания — вручную редактировать нужно только если в расписании была ошибка.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Шаг 1: выберите курс',
                description: 'Выберите курс и нажмите «ОК» — загрузится список групп этого курса.'
            }
        },
        {
            element: '#groupSelect',
            popover: {
                title: 'Шаг 2: выберите группу',
                description: 'Выберите группу. Форма 2 строится отдельно для каждой группы.'
            }
        },
        {
            element: '#semesterBtnsFilter',
            popover: {
                title: 'Шаг 3: семестр',
                description: 'Нажмите «1» или «2». Список доступных месяцев обновится: семестр 1 — сентябрь–январь, семестр 2 — февраль–июнь.'
            }
        },
        {
            element: '#monthSelect',
            popover: {
                title: 'Шаг 4: месяц',
                description: 'Выберите месяц из списка.'
            }
        },
        {
            element: '#yearPrevBtn',
            popover: {
                title: 'Шаг 5: год',
                description: 'Кнопками «‹» и «›» переключите учебный год. Январь 2025 и январь 2026 — разные семестры, учитывайте это.'
            }
        },
        {
            element: '#reloadBtn',
            popover: {
                title: 'Шаг 6: загрузить',
                description: 'Нажмите «ОК» — таблица загрузится для выбранного периода.'
            }
        },
        {
            element: '#ghostToggle',
            popover: {
                title: 'Прогноз до конца месяца',
                description: 'Включите, чтобы увидеть сколько часов <strong>будет</strong> отработано к концу месяца по шаблону расписания. Прогнозные ячейки показаны пунктиром — в базу не записываются. Удобно проверить, успеет ли группа выработать норму.'
            }
        },
        {
            element: '.legend-row',
            popover: {
                title: 'Цвета ячеек',
                description: '<strong>Фиолетовый</strong> — пара проведена. <strong>Жёлтый ■</strong> — предмет заменён (эта пара не считается у основного преподавателя). <strong>Красный</strong> — замена (бонусные часы заменяющему). <strong>Серый •</strong> — пары нет. <strong>П</strong> — праздник. <strong>Практика</strong> — группа на практике.'
            }
        },
        {
            element: '.form-two-table .col-subject',
            popover: {
                title: 'Колонка «Предмет»',
                description: 'Строки таблицы берутся из нормативов — списка предметов и плановых часов на семестр. Если нужного предмета нет — добавьте его через режим коррекции.'
            }
        },
        {
            element: '.form-two-table .col-norm',
            popover: {
                title: 'Плановые часы',
                description: 'Сколько часов должно быть проведено по этому предмету с начала семестра до текущего месяца включительно. Берётся из норматива.'
            }
        },
        {
            element: '.form-two-table .col-day',
            popover: {
                title: 'Дни месяца',
                description: 'Зелёный фон — выходной, жёлтый — праздник. Наведите на ячейку — увидите детали: номер пары, подгруппу, статус замены.'
            }
        },
        {
            element: '.form-two-table .col-used',
            popover: {
                title: 'Итоговые колонки',
                description: '<strong>Использовано</strong> — фактически проведено за месяц. <strong>Бонус</strong> — часы от замен. <strong>Остаток</strong> — сколько ещё нужно провести до конца семестра.'
            }
        },
        {
            element: '.column-totals-row',
            popover: {
                title: 'Итоговая строка',
                description: 'Сумма по всем предметам за каждый день. Используйте для проверки — если число в день больше реально возможного, значит есть дублирование в расписании.'
            }
        },
        {
            element: '.correction-switch',
            popover: {
                title: 'Режим коррекции',
                description: 'Включите, если нужно вручную исправить данные — например, в расписании была ошибка. После включения ячейки становятся редактируемыми.'
            }
        },
        {
            element: '#addSubjectBtn',
            popover: {
                title: 'Добавить предмет вручную',
                description: 'Доступно только в режиме коррекции. Добавляет строку предмета вручную — если предмет не попал в расписание, но занятия были.'
            }
        },
        {
            element: '#saveBtn',
            popover: {
                title: 'Сохранить коррекцию',
                description: 'После ручных правок нажмите «Сохранить» — изменения запишутся в базу.'
            }
        },
        {
            element: '.replacement-table',
            popover: {
                title: 'Таблица замен',
                description: 'Вторая таблица ниже — только строки с заменами. Показывает кто кого замещал и сколько бонусных часов начислено. Используется для расчёта доплаты преподавателям.'
            }
        },
        {
            element: '#subgroupTwoBody',
            popover: {
                title: 'Подгруппа 2',
                description: 'Если у группы есть подгруппы — здесь третья таблица с данными только по подгруппе 2. Структура такая же.'
            }
        },
        {
            element: 'a[href*="export"]:first-of-type',
            popover: {
                title: 'Экспорт в Excel',
                description: '«Экспорт» — скачивает Форму 2 за текущий месяц. «Экспорт 1 семестр» и «Экспорт 2 семестр» — сводная ведомость за весь семестр.'
            }
        }
    ];

    function startTour() {
        var driverFn = window.driver && window.driver.js && window.driver.js.driver;
        if (!driverFn) { console.warn('driver.js not loaded'); return; }
        var steps = STEPS.filter(function (s) {
            if (!s.element) return true;
            return document.querySelector(s.element) !== null;
        });
        var d = driverFn({
            showProgress: true, smoothScroll: true, allowClose: true,
            overlayOpacity: 0.55, stagePadding: 6, stageRadius: 8,
            nextBtnText: 'Далее →', prevBtnText: '← Назад', doneBtnText: 'Готово ✓',
            steps: steps
        });
        d.drive();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('tourHelpBtn');
        if (btn) { btn.style.display = 'block'; btn.addEventListener('click', startTour); }
    });
})();
