(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Пользователи',
                description: 'Управление доступом в систему. Создайте учётную запись для каждого, кто должен работать с KitOper.'
            }
        },
        {
            element: 'input[name="q"]',
            popover: {
                title: 'Поиск',
                description: 'Введите имя или email для поиска пользователя.'
            }
        },
        {
            element: 'select[name="role"]',
            popover: {
                title: 'Роль пользователя',
                description: '<strong>Диспетчер</strong> — полный доступ: редактирование расписания, Форма 2, справочники. <strong>Преподаватель</strong> — видит только свои пары на текущий день. <strong>Студент</strong> — видит расписание группы. Назначайте роль в соответствии с должностью.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Строка пользователя',
                description: 'Имя, email, роль. «Сохранить» — изменить роль. «Удалить» — удалить учётную запись. Удалённый пользователь потеряет доступ немедленно.'
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
