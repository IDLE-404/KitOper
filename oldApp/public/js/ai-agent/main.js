/* ─── AI Agent Chat JS ─────────────────────────────────────────────────── */
(function () {
    'use strict';

    const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const chatUrl      = document.getElementById('ai-chat-url')?.value ?? '';
    const parseFileUrl = document.getElementById('ai-parse-file-url')?.value ?? '';
    const statusUrl    = document.getElementById('ai-status-url')?.value ?? '';

    // Storage keys
    const LEGACY_KEY      = 'ai_agent_chat_history_v1'; // for migration
    const SESSIONS_KEY    = 'ai_agent_sessions_v2';
    const CUR_SESSION_KEY = 'ai_agent_current_session_v2';
    const SIDEBAR_KEY     = 'ai_agent_sidebar_v2';
    const SESSION_LIMIT   = 50;
    const MSG_LIMIT       = 100;

    // State
    let sessions         = [];
    let currentSessionId = null;
    let history          = []; // alias → current session messages
    let isTyping         = false;
    let pinnedTable      = null; // selected table context
    let pendingFile      = null; // { name, content } — parsed but not yet sent
    let abortController  = null; // current request abort controller
    
    // Table selection for AI context
    window.selectTableForAI = function(tableData, tableTitle) {
        pinnedTable = { data: tableData, title: tableTitle, ts: Date.now() };
        
        // Add tag to input
        const textarea = document.getElementById('ai-textarea');
        if (textarea) {
            const existing = textarea.value;
            const tag = '📎 ' + (tableTitle || 'таблица');
            if (!existing.includes(tag)) {
                textarea.value = existing + (existing ? '\n' : '') + tag + '\n';
            }
        }
        
        // Show visual feedback - highlight selected rows in current page tables
        document.querySelectorAll('.ai-table-selected').forEach(el => el.classList.remove('ai-table-selected'));
        
        // Dispatch event for pages to handle visual selection
        window.dispatchEvent(new CustomEvent('ai-table-select', { detail: pinnedTable }));
        
        // Show toast
        showToast('Таблица добавлена в контекст');
    };
    
    function showToast(msg) {
        const existing = document.querySelector('.ai-toast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = 'ai-toast';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    // Status messages for AI thinking visualization
    const STATUS_MESSAGES = {
        file: [
            'Читаю файл...',
            'Анализирую структуру Excel...',
            'Определяю тип данных...',
            'Сопоставляю колонки с базой...',
            'Готовлю preview...',
        ],
        db: [
            'Работаю с базой данных...',
            'Запрос к базе...',
            'Обрабатываю результаты...',
            'Форматирую ответ...',
        ],
        schedule: [
            'Проверяю расписание...',
            'Ищу конфликты...',
            'Проверяю аудитории...',
            'Проверяю преподавателей...',
        ],
        general: [
            'Анализирую запрос...',
            'Обрабатываю...',
            'Секунду...',
            'Думаю...',
        ]
    };

    function detectIntent(text) {
        const t = text.toLowerCase();
        if (t.includes('файл') || t.includes('загрузи') || t.includes('импорт') || t.includes('excel') || t.includes('xlsx') || t.includes('график')) return 'file';
        if (t.includes('расписание') || t.includes('конфликт') || t.includes('аудитори') || t.includes('свободн')) return 'schedule';
        if (t.includes('покажи') || t.includes('список') || t.includes('найди') || t.includes('базу') || t.includes('данны')) return 'db';
        return 'general';
    }

    // ─── DOM ─────────────────────────────────────────────────────────────
    const chatEl           = document.getElementById('ai-chat');
    const emptyState       = document.getElementById('ai-empty');
    const textarea         = document.getElementById('ai-textarea');
    const sendBtn          = document.getElementById('ai-send-btn');
    const statusDot        = document.getElementById('ai-status-dot');
    const statusText       = document.getElementById('ai-status-text');
    const modelSelect      = document.getElementById('ai-model-select');
    const fileInput        = document.getElementById('ai-file-input');
    const dropOverlay      = document.getElementById('ai-drop-overlay');
    const fileBar          = document.getElementById('ai-file-bar');
    const fileNameEl       = document.getElementById('ai-file-name');
    const fileClear        = document.getElementById('ai-file-clear');
    const refBar           = document.getElementById('ai-ref-bar');
    const refLabel         = document.getElementById('ai-ref-label');
    const refClear         = document.getElementById('ai-ref-clear');
    const sidebarEl        = document.getElementById('ai-sidebar');
    const sidebarList      = document.getElementById('ai-sidebar-list');
    const sidebarToggleBtn = document.getElementById('ai-sidebar-toggle');
    const newChatBtn       = document.getElementById('ai-new-chat-btn');

    // ─── Init ─────────────────────────────────────────────────────────────
    initSessions();
    checkStatus();
    setInterval(checkStatus, 15000);
    autoResize();

    // ─── Sidebar toggle ───────────────────────────────────────────────────
    sidebarToggleBtn?.addEventListener('click', () => {
        sidebarEl.classList.toggle('collapsed');
        try { localStorage.setItem(SIDEBAR_KEY, !sidebarEl.classList.contains('collapsed')); } catch (_) {}
    });

    newChatBtn?.addEventListener('click', () => createNewSession());

    // ─── Table reference (click to pin) ──────────────────────────────────
    function tableToText(tableEl) {
        const MAX     = 20;
        const headers = Array.from(tableEl.querySelectorAll('thead th')).map(c => c.textContent.trim());
        const bodyRows = Array.from(tableEl.querySelectorAll('tbody tr'));
        const total   = bodyRows.length;
        const lines   = [];

        if (headers.length) lines.push('Колонки: ' + headers.join(', '));

        bodyRows.slice(0, MAX).forEach((row, i) => {
            const cells = Array.from(row.querySelectorAll('td')).map(c => c.textContent.trim());
            if (headers.length) {
                const pairs = headers.map((h, j) => `${h}: ${cells[j] ?? '—'}`).join('; ');
                lines.push(`${i + 1}. ${pairs}`);
            } else {
                lines.push(`${i + 1}. ${cells.join('; ')}`);
            }
        });

        if (total > MAX) lines.push(`(показаны первые ${MAX} из ${total} строк)`);
        return lines.join('\n');
    }

    function pinTable(tableWrap, label) {
        // Deselect previous
        document.querySelectorAll('.ai-table-wrap.pinned').forEach(el => el.classList.remove('pinned'));
        tableWrap.classList.add('pinned');
        pinnedTable = tableToText(tableWrap.querySelector('table'));
        refLabel.textContent = label || 'Таблица прикреплена';
        refBar.classList.add('visible');
        textarea.focus();
    }

    function clearPin() {
        pinnedTable = null;
        document.querySelectorAll('.ai-table-wrap.pinned').forEach(el => el.classList.remove('pinned'));
        refBar.classList.remove('visible');
    }

    function showFileChip(name, loading = false) {
        if (fileNameEl) fileNameEl.textContent = name;
        if (fileBar) {
            fileBar.classList.add('visible');
            fileBar.classList.toggle('loading', loading);
        }
    }

    function clearFile() {
        pendingFile = null;
        if (fileBar) fileBar.classList.remove('visible', 'loading');
    }

    refClear?.addEventListener('click', clearPin);
    fileClear?.addEventListener('click', clearFile);

    chatEl.addEventListener('click', e => {
        const wrap = e.target.closest('.ai-table-wrap');
        if (!wrap) return;
        if (wrap.classList.contains('pinned')) { clearPin(); return; }
        // Count rows for label
        const rows = wrap.querySelectorAll('tbody tr').length;
        pinTable(wrap, `Таблица прикреплена (${rows} строк)`);
    });

    // ─── Textarea ─────────────────────────────────────────────────────────
    textarea.addEventListener('input', autoResize);

    textarea.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    function setStopMode(on) {
        if (on) {
            sendBtn.disabled = false;
            sendBtn.classList.add('is-stop');
            sendBtn.innerHTML = '<i class="bi bi-stop-fill"></i>';
            sendBtn.title = 'Остановить';
        } else {
            sendBtn.classList.remove('is-stop');
            sendBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
            sendBtn.title = 'Отправить';
        }
    }

    function stopGeneration() {
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
    }

    sendBtn.addEventListener('click', () => {
        if (sendBtn.classList.contains('is-stop')) {
            stopGeneration();
        } else {
            sendMessage();
        }
    });

    function autoResize() {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
    }

    // ─── Sessions ─────────────────────────────────────────────────────────
    function newSession(messages = []) {
        const firstUser = messages.find(m => m.role === 'user');
        const ts = messages[0]?.ts || Date.now();
        return {
            id: 'sess_' + ts + '_' + Math.random().toString(36).slice(2, 6),
            title: firstUser ? firstUser.content.slice(0, 50).trim() : 'Новый чат',
            createdAt: ts,
            updatedAt: messages[messages.length - 1]?.ts || ts,
            messages: messages.filter(m =>
                (m.role === 'user' || m.role === 'assistant') &&
                typeof m.content === 'string' && m.content.trim()
            ),
        };
    }

    function initSessions() {
        try {
            const raw = localStorage.getItem(SESSIONS_KEY);
            if (raw) sessions = JSON.parse(raw) || [];
        } catch (_) { sessions = []; }

        // Validate
        sessions = sessions.filter(s => s && typeof s.id === 'string' && Array.isArray(s.messages));

        // Migrate legacy single history
        if (sessions.length === 0) {
            try {
                const oldRaw = localStorage.getItem(LEGACY_KEY);
                if (oldRaw) {
                    const oldMsgs = JSON.parse(oldRaw) || [];
                    if (oldMsgs.length > 0) sessions.push(newSession(oldMsgs));
                }
            } catch (_) {}
        }

        if (sessions.length === 0) sessions.push(newSession([]));

        // Restore current session
        let savedId = null;
        try { savedId = localStorage.getItem(CUR_SESSION_KEY); } catch (_) {}
        const found = sessions.find(s => s.id === savedId);
        currentSessionId = found ? found.id : sessions[sessions.length - 1].id;

        loadSessionMessages();
        renderSidebar();

        // Restore sidebar state
        let sidebarOpen = true;
        try { sidebarOpen = localStorage.getItem(SIDEBAR_KEY) !== 'false'; } catch (_) {}
        if (!sidebarOpen) sidebarEl?.classList.add('collapsed');
    }

    function loadSessionMessages() {
        const session = sessions.find(s => s.id === currentSessionId);
        if (!session) return;

        history = session.messages;

        Array.from(chatEl.querySelectorAll('.ai-msg-row')).forEach(el => el.remove());
        if (emptyState) emptyState.style.display = history.length === 0 ? '' : 'none';

        history.forEach(item => addMessage(item.role, item.content, item.ts));
    }

    function switchToSession(id) {
        if (id === currentSessionId) return;
        currentSessionId = id;
        loadSessionMessages();
        saveSessions();
        renderSidebar();
    }

    function createNewSession() {
        const cur = sessions.find(s => s.id === currentSessionId);
        if (cur && cur.messages.length === 0) return; // already empty

        const sess = newSession([]);
        sessions.push(sess);
        currentSessionId = sess.id;
        loadSessionMessages();
        saveSessions();
        renderSidebar();
    }

    function deleteSession(id) {
        sessions = sessions.filter(s => s.id !== id);
        if (sessions.length === 0) sessions.push(newSession([]));
        const needSwitch = currentSessionId === id;
        if (needSwitch) currentSessionId = sessions[sessions.length - 1].id;
        saveSessions();
        if (needSwitch) loadSessionMessages();
        renderSidebar();
    }

    function saveCurrentSession() {
        const idx = sessions.findIndex(s => s.id === currentSessionId);
        if (idx === -1) return;
        sessions[idx].messages = [...history];
        sessions[idx].updatedAt = Date.now();
        const firstUser = history.find(m => m.role === 'user');
        if (firstUser) sessions[idx].title = firstUser.content.slice(0, 50).trim();
        saveSessions();
        renderSidebar();
    }

    function saveSessions() {
        try {
            localStorage.setItem(SESSIONS_KEY, JSON.stringify(sessions.slice(-SESSION_LIMIT)));
            localStorage.setItem(CUR_SESSION_KEY, currentSessionId || '');
        } catch (_) {}
    }

    function renderSidebar() {
        if (!sidebarList) return;
        sidebarList.innerHTML = '';
        const sorted = [...sessions].sort((a, b) => b.updatedAt - a.updatedAt);
        sorted.forEach(session => {
            const item = document.createElement('div');
            item.className = 'ai-session-item' + (session.id === currentSessionId ? ' active' : '');

            const title   = session.title || 'Новый чат';
            const dateStr = formatSessionDate(session.updatedAt);

            item.innerHTML = `
                <i class="bi bi-chat-left ai-session-icon"></i>
                <div class="ai-session-info">
                    <div class="ai-session-title">${esc(title)}</div>
                    <div class="ai-session-date">${esc(dateStr)}</div>
                </div>
                <button class="ai-session-del" title="Удалить"><i class="bi bi-trash3"></i></button>
            `;

            item.addEventListener('click', e => {
                if (e.target.closest('.ai-session-del')) return;
                switchToSession(session.id);
            });

            item.querySelector('.ai-session-del').addEventListener('click', e => {
                e.stopPropagation();
                if (confirm('Удалить этот чат?')) deleteSession(session.id);
            });

            sidebarList.appendChild(item);
        });
    }

    function formatSessionDate(ts) {
        const d         = new Date(ts);
        const now       = new Date();
        const today     = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const yesterday = new Date(today.getTime() - 86400000);
        const dDay      = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        if (dDay.getTime() === today.getTime())
            return d.toLocaleTimeString('ru', { hour: '2-digit', minute: '2-digit' });
        if (dDay.getTime() === yesterday.getTime()) return 'Вчера';
        return d.toLocaleDateString('ru', { day: 'numeric', month: 'short' });
    }

    // ─── Send message ─────────────────────────────────────────────────────
    function sendMessage() {
        const text = textarea.value.trim();
        if ((!text && !pendingFile) || isTyping) return;

        // Build message with optional file context
        let messageForAI = text;
        let displayText  = text;

        if (pendingFile) {
            // Limit file content to 3000 chars to avoid Ollama timeout
            const truncatedContent = pendingFile.content.length > 3000 
                ? pendingFile.content.slice(0, 3000) + '\n\n[файл обрезан - слишком большой]'
                : pendingFile.content;
            const question = text || 'Проанализируй что в файле и расскажи что там.';
            messageForAI = `Пользователь загрузил файл «${pendingFile.name}».\n\nСодержимое файла:\n${truncatedContent}\n\nВопрос: ${question}`;
            displayText  = (text ? text + '\n' : '') + `📎 ${pendingFile.name}`;
            clearFile();
        }

        // Build message with optional pinned table context
        if (pinnedTable) {
            messageForAI = `[ТАБЛИЦА]\nПользователь выделил таблицу со следующими данными:\n${pinnedTable}\n\nВопрос пользователя: ${messageForAI}`;
            clearPin();
        }

        addMessage('user', displayText || text);
        textarea.value = '';
        autoResize();
        history.push({ role: 'user', content: displayText || text, ts: Date.now() });
        saveCurrentSession();

        const typingEl = addTyping();
        isTyping = true;
        setStopMode(true);

        // Show thinking status with rotating messages based on intent
        let statusInterval = null;
        const statusEl = typingEl.querySelector('.ai-typing-text');

        function showStatus(msg) {
            if (statusEl) statusEl.textContent = msg;
        }

        const intent = detectIntent(text);
        const messages = STATUS_MESSAGES[intent] || STATUS_MESSAGES.general;

        let statusIndex = 0;
        showStatus(messages[0]);
        statusInterval = setInterval(() => {
            statusIndex = (statusIndex + 1) % messages.length;
            showStatus(messages[statusIndex]);
        }, 1500);

        abortController = new AbortController();

        // Timeout after 5 minutes (CPU inference is slow)
        const timeoutId = setTimeout(() => {
            abortController.abort();
        }, 300000);

        fetch(chatUrl, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                message: messageForAI,
                history: history.slice(-10).map(({ role, content }) => ({ role, content })),
                model:   modelSelect?.value,
            }),
            signal: abortController.signal,
        })
        .then(async r => {
            const raw = await r.text();
            let data = null;
            try { data = raw ? JSON.parse(raw) : null; } catch (_) { data = null; }
            if (!r.ok) {
                const fallback = r.status === 419
                    ? 'Сессия истекла. Обновите страницу и повторите запрос.'
                    : `Ошибка сервера (${r.status}). Попробуйте еще раз.`;
                throw new Error(data?.error || fallback);
            }
            if (!data || typeof data !== 'object')
                throw new Error('Сервер вернул некорректный ответ. Обновите страницу и повторите запрос.');
            return data;
        })
        .then(data => {
            clearTimeout(timeoutId);
            clearInterval(statusInterval);
            typingEl.remove();
            isTyping = false;
            abortController = null;
            setStopMode(false);
            if (data.success) {
                const reply = data.reply;
                addMessage('assistant', reply);
                history.push({ role: 'assistant', content: reply, ts: Date.now() });
                saveCurrentSession();
            } else {
                const errorText = '⚠ ' + (data.error ?? 'Ошибка сервера');
                addMessage('assistant', errorText);
                history.push({ role: 'assistant', content: errorText, ts: Date.now() });
                saveCurrentSession();
            }
        })
        .catch(err => {
            clearTimeout(timeoutId);
            clearInterval(statusInterval);
            typingEl.remove();
            isTyping = false;
            abortController = null;
            setStopMode(false);
            if (err.name === 'AbortError') {
                const msg = '⏱ Модель не успела ответить за 5 минут. Попробуйте задать более короткий вопрос.';
                addMessage('assistant', msg);
                history.push({ role: 'assistant', content: msg, ts: Date.now() });
                saveCurrentSession();
                return;
            }
            const errorText = '⚠ Ошибка соединения: ' + err.message;
            addMessage('assistant', errorText);
            history.push({ role: 'assistant', content: errorText, ts: Date.now() });
            saveCurrentSession();
        });
    }

    // ─── Render messages ──────────────────────────────────────────────────
    function addMessage(role, content, ts = Date.now()) {
        if (emptyState) emptyState.style.display = 'none';

        const row = document.createElement('div');
        row.className = `ai-msg-row ${role}`;

        const avatarHtml = role === 'user'
            ? `<div class="ai-avatar">Д</div>`
            : `<div class="ai-avatar"><i class="bi bi-lightbulb"></i></div>`;

        const time = formatTime(ts);

        row.innerHTML = `
            ${avatarHtml}
            <div class="ai-bubble">
                <div class="ai-bubble-inner">${renderContent(content, role)}</div>
                <div class="ai-msg-time">${time}</div>
            </div>
        `;

        chatEl.appendChild(row);
        chatEl.scrollTop = chatEl.scrollHeight;
        return row;
    }

    function addTyping() {
        if (emptyState) emptyState.style.display = 'none';
        const row = document.createElement('div');
        row.className = 'ai-msg-row assistant';
        row.innerHTML = `
            <div class="ai-avatar"><i class="bi bi-lightbulb"></i></div>
            <div class="ai-bubble">
                <div class="ai-bubble-inner">
                    <div class="ai-typing">
                        <span class="ai-typing-text">Думаю...</span>
                        <div class="ai-typing-dots"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>
        `;
        chatEl.appendChild(row);
        chatEl.scrollTop = chatEl.scrollHeight;
        return row;
    }

    function renderContent(text, role) {
        if (role === 'user') return esc(text).replace(/\n/g, '<br>');

        // Apply schedule planning renderers first
        text = renderPlanningContent(text);

        // 1. Fenced code blocks  ```lang\n...\n```
        text = text.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) => {
            const label = lang ? `<div class="ai-code-lang">${esc(lang)}</div>` : '';
            return `<div class="ai-code-block">${label}<pre><code>${esc(code.trim())}</code></pre></div>`;
        });

        // 2. Markdown tables
        const inLines  = text.split('\n');
        const outLines = [];
        let i = 0;
        while (i < inLines.length) {
            const line     = inLines[i];
            const nextLine = inLines[i + 1] ?? '';
            if (/^\|.+\|$/.test(line.trim()) && /^\|[\s\-|:]+\|$/.test(nextLine.trim())) {
                const ths = line.trim().slice(1, -1).split('|')
                    .map(h => `<th>${esc(h.trim())}</th>`).join('');
                i += 2;
                const trs = [];
                while (i < inLines.length && /^\|.+\|$/.test(inLines[i].trim())) {
                    const tds = inLines[i].trim().slice(1, -1).split('|')
                        .map(c => `<td>${esc(c.trim())}</td>`).join('');
                    trs.push(`<tr>${tds}</tr>`);
                    i++;
                }
                outLines.push(`<div class="ai-table-wrap"><table><thead><tr>${ths}</tr></thead><tbody>${trs.join('')}</tbody></table></div>`);
                continue;
            }
            outLines.push(line);
            i++;
        }
        text = outLines.join('\n');

        // 3. Bold
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // 4. Italic
        text = text.replace(/(?<!\*)\*([^*\n]+)\*(?!\*)/g, '<em>$1</em>');
        // 5. Inline code
        text = text.replace(/`([^`\n]+)`/g, '<code>$1</code>');
        // 5a. Highlight ==text==
        text = text.replace(/==([^=\n]+)==/g, '<mark class="ai-hl">$1</mark>');
        // 6. Bullet lists
        text = text.replace(/((?:(?:^|\n)- .+)+)/g, match => {
            const items = match.trim().split('\n').map(l => `<li>${l.replace(/^- /, '')}</li>`).join('');
            return `<ul>${items}</ul>`;
        });
        // 7. Numbered lists
        text = text.replace(/((?:(?:^|\n)\d+\. .+)+)/g, match => {
            const items = match.trim().split('\n').map(l => `<li>${l.replace(/^\d+\. /, '')}</li>`).join('');
            return `<ol>${items}</ol>`;
        });
        // 8. Blockquote > line
        text = text.replace(/((?:(?:^|\n)&gt; .+)+)/g, match => {
            const inner = match.trim().split('\n').map(l => l.replace(/^&gt; /, '')).join('<br>');
            return `<blockquote class="ai-quote">${inner}</blockquote>`;
        });
        // 9. Line breaks
        text = text.replace(/\n/g, '<br>');

        return text;
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatTime(ts) {
        const d = new Date(ts ?? Date.now());
        const safeDate = Number.isNaN(d.getTime()) ? new Date() : d;
        return safeDate.toLocaleTimeString('ru', { hour: '2-digit', minute: '2-digit' });
    }

    function hasUserMessages() {
        return history.some(item => item?.role === 'user');
    }

    // ─── Schedule Planning Renderers ──────────────────────────────────────

    function renderPlanningContent(html) {
        html = renderQuickSuggestions(html);
        html = renderStatusBadges(html);
        html = renderSlots(html);
        html = renderTeacherCards(html);
        html = renderStatsCards(html);
        html = renderConflictItems(html);
        html = renderActionButtons(html);
        return html;
    }

    function renderQuickSuggestions(text) {
        const suggestions = [
            { pattern: /проверь конфликт/i, icon: 'bi-shield-check', label: 'Проверить конфликты' },
            { pattern: /замен/i, icon: 'bi-person-shift', label: 'Найти замену' },
            { pattern: /свободн.*аудитор/i, icon: 'bi-door-open', label: 'Свободные аудитории' },
            { pattern: /статистик/i, icon: 'bi-bar-chart', label: 'Статистика недели' },
            { pattern: /запланируй/i, icon: 'bi-calendar-plus', label: 'Запланировать' },
        ];

        let hasIntent = false;
        let buttons = '';
        suggestions.forEach(s => {
            if (s.pattern.test(text)) {
                hasIntent = true;
                buttons += `<button class="ai-quick-btn" onclick="sendQuickMessage('${s.label}')"><i class="bi ${s.icon}"></i>${s.label}</button>`;
            }
        });

        if (hasIntent && !text.includes('ai-quick-suggestions')) {
            text += '\n\n<div class="ai-quick-suggestions">' + buttons + '</div>';
        }

        return text;
    }

    function renderStatusBadges(text) {
        const badges = [
            { pattern: /конфликт/i, class: 'danger', icon: 'bi-exclamation-triangle' },
            { pattern: /✓|свободен|нет конфликт/i, class: 'success', icon: 'bi-check-circle' },
            { pattern: /частичн|ограничен/i, class: 'warning', icon: 'bi-info-circle' },
            { pattern: /предложен/i, class: 'info', icon: 'bi-lightbulb' },
        ];

        badges.forEach(b => {
            if (b.pattern.test(text)) {
                const badge = `<span class="ai-status-badge ${b.class}"><i class="bi ${b.icon}"></i></span>`;
                text = text.replace(b.pattern, match => badge + ' ' + match);
            }
        });

        return text;
    }

    function renderSlots(text) {
        const slotPattern = /(Понедельник|Вторник|Среда|Четверг|пятница|[Сс]уббота),?\s*(\d+)\s*пара/gi;
        if (slotPattern.test(text)) {
            return text;
        }
        return text;
    }

    function renderTeacherCards(text) {
        const lines = text.split('\n');
        let inList = false;
        let result = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const teacherMatch = line.match(/^[-\s]*(✓|○)?\s*\*\*(.+?)\*\*\s*\((.+?)\)/);

            if (teacherMatch) {
                const [, status, name, initials] = teacherMatch;
                const isFree = status === '✓';
                const statusClass = isFree ? 'free' : 'busy';
                const statusText = isFree ? 'Свободен' : 'Частично занят';

                result.push(`<div class="ai-teacher-card">
                    <div class="ai-teacher-avatar">${initials.split(' ')[0]?.[0] || name[0]}</div>
                    <div class="ai-teacher-info">
                        <div class="ai-teacher-name">${esc(name)}</div>
                        <div class="ai-teacher-meta">${esc(initials)}</div>
                    </div>
                    <span class="ai-teacher-status ${statusClass}">${statusText}</span>
                </div>`);
            } else {
                result.push(line);
            }
        }

        return result.join('\n');
    }

    function renderStatsCards(text) {
        const statsMatch = text.match(/Всего пар[:\s]*==?(\d+)==?/i);
        if (statsMatch) {
            const totalPairs = statsMatch[1];
            text = text.replace(statsMatch[0], '');

            const statsHtml = `<div class="ai-stats-grid">
                <div class="ai-stat-card">
                    <div class="ai-stat-value">${esc(totalPairs)}</div>
                    <div class="ai-stat-label">Пар всего</div>
                </div>
            </div>`;

            text = text.replace(/(\*\*Статистика[^\n]*\*\*)(\n|$)/i, '$1' + statsHtml);
        }

        return text;
    }

    function renderConflictItems(text) {
        const conflictLines = text.split('\n');
        let result = [];

        for (let i = 0; i < conflictLines.length; i++) {
            const line = conflictLines[i];

            if (line.match(/^(?:⚠️|⚠)/)) {
                const conflictMatch = line.match(/аудитория\s+\*\*([^*]+)\*\*/i) ||
                                     line.match(/преподаватель\s+\*\*([^*]+)\*\*/i);

                let itemClass = 'warning';
                let icon = 'bi-exclamation-triangle';

                if (line.includes('Конфликтов не обнаружено') || line.includes('✓')) {
                    itemClass = 'success';
                    icon = 'bi-check-circle';
                }

                result.push(`<div class="ai-conflict-item ${itemClass}">
                    <div class="ai-conflict-icon"><i class="bi ${icon}"></i></div>
                    <div class="ai-conflict-text">${esc(line.replace(/^[⚠️⚠]\s*/, ''))}</div>
                </div>`);
            } else {
                result.push(line);
            }
        }

        return result.join('\n');
    }

    function renderActionButtons(text) {
        if (text.includes('Скажите') || text.includes('Чтобы') || text.includes('скажите')) {
            const btnHtml = `<div class="ai-action-buttons">
                <button class="ai-action-btn secondary" onclick="clearChat()">
                    <i class="bi bi-arrow-clockwise"></i> Новая проверка
                </button>
            </div>`;

            if (!text.includes('ai-action-buttons')) {
                text = text + '\n\n' + btnHtml;
            }
        }

        return text;
    }

    // Quick message sender
    window.sendQuickMessage = function(message) {
        const textarea = document.getElementById('ai-textarea');
        if (textarea) {
            textarea.value = message;
            sendMessage();
        }
    };

    window.clearChat = function() {
        if (hasUserMessages() && confirm('Очистить историю чата?')) {
            const idx = sessions.findIndex(s => s.id === currentSessionId);
            if (idx !== -1) {
                sessions[idx].messages = [];
                sessions[idx].title = 'Новый чат';
            }
            history = [];
            Array.from(chatEl.querySelectorAll('.ai-msg-row')).forEach(el => el.remove());
            if (emptyState) emptyState.style.display = '';
            saveSessions();
            renderSidebar();
        }
    };

    // ─── Ollama status ────────────────────────────────────────────────────
    function checkStatus() {
        fetch(statusUrl)
            .then(r => r.json())
            .then(data => {
                if (data.running) {
                    statusDot.className = 'ai-status-dot online';
                    statusText.textContent = 'Ollama работает';
                    if (modelSelect && data.models?.length) {
                        const cur = modelSelect.value;
                        modelSelect.innerHTML = data.models.map(m =>
                            `<option value="${esc(m)}" ${m === cur ? 'selected' : ''}>${esc(m)}</option>`
                        ).join('');
                    }
                } else {
                    statusDot.className = 'ai-status-dot offline';
                    statusText.textContent = 'Ollama недоступна';
                }
            })
            .catch(() => {
                statusDot.className = 'ai-status-dot offline';
                statusText.textContent = 'Ollama недоступна';
            });
    }

    // ─── Clear chat ───────────────────────────────────────────────────────
    document.getElementById('ai-clear-btn')?.addEventListener('click', () => {
        if (!hasUserMessages()) return;
        if (!confirm('Очистить историю чата?')) return;

        const idx = sessions.findIndex(s => s.id === currentSessionId);
        if (idx !== -1) {
            sessions[idx].messages = [];
            sessions[idx].title = 'Новый чат';
        }
        history = [];
        Array.from(chatEl.querySelectorAll('.ai-msg-row')).forEach(el => el.remove());
        if (emptyState) emptyState.style.display = '';
        saveSessions();
        renderSidebar();
    });

    // ─── File upload (paperclip button + drag-and-drop) ──────────────────
    fileInput?.addEventListener('change', () => {
        if (fileInput.files[0]) uploadFileToChat(fileInput.files[0]);
        fileInput.value = '';
    });

    // Page-level drag-and-drop
    const pageEl = document.querySelector('.ai-page');
    let dragCounter = 0;

    pageEl?.addEventListener('dragenter', e => {
        e.preventDefault();
        dragCounter++;
        dropOverlay?.classList.add('visible');
    });
    pageEl?.addEventListener('dragleave', () => {
        dragCounter--;
        if (dragCounter <= 0) { dragCounter = 0; dropOverlay?.classList.remove('visible'); }
    });
    pageEl?.addEventListener('dragover', e => e.preventDefault());
    pageEl?.addEventListener('drop', e => {
        e.preventDefault();
        dragCounter = 0;
        dropOverlay?.classList.remove('visible');
        const file = e.dataTransfer.files[0];
        if (file) uploadFileToChat(file);
    });

    function uploadFileToChat(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls', 'docx', 'doc'].includes(ext)) {
            addMessage('assistant', '⚠ Поддерживаются только файлы Excel (.xlsx) и Word (.docx).');
            return;
        }

        // Show loading chip immediately, then parse
        clearFile();
        showFileChip('Читаю файл…', true);

        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', CSRF);

        fetch(parseFileUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.error ?? 'Ошибка разбора файла');
                pendingFile = { name: data.filename, content: data.content };
                showFileChip(data.filename, false);
                textarea.focus();
            })
            .catch(err => {
                clearFile();
                addMessage('assistant', '⚠ Не удалось прочитать файл: ' + err.message);
            });
    }
})();
