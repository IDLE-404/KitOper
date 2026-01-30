# KitOper — подробное описание проекта

Проект: система расписания и отчётности (формат “Форма 2”) для 1–4 курсов с поддержкой чередования недель (числитель/знаменатель), практики, полевых сборов, праздников, отсутствий преподавателей и экспортом в Excel.

Ниже — полный обзор модулей, маршрутов, логики и структуры данных.

---

## 1. Технологии и структура

- PHP (Laravel)
- Blade шаблоны
- MySQL/MariaDB (см. `KitOper(35).sql`)

Основные каталоги:
- `app/Http/Controllers` — контроллеры модулей.
- `app/Services` — сервисная бизнес‑логика.
- `app/Support` — общие утилиты (например, `CourseContext`).
- `resources/views` — интерфейсы страниц.
- `routes/web.php` — все маршруты.
- `database/migrations` — миграции структуры БД.
- `KitOper(35).sql` — пример дампа БД.

---

## 2. Схема таблиц по курсам

Используется единая логика для 1–4 курсов, отличающаяся префиксом таблиц:

- **1 курс**: `first_course_*`
- **2 курс**: `second_course_*`
- **3 курс**: `third_course_*`
- **4 курс**: `fourth_course_*`

Префикс определяется через `app/Support/CourseContext.php`.

Пример маппинга таблиц:
```
groups            => {prefix}_course_group
subjects          => {prefix}_course_subjects
teachers          => teachers (общая таблица)
schedules         => {prefix}_course_schedules
form_two_normatives => {prefix}_form_two_normatives (1 курс: form_two_normatives)
form_two_records    => {prefix}_form_two_records (1 курс: form_two_records)
teacher_subjects    => {prefix}_course_teacher_subjects
```

---

## 3. Маршруты (routes/web.php)

### Главные:
- `/` → расписание (FirstCourseSchedulePageController@index)
- `/first-course/schedule` → расписание (неделя)
- `/first-course/schedule/day` → расписание (день)
- `/first-course/schedule/week` → редактор недели

### Форма 2:
- `/first-course/form-two` → отчёт по форме 2
- `/first-course/form-two/save` → сохранение коррекции
- `/first-course/form-two/export` → экспорт Excel (месяц)
- `/first-course/form-two/export-semester` → экспорт Excel (семестр)

### Практика:
- `/practice` (index, store, destroy)

### Полевые сборы (1 курс):
- `/field-camps` (index, store, destroy)

### Справочники:
- `/teachers`
- `/groups`
- `/subjects`
- `/rooms`
- `/holidays`
- `/teacher-absences`

---

## 4. Расписание

### Ключевой контроллер
`app/Http/Controllers/FirstCourseSchedulePageController.php`

Основные функции:
- `index()` — строит сетку расписания по всем группам или по дню.
- `week()` — визуальный редактор недельного расписания.
- `weekSave()` — сохранение недели, запись в БД.
- `updatePair()` — модальное редактирование пары.
- `deletePair()` — удаление пары.
- `expandSemester()` — копирование шаблонной недели на весь семестр.
- `availability()`, `freeTeachers()`, `freeRooms()` — проверка занятости.
- `autoAssignRoomsDay()` — авто‑кабинеты.
- `clearRoomsDay()` — очистка кабинетов.

### Чередование недели
Определяется через:
`ScheduleToFormTwoSyncService::resolveWeekMode()`

Логика:
- Семестр стартует с числителя.
- Следующая неделя — знаменатель.
- Чётность определяется разницей недель от даты старта семестра.

### Подгруппы
В расписании одна запись может содержать:
- подгруппа 1 (основная строка)
- подгруппа 2 (поле `_2` и/или отдельная строка с `subgroup=2`)

### Проверка конфликтов
Контроллер валидирует:
- занятость кабинетов;
- занятость преподавателей;
на уровне курса и между курсами.

---

## 5. Форма 2

### Контроллер
`app/Http/Controllers/FormTwoController.php`

### Основной сервис
`app/Services/FormTwoService.php`

### Источники данных
- `form_two_normatives` — нормативы по предмету/учителю/месяцу.
- `form_two_records` — фактические записи (сгенерированы из расписания + практика + сборы).

### Синхронизация из расписания
`app/Services/ScheduleToFormTwoSyncService.php`

Сценарий:
1) При сохранении пары/недели → вызывается `syncWeekWithAlternation`.
2) Рассчитываются статусы и часы.
3) Записывается в `form_two_records`.

### Статусы в форме 2
- `normal` — обычная пара.
- `replaced` — отмена пары (замена другим).
- `replacement` — замена (бонус часов другому предмету/преподавателю).
- `sick` — трактуется как `replaced`.

### Экспорт
`app/Services/FormTwoExportService.php`

Экспорт доступен:
- за месяц;
- за семестр (1 или 2).

---

## 6. Практика (2–4 курс)

### Контроллер
`app/Http/Controllers/PracticeController.php`

### Сервис
`app/Services/PracticeService.php`

### Как работает
1) Создаётся период в `practice_periods`.
2) Для каждого рабочего дня:
   - создаются записи в `form_two_records` с предметом
     **“Учебная практика”** или **“Производственная практика”**;
   - обычные записи по расписанию в этом диапазоне удаляются.
3) При удалении периода:
   - записи практики удаляются;
   - расписание пересинхронизируется в форме 2.

---

## 7. Полевые сборы (1 курс)

### Контроллер
`app/Http/Controllers/FieldCampController.php`

### Сервис
`app/Services/FieldCampService.php`

### Как работает
Полностью аналогично практике, но:
- только для 1 курса;
- предмет в форме 2: **“Полевые сборы”**.

---

## 8. Праздники и отсутствие преподавателей

### Праздники
Контроллер: `HolidayController`
Таблица: `holidays`

Логика:
- праздники исключаются из недельного расписания;
- исключаются из практики и сборов.

### Отсутствия преподавателей
Контроллер: `TeacherAbsenceController`
Таблица: `teacher_absences`

Логика:
- подсвечиваются в расписании;
- блокируют выбор занятых преподавателей.

---

## 9. Таблицы данных (ключевые)

### 9.1 first_course_group (аналогично другим курсам)
```
id
group_name
group_number
subgroup
has_subgroups
group_type   // ru/kz
created_at
updated_at
```

### 9.2 first_course_subjects (аналогично другим курсам)
```
id
module_title
module_index
subject_name
name_ru
name_kz
group_type  // ru/kz/both/hidden
created_at
updated_at
```

### 9.3 teachers (общая таблица)
```
id
teacher_name
initials
created_at
updated_at
```

### 9.4 first_course_teacher_subjects (аналогично другим курсам)
```
id
teacher_id
subject_id
created_at
updated_at
```

### 9.5 first_course_schedules (аналогично для 2–4 курсов)
```
id
replaces_schedule_id
week_start
study_day
lesson_number
group_id
subject_id
subject_id_denominator
subject_id_denominator_2
subject_id_2
teacher_id
teacher_id_denominator
teacher_id_denominator_2
teacher_id_2
room_id
room_id_denominator
room_id_denominator_2
room_id_2
subgroup
is_replacement
is_absent_1_num
is_replacement_1_num
replacement_teacher_id_1_num
replacement_comment_1_num
replacement_subject_id_1_num
is_absent_1_den
is_replacement_1_den
replacement_teacher_id_1_den
replacement_comment_1_den
replacement_subject_id_1_den
is_absent_2_num
is_replacement_2_num
replacement_teacher_id_2_num
replacement_comment_2_num
replacement_subject_id_2_num
is_absent_2_den
is_replacement_2_den
replacement_teacher_id_2_den
replacement_comment_2_den
replacement_subject_id_2_den
created_at
updated_at
mode (computed: single|numerator)
```

### 9.6 form_two_normatives (и prefixed для 2–4)
```
id
group_id
subject_id
teacher_id (nullable)
month
year
total_hours
hours_per_class
created_at
updated_at
```

### 9.7 form_two_records (и prefixed для 2–4)
```
id
group_id
month
year
class_date
lesson_number
day
subject_id
teacher_id
subgroup
total_hours
hours_per_class
status        // normal|sick|replacement|replaced
replacement_teacher_id
replacement_subject_id
bonus_hours
used_hours
absent_reason
replacement_comment
mode          // single|numerator|denominator
created_at
updated_at
```

### 9.8 practice_periods
```
id
course (2–4)
group_id
type            // educational|production
teacher_id
room_id
start_date
end_date
hours_per_day
created_at
updated_at
```

### 9.9 field_camp_periods
```
id
course (1)
group_id
teacher_id
room_id
start_date
end_date
hours_per_day
created_at
updated_at
```

### 9.10 rooms
```
id
code
type        // standard|computer
title
notes
is_active
created_at
updated_at
```

### 9.11 holidays
```
id
name
start_month
start_day
end_month
end_day
year (nullable)
is_active
created_at
updated_at
```

### 9.12 teacher_absences
```
id
teacher_id
type
start_date
end_date
notes
created_at
updated_at
```

---

## 10. Основные потоки (что где вызывается)

### Сохранение недели (редактор)
`FirstCourseSchedulePageController@weekSave`
1) Валидация.
2) Удаление существующих строк недели.
3) Вставка новых строк.
4) Синхронизация с формой 2:
   - `ScheduleToFormTwoSyncService::syncWeekWithAlternation`.

### Обновление пары (модалка)
`FirstCourseSchedulePageController@updatePair`
1) Считывание данных числителя и знаменателя.
2) Проверка занятости кабинетов/преподавателей.
3) Полная перезапись пары.
4) Синк формы 2.

### Удаление пары
`FirstCourseSchedulePageController@deletePair`
- удаляет пару в текущей неделе и соседней (чередующейся);
- пересинхронизирует форму 2.

### Практика / Полевые сборы
1) Добавление периода → записи в `form_two_records`.
2) Удаление периода → пересборка формы 2 из расписания.

---

## 11. Полезные файлы

- `app/Services/ScheduleToFormTwoSyncService.php` — главная синхронизация расписания → форма 2.
- `app/Services/FormTwoService.php` — построение отчётов формы 2.
- `resources/views/first_course/schedule/index.blade.php` — визуальная сетка расписания.
- `resources/views/first_course/schedule/week.blade.php` — редактор недели.
- `resources/views/first_course/form_two.blade.php` — интерфейс формы 2.

---

## 12. Что важно помнить

- Праздники и выходные исключаются из практики/сборов.
- При изменении расписания необходимо учитывать знаменатель/числитель.
- Для групп с определённой специализацией порядок предметов в “Форме 2” меняется.
- В таблицах для 2–4 курса используется тот же формат полей, но с префиксом.

---

Если нужен ещё более детальный разбор по конкретной части (например, разбор всех условий в `updatePair` или порядок расчёта замен в `ScheduleToFormTwoSyncService`) — скажи, добавлю в README отдельный раздел.
