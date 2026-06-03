(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.week-title',
            popover: {
                title: 'Редактор шаблона недели',
                description: 'Здесь вы составляете шаблон расписания группы. После настройки нажмите «Развернуть на семестр» — система сама расставит пары по всем неделям с чередованием A/B.'
            }
        },
        {
            element: '#groupSelect',
            popover: {
                title: 'Выберите группу',
                description: 'Выберите группу из списка — таблица пар загрузится. Каждая группа редактируется отдельно.'
            }
        },
        {
            element: '#dayTabs',
            popover: {
                title: 'Дни недели',
                description: 'Нажмите на нужный день — таблица покажет пары этого дня. Каждый день настраивается независимо.'
            }
        },
        {
            element: '.pair-row',
            popover: {
                title: 'Строка пары',
                description: 'В каждой строке — два столбца: <strong>числитель (A)</strong> и <strong>знаменатель (B)</strong>. Заполните числитель. Если в неделю B нужен другой предмет или преподаватель — заполните знаменатель тоже. Если знаменатель пустой, пара будет одинакова в обе недели.'
            }
        },
        {
            element: 'input[name*="has_subgroups"]',
            popover: {
                title: 'Добавить подгруппу 2',
                description: 'Поставьте галочку на паре, где группа делится. Появится дополнительная строка — задайте предмет и преподавателя для подгруппы 2. Обе подгруппы занимаются одновременно в разных кабинетах.'
            }
        },
        {
            element: '.add-pair-btn',
            popover: {
                title: 'Добавить пару',
                description: 'Нажмите чтобы добавить ещё одну пару в этот день.'
            }
        },
        {
            element: '.semester-expand',
            popover: {
                title: 'Развернуть на семестр',
                description: 'После заполнения шаблона укажите даты начала и конца семестра и нажмите «Развернуть». Система скопирует шаблон на все рабочие недели, чередуя A и B, пропуская праздники и каникулы.'
            }
        },
        {
            element: 'a[href*="week-duplicate"]',
            popover: {
                title: 'Дубликат недели',
                description: 'Если нужно скопировать расписание одной конкретной недели на другую — используйте этот раздел в меню.'
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
