@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ai-agent.css') }}?v={{ filemtime(public_path('css/ai-agent.css')) }}">
    <style>
        /* Убираем отступы layout-а для чат-страницы */
        .ko-main { padding: 0 !important; }
        .ko-main-inner { padding: 0 !important; height: 100%; }
        .ko-content { overflow: hidden; }
        .ko-main { height: calc(100vh - 48px); overflow: hidden; }
    </style>
@endpush

@section('content')

{{-- URL-пины для JS --}}
<input type="hidden" id="ai-chat-url"   value="{{ route('ai_agent.chat') }}">
<input type="hidden" id="ai-upload-url" value="{{ route('ai_agent.upload') }}">
<input type="hidden" id="ai-import-url" value="{{ route('ai_agent.import') }}">
<input type="hidden" id="ai-status-url" value="{{ route('ai_agent.ollama_status') }}">

<div class="ai-page">

    {{-- ─── Top bar ──────────────────────────────────────────────── --}}
    <div class="ai-topbar">
        <div class="ai-topbar-title">
            <div class="ai-topbar-icon"><i class="bi bi-stars"></i></div>
            ИИ-Агент
        </div>

        <div class="ai-topbar-right">
            <div class="ai-status-dot {{ $ollamaStatus ? 'online' : 'offline' }}" id="ai-status-dot">
                <span class="dot"></span>
                <span id="ai-status-text">{{ $ollamaStatus ? 'Ollama работает' : 'Ollama недоступна' }}</span>
            </div>

            @if($ollamaModels)
            <select class="ai-model-select" id="ai-model-select">
                @foreach($ollamaModels as $model)
                    <option value="{{ $model }}">{{ $model }}</option>
                @endforeach
            </select>
            @else
            <select class="ai-model-select" id="ai-model-select">
                <option value="llama3.2:3b">llama3.2:3b</option>
            </select>
            @endif
        </div>
    </div>

    {{-- ─── Chat ────────────────────────────────────────────────── --}}
    <div class="ai-chat" id="ai-chat">
        <div class="ai-empty-state" id="ai-empty">
            <div class="ai-empty-logo"><i class="bi bi-stars"></i></div>
            <div>
                <p class="ai-empty-title">Чем могу помочь?</p>
                <p class="ai-empty-sub">Я умею работать с базой данных: выводить списки, добавлять, изменять и удалять записи.</p>
            </div>
            <div class="ai-suggestions">
                <button class="ai-suggestion">Покажи всех преподавателей</button>
                <button class="ai-suggestion">Список групп 2 курса</button>
                <button class="ai-suggestion">Покажи все аудитории</button>
                <button class="ai-suggestion">Список праздников</button>
                <button class="ai-suggestion">Сколько всего преподавателей?</button>
                <button class="ai-suggestion">Покажи дисциплины 1 курса</button>
            </div>
        </div>
    </div>

    {{-- ─── Input ───────────────────────────────────────────────── --}}
    <div class="ai-input-area">
        <div class="ai-input-wrap">
            <textarea
                id="ai-textarea"
                class="ai-textarea"
                placeholder="Напишите запрос... (Enter — отправить, Shift+Enter — новая строка)"
                rows="1"
            ></textarea>
            <div class="ai-input-footer">
                {{-- + кнопка с меню --}}
                <div class="ai-attach-wrap" id="ai-attach-wrap">
                    <button class="ai-attach-btn" id="ai-attach-btn" title="Прикрепить">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <div class="ai-attach-menu" id="ai-attach-menu">
                        <button class="ai-attach-item" id="ai-import-toggle">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            <span>Загрузить Excel</span>
                        </button>
                        <button class="ai-attach-item" id="ai-import-word-toggle">
                            <i class="bi bi-file-earmark-word"></i>
                            <span>Загрузить Word</span>
                        </button>
                        <div class="ai-attach-divider"></div>
                        <button class="ai-attach-item" onclick="document.getElementById('ai-textarea').value='Покажи всех преподавателей';document.getElementById('ai-attach-menu').classList.remove('open')">
                            <i class="bi bi-mortarboard"></i>
                            <span>Список преподавателей</span>
                        </button>
                        <button class="ai-attach-item" onclick="document.getElementById('ai-textarea').value='Список всех групп';document.getElementById('ai-attach-menu').classList.remove('open')">
                            <i class="bi bi-people"></i>
                            <span>Список групп</span>
                        </button>
                    </div>
                </div>

                <span class="ai-input-hint">Enter — отправить &nbsp;·&nbsp; Shift+Enter — новая строка</span>
                <button class="ai-send-btn" id="ai-send-btn" title="Отправить">
                    <i class="bi bi-arrow-up"></i>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ─── Import modal ─────────────────────────────────────────── --}}
<div class="ai-modal-backdrop" id="ai-modal">
    <div class="ai-modal">
        <div class="ai-modal-head">
            <div class="ai-modal-title"><i class="bi bi-file-earmark-arrow-up" style="color:var(--ai-primary)"></i> &nbsp;Импорт из файла</div>
            <button class="ai-modal-close" id="ai-modal-close"><i class="bi bi-x"></i></button>
        </div>

        <div class="ai-modal-body">

            {{-- Import type --}}
            <div class="ai-form-group">
                <label class="ai-label">Тип данных</label>
                <div style="display:flex;gap:16px">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px">
                        <input type="radio" name="ai-import-type" value="workload" checked style="accent-color:var(--ai-primary)">
                        Нагрузка (Форма 2)
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px">
                        <input type="radio" name="ai-import-type" value="teachers" style="accent-color:var(--ai-primary)">
                        Преподаватели
                    </label>
                </div>
            </div>

            {{-- File drop --}}
            <div class="ai-form-group">
                <label class="ai-label">Файл</label>
                <div class="ai-dropzone" id="ai-dropzone">
                    <input type="file" id="ai-file-input" accept=".xlsx,.xls,.docx,.doc">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <div class="ai-dropzone-text">Перетащите файл или нажмите</div>
                    <div class="ai-dropzone-hint">Excel (.xlsx) или Word (.docx)</div>
                    <div id="ai-file-name" style="display:none" class="ai-file-name"></div>
                </div>
            </div>

            {{-- Months --}}
            <div class="ai-form-group">
                <label class="ai-label">Месяцы</label>
                <div class="ai-months">
                    @php $months = [1=>'Янв',2=>'Фев',3=>'Мар',4=>'Апр',5=>'Май',6=>'Июн',7=>'Июл',8=>'Авг',9=>'Сен',10=>'Окт',11=>'Ноя',12=>'Дек']; $cm = (int)date('n'); @endphp
                    @foreach($months as $n => $name)
                        <label class="ai-month-chip {{ $n === $cm ? 'on' : '' }}">
                            <input type="checkbox" value="{{ $n }}" {{ $n === $cm ? 'checked' : '' }}>
                            {{ $name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Year --}}
            <div class="ai-form-group">
                <label class="ai-label">Год</label>
                <input type="number" class="ai-input" id="ai-year" value="{{ date('Y') }}" min="2020" max="2030" style="max-width:110px">
            </div>

            {{-- Preview --}}
            <div id="ai-preview-wrap" style="display:none">
                <div style="font-size:12.5px;color:var(--ai-muted);margin-bottom:6px" id="ai-preview-summary"></div>
                <div class="ai-preview-table-wrap">
                    <table class="ai-preview-table">
                        <thead>
                            <tr>
                                <th>Группа</th><th>Предмет</th><th>Преподаватель</th><th>Часы</th><th>Статус</th><th>Проверка</th>
                            </tr>
                        </thead>
                        <tbody id="ai-preview-tbody"></tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="ai-modal-foot">
            <button class="ai-btn ai-btn-ghost" id="ai-modal-close-2" onclick="document.getElementById('ai-modal').classList.remove('open')">Отмена</button>
            <button class="ai-btn ai-btn-ghost" id="ai-upload-btn" disabled>
                <i class="bi bi-search"></i> Анализировать
            </button>
            <button class="ai-btn ai-btn-success" id="ai-import-btn" disabled>
                <i class="bi bi-database-add"></i> Импортировать
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/ai-agent.js') }}?v={{ filemtime(public_path('js/ai-agent.js')) }}"></script>
@endpush
