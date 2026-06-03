<?php

return [
    'capabilities' => [
        'intent_phrases' => [
            'что ты умеешь',
            'что умеешь',
            'твои возможности',
            'что можешь',
        ],

        'short_reply' => 'Я помогаю работать с данными учебной части: показываю списки, ищу записи, изменяю данные. Также проверяю расписание на конфликты, предлагаю замены преподавателям и помогаю планировать пары.',

        'detailed_intro' => 'Я — умный помощник диспетчера учебной части. Работаю с данными и помогаю планировать расписание.',

        'detailed_items' => [
            '**Данные:** показывать и искать преподавателей, группы, дисциплины, аудитории, праздники, отсутствия.',
            '**Изменение данных:** добавлять, изменять и удалять записи (с подтверждением).',
            '**Конфликты расписания:** проверять занятость аудиторий и преподавателей на любое время.',
            '**Поиск замен:** находить подходящих преподавателей для замены отсутствующего.',
            '**Планирование пар:** предлагать оптимальные слоты для новых дисциплин в расписании.',
            '**Свободные ресурсы:** показывать какие аудитории и преподаватели свободны в конкретное время.',
            '**Статистика недели:** анализировать нагрузку преподавателей и заполняемость аудиторий.',
            '**Импорт из файлов:** загружать нагрузку из Excel и Word документов.',
        ],

        'examples' => [
            'Покажи дисциплины 1 курса',
            'Список групп 2 курса',
            'Найди преподавателя Иванов',
            'Проверь конфликты на этой неделе для 1 курса',
            'Кто может заменить Петрова в среду на 3 паре?',
            'Покажи свободные аудитории на понедельник 2 пара',
            'Запланируй пары для дисциплины Физика',
            'Какая нагрузка у преподавателей на этой неделе?',
        ],

        'detailed_outro' => 'Просто опишите задачу — я пойму что нужно сделать. Например: «Проверь не будет ли конфликта, если поставлю Иванова на среду 4 пару».',
    ],

    'scenarios' => [
        'subjects_list' => [
            'enabled' => true,
            'intent_all' => ['дисциплин'],
            'intent_any' => ['покажи', 'список', 'все'],
            'requires_course' => true,
            'action' => [
                'action' => 'select',
                'table_by_course' => [
                    1 => 'first_course_subjects',
                    2 => 'second_course_subjects',
                    3 => 'third_course_subjects',
                    4 => 'fourth_course_subjects',
                ],
                'data' => ['id', 'subject_name'],
                'limit' => 200,
            ],
        ],

        'groups_list' => [
            'enabled' => true,
            'intent_all' => ['групп'],
            'intent_any' => ['покажи', 'список', 'все'],
            'requires_course' => true,
            'action' => [
                'action' => 'select',
                'table_by_course' => [
                    1 => 'first_course_group',
                    2 => 'second_course_group',
                    3 => 'third_course_group',
                    4 => 'fourth_course_group',
                ],
                'data' => ['id', 'group_name', 'group_number'],
                'limit' => 200,
            ],
        ],

        'teachers_list' => [
            'enabled' => true,
            'intent_all' => ['преподав'],
            'intent_any' => ['покажи', 'список', 'все'],
            'action' => [
                'action' => 'select',
                'table' => 'teachers',
                'data' => ['id', 'teacher_name', 'initials'],
                'limit' => 200,
            ],
        ],

        'rooms_list' => [
            'enabled' => true,
            'intent_any' => ['аудитор', 'кабинет'],
            'requires_any' => ['покажи', 'список', 'все'],
            'action' => [
                'action' => 'select',
                'table' => 'rooms',
                'data' => ['id', 'code', 'title', 'room_type'],
                'limit' => 200,
            ],
        ],

        'holidays_list' => [
            'enabled' => true,
            'intent_any' => ['праздник', 'выходн'],
            'requires_any' => ['покажи', 'список', 'все'],
            'action' => [
                'action' => 'select',
                'table' => 'holidays',
                'data' => ['id', 'name', 'start_date', 'end_date'],
                'limit' => 200,
            ],
        ],

        'check_conflicts' => [
            'enabled' => true,
            'intent_all' => ['конфликт'],
            'intent_any' => ['проверь', 'найди', 'есть'],
            'action_type' => 'planning',
        ],

        'find_replacement' => [
            'enabled' => true,
            'intent_all' => ['замен'],
            'intent_any' => ['найди', 'кто может', 'поставь'],
            'action_type' => 'planning',
        ],

        'free_rooms' => [
            'enabled' => true,
            'intent_all' => ['аудитор', 'кабинет'],
            'intent_any' => ['свободн', 'какие'],
            'action_type' => 'planning',
        ],

        'free_teachers' => [
            'enabled' => true,
            'intent_all' => ['преподав'],
            'intent_any' => ['свободн', 'какие', 'доступн'],
            'action_type' => 'planning',
        ],

        'plan_schedule' => [
            'enabled' => true,
            'intent_all' => ['запланируй', 'расставь', 'построй'],
            'intent_any' => ['расписание', 'пары', 'дисциплин'],
            'action_type' => 'planning',
        ],

        'week_stats' => [
            'enabled' => true,
            'intent_all' => ['статистик', 'нагрузк'],
            'intent_any' => ['покажи', 'какая', 'сколько'],
            'action_type' => 'planning',
        ],
    ],
];
