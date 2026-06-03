<div id="ai-widget-root">
    <button class="aw-btn" id="ai-widget-toggle" title="ИИ-Помощник">
        <i class="bi bi-lightbulb"></i>
    </button>

    <div class="aw-panel" id="ai-widget-panel">
        <div class="aw-header">
            <div class="aw-hd-left">
                <div class="aw-hd-icon"><i class="bi bi-lightbulb"></i></div>
                <div>
                    <div class="aw-hd-name">ИИ-Помощник</div>
                    <div class="aw-hd-hint">Поможет с расписанием</div>
                </div>
            </div>
            <div class="aw-hd-actions">
                <a href="{{ route('ai_agent.index') }}" class="aw-hd-btn" title="Открыть страницу ИИ">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <button class="aw-hd-btn" id="ai-widget-clear" title="Очистить чат">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="aw-messages" id="ai-messages">
            <div class="aw-empty" id="ai-empty">
                <div class="aw-empty-icon"><i class="bi bi-lightbulb"></i></div>
                <p>Привет! Я помогу с расписанием.<br>Просто напишите, что нужно сделать.</p>
            </div>
        </div>

        <div class="aw-footer">
            <div class="aw-input-row">
                <textarea class="aw-ta" id="ai-input" placeholder="Например: покажи преподавателей" rows="1"></textarea>
                <button class="aw-send-btn" id="ai-send" title="Отправить">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const btn = document.getElementById('ai-widget-toggle');
    const panel = document.getElementById('ai-widget-panel');
    const input = document.getElementById('ai-input');
    const sendBtn = document.getElementById('ai-send');
    const messagesEl = document.getElementById('ai-messages');
    const emptyEl = document.getElementById('ai-empty');
    const clearBtn = document.getElementById('ai-widget-clear');

    let history = [];
    let isLoading = false;

    btn.addEventListener('click', () => {
        panel.classList.toggle('open');
        btn.classList.toggle('open');
        if (panel.classList.contains('open')) {
            input.focus();
        }
    });

    clearBtn.addEventListener('click', () => {
        history = [];
        renderMessages();
    });

    function renderMessages() {
        if (history.length === 0) {
            emptyEl.style.display = 'flex';
            messagesEl.querySelectorAll('.aw-msg').forEach(e => e.remove());
            return;
        }

        emptyEl.style.display = 'none';
        messagesEl.querySelectorAll('.aw-msg').forEach(e => e.remove());

        history.forEach(msg => {
            const row = document.createElement('div');
            row.className = `aw-msg ${msg.role}`;
            row.innerHTML = `
                <div class="aw-msg-av">${msg.role === 'user' ? 'В' : 'ИИ'}</div>
                <div class="aw-msg-bubble">${msg.content}</div>
            `;
            messagesEl.appendChild(row);
        });

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addMessage(role, content) {
        if (history.length === 0) {
            emptyEl.style.display = 'none';
        }

        const row = document.createElement('div');
        row.className = `aw-msg ${role}`;
        row.innerHTML = `
            <div class="aw-msg-av">${role === 'user' ? 'В' : 'ИИ'}</div>
            <div class="aw-msg-bubble">${content}</div>
        `;
        messagesEl.appendChild(row);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text || isLoading) return;

        isLoading = true;
        input.value = '';
        sendBtn.disabled = true;

        addMessage('user', text);
        history.push({ role: 'user', content: text });

        const typing = document.createElement('div');
        typing.className = 'aw-msg assistant';
        typing.id = 'ai-typing';
        typing.innerHTML = `
            <div class="aw-msg-av">ИИ</div>
            <div class="aw-msg-bubble"><div class="aw-typing"><span></span><span></span><span></span></div></div>
        `;
        messagesEl.appendChild(typing);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const res = await fetch('{{ route('ai_agent.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: text, history: history.slice(-10) })
            });

            const data = await res.json();

            const typingEl = document.getElementById('ai-typing');
            if (typingEl) typingEl.remove();

            if (data.success) {
                addMessage('assistant', data.reply);
                history.push({ role: 'assistant', content: data.reply });
            } else {
                const errMsg = data.error || 'Ошибка';
                addMessage('assistant', '❌ ' + errMsg);
                history.push({ role: 'assistant', content: '❌ ' + errMsg });
            }
        } catch (e) {
            const typingEl = document.getElementById('ai-typing');
            if (typingEl) typingEl.remove();
            addMessage('assistant', '❌ Ошибка связи');
        }

        isLoading = false;
        sendBtn.disabled = false;
        input.focus();
    }

    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 90) + 'px';
    });
})();
</script>
