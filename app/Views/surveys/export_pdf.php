<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Exportação — <?= htmlspecialchars($survey['name']) ?> — PesquisaIA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1e1b4b; font-size: 13px; line-height: 1.6; }
        header { border-bottom: 2px solid #6366f1; padding-bottom: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; }
        .logo { font-size: 18px; font-weight: 700; color: #6366f1; }
        h1 { font-size: 22px; font-weight: 600; color: #1e1b4b; }
        .meta { color: #6b7280; font-size: 12px; margin-top: 4px; }
        section { margin-bottom: 24px; }
        h2 { font-size: 14px; font-weight: 600; color: #4338ca; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 12px; }
        .qa { margin-bottom: 12px; padding: 10px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #6366f1; }
        .question { font-weight: 600; font-size: 12px; color: #4338ca; margin-bottom: 4px; }
        .answer { font-size: 12px; color: #374151; }
        .respondent { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 16px; page-break-inside: avoid; }
        .rname { font-weight: 600; margin-bottom: 8px; font-size: 13px; }
        .rdate { color: #6b7280; font-size: 11px; float: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 500; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .no-print-btn { position: fixed; top: 20px; right: 20px; background: #6366f1; color: white; border: none; border-radius: 8px; padding: 10px 20px; font-size: 14px; cursor: pointer; font-family: inherit; }
        @media print {
            .no-print-btn { display: none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <button class="no-print-btn" onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>

    <header>
        <div>
            <div class="logo">✦ PesquisaIA</div>
            <h1><?= htmlspecialchars($survey['name']) ?></h1>
            <div class="meta">
                Objetivo: <?= htmlspecialchars($survey['objective']) ?> &nbsp;|&nbsp;
                Público: <?= htmlspecialchars($survey['audience'] ?? 'Não definido') ?> &nbsp;|&nbsp;
                <?= $survey['response_count'] ?> respostas
            </div>
        </div>
        <div class="meta" style="text-align:right">
            Gerado em <?= date('d/m/Y H:i') ?><br>
            Status: <?= ucfirst($survey['status']) ?>
        </div>
    </header>

    <?php foreach ($respondents as $rsp):
        $rAnswers = $indexed[$rsp['id']] ?? [];
        if (empty($rAnswers)) continue;
    ?>
    <div class="respondent">
        <div class="rdate"><?= date('d/m/Y', strtotime($rsp['created_at'])) ?></div>
        <div class="rname">
            <?= htmlspecialchars($rsp['name'] ?? 'Respondente #' . $rsp['id']) ?>
            <span class="badge <?= $rsp['status'] === 'concluida' ? 'badge-green' : 'badge-yellow' ?>">
                <?= $rsp['status'] === 'concluida' ? 'Concluída' : 'Em andamento' ?>
            </span>
        </div>
        <?php foreach ($questions as $q): ?>
            <?php if (!empty($rAnswers[$q['id']])): ?>
            <div class="qa">
                <div class="question"><?= $q['order_index'] + 1 ?>. <?= htmlspecialchars($q['text']) ?></div>
                <div class="answer"><?= htmlspecialchars($rAnswers[$q['id']]) ?></div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if (empty($respondents)): ?>
    <p style="text-align:center;color:#6b7280;padding:40px 0">Nenhuma resposta registrada ainda.</p>
    <?php endif; ?>
</body>
</html>
