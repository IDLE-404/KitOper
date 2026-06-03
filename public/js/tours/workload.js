(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Занятость преподавателей',
                description: 'Таблица показывает все пары всех преподавателей на выбранной неделе. Используйте чтобы найти свободное время у нужного преподавателя или проверить его перегрузку.'
            }
        },
        {
            element: '#teacherSearch',
            popover: {
                title: 'Поиск преподавателя',
                description: 'Введите фамилию — таблица отфильтруется по нужному преподавателю.'
            }
        },
        {
            element: '#workloadTable',
            popover: {
                title: 'Таблица занятости',
                description: 'Столбцы — преподаватели, строки — пары по дням. Заполненная ячейка — пара назначена. Пустая — преподаватель свободен в это время. Удобно при подборе замены: сразу видно кто свободен.'
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
