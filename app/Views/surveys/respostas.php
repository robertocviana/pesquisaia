<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/detalhe?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar à pesquisa
    </a>

    <?php if (isset($flashError) && $flashError): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200 flex items-start gap-2.5 shadow-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <div><?= htmlspecialchars($flashError) ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($flashSuccess) && $flashSuccess): ?>
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-200 flex items-start gap-2.5 shadow-sm">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <div><?= htmlspecialchars($flashSuccess) ?></div>
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Respostas</h1>
            <p class="text-sm text-[#6b7280] mt-1"><?= count($respondents) ?> respostas em "<?= htmlspecialchars($survey['name']) ?>"</p>
        </div>
        
        <?php if ($survey['status'] === 'ativa'): ?>
        <div>
            <form action="/pesquisas/respostas/gerar" method="POST" class="flex flex-wrap items-center gap-2 bg-white border border-[#e5e7eb] rounded-lg p-2 shadow-sm">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
                
                <span class="text-xs text-[#6b7280] font-medium pl-1.5 hidden sm:inline">Simular:</span>
                <select name="count" class="rounded-md border border-[#e5e7eb] px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-[#6366f1] bg-white">
                    <option value="5">5 respostas</option>
                    <option value="15" selected>15 respostas</option>
                    <option value="30">30 respostas</option>
                    <option value="50">50 respostas</option>
                    <option value="100">100 respostas</option>
                </select>
                
                <select name="strategy" class="rounded-md border border-[#e5e7eb] px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-[#6366f1] bg-white">
                    <option value="hybrid">Híbrida (IA + Local)</option>
                    <option value="local">Local (Custo Zero)</option>
                </select>

                <button type="submit" onclick="const btn = this; setTimeout(function(){ btn.disabled = true; btn.innerHTML = '<span class=\'animate-spin border-2 border-white border-t-transparent rounded-full w-3.5 h-3.5 mr-1.5 inline-block\'></span> Gerando...'; }, 10);"
                    class="inline-flex items-center justify-center rounded-md bg-[#6366f1] hover:bg-[#4f46e5] text-white px-3 py-1.5 text-xs font-semibold shadow-sm transition">
                    <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i> Gerar
                </button>
            </form>
        </div>
        <?php endif; ?>
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
                    <th class="text-left px-4 py-2.5 font-medium">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e7eb]" id="responses-body">
                <?php foreach ($respondents as $r):
                    $statusStyle = $r['status'] === 'concluida'
                        ? 'bg-[#22c55e]/10 text-[#22c55e]'
                        : 'bg-[#eab308]/10 text-[#713f12]';
                    $statusLabel = $r['status'] === 'concluida' ? 'concluída' : 'em andamento';
                ?>
                <tr class="hover:bg-[#f3f4f6]/30 transition response-row">
                    <td class="px-4 py-3 font-medium text-[#1e1b4b]"><?= htmlspecialchars($r['name'] ?? 'Anônimo') ?></td>
                    <td class="px-4 py-3 text-[#6b7280]"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full <?= $statusStyle ?> px-2 py-0.5 text-xs font-medium capitalize">
                            <?= $statusLabel ?>
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
                <?php if (empty($respondents)): ?>
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-[#6b7280]">
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
