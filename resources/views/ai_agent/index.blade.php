@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ai-agent/main.css') }}?v={{ filemtime(public_path('css/ai-agent/main.css')) }}">
    <style>
        /* Фиксируем страницу чата — скроллится только чат, не вся страница */
        html, body { overflow: hidden !important; height: 100% !important; }
        .ko-app        { height: 100vh; overflow: hidden; }
        .ko-content    { overflow: hidden !important; height: 100% !important; flex: 1; }
        .ko-main       { padding: 0 !important; overflow: hidden !important; height: 100% !important; }
        .ko-main-inner { padding: 0 !important; height: 100% !important; min-height: 0 !important;
                         border-radius: 0 !important; box-shadow: none !important;
                         display: flex; flex-direction: column; overflow: hidden; }
        .ai-page       { height: 100%; overflow: hidden; }
    </style>
@endpush

@section('content')

{{-- URL-пины для JS --}}
<input type="hidden" id="ai-chat-url"       value="{{ route('ai_agent.chat') }}">
<input type="hidden" id="ai-parse-file-url" value="{{ route('ai_agent.parse_file') }}">
<input type="hidden" id="ai-status-url"     value="{{ route('ai_agent.ollama_status') }}">

<div class="ai-page">

    {{-- ─── Sidebar ────────────────────────────────────────────────── --}}
    <div class="ai-sidebar" id="ai-sidebar">
        <div class="ai-sidebar-header">
            <span class="ai-sidebar-title">История</span>
            <button class="ai-new-chat-btn" id="ai-new-chat-btn">
                <i class="bi bi-plus-lg"></i> Новый чат
            </button>
        </div>
        <div class="ai-sidebar-list" id="ai-sidebar-list"></div>
    </div>

    {{-- ─── Main ───────────────────────────────────────────────────── --}}
    <div class="ai-main">

        {{-- ─── Top bar ──────────────────────────────────────────────── --}}
        <div class="ai-topbar">
            <div class="ai-topbar-left">
                <button class="ai-sidebar-toggle" id="ai-sidebar-toggle" title="История чатов">
                    <i class="bi bi-layout-sidebar"></i>
                </button>
                <div class="ai-topbar-title">
                    <div class="ai-topbar-icon"><i class="bi bi-stars"></i></div>
                    ИИ-Агент
                </div>
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
                    <option value="qwen2.5:3b">qwen2.5:3b</option>
                </select>
                @endif
            </div>
        </div>

        {{-- ─── Chat ───────────────────────────────────────────────── --}}
        <div class="ai-chat" id="ai-chat">
            <div class="ai-empty-state" id="ai-empty">
                <div class="ai-empty-logo"><i class="bi bi-stars"></i></div>
                <p class="ai-empty-title">Чем могу помочь?</p>
            </div>
        </div>

        {{-- ─── Input ──────────────────────────────────────────────── --}}
        <div class="ai-input-area">
            <div class="ai-input-wrap">
                <div class="ai-file-bar" id="ai-file-bar">
                    <i class="bi bi-paperclip"></i>
                    <span id="ai-file-name">файл</span>
                    <button class="ai-file-clear" id="ai-file-clear" title="Убрать"><i class="bi bi-x"></i></button>
                </div>
                <div class="ai-ref-bar" id="ai-ref-bar">
                    <i class="bi bi-table"></i>
                    <span id="ai-ref-label">таблица</span>
                    <button class="ai-ref-clear" id="ai-ref-clear" title="Убрать"><i class="bi bi-x"></i></button>
                </div>
                <textarea
                    id="ai-textarea"
                    class="ai-textarea"
                    placeholder="Напишите запрос..."
                    rows="1"
                ></textarea>
                <div class="ai-input-footer">
                    <div class="ai-footer-left">
                        <label class="ai-attach-btn" title="Прикрепить файл (Excel, Word)">
                            <i class="bi bi-paperclip"></i>
                            <input type="file" id="ai-file-input" accept=".xlsx,.xls,.docx,.doc" style="display:none">
                        </label>
                        <button class="ai-icon-btn ai-clear-icon-btn" id="ai-clear-btn" title="Очистить чат">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    <button class="ai-send-btn" id="ai-send-btn" title="Отправить">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Drag-and-drop overlay for the whole chat --}}
<div class="ai-drop-overlay" id="ai-drop-overlay">
    <div class="ai-drop-overlay-inner">
        <i class="bi bi-file-earmark-arrow-up"></i>
        <span>Отпустите файл</span>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/ai-agent/main.js') }}?v={{ filemtime(public_path('js/ai-agent/main.js')) }}"></script>
@endpush
