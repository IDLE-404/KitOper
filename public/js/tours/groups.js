(function () {
    'use strict';

    var STEPS = [
        {
            element: 'h1.page-title',
            popover: {
                title: 'Группы',
                description: 'Перед составлением расписания добавьте все группы. Без группы вы не сможете открыть расписание или Форму 2.'
            }
        },
        {
            element: '#courseSelect',
            popover: {
                title: 'Выберите курс',
                description: 'Переключите курс — список обновится. Группы каждого курса хранятся отдельно.'
            }
        },
        {
            element: '#groupName',
            popover: {
                title: 'Название группы',
                description: 'Введите название группы, например <strong>ПО-115</strong>.'
            }
        },
        {
            element: '#groupType',
            popover: {
                title: 'Тип: рус или каз',
                description: 'Выберите язык обучения. Казахские группы будут видеть казахские названия предметов в расписании — если они заполнены в справочнике дисциплин.'
            }
        },
        {
            element: '#groupHasSubgroups',
            popover: {
                title: 'Подгруппа 2',
                description: 'Поставьте галочку <strong>только если группа делится на две части</strong> на некоторых предметах (информатика, иностранный, лабораторные). После этого в редакторе расписания появится кнопка «Добавить подгруппу 2» для каждой пары — можно будет поставить двум половинам разные предметы в одно время.'
            }
        },
        {
            element: 'form[action*="/groups"] button[type="submit"].btn-primary',
            popover: {
                title: 'Создать группу',
                description: 'Нажмите «Добавить». Группа сразу появится в расписании и Форме 2.'
            }
        },
        {
            element: '.app-table tbody tr',
            popover: {
                title: 'Редактирование группы',
                description: '«Изменить» — меняет название, тип или наличие подгрупп прямо в строке. «Удалить» — доступно только пока у группы нет записей в расписании.'
            }
        },
        {
            element: 'form[action*="finish-year"] button[type="submit"]',
            popover: {
                title: 'Завершить учебный год',
                description: 'Нажимайте <strong>один раз в год</strong>, когда группа переходит на следующий курс. Архивирует расписание и очищает шаблон. Действие необратимо.'
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
