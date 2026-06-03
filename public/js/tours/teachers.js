(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Преподаватели',
                description: 'Добавьте всех преподавателей до составления расписания. Без преподавателя в справочнике его нельзя поставить на пару.'
            }
        },
        {
            element: 'form[action*="/teachers"] button[type="submit"]',
            popover: {
                title: 'Добавить преподавателя',
                description: 'Заполните ФИО и инициалы. <strong>Инициалы</strong> — обязательно, они отображаются в ячейках расписания. Кабинет по умолчанию — тот, в который система поставит преподавателя при автоподстановке кабинетов.'
            }
        },
        {
            element: '.teacher-row',
            popover: {
                title: 'Строка преподавателя',
                description: '«Изменить» — редактировать ФИО или кабинет по умолчанию. «Удалить» — только если преподаватель не задействован в расписании.'
            }
        },
        {
            element: '.subject-accordion-toggle',
            popover: {
                title: 'Предметы преподавателя',
                description: 'Нажмите «Развернуть предметы» и отметьте галочками все предметы, которые ведёт этот преподаватель. <strong>Это важно:</strong> в диалоге редактирования пары система показывает только преподавателей, у которых отмечен нужный предмет. Если галочки нет — преподаватель не появится в списке.'
            }
        },
        {
            element: '.subject-filter-input',
            popover: {
                title: 'Поиск',
                description: 'Введите фамилию для быстрого поиска среди большого списка преподавателей.'
            }
        },
        {
            element: '.duplicate-badge',
            popover: {
                title: 'Дубликат',
                description: 'Оранжевая строка — два преподавателя с одинаковым именем. Проверьте инициалы и удалите лишнюю запись, иначе в расписании будет путаница.'
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
