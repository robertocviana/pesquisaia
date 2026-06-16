<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-[#eef2ff]/40 via-[#fafafa] to-[#eef2ff]/40 flex items-center justify-center p-6">
    <div class="w-full max-w-lg rounded-2xl border border-[#e5e7eb] bg-white p-8 shadow-[0_4px_6px_-1px_rgb(15_23_42_/_0.06),_0_10px_20px_-10px_rgb(15_23_42_/_0.10)] text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-[#22c55e]/10 flex items-center justify-center">
            <i data-lucide="check-circle-2" class="w-8 h-8 text-[#22c55e]"></i>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b] mt-5">Pesquisa concluída!</h1>
        <p class="text-[#6b7280] mt-2 leading-relaxed">
            Muito obrigada por compartilhar sua opinião. Suas respostas são essenciais para que possamos
            melhorar continuamente.
        </p>

        <div class="mt-8 text-left">
            <label class="text-sm font-medium text-[#1e1b4b]">Quer deixar um feedback adicional?</label>
            <div id="feedback-section">
                <textarea id="feedback-text" rows="3" placeholder="Opcional"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"></textarea>
                <button id="btn-enviar"
                    class="mt-3 w-full rounded-lg bg-[#6366f1] py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition disabled:opacity-40"
                    disabled onclick="sendFeedback()">
                    Enviar feedback
                </button>
            </div>
            <div id="feedback-success" class="hidden mt-2 rounded-lg bg-[#22c55e]/10 text-[#22c55e] px-3 py-3 text-sm flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Feedback enviado, obrigada!
            </div>
        </div>

        <button onclick="window.close()"
            class="mt-4 w-full rounded-lg border border-[#e5e7eb] bg-white py-2.5 text-sm text-[#1e1b4b] hover:bg-[#f3f4f6] transition">
            Encerrar
        </button>
    </div>
</div>

<script>
lucide.createIcons();

document.getElementById('feedback-text').addEventListener('input', function() {
    document.getElementById('btn-enviar').disabled = !this.value.trim();
});

function sendFeedback() {
    document.getElementById('feedback-section').classList.add('hidden');
    const success = document.getElementById('feedback-success');
    success.classList.remove('hidden');
    success.classList.add('flex');
    lucide.createIcons();
}
</script>
</body>
</html>
