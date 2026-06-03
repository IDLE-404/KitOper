(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Журнал изменений',
                description: 'Полная история всех изменений в системе. Используйте для поиска ошибок: кто и когда изменил расписание.'
            }
        },
        {
            element: 'form button[type="submit"].btn-primary',
            popover: {
                title: 'Фильтр',
                description: 'Укажите период, пользователя или тип действия — нажмите «Найти». Удобно при расследовании конкретного изменения.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Запись в журнале',
                description: 'Кто, когда, что изменил и на каком объекте. Нажмите на запись чтобы увидеть детали изменения.'
            }
        },
        {
            element: 'button[type="submit"][style*="fee2e2"]',
            popover: {
                title: 'Очистить журнал',
                description: 'Удаляет все записи. Восстановить нельзя. Используйте только в начале нового учебного года.'
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
