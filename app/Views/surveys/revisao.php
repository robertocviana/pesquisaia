<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-3xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/nova" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao chat
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Revisão da pesquisa</h1>
            <p class="text-sm text-[#6b7280] mt-1">Ajuste as informações antes de publicar.</p>
        </div>
    </div>

    <!-- Dados gerais -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <h2 class="font-semibold text-[#1e1b4b] mb-4">Dados gerais</h2>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Nome da pesquisa</label>
                <input type="text" value="<?= htmlspecialchars($survey['name']) ?>"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Objetivo</label>
                <textarea rows="2"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"><?= htmlspecialchars($survey['objective']) ?></textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Público-alvo</label>
                <input type="text" value="<?= htmlspecialchars($survey['audience']) ?>"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
        </div>
    </section>

    <!-- Perguntas -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-[#1e1b4b]">Perguntas (<?= count($survey['questions']) ?>)</h2>
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-1.5 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Adicionar pergunta
            </button>
        </div>
        <ul class="space-y-2" id="questions-list">
            <?php foreach ($survey['questions'] as $i => $q): ?>
            <li class="flex items-start gap-2 rounded-lg border border-[#e5e7eb] p-3 group">
                <i data-lucide="grip-vertical" class="w-4 h-4 text-[#6b7280] mt-0.5 cursor-grab shrink-0"></i>
                <span class="text-xs text-[#6b7280] mt-0.5 w-6 shrink-0"><?= $i + 1 ?>.</span>
                <span class="flex-1 text-sm text-[#1e1b4b]"><?= htmlspecialchars($q['text']) ?></span>
                <button class="p-1 rounded hover:bg-[#f3f4f6] opacity-0 group-hover:opacity-100 transition" title="Editar">
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                </button>
                <button class="p-1 rounded hover:bg-[#f3f4f6] opacity-0 group-hover:opacity-100 transition" title="Excluir">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-[#ef4444]"></i>
                </button>
            </li>
            <?php endforeach; ?>
            <?php if (empty($survey['questions'])): ?>
            <li class="text-sm text-center text-[#6b7280] py-6">Nenhuma pergunta ainda. Adicione acima.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- Ações -->
    <div class="flex justify-end gap-2">
        <a href="/pesquisas" class="rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm hover:bg-[#f3f4f6] transition">
            Voltar
        </a>
        <form method="POST" action="/pesquisas/publicar">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">
            <button type="submit"
               class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-4 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                <i data-lucide="rocket" class="w-4 h-4"></i> Publicar pesquisa
            </button>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
