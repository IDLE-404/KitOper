(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Праздники',
                description: 'Добавьте государственные праздники и каникулы до разворачивания семестра. Система пропустит эти даты при копировании шаблона и пометит их в Форме 2 буквой «П».'
            }
        },
        {
            element: '#holidayName',
            popover: {
                title: 'Добавить праздник',
                description: 'Введите название, укажите дату начала и конца (для каникул — диапазон). Нажмите «Добавить».'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Праздник в списке',
                description: '«Изменить» — скорректировать даты. «Удалить» — убрать праздник, после чего дни снова станут рабочими в расписании.'
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
