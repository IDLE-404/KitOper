(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Шаблоны Формы 2',
                description: 'Шаблон — это набор предметов и плановых часов для определённого типа групп. Когда открывается Форма 2 группы, строки таблицы берутся из нормативов, которые в свою очередь строятся по шаблону. Настройте шаблоны до начала семестра.'
            }
        },
        {
            element: 'button[type="submit"]',
            popover: {
                title: 'Показать шаблон',
                description: 'Выберите шаблон из списка и нажмите «Показать» — откроется список предметов и часов этого шаблона.'
            }
        },
        {
            element: '#newTemplateActive',
            popover: {
                title: 'Активный шаблон',
                description: 'Галочка «Активен» — шаблон используется при автоматическом создании нормативов. Неактивные шаблоны хранятся, но не применяются к новым группам.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Предмет в шаблоне',
                description: 'Каждая строка — предмет с плановыми часами. Отредактируйте часы если норматив изменился. Добавьте строку если в семестре появился новый предмет.'
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
