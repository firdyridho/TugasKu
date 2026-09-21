/**
 * TugasKu AI Assistant - Powered by NVIDIA Build
 * Pure JavaScript client without external dependencies
 */
(function() {
    let aiChatHistory = [];
    let isAiBusy = false;

    // Detect BASE_URL
    const baseUrl = window.BASE_URL || '/TugasKu';

    function initAiWidget() {
        if (document.getElementById('aiFloatingBtn')) return;

        // 1. Floating trigger button
        const btn = document.createElement('button');
        btn.id = 'aiFloatingBtn';
        btn.className = 'ai-floating-btn';
        btn.setAttribute('type', 'button');
        btn.setAttribute('aria-label', 'Buka Asisten AI TugasKu');
        btn.innerHTML = `
            <div class="ai-sparkle-pulse"></div>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            <span class="ai-btn-label">TugasKu AI</span>
        `;
        btn.addEventListener('click', toggleAiDrawer);
        document.body.appendChild(btn);

        // 2. Chat Drawer Container
        const drawer = document.createElement('div');
        drawer.id = 'aiChatDrawer';
        drawer.className = 'ai-chat-drawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');
        drawer.innerHTML = `
            <div class="ai-drawer-header">
                <div class="ai-drawer-title-group">
                    <div class="ai-avatar-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                    </div>
                    <div>
                        <div class="ai-drawer-title">TugasKu AI Assistant</div>
                        <div class="ai-drawer-badge">NVIDIA Build • Cloud AI</div>
                    </div>
                </div>
                <div class="ai-header-actions">
                    <button type="button" class="ai-btn-icon" id="aiClearBtn" title="Hapus Riwayat Obrolan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </button>
                    <button type="button" class="ai-btn-icon" id="aiCloseBtn" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Quick Chips -->
            <div class="ai-chips-wrapper">
                <button type="button" class="ai-chip-btn" data-query="Bantu saya menyusun prioritas tugas kuliah dan deadline terdekat minggu ini.">
                    Prioritaskan Deadline
                </button>
                <button type="button" class="ai-chip-btn" data-query="Bagaimana cara membagi waktu yang efektif antara kuliah, tugas, dan kegiatan organisasi?">
                    Bagi Waktu Organisasi
                </button>
                <button type="button" class="ai-chip-btn" data-query="Buatkan draf email atau pesan sopan izin tidak hadir kuliah ke dosen karena sakit.">
                    Draf Pesan ke Dosen
                </button>
                <button type="button" class="ai-chip-btn" data-query="Beri tips belajar terstruktur dan strategi menghadapi ujian akhir semester (UAS).">
                    Tips Menghadapi Ujian
                </button>
            </div>

            <!-- Messages Stream -->
            <div class="ai-messages-container" id="aiMessagesContainer">
                <div class="ai-msg-bubble ai">
                    <div class="ai-bubble-content">
                        Halo! Saya asisten cerdas <strong>TugasKu AI</strong> yang didukung oleh model NVIDIA. Ada tugas kuliah, strategi belajar, atau agenda organisasi yang ingin dibahas hari ini?
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <form class="ai-input-form" id="aiInputForm">
                <textarea
                    id="aiInputText"
                    class="ai-input-textarea"
                    placeholder="Ketik pertanyaan atau tugas Anda di sini... (Enter untuk kirim)"
                    rows="1"
                ></textarea>
                <button type="submit" class="ai-btn-send" id="aiSendBtn" aria-label="Kirim Pesan">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        `;
        document.body.appendChild(drawer);

        // Events
        document.getElementById('aiCloseBtn').addEventListener('click', closeAiDrawer);
        document.getElementById('aiClearBtn').addEventListener('click', clearAiChat);

        // Chip click events
        drawer.querySelectorAll('.ai-chip-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const query = this.getAttribute('data-query');
                if (query) {
                    sendAiMessage(query);
                }
            });
        });

        // Form submit
        const form = document.getElementById('aiInputForm');
        const textarea = document.getElementById('aiInputText');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = textarea.value.trim();
            if (text) {
                sendAiMessage(text);
                textarea.value = '';
                textarea.style.height = 'auto';
            }
        });

        // Enter to send, Shift+Enter for newline
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Auto-expand textarea
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    function toggleAiDrawer() {
        const drawer = document.getElementById('aiChatDrawer');
        if (!drawer) return;
        if (drawer.classList.contains('open')) {
            closeAiDrawer();
        } else {
            openAiDrawer();
        }
    }

    function openAiDrawer() {
        const drawer = document.getElementById('aiChatDrawer');
        if (!drawer) return;
        drawer.classList.add('open');
        setTimeout(() => {
            const input = document.getElementById('aiInputText');
            if (input) input.focus();
        }, 150);
    }

    function closeAiDrawer() {
        const drawer = document.getElementById('aiChatDrawer');
        if (!drawer) return;
        drawer.classList.remove('open');
    }

    function clearAiChat() {
        aiChatHistory = [];
        const container = document.getElementById('aiMessagesContainer');
        if (container) {
            container.innerHTML = `
                <div class="ai-msg-bubble ai">
                    <div class="ai-bubble-content">
                        Obrolan telah dibersihkan. Apa yang ingin Anda diskusikan atau tanyakan selanjutnya?
                    </div>
                </div>
            `;
        }
    }

    function appendMessage(role, text) {
        const container = document.getElementById('aiMessagesContainer');
        if (!container) return null;

        const bubble = document.createElement('div');
        bubble.className = `ai-msg-bubble ${role}`;

        // Format basic markdown (bold, lists, code, linebreaks)
        const formatted = formatAiText(text);

        bubble.innerHTML = `
            <div class="ai-bubble-content">${formatted}</div>
            ${role === 'ai' ? `
                <button type="button" class="ai-copy-btn" title="Salin jawaban">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    <span>Salin</span>
                </button>
            ` : ''}
        `;

        if (role === 'ai') {
            const copyBtn = bubble.querySelector('.ai-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    navigator.clipboard.writeText(text).then(() => {
                        this.querySelector('span').textContent = 'Tersalin!';
                        setTimeout(() => {
                            this.querySelector('span').textContent = 'Salin';
                        }, 2000);
                    });
                });
            }
        }

        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
        return bubble;
    }

    function showTypingIndicator() {
        const container = document.getElementById('aiMessagesContainer');
        if (!container) return null;

        const indicator = document.createElement('div');
        indicator.id = 'aiTypingIndicator';
        indicator.className = 'ai-msg-bubble ai typing';
        indicator.innerHTML = `
            <div class="ai-typing-dots">
                <span></span><span></span><span></span>
            </div>
        `;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
        return indicator;
    }

    function removeTypingIndicator() {
        const el = document.getElementById('aiTypingIndicator');
        if (el) el.remove();
    }

    function formatAiText(raw) {
        if (!raw) return '';
        // Escape HTML first
        let text = raw
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold: **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Italic: *text*
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // Inline code: `code`
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Line breaks
        text = text.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');

        return text;
    }

    async function sendAiMessage(messageText) {
        if (isAiBusy || !messageText.trim()) return;
        isAiBusy = true;

        const sendBtn = document.getElementById('aiSendBtn');
        if (sendBtn) sendBtn.disabled = true;

        // 1. Append User message
        appendMessage('user', messageText);

        // 2. Show typing
        showTypingIndicator();

        try {
            const response = await fetch(baseUrl + '/api/ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: messageText,
                    history: aiChatHistory
                })
            });

            removeTypingIndicator();

            const data = await response.json();

            if (data.success && data.reply) {
                appendMessage('ai', data.reply);
                // Push to conversation memory
                aiChatHistory.push({ role: 'user', content: messageText });
                aiChatHistory.push({ role: 'assistant', content: data.reply });
            } else {
                appendMessage('ai', 'Maaf, terjadi kendala saat memproses jawaban: ' + (data.error || 'Server tidak merespons.'));
            }
        } catch (err) {
            removeTypingIndicator();
            appendMessage('ai', 'Gagal terhubung ke layanan AI. Pastikan koneksi internet aktif.');
        } finally {
            isAiBusy = false;
            if (sendBtn) sendBtn.disabled = false;
            const input = document.getElementById('aiInputText');
            if (input) input.focus();
        }
    }

    // Expose toggle globally
    window.toggleTugasKuAi = toggleAiDrawer;

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAiWidget);
    } else {
        initAiWidget();
    }
})();

