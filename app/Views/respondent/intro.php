<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Participar — <?= htmlspecialchars($survey['name'] ?? 'Pesquisa') ?></title>
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
      --card-foreground:#1e1f3b;
      --primary:        #5b5ef4;
      --primary-foreground: #f8f9ff;
      --primary-soft:   #eeeeff;
      --muted:          #f4f5f9;
      --muted-foreground: #767fa0;
      --accent:         #eeeeff;
      --accent-foreground: #3f42c4;
      --border:         #e6e7f0;
      --input:          #e6e7f0;
      --ring:           #5b5ef4;
      --success:        #22c55e;
      --success-foreground: #fff;
      --shadow-soft:    0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06);
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
      background: linear-gradient(135deg, color-mix(in srgb, var(--primary-soft) 40%, transparent) 0%, var(--background) 50%, color-mix(in srgb, var(--accent) 40%, transparent) 100%);
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
    }

    /* ─── Header / Brand ─────────────────────────────────────────── */
    .brand {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 2rem;
    }
    .brand-icon {
      width: 2.25rem;
      height: 2.25rem;
      border-radius: calc(var(--radius) - 0.125rem);
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .brand-icon svg { color: var(--primary-foreground); }
    .brand-name { font-weight: 600; font-size: 0.9375rem; }

    /* ─── Content ────────────────────────────────────────────────── */
    h1 {
      font-size: 1.5rem;
      font-weight: 600;
      letter-spacing: -0.02em;
      color: var(--foreground);
    }
    .description {
      color: var(--muted-foreground);
      margin-top: 0.75rem;
      line-height: 1.65;
      font-size: 0.9375rem;
    }

    /* ─── Info chips ─────────────────────────────────────────────── */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }
    .info-chip {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 0.875rem;
      color: var(--foreground);
    }
    .info-chip svg { color: var(--primary); flex-shrink: 0; }

    /* ─── CTA Button ─────────────────────────────────────────────── */
    .btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      margin-top: 2rem;
      padding: 0.875rem 1.5rem;
      background: var(--primary);
      color: var(--primary-foreground);
      font-size: 0.875rem;
      font-weight: 500;
      font-family: inherit;
      border: none;
      border-radius: var(--radius);
      cursor: pointer;
      text-decoration: none;
      box-shadow: var(--shadow-pop);
      transition: opacity 0.15s ease;
    }
    .btn-primary:hover { opacity: 0.9; }
  </style>
</head>
<body>
  <div class="page">
    <div class="card">

      <!-- Brand -->
      <div class="brand">
        <div class="brand-icon">
          <!-- Sparkles icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
            <path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>
          </svg>
        </div>
        <span class="brand-name">PesquisaIA</span>
      </div>

      <!-- Title — PHP: echo $survey->name -->
      <h1 id="survey-title"><?= htmlspecialchars($survey['name']) ?></h1>

      <p class="description">
        Olá! Estamos coletando opiniões para melhorar a nossa experiência. A conversa é informal e
        leva poucos minutos. Suas respostas são confidenciais.
      </p>

      <!-- Info chips -->
      <div class="info-grid">
        <div class="info-chip">
          <!-- Clock icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          ~3 min
        </div>
        <div class="info-chip">
          <!-- ShieldCheck icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
            <path d="m9 12 2 2 4-4"/>
          </svg>
          Confidencial
        </div>
      </div>

      <!-- CTA — PHP: href="/r/<?= $survey['public_slug'] ?>/chat" -->
      <a href="/r/<?= htmlspecialchars($survey['public_slug'] ?? '') ?>/chat" class="btn-primary" id="start-btn">
        Iniciar pesquisa
        <!-- ArrowRight icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
        </svg>
      </a>

    </div>
  </div>
</body>
</html>
