/**
 * TugasKu AI Assistant
 * Solid, Minimalist & Executive UI (Zero Gradient, Clean Output)
 */
(function() {
    let aiChatHistory = [];
    let isAiBusy = false;
    let thinkingInterval = null;

    // Detect BASE_URL
    const baseUrl = window.BASE_URL || '/TugasKu';

    // Sleek Quantum Neural Spark AI Glyph (Clean & Futuristic)
    function getAiSparkSvg(size = 17, className = "") {
        return `
            <svg class="${className}" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C12 7.52 7.52 12 2 12C7.52 12 12 16.48 12 22C12 16.48 16.48 12 22 12C16.48 12 12 7.52 12 2Z" fill="currentColor" fill-opacity="0.18"/>
                <path d="M19 2C19 3.8 17.5 5.3 15.7 5.3C17.5 5.3 19 6.8 19 8.6C19 6.8 20.5 5.3 22.3 5.3C20.5 5.3 19 3.8 19 2Z" fill="currentColor"/>
                <circle cx="5" cy="19" r="1.5" fill="currentColor"/>
            </svg>
        `;
    }

    const thinkingStates = [
        'Memproses jawaban...',
        'Menyusun rekomendasi terstruktur...',
        'Memformat poin jawaban...'
    ];

    function initAiWidget() {
        if (document.getElementById('aiFloatingBtn')) return;

        // 1. Floating trigger button (Solid, Clean, Professional)
        const btn = document.createElement('button');
        btn.id = 'aiFloatingBtn';
        btn.className = 'ai-floating-btn';
        btn.setAttribute('type', 'button');
        btn.setAttribute('aria-label', 'Buka Asisten AI TugasKu');
        btn.innerHTML = `
            <div class="ai-btn-icon-wrap">
                ${getAiSparkSvg(16)}
            </div>
            <span class="ai-btn-label">TugasKu AI</span>
            <span class="ai-status-indicator" title="Online"></span>
        `;
        btn.addEventListener('click', toggleAiDrawer);
        document.body.appendChild(btn);

        // 2. Chat Drawer Container (Clean Slate & Solid Neutral)
        const drawer = document.createElement('div');
        drawer.id = 'aiChatDrawer';
        drawer.className = 'ai-chat-drawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');
        drawer.innerHTML = `
            <div class="ai-drawer-header">
                <div class="ai-drawer-title-group">
                    <div class="ai-header-icon-wrap">
                        ${getAiSparkSvg(16)}
                    </div>
                    <div>
                        <div class="ai-drawer-title">TugasKu AI</div>
                        <div class="ai-drawer-badge">Asisten Akademik &amp; Organisasi</div>
                    </div>
                </div>
                <div class="ai-header-actions">
                    <button type="button" class="ai-btn-icon" id="aiClearBtn" title="Bersihkan Percakapan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </button>
                    <button type="button" class="ai-btn-icon" id="aiCloseBtn" title="Tutup">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

                        <!-- Quick Chips / Tabs (Clean & Icon-Enriched) -->
            <div class="ai-chips-wrapper">
                <button type="button" class="ai-chip-btn" data-query="Bantu saya menyusun prioritas tugas kuliah dan deadline terdekat minggu ini.">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Prioritas Deadline</span>
                </button>
                <button type="button" class="ai-chip-btn" data-query="Bagaimana cara membagi waktu yang efektif antara kuliah, tugas, dan kegiatan organisasi?">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Bagi Waktu Organisasi</span>
                </button>
                <button type="button" class="ai-chip-btn" data-query="Buatkan draf pesan WhatsApp atau email sopan izin tidak hadir kuliah ke dosen.">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Draf Chat Dosen</span>
                </button>
                <button type="button" class="ai-chip-btn" data-query="Beri tips belajar terstruktur dan strategi menghadapi ujian (UTS/UAS).">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                    <span>Strategi Ujian</span>
                </button>
                <button type="button" class="ai-chip-btn" data-query="Rangkum materi perkuliahan secara ringkas, terstruktur, dan mudah dipahami.">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    <span>Rangkum Materi</span>
                </button>
            </div>

            <!-- Messages Stream -->
            <div class="ai-messages-container" id="aiMessagesContainer">
                <div class="ai-msg-bubble ai">
                    <div class="ai-bubble-avatar">
                        ${getAiSparkSvg(14)}
                    </div>
                    <div class="ai-bubble-content">
                        Halo! Saya <strong>TugasKu AI</strong>, asisten cerdas untuk kebutuhan perkuliahan dan manajemen organisasi Anda.
                        <br><br>
                        Ada prioritas tugas kuliah, draf pesan dosen, atau koordinasi proker organisasi yang ingin didiskusikan?
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <form class="ai-input-form" id="aiInputForm">
                <textarea
                    id="aiInputText"
                    class="ai-input-textarea"
                    placeholder="Tanyakan tugas, jadwal, atau organisasi..."
                    rows="1"
                ></textarea>
                <button type="submit" class="ai-btn-send" id="aiSendBtn" aria-label="Kirim Pesan">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
        drawer.querySelectorAll('.ai-chip-btn').forEach(chip => {
            chip.addEventListener('click', function() {
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
                    <div class="ai-bubble-avatar">
                        ${getAiSparkSvg(14)}
                    </div>
                    <div class="ai-bubble-content">
                        Riwayat percakapan telah dibersihkan. Silakan tanyakan hal yang ingin Anda ketahui!
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

        const formatted = formatAiText(text);

        if (role === 'ai') {
            bubble.innerHTML = `
                <div class="ai-bubble-avatar">
                    ${getAiSparkSvg(14)}
                </div>
                <div class="ai-bubble-content-wrap">
                    <div class="ai-bubble-content">${formatted}</div>
                    <div class="ai-bubble-actions">
                        <button type="button" class="ai-copy-btn" title="Salin seluruh jawaban">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>
            `;

            const copyBtn = bubble.querySelector('.ai-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const cleanText = text.replace(/<think>[\s\S]*?<\/think>/gi, '').trim();
                    navigator.clipboard.writeText(cleanText).then(() => {
                        const span = this.querySelector('span');
                        if (span) span.textContent = 'Tersalin!';
                        setTimeout(() => {
                            if (span) span.textContent = 'Salin';
                        }, 2000);
                    });
                });
            }
        } else {
            bubble.innerHTML = `
                <div class="ai-user-bubble-content">${formatted}</div>
            `;
        }

        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
        return bubble;
    }

    /**
     * Minimalist Clean Typing / Thinking Indicator (Zero Gradient)
     */
    function showTypingIndicator() {
        const container = document.getElementById('aiMessagesContainer');
        if (!container) return null;

        removeTypingIndicator();

        const indicator = document.createElement('div');
        indicator.id = 'aiTypingIndicator';
        indicator.className = 'ai-msg-bubble ai thinking-bubble';
        indicator.innerHTML = `
            <div class="ai-bubble-avatar">
                ${getAiSparkSvg(14)}
            </div>
            <div class="ai-thinking-minimal">
                <div class="ai-thinking-dots">
                    <span></span><span></span><span></span>
                </div>
                <span class="ai-thinking-label" id="aiThinkingStatusText">Memproses jawaban...</span>
            </div>
        `;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;

        let stateIdx = 0;
        if (thinkingInterval) clearInterval(thinkingInterval);
        thinkingInterval = setInterval(() => {
            stateIdx = (stateIdx + 1) % thinkingStates.length;
            const statusTextEl = document.getElementById('aiThinkingStatusText');
            if (statusTextEl) {
                statusTextEl.style.opacity = '0';
                setTimeout(() => {
                    statusTextEl.textContent = thinkingStates[stateIdx];
                    statusTextEl.style.opacity = '1';
                }, 150);
            }
        }, 2000);

        return indicator;
    }

    function removeTypingIndicator() {
        if (thinkingInterval) {
            clearInterval(thinkingInterval);
            thinkingInterval = null;
        }
        const el = document.getElementById('aiTypingIndicator');
        if (el) el.remove();
    }

    /**
     * Professional, Clean Markdown Formatter
     * Produces clean paragraphs, neat lists, code blocks, and tables
     * Completely strips <think> tags and eliminates chaotic line-breaks.
     */
    function formatAiText(raw) {
        if (!raw) return '';

        // 1. Strip thinking tags (<think>...</think>)
        let text = raw.replace(/<think>[\s\S]*?<\/think>/gi, '').trim();

        // 2. Extract code blocks (```lang ... ```)
        const codeBlocks = [];
        text = text.replace(/```([a-zA-Z0-9_\-#+]*)\r?\n([\s\S]*?)```/g, function(match, lang, code) {
            const placeholder = `___AI_CODE_BLOCK_${codeBlocks.length}___`;
            const cleanLang = lang.trim() || 'code';
            const escapedCode = code
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            codeBlocks.push(`
                <div class="ai-code-wrapper">
                    <div class="ai-code-header">
                        <span>${cleanLang}</span>
                        <button type="button" class="ai-code-copy-btn">Salin</button>
                    </div>
                    <pre><code class="ai-code-block">${escapedCode.trim()}</code></pre>
                </div>
            `);
            return `\n\n${placeholder}\n\n`;
        });

        // 3. Extract tables
        const tables = [];
        text = text.replace(/((?:\|[^\n]+\|(?:\r?\n|$))+)/g, function(match, tableBlock) {
            const rows = tableBlock.trim().split(/\r?\n/).filter(r => r.trim());
            if (rows.length < 2) return tableBlock;

            let html = '<div class="ai-table-wrap"><table class="ai-markdown-table">';

            rows.forEach((row, idx) => {
                if (/^\|[\s\-:|]+\|$/.test(row.trim())) {
                    return;
                }

                const cells = row.split('|').slice(1, -1);
                if (cells.length === 0) return;

                html += '<tr>';
                cells.forEach(cell => {
                    const tag = (idx === 0) ? 'th' : 'td';
                    html += `<${tag}>${cell.trim()}</${tag}>`;
                });
                html += '</tr>';
            });

            html += '</table></div>';
            const placeholder = `___AI_TABLE_${tables.length}___`;
            tables.push(html);
            return `\n\n${placeholder}\n\n`;
        });

        // 4. Escape HTML for the remaining text
        text = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // 5. Inline formatting (bold, italic, inline code)
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');

        // 6. Split text into logical lines / blocks
        const lines = text.split(/\r?\n/);
        const output = [];
        let inUl = false;
        let inOl = false;
        let paragraph = [];

        function flushParagraph() {
            if (paragraph.length > 0) {
                output.push(`<p class="ai-p">${paragraph.join('<br>')}</p>`);
                paragraph = [];
            }
        }

        function closeLists() {
            if (inUl) {
                output.push('</ul>');
                inUl = false;
            }
            if (inOl) {
                output.push('</ol>');
                inOl = false;
            }
        }

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (!line) {
                flushParagraph();
                closeLists();
                continue;
            }

            // Check if placeholder for code block or table
            if (/^___AI_(CODE_BLOCK|TABLE)_\d+___$/.test(line)) {
                flushParagraph();
                closeLists();
                output.push(line);
                continue;
            }

            // Headings
            if (/^###\s+(.*)$/.test(line)) {
                flushParagraph();
                closeLists();
                output.push(`<h5 class="ai-h5">${line.replace(/^###\s+/, '')}</h5>`);
                continue;
            }
            if (/^##?\s+(.*)$/.test(line)) {
                flushParagraph();
                closeLists();
                output.push(`<h4 class="ai-h4">${line.replace(/^##?\s+/, '')}</h4>`);
                continue;
            }

            // Blockquotes
            if (/^&gt;\s?(.*)$/.test(line)) {
                flushParagraph();
                closeLists();
                output.push(`<blockquote class="ai-blockquote">${line.replace(/^&gt;\s?/, '')}</blockquote>`);
                continue;
            }

            // Unordered list item (- or *)
            if (/^[-*]\s+(.*)$/.test(line)) {
                flushParagraph();
                if (inOl) {
                    output.push('</ol>');
                    inOl = false;
                }
                if (!inUl) {
                    output.push('<ul class="ai-ul">');
                    inUl = true;
                }
                output.push(`<li class="ai-li">${line.replace(/^[-*]\s+/, '')}</li>`);
                continue;
            }

            // Ordered list item (1. or 2.)
            if (/^\d+\.\s+(.*)$/.test(line)) {
                flushParagraph();
                if (inUl) {
                    output.push('</ul>');
                    inUl = false;
                }
                if (!inOl) {
                    output.push('<ol class="ai-ol">');
                    inOl = true;
                }
                output.push(`<li class="ai-oli">${line.replace(/^\d+\.\s+/, '')}</li>`);
                continue;
            }

            // Normal text inside paragraph
            closeLists();
            paragraph.push(line);
        }

        flushParagraph();
        closeLists();

        let finalHtml = output.join('\n');

        // Restore code blocks
        codeBlocks.forEach((codeBlock, idx) => {
            finalHtml = finalHtml.replace(`___AI_CODE_BLOCK_${idx}___`, codeBlock);
        });

        // Restore tables
        tables.forEach((table, idx) => {
            finalHtml = finalHtml.replace(`___AI_TABLE_${idx}___`, table);
        });

        return finalHtml;
    }

    // Attach copy button listeners inside code blocks dynamically
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('ai-code-copy-btn')) {
            const wrapper = e.target.closest('.ai-code-wrapper');
            if (wrapper) {
                const code = wrapper.querySelector('code');
                if (code) {
                    navigator.clipboard.writeText(code.innerText).then(() => {
                        e.target.textContent = 'Tersalin!';
                        setTimeout(() => {
                            e.target.textContent = 'Salin';
                        }, 1500);
                    });
                }
            }
        }
    });

    async function sendAiMessage(messageText) {
        if (isAiBusy || !messageText.trim()) return;
        isAiBusy = true;

        const sendBtn = document.getElementById('aiSendBtn');
        if (sendBtn) sendBtn.disabled = true;

        // 1. Append User message
        appendMessage('user', messageText);

        // 2. Show clean typing indicator
        showTypingIndicator();

        try {
            const response = await fetch(baseUrl + '/api/ai', {
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
                aiChatHistory.push({ role: 'user', content: messageText });
                aiChatHistory.push({ role: 'assistant', content: data.reply });
            } else {
                appendMessage('ai', 'Maaf, terjadi kendala: ' + (data.error || 'Layanan tidak merespons.'));
            }
        } catch (err) {
            removeTypingIndicator();
            appendMessage('ai', 'Gagal terhubung ke layanan AI. Pastikan server lokal aktif.');
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
