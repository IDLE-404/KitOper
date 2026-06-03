(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1',
            popover: {
                title: 'Генератор расписания',
                description: 'Автоматически создаёт шаблон расписания для группы на основе нормативов и ограничений. Используйте в начале семестра, если нужно быстро сгенерировать стартовый шаблон, который потом можно скорректировать вручную.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Курс',
                description: 'Выберите курс.'
            }
        },
        {
            element: '#groupSelect',
            popover: {
                title: 'Группа',
                description: 'Выберите группу, для которой будет сгенерировано расписание.'
            }
        },
        {
            element: '#semesterSelect',
            popover: {
                title: 'Семестр',
                description: 'Выберите 1 или 2 семестр — система подберёт предметы из соответствующих нормативов.'
            }
        },
        {
            element: '#templateWeek',
            popover: {
                title: 'Стартовая неделя',
                description: 'Укажите понедельник недели, с которой начнётся сгенерированное расписание.'
            }
        },
        {
            element: '#maxPairs',
            popover: {
                title: 'Максимум пар в день',
                description: 'Ограничение на количество пар в один день. Обычно 4–5. Генератор не будет ставить больше указанного числа пар в день.'
            }
        },
        {
            element: '#genBtn',
            popover: {
                title: 'Сгенерировать',
                description: 'Нажмите — система создаст шаблон расписания. После генерации откройте редактор недели и скорректируйте результат при необходимости.'
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
