(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Дисциплины',
                description: 'Справочник предметов по курсам. Добавьте все дисциплины до составления расписания — только отсюда можно выбрать предмет при редактировании пары.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Переключите курс — список предметов этого курса. Один предмет может быть в нескольких курсах — это отдельные записи.'
            }
        },
        {
            element: 'form[action*="/subjects"] button[type="submit"]',
            popover: {
                title: 'Добавить дисциплину',
                description: 'Заполните название на русском и казахском (для казахских групп), укажите модуль и тип групп (рус/каз/оба). Нажмите «Добавить».'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Строка предмета',
                description: '«Редактировать» — изменить название. Изменение сразу отразится везде: в расписании и Форме 2.'
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
