<?php
$slug  = $_GET['slug'] ?? '';
$total = count($questions);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($survey['name']) ?> — Pesquisa</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* ─── Design Tokens ─────────────────────────────────────────── */
    :root {
      --radius: 0.75rem;
      --background:     #f8f9ff;
      --foreground:     #1e1f3b;
      --card:           #ffffff;
      --primary:        #5b5ef4;
      --primary-foreground: #f8f9ff;
      --primary-soft:   #eeeeff;
      --muted:          #f4f5f9;
      --muted-foreground: #767fa0;
      --border:         #e6e7f0;
      --input:          #e6e7f0;
      --ring:           #5b5ef4;
      --shadow-elevated:0 4px 6px -1px rgb(15 23 42 / 0.06), 0 10px 20px -10px rgb(15 23 42 / 0.10);
      --shadow-pop:     0 20px 40px -20px rgb(91 94 244 / 0.35);
    }

    /* ─── Reset ─────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      min-height: 100vh;
      font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
      background-color: var(--muted);
      color: var(--foreground);
      -webkit-font-smoothing: antialiased;
    }

    /* ─── Page Shell ─────────────────────────────────────────────── */
    .shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background-color: color-mix(in srgb, var(--muted) 30%, transparent);
    }

    /* ─── Top Header ─────────────────────────────────────────────── */
    header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .header-inner {
      max-width: 42rem;
      margin: 0 auto;
      padding: 0.75rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .brand-icon {
      width: 2.25rem;
      height: 2.25rem;
      flex-shrink: 0;
      border-radius: calc(var(--radius) + 0.125rem);
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .brand-icon svg { color: var(--primary-foreground); }
    .survey-name {
      font-weight: 600;
      font-size: 0.875rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      flex: 1;
      min-width: 0;
    }

    /* ─── Progress Bar ───────────────────────────────────────────── */
    .progress-track {
      height: 0.25rem;
      background: var(--border);
    }
    .progress-fill {
      height: 100%;
      background: var(--primary);
      transition: width 0.4s ease;
    }

    /* ─── Main Content ───────────────────────────────────────────── */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .content {
      flex: 1;
      max-width: 42rem;
      width: 100%;
      margin: 0 auto;
      padding: 2rem 1.5rem 3rem;
      display: flex;
      flex-direction: column;
    }
    @media (min-width: 640px) {
      .content { padding: 3rem 1.5rem 4rem; }
    }

    /* ─── Question card transitions ──────────────────────────────── */
    #question-card {
      will-change: transform, opacity;
      transition: transform 350ms cubic-bezier(0.4, 0, 0.2, 1),
                  opacity  350ms cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      flex: 1;
      width: 100%;
    }

    #question-card.exit-left {
      transform: translateX(-48px);
      opacity: 0;
    }

    #question-card.enter-right {
      transform: translateX(48px);
      opacity: 0;
    }

    /* ─── Question counter badge ─────────────────────────────────── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      align-self: flex-start;
      border-radius: 999px;
      background: color-mix(in srgb, var(--primary) 10%, transparent);
      padding: 0.25rem 0.75rem;
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1.25rem;
      letter-spacing: 0.025em;
    }
    .badge svg { color: var(--primary); }

    /* ─── Question text ──────────────────────────────────────────── */
    .question-text {
      font-size: 1.5rem;
      font-weight: 700;
      line-height: 1.35;
      color: var(--foreground);
      margin-bottom: 1.5rem;
    }
    @media (min-width: 640px) {
      .question-text { font-size: 1.875rem; margin-bottom: 2rem; }
    }

    /* ─── Textarea ───────────────────────────────────────────────── */
    textarea {
      width: 100%;
      resize: none;
      border-radius: 1rem;
      border: 1px solid var(--input);
      background: var(--card);
      padding: 0.875rem 1rem;
      font-size: 1rem;
      line-height: 1.65;
      font-family: inherit;
      color: var(--foreground);
      box-shadow: 0 1px 2px 0 rgb(15 23 42 / 0.04);
      min-height: 10rem;
      outline: none;
      transition: box-shadow 0.15s, border-color 0.15s;
    }
    textarea:focus {
      border-color: var(--ring);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 18%, transparent);
    }
    textarea::placeholder { color: var(--muted-foreground); }

    .tip {
      font-size: 0.75rem;
      color: var(--muted-foreground);
      margin-top: 0.5rem;
    }
    @media (max-width: 639px) { .tip { display: none; } }

    /* ─── Bottom Nav ─────────────────────────────────────────────── */
    .bottom-bar {
      position: sticky;
      bottom: 0;
      border-top: 1px solid var(--border);
      background: color-mix(in srgb, var(--card) 95%, transparent);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }
    .bottom-bar-inner {
      max-width: 42rem;
      margin: 0 auto;
      padding: 0.75rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: flex-end; /* Align Next button to the right */
      gap: 0.75rem;
    }

    /* ─── Buttons ────────────────────────────────────────────────── */
    .btn-next {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-family: inherit;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      background: var(--primary);
      color: var(--primary-foreground);
      padding: 0.625rem 1.25rem;
      border-radius: calc(var(--radius) + 0.125rem);
      box-shadow: var(--shadow-pop);
      transition: opacity 0.15s;
    }
    .btn-next:hover:not(:disabled) { opacity: 0.9; }
    .btn-next:disabled { opacity: 0.4; cursor: not-allowed; }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .animate-spin {
      animation: spin 1s linear infinite;
    }

    /* Idle dots while transitioning */
    #loading-dots {
      display: none;
      gap: 6px;
      justify-content: center;
      align-items: center;
      margin: 2rem 0;
    }

    #loading-dots.visible {
      display: flex;
    }

    #loading-dots span {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--primary);
      animation: bounce-dot 1.2s infinite ease-in-out;
    }

    #loading-dots span:nth-child(2) { animation-delay: 0.2s; }
    #loading-dots span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce-dot {
      0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
      40% { transform: scale(1); opacity: 1; }
    }

    /* ── Respect prefers-reduced-motion ──────────────────────────────────── */
    @media (prefers-reduced-motion: reduce) {
      #question-card,
      .progress-fill {
        transition: none !important;
      }
      #loading-dots span {
        animation: none !important;
        opacity: 0.6;
      }
    }
  </style>
</head>
<body>

<div class="shell">

  <!-- ── Header ───────────────────────────────────────────────────── -->
  <header>
    <div class="header-inner">
      <div class="brand-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          <path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>
        </svg>
      </div>
      <div class="survey-name" id="survey-name"><?= htmlspecialchars($survey['name']) ?></div>
    </div>
    <div class="progress-track">
      <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
    </div>
  </header>

  <!-- ── Main ─────────────────────────────────────────────────────── -->
  <main>
    <div class="content">
      <div id="question-card">
        <!-- Question badge -->
        <div class="badge" id="question-badge">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
          </svg>
          <span id="badge-text">Carregando...</span>
        </div>

        <!-- Question text -->
        <h1 class="question-text" id="question-text">Carregando pergunta...</h1>

        <!-- Answer textarea -->
        <textarea
          id="answer-input"
          placeholder="Digite sua resposta aqui..."
          autocomplete="off"
        ></textarea>
        <p class="tip">Dica: pressione Ctrl + Enter para avançar</p>
      </div>

      <div id="loading-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </main>

  <!-- ── Bottom Nav ────────────────────────────────────────────────── -->
  <div class="bottom-bar">
    <div class="bottom-bar-inner">
      <button class="btn-next" id="btn-next" onclick="next()" disabled>
        <span id="btn-next-label">Próxima</span>
        <!-- ArrowRight icon -->
        <svg id="icon-next" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
        </svg>
        <!-- Check icon -->
        <svg id="icon-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
          <path d="M20 6 9 17l-5-5"/>
        </svg>
        <!-- Spinner/Loading icon -->
        <svg id="icon-loading" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" style="opacity: 0.25;"></circle>
          <path d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="3"></path>
        </svg>
      </button>
    </div>
  </div>

</div>

<script>
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
let step            = answered;                                    // current question index
let nameCollected   = <?= ($respondent['name'] ? 'true' : 'false') ?>;
let pendingName     = false;
let isTransitioning = false;

/* ── DOM refs ────────────────────────────────────────────────────────────── */
const $input      = document.getElementById('answer-input');
const $next       = document.getElementById('btn-next');
const $nextLabel  = document.getElementById('btn-next-label');
const $iconNext   = document.getElementById('icon-next');
const $iconChk    = document.getElementById('icon-check');
const $iconLoad   = document.getElementById('icon-loading');
const $card       = document.getElementById('question-card');
const $qText      = document.getElementById('question-text');
const $badgeText  = document.getElementById('badge-text');
const $dots       = document.getElementById('loading-dots');
const $progress   = document.getElementById('progress-fill');

/* ── Auto-resize textarea (optional but nice) ────────────────────────────── */
$input.addEventListener('input', onInput);
$input.addEventListener('keydown', function(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
    e.preventDefault();
    next();
  }
});

/* ── UI helpers ──────────────────────────────────────────────────────────── */
function updateProgress(s) {
    const pct = total > 0 ? Math.min(100, Math.round((s / total) * 100)) : 0;
    $progress.style.width = pct + '%';
}

function setLoading(on) {
    $next.disabled  = on;
    $input.disabled = on;
    if (on) {
        $iconNext.style.display = 'none';
        $iconChk.style.display  = 'none';
        $iconLoad.style.display = '';
    } else {
        $iconLoad.style.display = 'none';
        renderButtons();
    }
}

function renderButtons() {
    const isLast = step === total - 1;
    if (isLast) {
        $nextLabel.textContent = 'Finalizar';
        $iconNext.style.display = 'none';
        $iconChk.style.display  = '';
    } else {
        $nextLabel.textContent = 'Próxima';
        $iconNext.style.display = '';
        $iconChk.style.display  = 'none';
    }
}

function showDots(on) {
    $dots.classList.toggle('visible', on);
    $card.style.display = on ? 'none' : 'flex';
}

/**
 * Display a new question with a slide-in animation.
 * @param {string} text  - Question text to display
 * @param {string} label - Badge label (e.g. "Pergunta 2")
 */
function revealQuestion(text, label) {
    return new Promise(resolve => {
        $badgeText.textContent = label;
        $qText.textContent     = text;
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

        // Fallback in case transition doesn't fire
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

function onInput() {
    $next.disabled = !$input.value.trim();
}

/* ── API: save answer via AJAX ───────────────────────────────────────────── */
async function saveAnswer(questionId, answer, name = null) {
    try {
        const response = await fetch('/r/responder', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                slug:        SURVEY_SLUG,
                question_id: questionId,
                answer,
                name,
            }),
        });
        return await response.json();
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
    renderButtons();
    onInput();

    setTimeout(() => $input.focus(), 50);
    isTransitioning = false;
}

async function askNameQuestion() {
    await revealQuestion(
        'Olá! Obrigado por participar 😊 Antes de começar, qual é o seu nome?',
        'Boas-vindas'
    );
    updateProgress(0);
    $nextLabel.textContent = 'Iniciar';
    $iconNext.style.display = '';
    $iconChk.style.display  = 'none';
    onInput();

    setTimeout(() => $input.focus(), 50);
    isTransitioning = false;
}

async function next() {
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
        $input.value = '';
        showDots(false);

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

    if (step >= total) {
        // Survey complete
        await exitQuestion();
        await revealQuestion(
            'Obrigado pelas suas respostas! Isso significa muito para nós. 🎉',
            'Concluído'
        );
        updateProgress(step);

        setTimeout(() => {
            window.location.href = '/r/' + SURVEY_SLUG + '/concluido';
        }, 1500);
    } else {
        await exitQuestion();
        showDots(true);
        await new Promise(r => setTimeout(r, 250));
        showDots(false);
        await askQuestion(step);
    }
}

/* ── Bootstrap ───────────────────────────────────────────────────────────── */
if (total > 0) {
    updateProgress(step);

    if (!nameCollected) {
        pendingName = true;
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
