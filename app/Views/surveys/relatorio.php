<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/detalhe?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar à pesquisa
    </a>

    <?php if (!empty($flashError)): ?>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm px-4 py-3 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
        <?= htmlspecialchars($flashError) ?>
    </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Relatório</h1>
            <p class="text-sm text-[#6b7280] mt-1">Análise consolidada — <?= htmlspecialchars($survey['name']) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($survey['status'] === 'encerrada'): ?>
            <form method="POST" action="/pesquisas/relatorio/gerar">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> <?= $report ? 'Regenerar' : 'Gerar com IA' ?>
                </button>
            </form>
            <?php endif; ?>
            <a href="/pesquisas/exportar?id=<?= (int) $survey['id'] ?>&format=pdf"
               class="inline-flex items-center gap-1.5 rounded-lg bg-[#6366f1] px-3 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                <i data-lucide="download" class="w-4 h-4"></i> Exportar PDF
            </a>
            <a href="/pesquisas/exportar?id=<?= (int) $survey['id'] ?>&format=csv"
               class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> CSV
            </a>
        </div>
    </div>

    <?php if ($report): ?>
    <!-- Resumo executivo gerado pela IA -->
    <section class="rounded-xl border border-[#e5e7eb] bg-gradient-to-br from-[#eef2ff]/60 to-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#6366f1]"></i>
            <h2 class="font-semibold text-[#1e1b4b]">Resumo executivo</h2>
            <?php if ($report['generated_at']): ?>
            <span class="ml-auto text-xs text-[#6b7280]">Gerado em <?= date('d/m/Y H:i', strtotime($report['generated_at'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div class="rounded-lg bg-white border border-[#e5e7eb] p-4">
                <div class="text-xs uppercase tracking-wide text-[#6b7280]">Total de respostas</div>
                <div class="mt-1.5 text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= (int) $survey['response_count'] ?></div>
            </div>
            <div class="rounded-lg bg-white border border-[#e5e7eb] p-4">
                <div class="text-xs uppercase tracking-wide text-[#6b7280]">Perguntas analisadas</div>
                <div class="mt-1.5 text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= count($questions) ?></div>
            </div>
        </div>
        <p class="text-sm text-[#6b7280] leading-relaxed whitespace-pre-line"><?= htmlspecialchars($report['summary']) ?></p>
    </section>

    <!-- Insights identificados pela IA -->
    <?php if (!empty($report['insights'])): ?>
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <h2 class="font-semibold text-[#1e1b4b] mb-4">Insights identificados pela IA</h2>
        <ul class="space-y-3">
            <?php
            $insightIcons = [
                'positive'    => ['icon' => 'trending-up',   'color' => 'text-[#22c55e]', 'bg' => 'bg-[#22c55e]/5'],
                'negative'    => ['icon' => 'trending-down',  'color' => 'text-[#ef4444]', 'bg' => 'bg-[#ef4444]/5'],
                'neutral'     => ['icon' => 'minus-circle',   'color' => 'text-[#6b7280]', 'bg' => 'bg-[#f3f4f6]'],
                'opportunity' => ['icon' => 'lightbulb',      'color' => 'text-[#6366f1]', 'bg' => 'bg-[#eef2ff]'],
            ];
            foreach ($report['insights'] as $insight):
                $type  = $insight['type'] ?? 'neutral';
                $style = $insightIcons[$type] ?? $insightIcons['neutral'];
            ?>
            <li class="flex items-start gap-3 p-4 rounded-lg <?= $style['bg'] ?> border border-[#e5e7eb]">
                <i data-lucide="<?= $style['icon'] ?>" class="w-4 h-4 <?= $style['color'] ?> shrink-0 mt-0.5"></i>
                <div>
                    <div class="font-medium text-sm text-[#1e1b4b]"><?= htmlspecialchars($insight['title'] ?? '') ?></div>
                    <p class="text-xs text-[#6b7280] mt-1"><?= htmlspecialchars($insight['description'] ?? '') ?></p>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <?php else: ?>
    <!-- Estado sem relatório -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-10 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6 text-center">
        <i data-lucide="bar-chart-3" class="w-12 h-12 text-[#6366f1] mx-auto mb-4 opacity-40"></i>
        <h2 class="font-semibold text-[#1e1b4b] mb-2">Relatório ainda não gerado</h2>
        <?php if ($survey['status'] === 'encerrada'): ?>
        <p class="text-sm text-[#6b7280] mb-6">Clique em "Gerar com IA" para analisar as <?= (int) $survey['response_count'] ?> respostas coletadas.</p>
        <form method="POST" action="/pesquisas/relatorio/gerar" class="inline-block">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-5 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Gerar relatório com IA
            </button>
        </form>
        <?php else: ?>
        <p class="text-sm text-[#6b7280]">O relatório só pode ser gerado para pesquisas <strong>encerradas</strong>.</p>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
