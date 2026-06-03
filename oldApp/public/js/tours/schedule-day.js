(function () {
    'use strict';

    var STEPS = [
        {
            element: '.page-title',
            popover: {
                title: 'Расписание — режим «День»',
                description: 'Показывает все группы только на один выбранный день. Здесь удобно работать с кабинетами: можно автоматически расставить их за один клик.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Переключите курс — покажутся группы этого курса на выбранный день.'
            }
        },
        {
            element: '#daySelect',
            popover: {
                title: 'День недели',
                description: 'Выберите день (Пн–Пт) из выпадающего списка.'
            }
        },
        {
            element: '#weekStartInput',
            popover: {
                title: 'Дата недели',
                description: 'Выберите понедельник нужной недели, затем нажмите «Показать».'
            }
        },
        {
            element: '#dayPrev',
            popover: {
                title: 'Навигация по дням',
                description: '«‹» и «›» переключают дни вперёд-назад. «Сегодня» — перейти на текущий день.'
            }
        },
        {
            element: '#autoAssignRoomsDayBtn',
            popover: {
                title: 'Автоподстановка кабинетов',
                description: 'Нажмите — система автоматически назначит свободные кабинеты всем парам этого дня. Для преподавателей с кабинетом по умолчанию подставляется их кабинет, для остальных — любой свободный. После подстановки проверьте результат и при необходимости скорректируйте вручную.'
            }
        },
        {
            element: '#clearRoomsDayBtn',
            popover: {
                title: 'Очистить кабинеты',
                description: 'Убирает все кабинеты у пар этого дня. Используйте если автоподстановка дала неверный результат и нужно начать заново.'
            }
        },
        {
            element: '#scheduleHealthBtn',
            popover: {
                title: 'Проверить конфликты',
                description: 'Проверяет текущую неделю на конфликты кабинетов и отсутствующих без замены преподавателей.'
            }
        },
        {
            element: '.cell-edit',
            popover: {
                title: 'Редактировать пару',
                description: 'Нажмите ✏️ на любой ячейке — откроется диалог редактирования. Всё то же самое, что в режиме «Неделя»: предмет, преподаватель, кабинет, замена, подгруппа 2.'
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
