/* ─── AI Agent Chat JS ─────────────────────────────────────────────────── */
(function () {
    'use strict';

    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const chatUrl   = document.getElementById('ai-chat-url')?.value ?? '';
    const uploadUrl = document.getElementById('ai-upload-url')?.value ?? '';
    const importUrl = document.getElementById('ai-import-url')?.value ?? '';
    const statusUrl = document.getElementById('ai-status-url')?.value ?? '';

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
    let selectedFile     = null;
    let previewData      = [];

    // ─── DOM ─────────────────────────────────────────────────────────────
    const chatEl           = document.getElementById('ai-chat');
    const emptyState       = document.getElementById('ai-empty');
    const textarea         = document.getElementById('ai-textarea');
    const sendBtn          = document.getElementById('ai-send-btn');
    const statusDot        = document.getElementById('ai-status-dot');
    const statusText       = document.getElementById('ai-status-text');
    const modelSelect      = document.getElementById('ai-model-select');
    const importToggle     = document.getElementById('ai-import-toggle');
    const modal            = document.getElementById('ai-modal');
    const modalClose       = document.getElementById('ai-modal-close');
    const fileInput        = document.getElementById('ai-file-input');
    const dropzone         = document.getElementById('ai-dropzone');
    const fileNameEl       = document.getElementById('ai-file-name');
    const uploadBtn        = document.getElementById('ai-upload-btn');
    const importBtn        = document.getElementById('ai-import-btn');
    const previewWrap      = document.getElementById('ai-preview-wrap');
    const previewTbody     = document.getElementById('ai-preview-tbody');
    const monthChips       = document.querySelectorAll('.ai-month-chip');
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

    // ─── Textarea ─────────────────────────────────────────────────────────
    textarea.addEventListener('input', autoResize);

    textarea.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    sendBtn.addEventListener('click', () => sendMessage());

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
        if (!text || isTyping) return;

        addMessage('user', text);
        textarea.value = '';
        autoResize();
        history.push({ role: 'user', content: text, ts: Date.now() });
        saveCurrentSession();

        const typingEl = addTyping();
        isTyping = true;
        sendBtn.disabled = true;

        fetch(chatUrl, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                message: text,
                history: history.slice(-10).map(({ role, content }) => ({ role, content })),
                model:   modelSelect?.value,
            }),
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
            typingEl.remove();
            isTyping = false;
            sendBtn.disabled = false;
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
            typingEl.remove();
            isTyping = false;
            sendBtn.disabled = false;
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
            : `<div class="ai-avatar"><i class="bi bi-stars"></i></div>`;

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
            <div class="ai-avatar"><i class="bi bi-robot"></i></div>
            <div class="ai-bubble">
                <div class="ai-bubble-inner">
                    <div class="ai-typing"><span></span><span></span><span></span></div>
                </div>
            </div>
        `;
        chatEl.appendChild(row);
        chatEl.scrollTop = chatEl.scrollHeight;
        return row;
    }

    function renderContent(text, role) {
        if (role === 'user') return esc(text).replace(/\n/g, '<br>');

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

    // ─── Attach + menu ────────────────────────────────────────────────────
    const attachBtn  = document.getElementById('ai-attach-btn');
    const attachMenu = document.getElementById('ai-attach-menu');

    attachBtn?.addEventListener('click', e => {
        e.stopPropagation();
        attachMenu.classList.toggle('open');
    });

    document.addEventListener('click', e => {
        if (!attachMenu?.contains(e.target) && e.target !== attachBtn) {
            attachMenu?.classList.remove('open');
        }
    });

    // ─── Import modal ─────────────────────────────────────────────────────
    importToggle?.addEventListener('click', () => {
        attachMenu?.classList.remove('open');
        modal?.classList.add('open');
    });
    document.getElementById('ai-import-word-toggle')?.addEventListener('click', () => {
        attachMenu?.classList.remove('open');
        modal?.classList.add('open');
    });
    modalClose?.addEventListener('click', () => modal.classList.remove('open'));
    modal?.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

    // Month chips
    monthChips.forEach(chip => {
        chip.addEventListener('click', () => {
            chip.classList.toggle('on');
            chip.querySelector('input').checked = chip.classList.contains('on');
        });
    });

    // Dropzone
    if (dropzone) {
        dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', () => { if (fileInput.files[0]) handleFile(fileInput.files[0]); });
    }

    function handleFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['xlsx','xls','docx','doc'].includes(ext)) { alert('Разрешены только Excel и Word файлы'); return; }
        selectedFile = file;
        fileNameEl.textContent = file.name;
        fileNameEl.style.display = 'block';
        uploadBtn.disabled = false;
        previewWrap.style.display = 'none';
        previewData = [];
        importBtn.disabled = true;
    }

    uploadBtn?.addEventListener('click', () => {
        if (!selectedFile) return;
        const months     = Array.from(document.querySelectorAll('.ai-month-chip.on input')).map(e => e.value).join(',') || new Date().getMonth() + 1;
        const year       = document.getElementById('ai-year')?.value ?? new Date().getFullYear();
        const importType = document.querySelector('[name="ai-import-type"]:checked')?.value ?? 'workload';

        const fd = new FormData();
        fd.append('file', selectedFile);
        fd.append('import_type', importType);
        fd.append('months', months);
        fd.append('year', year);
        fd.append('_token', CSRF);

        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="ai-spinner"></span> Анализ...';

        fetch(uploadUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-search"></i> Анализировать';
                if (!data.success) { alert(data.error); return; }
                previewData = data.preview;
                renderPreviewTable(data);
                previewWrap.style.display = 'block';
                importBtn.disabled = data.preview.filter(r => r.status !== 'exists').length === 0;
            })
            .catch(err => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-search"></i> Анализировать';
                alert('Ошибка: ' + err.message);
            });
    });

    importBtn?.addEventListener('click', () => {
        if (!previewData.length) return;
        const months     = Array.from(document.querySelectorAll('.ai-month-chip.on input')).map(e => parseInt(e.value));
        const year       = parseInt(document.getElementById('ai-year')?.value ?? new Date().getFullYear());
        const importType = document.querySelector('[name="ai-import-type"]:checked')?.value ?? 'workload';

        importBtn.disabled = true;
        importBtn.innerHTML = '<span class="ai-spinner"></span> Импорт...';

        fetch(importUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ import_type: importType, rows: previewData, months, year }),
        })
        .then(r => r.json())
        .then(data => {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="bi bi-database-add"></i> Импортировать';
            modal.classList.remove('open');

            const s = data.stats ?? {};
            const msg = data.success
                ? `✓ Импорт завершён. Добавлено: ${s.inserted ?? 0}, обновлено: ${s.updated ?? 0}, пропущено: ${s.skipped ?? 0}.`
                : '⚠ Ошибка импорта: ' + data.error;

            addMessage('assistant', msg);
            history.push({ role: 'assistant', content: msg, ts: Date.now() });
            saveCurrentSession();
        })
        .catch(err => {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="bi bi-database-add"></i> Импортировать';
            alert('Ошибка: ' + err.message);
        });
    });

    function renderPreviewTable(data) {
        previewTbody.innerHTML = '';
        let newC = 0, updC = 0;

        data.preview.forEach(row => {
            if (row.status === 'new')    newC++;
            if (row.status === 'update') updC++;

            const hasWarn = row.warnings?.length > 0;
            const tr = document.createElement('tr');
            tr.innerHTML = data.import_type === 'workload' ? `
                <td><strong>${esc(row.group_name)}</strong></td>
                <td>${esc(row.subject_name)}</td>
                <td>${esc(row.teacher_name) || '—'}</td>
                <td>${row.total_hours}</td>
                <td><span class="ai-badge ai-badge-${row.status}">${row.status === 'new' ? 'Новая' : row.status === 'update' ? 'Обновить' : 'Есть'}</span></td>
                <td>${hasWarn ? row.warnings.map(w => `<div class="warn-text">${esc(w)}</div>`).join('') : '✓'}</td>
            ` : `
                <td><strong>${esc(row.teacher_name)}</strong></td>
                <td>${esc(row.initials ?? '—')}</td>
                <td><span class="ai-badge ai-badge-${row.status}">${row.status === 'new' ? 'Новая' : 'Есть'}</span></td>
            `;
            previewTbody.appendChild(tr);
        });

        document.getElementById('ai-preview-summary').textContent =
            `Найдено: ${data.preview.length} записей (новых: ${newC}, обновить: ${updC})`;
    }
})();
