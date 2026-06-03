(function () {
    'use strict';
    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Документация',
                description: 'Полное руководство по работе с KitOper. Разделы раскрываются кликом. Для быстрой навигации используйте оглавление вверху.'
            }
        },
        {
            element: '.docs-toc',
            popover: {
                title: 'Оглавление',
                description: 'Нажмите на любой раздел — страница прокрутится и откроет нужную секцию.'
            }
        }
    ];
    function startTour() {
        var driverFn = window.driver && window.driver.js && window.driver.js.driver;
        if (!driverFn) return;
        var d = driverFn({
            showProgress: true, smoothScroll: true, allowClose: true,
            overlayOpacity: 0.55, stagePadding: 6, stageRadius: 8,
            nextBtnText: 'Далее →', prevBtnText: '← Назад', doneBtnText: 'Готово ✓',
            steps: STEPS
        });
        d.drive();
    }
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('tourHelpBtn');
        if (btn) { btn.style.display = 'block'; btn.addEventListener('click', startTour); }
    });
})();
