(function () {
    'use strict';

    var STEPS = [
        {
            element: '.page-title',
            popover: {
                title: 'Расписание',
                description: 'Главная страница расписания. Здесь вы видите все группы выбранного курса на текущей неделе и редактируете отдельные пары.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Выберите курс (1–4) — расписание переключится. У каждого курса своё независимое расписание.'
            }
        },
        {
            element: '.segmented',
            popover: {
                title: 'Неделя / День',
                description: 'Переключайтесь между режимами. <strong>Неделя</strong> — все дни сразу, удобно для контроля. <strong>День</strong> — один день, есть кнопка автоподстановки кабинетов.'
            }
        },
        {
            element: '#weekStartInput',
            popover: {
                title: 'Переход к нужной неделе',
                description: 'Выберите понедельник нужной недели и нажмите «Показать». Над таблицей появится метка <strong>A</strong> или <strong>B</strong> — это числитель или знаменатель, система определяет автоматически по дате.'
            }
        },
        {
            element: '.nav-right',
            popover: {
                title: 'Листать недели',
                description: 'Кнопки «‹» и «›» переключают недели вперёд-назад. Режим A/B чередуется автоматически.'
            }
        },
        {
            element: '#scheduleHealthBtn',
            popover: {
                title: 'Проверить конфликты',
                description: 'Нажмите чтобы система проверила текущую неделю. Если есть конфликт кабинета или преподаватель отсутствует без замены — появятся предупреждения с описанием проблемы.'
            }
        },
        {
            element: '.cell-edit',
            popover: {
                title: 'Редактировать пару',
                description: 'Нажмите <strong>✏️</strong> на любой ячейке — откроется диалог редактирования этой пары.'
            }
        },
        {
            element: '#pairModal',
            popover: {
                title: 'Диалог редактирования пары',
                description: 'Выберите предмет, преподавателя и кабинет. По умолчанию настраивается числитель (A). Если в неделю B нужно другое — поставьте галочку «Включить знаменатель» и заполните второй блок.',
                side: 'left'
            }
        },
        {
            element: '#modalHasSub2',
            popover: {
                title: 'Включить подгруппу 2',
                description: 'Отметьте если <strong>на этой паре</strong> группа делится. Появится второй блок — задайте отдельный предмет и преподавателя для подгруппы 2. Оба занятия проходят одновременно в разных кабинетах.'
            }
        },
        {
            element: '#modalHasDen',
            popover: {
                title: 'Включить знаменатель',
                description: 'Отметьте если в неделю B (знаменатель) нужен <strong>другой</strong> предмет или преподаватель. Появится второй блок. Если знаменатель не включён — пара одинакова в обе недели.'
            }
        },
        {
            element: '#modalTeacher1',
            popover: {
                title: 'Список преподавателей',
                description: 'Преподаватели, занятые в это время у другой группы, выделены <strong>красным</strong>. Преподаватели из списка отсутствий — <strong>оранжевым</strong>. Выбрать их можно, но система покажет предупреждение о конфликте.'
            }
        },
        {
            element: '#modalReplacementToggle1',
            popover: {
                title: 'Включить замену',
                description: 'Если основной преподаватель не может провести пару — отметьте «Включить замену» и выберите заменяющего. В Форме 2 у основного эта пара будет пустой, а у заменяющего добавятся бонусные часы.'
            }
        },
        {
            element: '.tools-dropdown',
            popover: {
                title: 'Дополнительно',
                description: 'Переход к вспомогательным разделам: праздники, практика, отсутствия преподавателей, дисциплины, занятость. Всё это влияет на то, как отображается расписание и Форма 2.'
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
