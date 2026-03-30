<?php

return [
    'capabilities' => [
        'intent_phrases' => [
            'что ты умеешь',
            'что умеешь',
            'твои возможности',
            'что можешь',
        ],

        'short_reply' => 'Я могу показывать данные по преподавателям, группам, дисциплинам, аудиториям, праздникам и отсутствиям, а также изменять записи по вашей команде.',

        'detailed_intro' => 'Я помогаю диспетчеру работать с данными учебной части простыми командами.',

        'detailed_items' => [
            'Показать списки: преподаватели, группы, дисциплины, аудитории, праздники, отсутствия.',
            'Найти конкретную запись: например преподавателя по фамилии или группу по курсу.',
            'Добавить запись: если вы явно пишете, что и куда добавить.',
            'Изменить запись: сначала покажу план изменения, затем выполню только после вашего «подтверждаю».',
            'Удалить запись: также только через подтверждение.',
            'Уточнять недостающие детали: курс, месяц, период, ID — чтобы не сделать ошибку.',
            'Понимать разговорные формулировки и часть опечаток.',
            'Помнить контекст диалога (например выбранный курс) в рамках текущей сессии.',
        ],

        'examples' => [
            'Покажи дисциплины 1 курса',
            'Список групп 2 курса',
            'Найди преподавателя Иванов',
            'Переименуй преподавателя id 15 в Петров Иван Иванович',
        ],

        'detailed_outro' => 'Если хотите, могу сразу начать с любой команды из примеров.',
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
                'limit' => 50,
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
                'limit' => 50,
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
                'limit' => 50,
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
                'limit' => 50,
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
                'limit' => 50,
            ],
        ],
    ],
];

