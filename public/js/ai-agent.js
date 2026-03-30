/* ─── AI Agent Chat JS ─────────────────────────────────────────────────── */
(function () {
    'use strict';

    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const chatUrl   = document.getElementById('ai-chat-url')?.value ?? '';
    const uploadUrl = document.getElementById('ai-upload-url')?.value ?? '';
    const importUrl = document.getElementById('ai-import-url')?.value ?? '';
    const statusUrl = document.getElementById('ai-status-url')?.value ?? '';

    let history     = [];   // [{role, content}]
    let isTyping    = false;
    let selectedFile = null;
    let previewData  = [];

    // ─── DOM ─────────────────────────────────────────────────────────────
    const chatEl       = document.getElementById('ai-chat');
    const emptyState   = document.getElementById('ai-empty');
    const textarea     = document.getElementById('ai-textarea');
    const sendBtn      = document.getElementById('ai-send-btn');
    const statusDot    = document.getElementById('ai-status-dot');
    const statusText   = document.getElementById('ai-status-text');
    const modelSelect  = document.getElementById('ai-model-select');
    const importToggle = document.getElementById('ai-import-toggle');
    const modal        = document.getElementById('ai-modal');
    const modalClose   = document.getElementById('ai-modal-close');
    const fileInput    = document.getElementById('ai-file-input');
    const dropzone     = document.getElementById('ai-dropzone');
    const fileNameEl   = document.getElementById('ai-file-name');
    const uploadBtn    = document.getElementById('ai-upload-btn');
    const importBtn    = document.getElementById('ai-import-btn');
    const previewWrap  = document.getElementById('ai-preview-wrap');
    const previewTbody = document.getElementById('ai-preview-tbody');
    const monthChips   = document.querySelectorAll('.ai-month-chip');

    // ─── Init ─────────────────────────────────────────────────────────────
    checkStatus();
    setInterval(checkStatus, 15000);
    autoResize();

    // ─── Textarea ─────────────────────────────────────────────────────────
    textarea.addEventListener('input', autoResize);

    textarea.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    function autoResize() {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
    }

    // ─── Suggestions ─────────────────────────────────────────────────────
    document.querySelectorAll('.ai-suggestion').forEach(btn => {
        btn.addEventListener('click', () => {
            textarea.value = btn.textContent.trim();
            autoResize();
            sendMessage();
        });
    });

    // ─── Send message ─────────────────────────────────────────────────────
    function sendMessage() {
        const text = textarea.value.trim();
        if (!text || isTyping) return;

        addMessage('user', text);
        textarea.value = '';
        autoResize();
        history.push({ role: 'user', content: text });

        const typingEl = addTyping();
        isTyping = true;
        sendBtn.disabled = true;

        fetch(chatUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify({
                message: text,
                history: history.slice(-10),
                model:   modelSelect?.value,
            }),
        })
            .then(r => r.json())
            .then(data => {
                typingEl.remove();
                isTyping = false;
                sendBtn.disabled = false;

                if (data.success) {
                    const reply = data.reply;
                    addMessage('assistant', reply);
                    history.push({ role: 'assistant', content: reply });
                } else {
                    addMessage('assistant', '⚠ ' + (data.error ?? 'Ошибка сервера'));
                }
            })
            .catch(err => {
                typingEl.remove();
                isTyping = false;
                sendBtn.disabled = false;
                addMessage('assistant', '⚠ Ошибка соединения: ' + err.message);
            });
    }

    // ─── Render messages ──────────────────────────────────────────────────
    function addMessage(role, content) {
        if (emptyState) emptyState.style.display = 'none';

        const row = document.createElement('div');
        row.className = `ai-msg-row ${role}`;

        const avatarHtml = role === 'user'
            ? `<div class="ai-avatar">Д</div>`
            : `<div class="ai-avatar"><i class="bi bi-stars"></i></div>`;

        const time = new Date().toLocaleTimeString('ru', { hour: '2-digit', minute: '2-digit' });

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

        // Render markdown tables
        text = text.replace(/\|(.+)\|\n\|[-| ]+\|\n((?:\|.+\|\n?)*)/g, (match, header, rows) => {
            const ths = header.split('|').filter(s => s.trim()).map(h => `<th>${esc(h.trim())}</th>`).join('');
            const trs = rows.trim().split('\n').map(row => {
                const tds = row.split('|').filter(s => s.trim() !== undefined && row.includes('|')).slice(1, -1)
                    .map(c => `<td>${esc(c.trim())}</td>`).join('');
                return `<tr>${tds}</tr>`;
            }).join('');
            return `<table><thead><tr>${ths}</tr></thead><tbody>${trs}</tbody></table>`;
        });

        // Bold
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Code
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        // Line breaks
        text = text.replace(/\n/g, '<br>');

        return text;
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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
        modal.classList.add('open');
    });
    document.getElementById('ai-import-word-toggle')?.addEventListener('click', () => {
        attachMenu?.classList.remove('open');
        modal.classList.add('open');
    });
    modalClose?.addEventListener('click',  () => modal.classList.remove('open'));
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
        const months      = Array.from(document.querySelectorAll('.ai-month-chip.on input')).map(e => e.value).join(',') || new Date().getMonth() + 1;
        const year        = document.getElementById('ai-year')?.value ?? new Date().getFullYear();
        const importType  = document.querySelector('[name="ai-import-type"]:checked')?.value ?? 'workload';

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
                history.push({ role: 'assistant', content: msg });
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
