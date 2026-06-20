<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-6xl mx-auto p-6 sm:p-10">
    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Olá, Ana 👋</h1>
            <p class="text-sm text-[#6b7280] mt-1">Aqui está um resumo das suas pesquisas.</p>
        </div>
        <a href="/pesquisas/nova?new=1"
           class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-4 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Nova pesquisa
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php
        $statItems = [
            ['label' => 'Total de pesquisas',   'value' => $stats['total'],      'icon' => 'file-text'],
            ['label' => 'Pesquisas ativas',      'value' => $stats['ativas'],     'icon' => 'check-circle-2'],
            ['label' => 'Encerradas',            'value' => $stats['encerradas'], 'icon' => 'x-circle'],
            ['label' => 'Respostas recebidas',   'value' => $stats['respostas'],  'icon' => 'messages-square'],
        ];
        foreach ($statItems as $stat): ?>
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04),_0_1px_3px_0_rgb(15_23_42_/_0.06)]">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-wide text-[#6b7280]"><?= $stat['label'] ?></span>
                <i data-lucide="<?= $stat['icon'] ?>" class="w-4 h-4 text-[#6b7280]"></i>
            </div>
            <div class="mt-3 text-3xl font-semibold tracking-tight text-[#1e1b4b]"><?= $stat['value'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Surveys -->
    <div class="rounded-xl border border-[#e5e7eb] bg-white shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04),_0_1px_3px_0_rgb(15_23_42_/_0.06)] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#e5e7eb] flex items-center justify-between">
            <h2 class="font-semibold text-[#1e1b4b]">Pesquisas recentes</h2>
            <a href="/pesquisas" class="text-sm text-[#6366f1] hover:underline inline-flex items-center gap-1">
                Ver todas <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <ul class="divide-y divide-[#e5e7eb]">
            <?php foreach ($recent as $s): ?>
            <li class="px-5 py-4 flex items-center gap-4 hover:bg-[#f3f4f6]/40 transition">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5">
                        <span class="font-medium truncate text-[#1e1b4b]"><?= htmlspecialchars($s['name']) ?></span>
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
                        $style = $badgeStyles[$s['status']] ?? $badgeStyles['rascunho'];
                        $dot   = $dotStyles[$s['status']] ?? $dotStyles['rascunho'];
                        ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize <?= $style ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $dot ?>"></span>
                            <?= $s['status'] ?>
                        </span>
                    </div>
                    <div class="text-xs text-[#6b7280] mt-1">
                        <?= count($s['responses']) ?> respostas · criada em <?= \App\Helpers\MockData::formatDate($s['createdAt']) ?>
                    </div>
                </div>
                <a href="/pesquisas/detalhe?id=<?= $s['id'] ?>" class="text-sm text-[#6366f1] hover:underline">
                    Visualizar
                </a>
            </li>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
            <li class="px-5 py-10 text-center text-sm text-[#6b7280]">
                Você ainda não criou nenhuma pesquisa.
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
