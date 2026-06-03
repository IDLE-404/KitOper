(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Отсутствия преподавателей',
                description: 'Здесь фиксируются периоды, когда преподаватель не работает. После добавления — откройте расписание, найдите его пары и назначьте замену через ✏️ → «Включить замену». Иначе пары останутся без замены и анализ расписания покажет ошибку.'
            }
        },
        {
            element: '#absenceTeacherSelect',
            popover: {
                title: 'Выберите преподавателя',
                description: 'Выберите преподавателя из списка.'
            }
        },
        {
            element: '#absenceSearch',
            popover: {
                title: 'Поиск',
                description: 'Введите фамилию для быстрого поиска.'
            }
        },
        {
            element: '#absenceTable tbody tr',
            popover: {
                title: 'Запись об отсутствии',
                description: 'Преподаватель, период, причина. После добавления записи его ФИО окрасится оранжевым в списке преподавателей в диалоге редактирования пар — это сигнал что нужна замена. «Удалить» — если отсутствие отменено.'
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
