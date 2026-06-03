# DEVLOG — KitOper

Дневник разработки. Читай перед тем как что-то трогать.

---

## Что это за проект

**KitOper** — веб-система управления расписанием колледжа. Laravel 12 + Bootstrap 5 + Tailwind (только в сборке, UI на Bootstrap). База — MySQL 8. Кэш — Redis. AI — Ollama (локальная модель, по умолчанию qwen2.5:3b).

Три роли: `dispatcher` (полный доступ), `teacher` (только свои пары), `student` (просмотр).

---

## Как запустить локально

```bash
cd oldApp
cp .env.example .env          # или возьми .env из команды
docker compose up -d           # docker-compose.yml лежит в oldApp/
php artisan migrate            # если БД пустая
php artisan db:seed            # опционально
```

Приложение доступно на `http://localhost:8000`.  
phpMyAdmin — `http://localhost:8081`.

> **Важно:** контейнер `DataBase` (MySQL) иногда падает при рестарте системы. Проверяй командой `docker ps` и при необходимости `docker start DataBase`.

---

## Архитектура проекта

```
oldApp/
├── app/Http/Controllers/     # Контроллеры (один файл — один раздел)
├── app/Http/Middleware/      # auth, role, audit
├── app/Models/               # User, Group, Teacher, Subject, Room, ...
├── app/Services/             # Бизнес-логика (FormTwo, Ghost, Schedule...)
├── resources/views/          # Blade-шаблоны
│   ├── layouts/app.blade.php # Главный layout с sidebar
│   ├── first_course/         # Расписание и Форма 2 (1 курс)
│   ├── docs/index.blade.php  # Страница документации
│   └── ...
├── public/
│   ├── css/                  # Статические CSS (не через Vite)
│   └── js/
│       └── tours/            # Driver.js туры (см. ниже)
├── routes/web.php
└── database/migrations/
```

### Важное про CSS и JS

Проект **не использует Vite для страничного JS**. Весь JS — inline в Blade через `@push('scripts')` или отдельные файлы в `public/js/`. Vite собирает только `app.css` и `app.js` (bootstrap/axios).

CDN-зависимости в `layouts/app.blade.php`:
- Bootstrap 5.3.3
- Bootstrap Icons 1.11.3
- Driver.js 1.x (для туров)

---

## Числитель и знаменатель

Расписание чередуется еженедельно: неделя A (числитель) → неделя B (знаменатель). Логика определения: стартовая дата семестра + чётность недели. Смотри `FirstCourseSchedulePageController::getCurrentWeekMode()`.

---

## Форма 2

Официальный журнал учёта часов. Строки берутся из **нормативов** (`form_two_normatives`). Нормативы строятся из **шаблонов** (`form_two_templates`).

Колонка `semester` в `form_two_normatives` — добавлена миграцией `2027_07_02`. Если делаешь свежий импорт SQL-дампа — проверь что она есть:

```sql
DESCRIBE form_two_normatives;
-- должна быть колонка semester TINYINT
```

Если нет — запусти:
```sql
ALTER TABLE form_two_normatives ADD COLUMN semester TINYINT UNSIGNED AFTER teacher_id;
UPDATE form_two_normatives SET semester = CASE WHEN month >= 9 THEN 1 ELSE 2 END;
```

---

## Ghost-режим (прогноз Формы 2)

Сервис `SemesterGhostService`. При включении переключателя `#ghostToggle` подгружает данные шаблона расписания и проецирует их на оставшиеся дни месяца. Данные НЕ пишутся в БД — только для отображения.

---

## Система интерактивных туров (Driver.js)

Добавлена в июне 2026. Кнопка «? Помощь» — фиксированная, правый нижний угол, появляется только если на странице загружен тур.

### Файлы

| Файл | Страница |
|------|----------|
| `public/js/tours/schedule-index.js` | Расписание (неделя) |
| `public/js/tours/schedule-day.js` | Расписание (день) |
| `public/js/tours/schedule-week.js` | Редактор недели |
| `public/js/tours/form-two.js` | Форма 2 |
| `public/js/tours/teachers.js` | Преподаватели |
| `public/js/tours/groups.js` | Группы |
| `public/js/tours/subjects.js` | Дисциплины |
| `public/js/tours/rooms.js` | Аудитории |
| `public/js/tours/holidays.js` | Праздники |
| `public/js/tours/practice.js` | Практика |
| `public/js/tours/field-camps.js` | Полевые сборы |
| `public/js/tours/absences.js` | Отсутствия |
| `public/js/tours/week-duplicate.js` | Дубликат недели |
| `public/js/tours/generate.js` | Генератор |
| `public/js/tours/workload.js` | Занятость |
| `public/js/tours/form-two-templates.js` | Шаблоны Ф2 |
| `public/js/tours/users.js` | Пользователи |
| `public/js/tours/ai-agent.js` | ИИ-Агент |
| `public/js/tours/audit.js` | Аудит |
| `public/js/tours/docs.js` | Документация |

### Как добавить новый тур

1. Создай `public/js/tours/my-page.js` по образцу любого существующего файла
2. В конце Blade-шаблона добавь:
   ```blade
   @push('scripts')
   <script src="{{ asset('js/tours/my-page.js') }}"></script>
   @endpush
   ```
3. Кнопка `#tourHelpBtn` уже есть в `layouts/app.blade.php` — тур её покажет сам

### Глобальный объект driver.js

```js
const driverFn = window.driver.js.driver;
```

Не `window.driver.driver` — именно `window.driver.js.driver`. Такой namespace у IIFE-сборки driver.js v1.

---

## Страница документации `/docs`

Маршрут: `GET /docs` → `view('docs.index')`, middleware `auth`.  
View: `resources/views/docs/index.blade.php` — Bootstrap Accordion, 10 разделов.  
Ссылка в sidebar: `layouts/app.blade.php`, только для диспетчера.

---

## Известные особенности и грабли

### Docker

- `DataBase` контейнер не входит в основной `docker-compose.yml` в папке `docker/` (там nginx-конфиг). Контейнер MySQL запускается из `oldApp/docker-compose.yml`. Если база недоступна — проверь `docker ps -a` и стартани `docker start DataBase`.
- Сеть: `kitoper_kitOper` (external). Если поднимаешь заново — убедись что сеть создана: `docker network create kitoper_kitOper`.

### База данных

- Пользователи создаются через `php artisan tinker` или через страницу `/users` (только для диспетчера).
- При импорте SQL-дампа (`KitOper.sql` в корне) могут быть ошибки дубликатов — используй `mysql --force`. Важно проверить колонку `semester` в `form_two_normatives` после импорта (см. выше).

### Расписание

- `first_course/schedule/index.blade.php` — огромный файл (~3100 строк). В нём и просмотр (неделя + день), и модальное окно редактирования пары. День-режим определяется по URL `/schedule/day`.
- Загрузка туров для day/week режима: `index.blade.php` определяет какой тур загрузить по `window.location.pathname`.

### Форма 2

- Если таблица отображается пустой — скорее всего нет нормативов для группы. Проверь `form_two_normatives` в БД.
- Ghost-режим работает только если заполнен шаблон недели для группы.

---

## Что можно улучшить

- [ ] Туры для режима «Просмотр семестра» в Форме 2 — Ghost-режим интерактивно
- [ ] Автоматический старт тура при первом входе нового пользователя
- [ ] Темпоральное расписание — сейчас только числитель/знаменатель, без поддержки 3-х и более вариантов
- [ ] Мобильная адаптация UI (sidebar сворачивается, но таблицы расписания не адаптированы)
- [ ] Уведомления при конфликтах расписания в реальном времени

---

*Последнее обновление: июнь 2026*
