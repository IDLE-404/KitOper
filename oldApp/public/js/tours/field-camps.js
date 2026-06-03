(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Полевые сборы',
                description: 'Периоды, когда группа на военных или спортивных полевых сборах. Работает аналогично практике — пары в эти даты будут помечены в Форме 2 как «Полевые сборы».'
            }
        },
        {
            element: '#fieldCampForm',
            popover: {
                title: 'Добавить период',
                description: 'Выберите группу, укажите даты начала и конца сборов. Нажмите «Сохранить».'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Период сборов',
                description: 'Группа и даты. «Удалить» — убирает период, пары восстанавливаются из расписания.'
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
