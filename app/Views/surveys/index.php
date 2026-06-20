<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-6xl mx-auto p-6 sm:p-10">
    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Minhas pesquisas</h1>
            <p class="text-sm text-[#6b7280] mt-1">Gerencie todas as suas pesquisas em um só lugar.</p>
        </div>
        <a href="/pesquisas/nova?new=1"
           class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-4 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Nova pesquisa
        </a>
    </div>

    <!-- Filtros -->
    <?php
    $filterParam = $_GET['status'] ?? 'todas';
    $filters = [
        ['key' => 'todas',     'label' => 'Todas'],
        ['key' => 'ativa',     'label' => 'Ativas'],
        ['key' => 'encerrada', 'label' => 'Encerradas'],
        ['key' => 'rascunho',  'label' => 'Rascunhos'],
    ];
    $filtered = $filterParam === 'todas'
        ? $surveys
        : array_filter($surveys, fn($s) => $s['status'] === $filterParam);
    ?>
    <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach ($filters as $f): ?>
        <a href="/pesquisas?status=<?= $f['key'] ?>"
           class="rounded-full px-4 py-1.5 text-sm transition <?= $filterParam === $f['key'] ? 'bg-[#6366f1] text-white' : 'bg-white border border-[#e5e7eb] text-[#6b7280] hover:text-[#1e1b4b]' ?>">
            <?= $f['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Lista -->
    <div class="grid gap-3">
        <?php foreach ($filtered as $s):
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
        ?>
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] hover:shadow-[0_4px_6px_-1px_rgb(15_23_42_/_0.06)] transition">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="font-semibold truncate text-[#1e1b4b]"><?= htmlspecialchars($s['name']) ?></h3>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize <?= $badgeStyles[$s['status']] ?? '' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $dotStyles[$s['status']] ?? '' ?>"></span>
                            <?= $s['status'] ?>
                        </span>
                    </div>
                    <p class="text-sm text-[#6b7280] mt-1">
                        <?= (int) $s['response_count'] ?> respostas · criada em <?= \App\Helpers\MockData::formatDate($s['created_at']) ?>
                    </p>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="/pesquisas/detalhe?id=<?= $s['id'] ?>"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-1.5 text-sm hover:bg-[#f3f4f6] transition">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Abrir
                    </a>
                    <button title="Duplicar" class="p-2 rounded-lg border border-[#e5e7eb] bg-white hover:bg-[#f3f4f6] transition">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                    <button title="Arquivar" class="p-2 rounded-lg border border-[#e5e7eb] bg-white hover:bg-[#f3f4f6] transition">
                        <i data-lucide="archive" class="w-4 h-4"></i>
                    </button>
                    <button title="Excluir" class="p-2 rounded-lg border border-[#e5e7eb] bg-white hover:bg-[#f3f4f6] transition">
                        <i data-lucide="trash-2" class="w-4 h-4 text-[#ef4444]"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($filtered)): ?>
        <div class="rounded-xl border border-dashed border-[#e5e7eb] p-12 text-center text-sm text-[#6b7280]">
            Nenhuma pesquisa nesta categoria.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
