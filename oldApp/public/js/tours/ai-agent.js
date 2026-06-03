(function () {
    'use strict';

    var STEPS = [
        {
            element: '#ai-chat',
            popover: {
                title: 'ИИ-Агент',
                description: 'Задавайте вопросы о расписании на русском языке. Агент имеет доступ к данным системы и отвечает по ним.'
            }
        },
        {
            element: '#ai-status-dot',
            popover: {
                title: 'Статус модели',
                description: 'Зелёный — готов. Жёлтый — загружается (подождите 1–2 минуты). Красный — недоступен.'
            }
        },
        {
            element: '#ai-model-select',
            popover: {
                title: 'Модель',
                description: 'Выберите ИИ-модель. Меньшие модели быстрее, большие — точнее. По умолчанию стоит оптимальная.'
            }
        },
        {
            element: '#ai-sidebar',
            popover: {
                title: 'История чатов',
                description: 'Список прошлых диалогов. Нажмите на любой чтобы продолжить. «Новый чат» — начать с чистого листа.'
            }
        },
        {
            element: '#ai-textarea',
            popover: {
                title: 'Введите вопрос',
                description: 'Пишите на русском. Например: «Сколько пар у группы ПО-115 в понедельник?» или «Покажи преподавателей без назначенных предметов». Enter — отправить.'
            }
        },
        {
            element: '#ai-file-input',
            popover: {
                title: 'Загрузить файл',
                description: 'Загрузите Excel с расписанием — агент поможет его проанализировать и импортировать данные.'
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
