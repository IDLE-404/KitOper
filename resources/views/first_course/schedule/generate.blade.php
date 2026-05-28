@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/schedule/main.css') }}">
<style>
    .gen-shell { max-width: 980px; margin: 0 auto; }
    .gen-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15,23,42,.06);
        padding: 24px;
    }
    .gen-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    .gen-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-top: 14px;
    }
    .gen-field label {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .gen-section {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #f0f0f0;
    }
    .gen-section-title {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 12px;
    }
    .gen-checks {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 12px;
    }
    .gen-info {
        margin-top: 16px;
        padding: 10px 14px;
        background: #f0f4ff;
        border: 1px solid #c7d7f9;
        border-radius: 10px;
        font-size: 13px;
        color: #3b4e8c;
        line-height: 1.6;
    }
    .gen-actions {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn-spinner { display: none; }
    .btn-loading .btn-text    { display: none; }
    .btn-loading .btn-spinner { display: inline; }
</style>
@endpush

@section('content')
<div class="gen-shell">

    <div class="header-row mb-3">
        <div>
            <h1 class="page-title">Генерация расписания</h1>
            <p class="page-subtitle mb-0">Автоматически строит шаблонную неделю на основе нормативов Формы 2.</p>
        </div>
        <div class="action-buttons">
            <a class="btn-pill ghost" href="{{ route('first.schedule.week', ['course' => $course]) }}">Редактор недели</a>
            <a class="btn-pill ghost" href="{{ route('first.schedule.index', ['course' => $course]) }}">К расписанию</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="gen-card">
        <form method="POST" action="{{ route('schedule.generate.store') }}" id="genForm">
            @csrf

            {{-- Строка 1: Курс / Группа / Семестр --}}
            <div class="gen-grid">
                <div class="gen-field">
                    <label for="courseSelect">Курс</label>
                    <select class="search-input" id="courseSelect" name="course">
                        @for($c = 1; $c <= 4; $c++)
                            <option value="{{ $c }}" @selected($course == $c)>{{ $c }}</option>
                        @endfor
                    </select>
                </div>
                <div class="gen-field">
                    <label for="groupSelect">Группа</label>
                    <select class="search-input" id="groupSelect" name="group_id" required>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>
                                {{ $group->group_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="gen-field">
                    <label for="semesterSelect">Семестр</label>
                    <select class="search-input" id="semesterSelect" name="semester">
                        <option value="1" @selected(old('semester', '2') == '1')>1 семестр (сен–янв)</option>
                        <option value="2" @selected(old('semester', '2') == '2')>2 семестр (фев–июн)</option>
                    </select>
                </div>
            </div>

            {{-- Строка 2: Неделя-шаблон / Недель в семестре --}}
            <div class="gen-grid-2">
                <div class="gen-field">
                    <label for="templateWeek">Неделя-шаблон <span style="color:#94a3b8">(будет создана)</span></label>
                    <input class="search-input" type="date" id="templateWeek" name="template_week"
                           value="{{ old('template_week', $defaultWeek) }}" required>
                </div>
                <div class="gen-field">
                    <label for="weeksCount">Недель в семестре</label>
                    <input class="search-input" type="number" id="weeksCount" name="weeks_in_semester"
                           value="{{ old('weeks_in_semester', 18) }}" min="8" max="24">
                </div>
            </div>

            {{-- Дополнительные параметры --}}
            <div class="gen-section">
                <div class="gen-section-title">Параметры расстановки</div>

                <div class="gen-grid-2">
                    <div class="gen-field">
                        <label for="maxPairs">Макс. пар в день на группу</label>
                        <input class="search-input" type="number" id="maxPairs" name="max_pairs_per_day"
                               value="{{ old('max_pairs_per_day', 4) }}" min="2" max="7">
                    </div>
                </div>

                <div class="gen-checks">
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="allow_saturday" value="1"
                               @checked(old('allow_saturday'))>
                        <span class="form-check-label">Разрешить субботу</span>
                    </label>
                    <label class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="overwrite" value="1"
                               @checked(old('overwrite'))>
                        <span class="form-check-label">Перезаписать существующее расписание</span>
                    </label>
                </div>
            </div>

            {{-- Подсказка --}}
            <div class="gen-info">
                <strong>Как это работает:</strong> генератор считает нужное количество пар в неделю
                по нормативам Формы 2, затем расставляет их по слотам избегая конфликтов преподавателей.
                Результат откроется в <strong>Редакторе недели</strong> — там можно поправить вручную,
                затем дублировать на весь семестр через «Дубликат недели».
            </div>

            <div class="gen-actions">
                <button class="btn-pill primary" type="submit" id="genBtn">
                    <span class="btn-text">⚡ Сгенерировать расписание</span>
                    <span class="btn-spinner">⏳ Генерация...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Перезагрузка при смене курса
    document.getElementById('courseSelect')?.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        params.set('course', this.value);
        window.location.search = params.toString();
    });

    // Спиннер при отправке
    document.getElementById('genForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('genBtn');
        btn.disabled = true;
        btn.classList.add('btn-loading');
    });

    // При смене семестра — выставить дефолтную неделю
    document.getElementById('semesterSelect')?.addEventListener('change', function () {
        const weekInput = document.getElementById('templateWeek');
        const now       = new Date();
        const year      = now.getFullYear();
        let date;

        if (this.value === '1') {
            // Сентябрь текущего или прошлого года
            const sep = new Date(year, 8, 1); // 1 сентября
            // Ближайший понедельник
            const day = sep.getDay();
            const diff = day === 0 ? 1 : (day === 1 ? 0 : 8 - day);
            sep.setDate(sep.getDate() + diff);
            date = sep;
        } else {
            // Февраль
            const feb = new Date(year, 1, 1);
            const day = feb.getDay();
            const diff = day === 0 ? 1 : (day === 1 ? 0 : 8 - day);
            feb.setDate(feb.getDate() + diff);
            date = feb;
        }

        weekInput.value = date.toISOString().split('T')[0];
    });
</script>
@endpush
