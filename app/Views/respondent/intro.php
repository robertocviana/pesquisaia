<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-[#eef2ff]/40 via-[#fafafa] to-[#eef2ff]/40 flex items-center justify-center p-6">
    <div class="w-full max-w-lg rounded-2xl border border-[#e5e7eb] bg-white p-8 shadow-[0_4px_6px_-1px_rgb(15_23_42_/_0.06),_0_10px_20px_-10px_rgb(15_23_42_/_0.10)]">
        <div class="flex items-center gap-2 mb-8">
            <div class="w-9 h-9 rounded-xl bg-[#6366f1] flex items-center justify-center">
                <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
            </div>
            <span class="font-semibold text-[#1e1b4b]">PesquisaIA</span>
        </div>

        <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= htmlspecialchars($survey['name']) ?></h1>
        <p class="text-[#6b7280] mt-3 leading-relaxed">
            Olá! Estamos coletando opiniões para melhorar a nossa experiência. A conversa é informal e
            leva poucos minutos. Suas respostas são confidenciais.
        </p>

        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="rounded-lg border border-[#e5e7eb] p-3 flex items-center gap-2 text-sm text-[#1e1b4b]">
                <i data-lucide="clock" class="w-4 h-4 text-[#6366f1]"></i> ~3 min
            </div>
            <div class="rounded-lg border border-[#e5e7eb] p-3 flex items-center gap-2 text-sm text-[#1e1b4b]">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#6366f1]"></i> Confidencial
            </div>
        </div>

        <a href="/r/<?= htmlspecialchars($survey['public_slug'] ?? '') ?>/chat"
           class="mt-8 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#6366f1] py-3 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            Iniciar pesquisa <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
