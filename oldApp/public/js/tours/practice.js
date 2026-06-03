(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Практика',
                description: 'Задайте периоды, когда группа на практике. В эти даты пары в Форме 2 будут отображаться как «Практика» — не как проведённые занятия.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Выберите курс — список групп обновится.'
            }
        },
        {
            element: '#practiceForm',
            popover: {
                title: 'Добавить период',
                description: 'Выберите группу, тип практики, даты начала и конца. Нажмите «Добавить». Все пары группы в этот период будут помечены в Форме 2.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Период практики',
                description: 'Группа, тип, даты. «Удалить» — убирает период, пары восстанавливаются из расписания.'
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
