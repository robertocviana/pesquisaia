<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar às pesquisas
    </a>

    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= htmlspecialchars($survey['name']) ?></h1>
            <p class="text-sm text-[#6b7280] mt-1">Criada em <?= date('d/m/Y', strtotime($survey['created_at'])) ?></p>
        </div>
        <?php
        $badgeStyles = [
            'ativa'     => 'bg-[#22c55e]/10 text-[#22c55e] border-[#22c55e]/20',
            'encerrada' => 'bg-[#f3f4f6] text-[#6b7280] border-[#e5e7eb]',
            'rascunho'  => 'bg-[#eab308]/10 text-[#713f12] border-[#eab308]/20',
        ];
        $dotStyles = [
            'ativa'     => 'bg-[#22c55e]',
            'encerrada' => 'bg-[#6b7280]',
            'rascunho'  => 'bg-[#eab308]',
        ];
        $style = $badgeStyles[$survey['status']] ?? $badgeStyles['rascunho'];
        $dot   = $dotStyles[$survey['status']] ?? $dotStyles['rascunho'];
        ?>
        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize <?= $style ?>">
            <span class="w-1.5 h-1.5 rounded-full <?= $dot ?>"></span>
            <?= $survey['status'] ?>
        </span>
    </div>

    <!-- Cards superiores -->
    <div class="grid lg:grid-cols-3 gap-5 mb-6">
        <!-- Compartilhamento -->
        <div class="lg:col-span-2 rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-1">Compartilhe sua pesquisa</h3>
            <p class="text-sm text-[#6b7280] mb-4">Envie o link ou QR code para seus respondentes.</p>

            <div class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-[#f3f4f6]/40 p-2">
                <input id="survey-link" readonly value="<?= htmlspecialchars($link) ?>"
                    class="flex-1 bg-transparent text-sm px-2 focus:outline-none text-[#1e1b4b]">
                <button id="copy-btn" onclick="copyLink()"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#6366f1] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90 transition">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar
                </button>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <div class="w-32 h-32 rounded-lg border border-[#e5e7eb] bg-[#fafafa] flex items-center justify-center">
                    <i data-lucide="qr-code" class="w-20 h-20 text-[#1e1b4b]" stroke-width="1.2"></i>
                </div>
                <div class="text-sm text-[#6b7280]">
                    <div class="font-medium text-[#1e1b4b] mb-1">QR Code</div>
                    Imprima ou exiba o código em telas para captar respostas presenciais.
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-4">Estatísticas rápidas</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-[#6b7280]">Respostas recebidas</span>
                    <span class="text-lg font-semibold text-[#1e1b4b]"><?= (int) $survey['response_count'] ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-[#6b7280]">Meta definida</span>
                    <span class="text-lg font-semibold text-[#1e1b4b]"><?= $survey['goal_responses'] ?? '—' ?></span>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-[#6b7280]">Progresso</span>
                        <span class="font-medium text-[#1e1b4b]"><?= $progress ?>%</span>
                    </div>
                    <div class="h-2 rounded-full bg-[#f3f4f6] overflow-hidden">
                        <div class="h-full bg-[#6366f1] transition-all" style="width:<?= $progress ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="flex flex-wrap gap-2">
        <a href="/pesquisas/respostas?id=<?= $survey['id'] ?>"
           class="inline-flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm hover:bg-[#f3f4f6] transition">
            <i data-lucide="messages-square" class="w-4 h-4"></i> Ver respostas
        </a>
        <a href="/pesquisas/relatorio?id=<?= $survey['id'] ?>"
           class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-4 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Ver relatório
        </a>
        <?php if ($survey['status'] === 'ativa'): ?>
        <form method="POST" action="/pesquisas/encerrar" class="ml-auto"
              onsubmit="return confirm('Encerrar esta pesquisa? Novos respondentes serão bloqueados.')">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-[#ef4444]/30 text-[#ef4444] bg-white px-4 py-2.5 text-sm hover:bg-[#ef4444]/5 transition">
                <i data-lucide="x-circle" class="w-4 h-4"></i> Encerrar pesquisa
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
lucide.createIcons();
function copyLink() {
    navigator.clipboard.writeText(document.getElementById('survey-link').value);
    const btn = document.getElementById('copy-btn');
    btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i> Copiado';
    lucide.createIcons();
    setTimeout(() => { btn.innerHTML = '<i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar'; lucide.createIcons(); }, 1500);
}
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
