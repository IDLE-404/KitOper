(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Дубликат недели',
                description: 'Копирует расписание одной конкретной недели на другой период. Используйте если нужно изменить одну неделю, не трогая остальные, или перенести расписание на каникулярную неделю.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Выберите курс — все группы этого курса будут скопированы.'
            }
        },
        {
            element: '#groupSelect',
            popover: {
                title: 'Группа (необязательно)',
                description: 'Если нужно скопировать только одну группу — выберите её. Если оставить пустым — скопируются все группы курса.'
            }
        },
        {
            element: '#templateWeekStart',
            popover: {
                title: 'Неделя-источник',
                description: 'Укажите понедельник той недели, расписание которой нужно скопировать.'
            }
        },
        {
            element: '#periodStart',
            popover: {
                title: 'Куда копировать',
                description: 'Укажите период (начало и конец), на который нужно распространить копию. Система скопирует расписание недели-источника на каждую неделю этого периода.'
            }
        },
        {
            element: 'button[type="submit"]',
            popover: {
                title: 'Сделать дубликат',
                description: 'Нажмите для копирования. После завершения проверьте расписание на главной странице.'
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
