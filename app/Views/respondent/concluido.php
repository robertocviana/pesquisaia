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
      Muito obrigado por compartilhar sua opinião. Suas respostas foram enviadas com sucesso! Você já pode fechar esta janela.
    </p>

  </div>
</div>

</body>
</html>
