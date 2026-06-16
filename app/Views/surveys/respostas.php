<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/detalhe?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar à pesquisa
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Respostas</h1>
            <p class="text-sm text-[#6b7280] mt-1"><?= count($survey['responses']) ?> respostas em "<?= htmlspecialchars($survey['name']) ?>"</p>
        </div>
    </div>

    <div class="rounded-xl border border-[#e5e7eb] bg-white shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] overflow-hidden">
        <!-- Toolbar de filtros -->
        <div class="px-4 py-3 border-b border-[#e5e7eb] flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <input id="search-respondent" type="text" placeholder="Buscar respondente..."
                    class="w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i> Data
            </button>
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i> Status
            </button>
        </div>

        <!-- Tabela -->
        <table class="w-full text-sm" id="responses-table">
            <thead class="bg-[#f3f4f6]/40 text-xs uppercase text-[#6b7280]">
                <tr>
                    <th class="text-left px-4 py-2.5 font-medium">Respondente</th>
                    <th class="text-left px-4 py-2.5 font-medium">Data</th>
                    <th class="text-left px-4 py-2.5 font-medium">Tempo</th>
                    <th class="text-left px-4 py-2.5 font-medium">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e7eb]" id="responses-body">
                <?php foreach ($survey['responses'] as $r): ?>
                <tr class="hover:bg-[#f3f4f6]/30 transition response-row">
                    <td class="px-4 py-3 font-medium text-[#1e1b4b]"><?= htmlspecialchars($r['respondent']) ?></td>
                    <td class="px-4 py-3 text-[#6b7280]"><?= \App\Helpers\MockData::formatDate($r['date']) ?></td>
                    <td class="px-4 py-3 text-[#6b7280]"><?= $r['durationMin'] ?> min</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-[#22c55e]/10 text-[#22c55e] px-2 py-0.5 text-xs font-medium capitalize">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="/pesquisas/resposta?id=<?= $survey['id'] ?>&rid=<?= $r['id'] ?>"
                           class="inline-flex items-center gap-1.5 text-[#6366f1] text-sm hover:underline">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Visualizar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($survey['responses'])): ?>
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-[#6b7280]">
                        Nenhuma resposta encontrada ainda.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
lucide.createIcons();
document.getElementById('search-respondent').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.response-row').forEach(row => {
        const name = row.querySelector('td:first-child').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
});
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
