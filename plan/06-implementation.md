# 06 — Техническая реализация: установка Driver.js и архитектура туров

## 1. Установка Driver.js

```bash
npm install driver.js
```

В `resources/js/app.js` или отдельном `resources/js/tours/index.js`:
```js
import Driver from 'driver.js';
import 'driver.js/dist/driver.css';
```

В `vite.config.js` ничего менять не нужно — Vite подхватит.

---

## 2. Кнопка «? Помощь» в layout

**Файл:** `resources/views/layouts/app.blade.php`

Добавить в шапку (рядом с логотипом или в правый угол nav-бара) один элемент:

```html
<button
    id="tourHelpBtn"
    class="ko-help-btn"
    type="button"
    title="Интерактивная помощь"
    style="display:none">
    ? Помощь
</button>
```

`display:none` по умолчанию — каждая страница сама делает `show()` через JS, если тур для неё определён.

CSS кнопки (в `public/css/app.css` или в layout `<style>`):
```css
.ko-help-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9000;
    background: #7f56d9;
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(127,86,217,0.35);
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
}
.ko-help-btn:hover {
    background: #6941c6;
    transform: translateY(-1px);
}
```

---

## 3. Архитектура файлов туров

Каждая страница получает свой JS-файл тура:

```
resources/js/tours/
    schedule-index.js    ← Расписание (просмотр недели/дня)
    schedule-week.js     ← Редактор недели
    form-two.js          ← Форма 2
    teachers.js          ← Преподаватели
    groups.js            ← Группы
    subjects.js          ← Дисциплины
    rooms.js             ← Аудитории
    holidays.js          ← Праздники
    practice.js          ← Практика
    absences.js          ← Отсутствия
    ai-agent.js          ← AI-агент
    audit.js             ← Аудит
```

Каждый файл экспортирует функцию `startTour()` и в конце привязывает её к кнопке:

```js
// resources/js/tours/form-two.js
import { driver } from 'driver.js';

export function startTour() {
    const d = driver({
        showProgress: true,
        nextBtnText: 'Далее →',
        prevBtnText: '← Назад',
        doneBtnText: 'Готово',
        steps: [ /* см. 03-tour-form-two.md */ ]
    });
    d.drive();
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('tourHelpBtn');
    if (btn) {
        btn.style.display = '';
        btn.addEventListener('click', startTour);
    }
});
```

В Blade-шаблоне подключить в секции `@push('scripts')`:
```html
@push('scripts')
<script type="module">
    import { startTour } from '/js/tours/form-two.js';
    // кнопка подключается внутри модуля автоматически
</script>
@endpush
```

> Если Vite-бандл — подключать через `@vite(['resources/js/tours/form-two.js'])`.

---

## 4. Маршрут и страница документации

**Файл:** `routes/web.php`

```php
Route::get('/docs', fn() => view('docs.index'))->name('docs.index')->middleware('auth');
```

**Файл:** `resources/views/docs/index.blade.php`

Структура: `@extends('layouts.app')` + Bootstrap accordion по разделам.

Ссылка «Документация» добавляется в sidebar (`layouts/app.blade.php`):
```html
<a class="ko-nav-item" href="{{ route('docs.index') }}">
    <i class="bi bi-book"></i>
    <span>Документация</span>
</a>
```

---

## 5. Driver.js — общие настройки для всех туров

```js
const commonConfig = {
    showProgress: true,
    smoothScroll: true,
    allowClose: true,
    overlayOpacity: 0.55,
    stagePadding: 6,
    stageRadius: 10,
    nextBtnText: 'Далее →',
    prevBtnText: '← Назад',
    doneBtnText: 'Готово ✓',
    onDestroyStarted: () => true, // разрешить закрытие крестиком
};
```

---

## 6. Что добавить в каждый Blade если элемент динамический

Если элемент, на который указывает тур, отсутствует (например, `addSubjectBtn` скрыт до включения режима коррекции), тур должен пропустить шаг:

```js
steps: steps.filter(step => {
    if (!step.element) return true;
    return document.querySelector(step.element) !== null;
})
```

---

## 7. Контрольный чеклист реализации

- [ ] `npm install driver.js`
- [ ] Кнопка `#tourHelpBtn` добавлена в `layouts/app.blade.php`
- [ ] CSS кнопки добавлен в `public/css/app.css`
- [ ] Маршрут `/docs` добавлен в `routes/web.php`
- [ ] Ссылка «Документация» добавлена в sidebar
- [ ] Все файлы туров созданы в `resources/js/tours/`
- [ ] Каждый тур подключён в соответствующем Blade через `@push('scripts')`
- [ ] Страница `resources/views/docs/index.blade.php` создана
