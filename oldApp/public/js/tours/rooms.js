(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Аудитории',
                description: 'Добавьте все кабинеты до составления расписания. Система проверяет занятость кабинетов и предупреждает о конфликтах.'
            }
        },
        {
            element: 'form[action*="/rooms"] button[type="submit"]',
            popover: {
                title: 'Добавить аудиторию',
                description: 'Введите код кабинета (например, «101» или «Спортзал»), выберите тип и активность. Неактивные кабинеты не предлагаются при назначении пар, но старые данные сохраняются.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Строка аудитории',
                description: 'Код, тип, статус. Деактивируйте кабинет если он на ремонте — он пропадёт из списков, но не удалится из истории расписания.'
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
