<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar às pesquisas
    </a>

    <!-- Page Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= htmlspecialchars($survey['name']) ?></h1>
            <p class="text-sm text-[#6b7280] mt-1">Criada em <?= \App\Helpers\DateHelper::format($survey['created_at'], 'd/m/Y') ?></p>
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

    <!-- Barra de Ações -->
    <div class="flex flex-wrap gap-2 mb-8 pb-6 border-b border-[#e5e7eb]">
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
                <i data-lucide="lock" class="w-4 h-4"></i> Encerrar pesquisa
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Cards superiores -->
    <div class="grid lg:grid-cols-3 gap-5 mb-6">
        <!-- Compartilhamento -->
        <div class="lg:col-span-2 rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-[#1e1b4b] mb-1">Compartilhe sua pesquisa</h3>
                    <p class="text-sm text-[#6b7280] mb-4">Envie o link ou QR code para seus respondentes.</p>
                </div>
                <button type="button" onclick="openFullScreen()" title="Exibir em Tela Cheia"
                    class="inline-flex items-center justify-center p-2 rounded-lg border border-[#e5e7eb] bg-white text-[#6b7280] hover:text-[#6366f1] hover:bg-[#6366f1]/5 hover:border-[#6366f1]/20 transition-all duration-200 shadow-sm shrink-0">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-[#f3f4f6]/40 p-2">
                <input id="survey-link" readonly value="<?= htmlspecialchars($link) ?>"
                    class="flex-1 bg-transparent text-sm px-2 focus:outline-none text-[#1e1b4b]">
                <button id="copy-btn" onclick="copyLink()"
                    class="inline-flex items-center gap-1.5 rounded-md bg-[#6366f1] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90 transition">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar
                </button>
            </div>

            <div class="mt-6 flex items-center gap-4">
                <div class="w-32 h-32 rounded-lg border border-[#e5e7eb] bg-[#fafafa] flex items-center justify-center p-1.5 overflow-hidden shrink-0">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=112x112&data=<?= urlencode($link) ?>" alt="QR Code" class="w-full h-full object-contain">
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

    <!-- Informações da Pesquisa -->
    <div class="grid lg:grid-cols-3 gap-5 mb-6">
        <!-- Planejamento (Objetivo, Público-alvo, Prazo) -->
        <div class="lg:col-span-1 rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-4">Planejamento</h3>
            <div class="space-y-4">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-[#6b7280]">Objetivo</span>
                    <p class="text-sm text-[#1e1b4b] mt-1 whitespace-pre-line"><?= htmlspecialchars($survey['objective']) ?: '<em>Não informado</em>' ?></p>
                </div>
                <hr class="border-[#e5e7eb]">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-[#6b7280]">Público-alvo</span>
                    <p class="text-sm text-[#1e1b4b] mt-1"><?= htmlspecialchars($survey['audience']) ?: '<em>Não informado</em>' ?></p>
                </div>
                <hr class="border-[#e5e7eb]">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-[#6b7280]">Prazo Final</span>
                    <p class="text-sm text-[#1e1b4b] mt-1">
                        <?= $survey['deadline_at'] ? \App\Helpers\DateHelper::format($survey['deadline_at'], 'd/m/Y H:i') : '<em>Sem prazo definido</em>' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Perguntas Definidas -->
        <div class="lg:col-span-2 rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-4">Perguntas (<?= count($questions) ?>)</h3>
            <?php if (empty($questions)): ?>
                <p class="text-sm text-[#6b7280] italic">Nenhuma pergunta foi cadastrada nesta pesquisa.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($questions as $index => $q): ?>
                        <li class="flex items-start gap-3 rounded-lg border border-[#e5e7eb] p-3.5 bg-[#fafafa]/50">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#6366f1]/10 text-[#6366f1] text-xs font-semibold shrink-0 mt-0.5">
                                <?= $index + 1 ?>
                            </span>
                            <span class="text-sm text-[#1e1b4b] font-medium leading-relaxed"><?= htmlspecialchars($q['text']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>


</div>

<!-- Modal Tela Cheia QR Code (Overlay) -->
<div id="fullscreen-qr-modal" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center p-4 sm:p-6 bg-[#0b0f19]/95 backdrop-blur-xl transition-all duration-300 opacity-0 pointer-events-none">
    <!-- Close button -->
    <button onclick="closeFullScreen()" 
        class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/50 hover:text-white hover:bg-white/10 p-2.5 rounded-full transition-all duration-300 hover:rotate-90 hover:scale-110"
        aria-label="Fechar">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>

    <!-- Modal Content Card -->
    <div id="fullscreen-qr-card" class="max-w-md w-full bg-[#111827] border border-gray-800 rounded-3xl p-8 shadow-[0_0_50px_rgba(99,102,241,0.2)] flex flex-col items-center text-center relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300 ease-out">
        <!-- Glow effect inside -->
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#6366f1]/20 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#a855f7]/10 rounded-full blur-[80px] pointer-events-none"></div>

        <!-- Survey Label/Badge -->
        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#6366f1]/10 border border-[#6366f1]/20 px-3 py-1 text-xs font-semibold text-[#818cf8] mb-4">
            <span class="w-2 h-2 rounded-full bg-[#6366f1] animate-pulse"></span>
            Compartilhar Pesquisa
        </span>

        <!-- Title -->
        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight mb-2 max-w-xs sm:max-w-sm">
            <?= htmlspecialchars($survey['name']) ?>
        </h2>
        <p class="text-sm text-gray-400 mb-8 max-w-xs leading-relaxed">
            Aponte a câmera do seu celular para o QR Code abaixo para responder
        </p>

        <!-- QR Code Frame with interactive hover/glow -->
        <div class="w-64 h-64 sm:w-72 sm:h-72 bg-white rounded-2xl flex items-center justify-center p-5 shadow-[0_12px_45px_rgba(0,0,0,0.6)] hover:scale-[1.03] transition-transform duration-300 border border-white/10 relative group mb-8">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?= urlencode($link) ?>" alt="QR Code" class="w-full h-full object-contain">
        </div>

        <!-- URL sharing field -->
        <div class="w-full flex items-center gap-2 rounded-xl border border-gray-800 bg-gray-900/50 p-2 max-w-sm">
            <input id="survey-link-modal" readonly value="<?= htmlspecialchars($link) ?>"
                class="flex-1 bg-transparent text-sm px-2 focus:outline-none text-gray-300 overflow-ellipsis border-none">
            <button id="copy-btn-modal" onclick="copyLinkModal()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-[#6366f1] px-4 py-2 text-xs font-semibold text-white hover:bg-[#5053e3] active:scale-95 transition-all">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar
            </button>
        </div>
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

function openFullScreen() {
    const modal = document.getElementById('fullscreen-qr-modal');
    const card = document.getElementById('fullscreen-qr-card');
    
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
    
    document.body.style.overflow = 'hidden';
}

function closeFullScreen() {
    const modal = document.getElementById('fullscreen-qr-modal');
    const card = document.getElementById('fullscreen-qr-card');
    
    modal.classList.remove('opacity-100', 'pointer-events-auto');
    modal.classList.add('opacity-0', 'pointer-events-none');
    
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    
    document.body.style.overflow = '';
}

function copyLinkModal() {
    navigator.clipboard.writeText(document.getElementById('survey-link-modal').value);
    const btn = document.getElementById('copy-btn-modal');
    btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i> Copiado';
    lucide.createIcons();
    setTimeout(() => { 
        btn.innerHTML = '<i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar'; 
        lucide.createIcons(); 
    }, 1500);
}

// Close on Escape or click outside
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullScreen();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('fullscreen-qr-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFullScreen();
            }
        });
    }
});
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
