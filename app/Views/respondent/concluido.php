<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Obrigado! — PesquisaIA</title>
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
      --accent:         #eeeeff;
      --border:         #e6e7f0;
      --input:          #e6e7f0;
      --ring:           #5b5ef4;
      --success:        #22c55e;
      --success-bg:     #f0fdf4;
      --shadow-elevated:0 4px 6px -1px rgb(15 23 42 / 0.06), 0 10px 20px -10px rgb(15 23 42 / 0.10);
      --shadow-pop:     0 20px 40px -20px rgb(91 94 244 / 0.35);
    }

    /* ─── Reset ─────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      min-height: 100vh;
      font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
      background-color: var(--background);
      color: var(--foreground);
      -webkit-font-smoothing: antialiased;
    }

    /* ─── Layout ─────────────────────────────────────────────────── */
    .page {
      min-height: 100vh;
      background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary-soft) 40%, transparent) 0%,
        var(--background) 50%,
        color-mix(in srgb, var(--accent) 40%, transparent) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .card {
      width: 100%;
      max-width: 32rem;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: var(--shadow-elevated);
      text-align: center;
    }

    /* ─── Success icon ───────────────────────────────────────────── */
    .icon-wrap {
      width: 4rem;
      height: 4rem;
      margin: 0 auto;
      border-radius: 50%;
      background: var(--success-bg);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon-wrap svg { color: var(--success); }

    h1 {
      font-size: 1.5rem;
      font-weight: 600;
      letter-spacing: -0.02em;
      margin-top: 1.25rem;
    }
    .description {
      color: var(--muted-foreground);
      margin-top: 0.5rem;
      line-height: 1.65;
      font-size: 0.9375rem;
    }

    /* ─── Optional feedback section ──────────────────────────────── */
    .feedback-section {
      margin-top: 2rem;
      text-align: left;
    }
    label {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--foreground);
    }
    textarea {
      margin-top: 0.375rem;
      width: 100%;
      resize: none;
      border-radius: var(--radius);
      border: 1px solid var(--input);
      background: var(--card);
      padding: 0.625rem 0.75rem;
      font-size: 0.875rem;
      font-family: inherit;
      color: var(--foreground);
      outline: none;
      transition: box-shadow 0.15s, border-color 0.15s;
    }
    textarea:focus {
      border-color: var(--ring);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 18%, transparent);
    }
    textarea::placeholder { color: var(--muted-foreground); }

    /* ─── Buttons ────────────────────────────────────────────────── */
    .btn-primary {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      margin-top: 0.75rem;
      padding: 0.625rem 1.25rem;
      background: var(--primary);
      color: var(--primary-foreground);
      font-size: 0.875rem;
      font-weight: 500;
      font-family: inherit;
      border: none;
      border-radius: var(--radius);
      cursor: pointer;
      box-shadow: var(--shadow-pop);
      transition: opacity 0.15s;
    }
    .btn-primary:hover:not(:disabled) { opacity: 0.9; }
    .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

    .btn-secondary {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      margin-top: 0.75rem;
      padding: 0.625rem 1.25rem;
      background: var(--card);
      color: var(--foreground);
      font-size: 0.875rem;
      font-family: inherit;
      border: 1px solid var(--input);
      border-radius: var(--radius);
      cursor: pointer;
      transition: background 0.15s;
    }
    .btn-secondary:hover { background: var(--muted); }

    /* ─── Success toast ──────────────────────────────────────────── */
    .toast-success {
      display: none;
      margin-top: 0.5rem;
      background: var(--success-bg);
      color: var(--success);
      border-radius: var(--radius);
      padding: 0.75rem;
      font-size: 0.875rem;
      align-items: center;
      gap: 0.5rem;
    }
    .toast-success.visible { display: flex; }

    /* ─── Fade-in animation ──────────────────────────────────────── */
    @keyframes fadeSlideUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .card { animation: fadeSlideUp 0.45s ease both; }
  </style>
</head>
<body>

<div class="page">
  <div class="card">

    <!-- Success icon -->
    <div class="icon-wrap">
      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <path d="m9 11 3 3L22 4"/>
      </svg>
    </div>

    <h1>Pesquisa concluída!</h1>
    <p class="description">
      Muito obrigada por compartilhar sua opinião. Suas respostas são essenciais para que possamos
      melhorar continuamente.
    </p>

    <!-- Optional feedback -->
    <div class="feedback-section">
      <label for="extra-feedback">Quer deixar um feedback adicional?</label>

      <!-- Toast success (shown after sending) -->
      <div class="toast-success" id="toast-success">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          <path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>
        </svg>
        Feedback enviado, obrigada!
      </div>

      <!-- Feedback form (hidden after sending) -->
      <div id="feedback-form">
        <textarea
          id="extra-feedback"
          rows="3"
          placeholder="Opcional"
        ></textarea>
        <button class="btn-primary" id="btn-send" onclick="sendFeedback()" disabled>
          Enviar feedback
        </button>
      </div>
    </div>

    <button class="btn-secondary" onclick="window.close()">Encerrar</button>

  </div>
</div>

<script>
  var elTextarea = document.getElementById('extra-feedback');
  var elBtn      = document.getElementById('btn-send');
  var elForm     = document.getElementById('feedback-form');
  var elToast    = document.getElementById('toast-success');

  elTextarea.addEventListener('input', function() {
    elBtn.disabled = !elTextarea.value.trim();
  });

  function sendFeedback() {
    var text = elTextarea.value.trim();
    if (!text) return;

    // Apenas UI local simulada
    elForm.style.display  = 'none';
    elToast.classList.add('visible');
  }
</script>
</body>
</html>
