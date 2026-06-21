<?php
$slug  = $_GET['slug'] ?? '';
$total = count($questions);
require BASE_PATH . '/app/Views/templates/header.php';
?>

<style>
    /* ── Dynamic viewport: accounts for mobile soft keyboard ─────────────── */
    :root {
        --header-h: 64px;
        --input-h: 76px;
        --indigo: #6366f1;
        --indigo-soft: #eef2ff;
        --indigo-dark: #4338ca;
        --text: #1e1b4b;
        --muted: #6b7280;
        --border: #e5e7eb;
        --bg: #fafafa;
    }

    html, body {
        height: 100%;
        overflow: hidden;
        background: var(--bg);
    }

    /* ── Layout shell: 3 fixed zones ─────────────────────────────────────── */
    #survey-shell {
        display: flex;
        flex-direction: column;
        height: 100dvh; /* dynamic viewport — respects soft keyboard */
        overflow: hidden;
    }

    /* ── Zone 1: Header + Progress (fixed top) ───────────────────────────── */
    #survey-header {
        flex-shrink: 0;
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: 0 20px;
        height: var(--header-h);
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        z-index: 10;
    }

    #header-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    #header-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    #header-brand .brand-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: var(--indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    #header-brand .brand-name {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    #step-counter {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--muted);
        white-space: nowrap;
        flex-shrink: 0;
    }

    #progress-track {
        height: 4px;
        background: var(--border);
        border-radius: 99px;
        overflow: hidden;
    }

    #progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--indigo), var(--indigo-dark));
        border-radius: 99px;
        transition: width 500ms cubic-bezier(0.4, 0, 0.2, 1);
        width: 0%;
    }

    /* ── Zone 2: Question stage (flex-grow, centred) ─────────────────────── */
    #question-stage {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        overflow: hidden;
        position: relative;
    }

    #question-card {
        max-width: 560px;
        width: 100%;
        text-align: center;
        will-change: transform, opacity;
        transition: transform 350ms cubic-bezier(0.4, 0, 0.2, 1),
                    opacity  350ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    #question-card.exit-left {
        transform: translateX(-48px);
        opacity: 0;
    }

    #question-card.enter-right {
        transform: translateX(48px);
        opacity: 0;
    }

    #question-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--indigo);
        background: var(--indigo-soft);
        padding: 4px 12px;
        border-radius: 99px;
        margin-bottom: 20px;
    }

    #question-text {
        font-size: clamp(1.25rem, 4vw, 2rem);
        font-weight: 600;
        color: var(--text);
        line-height: 1.4;
        letter-spacing: -0.02em;
    }

    /* Idle dots while transitioning */
    #loading-dots {
        display: none;
        gap: 6px;
        justify-content: center;
        align-items: center;
    }

    #loading-dots.visible {
        display: flex;
    }

    #loading-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--indigo);
        animation: bounce-dot 1.2s infinite ease-in-out;
    }

    #loading-dots span:nth-child(2) { animation-delay: 0.2s; }
    #loading-dots span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce-dot {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* ── Zone 3: Input (fixed bottom) ────────────────────────────────────── */
    #input-zone {
        flex-shrink: 0;
        background: #fff;
        border-top: 1px solid var(--border);
        padding: 12px 16px;
        /* Safe area for iPhone notch / home bar */
        padding-bottom: max(12px, env(safe-area-inset-bottom));
        z-index: 10;
    }

    #input-row {
        max-width: 560px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #chat-input {
        flex: 1;
        resize: none;
        border: 2px solid var(--border);
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 0.9375rem;
        font-family: inherit;
        color: var(--text);
        background: #fff;
        outline: none;
        line-height: 1.5;
        max-height: 120px;
        overflow-y: auto;
        transition: border-color 200ms;
    }

    #chat-input:focus {
        border-color: var(--indigo);
    }

    #chat-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #chat-send {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 14px;
        background: var(--indigo);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        transition: opacity 150ms, transform 150ms;
        /* Always visible — never hidden */
    }

    #chat-send:hover:not(:disabled) {
        opacity: 0.88;
        transform: scale(1.04);
    }

    #chat-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    #chat-send .spinner {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    #chat-send.loading .send-icon { display: none; }
    #chat-send.loading .spinner    { display: block; }

    /* ── Respect prefers-reduced-motion ──────────────────────────────────── */
    @media (prefers-reduced-motion: reduce) {
        #question-card,
        #progress-fill {
            transition: none !important;
        }
        #loading-dots span {
            animation: none !important;
            opacity: 0.6;
        }
    }
</style>

<div id="survey-shell">

    <!-- Zone 1 · Header + Progress ---------------------------------------- -->
    <header id="survey-header">
        <div id="header-meta">
            <div id="header-brand">
                <div class="brand-icon">
                    <i data-lucide="sparkles" style="width:15px;height:15px;color:#fff;"></i>
                </div>
                <span class="brand-name"><?= htmlspecialchars($survey['name']) ?></span>
            </div>
            <span id="step-counter">Iniciando...</span>
        </div>
        <div id="progress-track">
            <div id="progress-fill"></div>
        </div>
    </header>

    <!-- Zone 2 · Question stage -------------------------------------------- -->
    <main id="question-stage">
        <div id="question-card">
            <div id="question-label">
                <i data-lucide="message-circle" style="width:11px;height:11px;"></i>
                <span id="label-text">Pesquisa</span>
            </div>
            <p id="question-text">Carregando...</p>
            <div id="loading-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </main>

    <!-- Zone 3 · Input (always visible, fixed to bottom of shell) ---------- -->
    <div id="input-zone">
        <div id="input-row">
            <textarea
                id="chat-input"
                rows="1"
                placeholder="Digite sua resposta..."
                aria-label="Campo de resposta"
            ></textarea>
            <button id="chat-send" aria-label="Enviar resposta">
                <i data-lucide="send" class="send-icon" style="width:18px;height:18px;"></i>
                <div class="spinner"></div>
            </button>
        </div>
    </div>

</div>

<script>
lucide.createIcons();

/* ── Constants injected from PHP ─────────────────────────────────────────── */
const SURVEY_SLUG   = '<?= htmlspecialchars($slug) ?>';
const RESPONDENT_ID = <?= (int) ($respondent['id'] ?? 0) ?>;
const questions     = <?= json_encode(array_values(array_map(
    fn($q) => ['id' => (int)$q['id'], 'text' => $q['text']],
    $questions
))) ?>;
const total         = questions.length;
const answered      = <?= (int) ($answered ?? 0) ?>;

/* ── State ───────────────────────────────────────────────────────────────── */
let step          = answered;                                    // current question index
let nameCollected = <?= ($respondent['name'] ? 'true' : 'false') ?>;
let pendingName   = false;
let isTransitioning = false;

/* ── DOM refs ────────────────────────────────────────────────────────────── */
const $input    = document.getElementById('chat-input');
const $send     = document.getElementById('chat-send');
const $card     = document.getElementById('question-card');
const $qText    = document.getElementById('question-text');
const $qLabel   = document.getElementById('label-text');
const $dots     = document.getElementById('loading-dots');
const $counter  = document.getElementById('step-counter');
const $progress = document.getElementById('progress-fill');

/* ── Auto-resize textarea ────────────────────────────────────────────────── */
$input.addEventListener('input', () => {
    $input.style.height = 'auto';
    $input.style.height = Math.min($input.scrollHeight, 120) + 'px';
});

/* ── UI helpers ──────────────────────────────────────────────────────────── */
function updateProgress(s) {
    const pct = total > 0 ? Math.min(100, Math.round((s / total) * 100)) : 0;
    $progress.style.width = pct + '%';
    if (s < total) {
        $counter.textContent = `Pergunta ${s + 1} de ${total}`;
    } else {
        $counter.textContent = 'Concluindo...';
    }
}

function setLoading(on) {
    $send.classList.toggle('loading', on);
    $send.disabled  = on;
    $input.disabled = on;
}

function showDots(on) {
    $dots.classList.toggle('visible', on);
    $qText.style.display = on ? 'none' : '';
}

/**
 * Display a new question with a slide-in animation.
 * @param {string} text  - Question text to display
 * @param {string} label - Badge label (e.g. "Pergunta 2")
 */
function revealQuestion(text, label) {
    return new Promise(resolve => {
        $qLabel.textContent = label;
        $qText.textContent  = text;
        showDots(false);

        // Start off-screen right
        $card.classList.add('enter-right');
        $card.style.opacity   = '0';
        $card.style.transform = 'translateX(48px)';

        // Force reflow so transition fires
        $card.getBoundingClientRect();

        $card.classList.remove('enter-right');
        $card.style.opacity   = '';
        $card.style.transform = '';

        $card.addEventListener('transitionend', () => resolve(), { once: true });

        // Fallback in case transition doesn't fire (e.g. prefers-reduced-motion)
        setTimeout(resolve, 400);
    });
}

/**
 * Slide out the current question to the left.
 */
function exitQuestion() {
    return new Promise(resolve => {
        $card.style.transform = 'translateX(-48px)';
        $card.style.opacity   = '0';
        $card.addEventListener('transitionend', () => resolve(), { once: true });
        setTimeout(resolve, 400);
    });
}

/* ── API: save answer via AJAX ───────────────────────────────────────────── */
async function saveAnswer(questionId, answer, name = null) {
    try {
        await fetch('/r/responder', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                slug:        SURVEY_SLUG,
                question_id: questionId,
                answer,
                name,
            }),
        });
    } catch (err) {
        console.error('[PesquisaIA] Erro ao salvar resposta:', err);
    }
}

/* ── Core flow ───────────────────────────────────────────────────────────── */
async function askQuestion(s) {
    if (s >= total) return;

    const q     = questions[s];
    const label = `Pergunta ${s + 1} de ${total}`;

    await revealQuestion(q.text, label);
    updateProgress(s);

    // Re-focus input after animation for smooth mobile experience
    setTimeout(() => $input.focus(), 50);
    isTransitioning = false;
}

async function askNameQuestion() {
    await revealQuestion(
        'Olá! Obrigado por participar 😊 Antes de começar, qual é o seu nome?',
        'Boas-vindas'
    );
    updateProgress(0);
    setTimeout(() => $input.focus(), 50);
    isTransitioning = false;
}

async function send() {
    if (isTransitioning) return;

    const text = $input.value.trim();
    if (!text) return;

    isTransitioning = true;
    setLoading(true);

    // --- Handle name collection ---
    if (!nameCollected && pendingName) {
        nameCollected = true;
        pendingName   = false;
        await saveAnswer(0, '', text);  // name stored separately

        setLoading(false);
        await exitQuestion();
        showDots(true);

        await new Promise(r => setTimeout(r, 300));
        $input.value  = '';
        $input.style.height = 'auto';

        await askQuestion(step);
        return;
    }

    // --- Handle survey question ---
    if (step < total) {
        await saveAnswer(questions[step].id, text);
        step++;
    }

    setLoading(false);
    $input.value = '';
    $input.style.height = 'auto';

    if (step >= total) {
        // Survey complete
        await exitQuestion();
        $qLabel.textContent = '🎉 Concluído';
        $qText.textContent  = 'Obrigado pelas suas respostas! Isso significa muito para nós.';
        await revealQuestion(
            'Obrigado pelas suas respostas! Isso significa muito para nós. 🎉',
            'Concluído'
        );
        updateProgress(step);

        setTimeout(() => {
            window.location.href = '/r/' + SURVEY_SLUG + '/concluido';
        }, 1800);
    } else {
        await exitQuestion();
        showDots(true);
        await new Promise(r => setTimeout(r, 250));
        await askQuestion(step);
    }
}

/* ── Event listeners ─────────────────────────────────────────────────────── */
$send.addEventListener('click', send);

$input.addEventListener('keydown', (e) => {
    // Enter sends on desktop; Shift+Enter = new line
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        send();
    }
});

/* ── Bootstrap ───────────────────────────────────────────────────────────── */
if (total > 0) {
    updateProgress(step);

    if (!nameCollected) {
        pendingName = true;
        // Small delay for smooth entrance on page load
        setTimeout(askNameQuestion, 400);
    } else {
        setTimeout(() => askQuestion(step), 400);
    }
} else {
    revealQuestion('Esta pesquisa ainda não tem perguntas configuradas.', 'Atenção');
}
</script>
</body>
</html>
