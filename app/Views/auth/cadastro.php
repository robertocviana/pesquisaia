<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>

<div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-[#eef2ff]/50 via-[#fafafa] to-[#eef2ff]/50">
    <form action="/dashboard" method="GET" class="w-full max-w-md bg-white rounded-2xl border border-[#e5e7eb] p-8 shadow-[0_4px_6px_-1px_rgb(15_23_42_/_0.06),_0_10px_20px_-10px_rgb(15_23_42_/_0.10)]">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-[#6366f1] flex items-center justify-center">
                <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
            </div>
            <span class="font-semibold text-lg text-[#1e1b4b]">PesquisaIA</span>
        </div>

        <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Criar conta</h1>
        <p class="text-sm text-[#6b7280] mt-1">Leva menos de um minuto.</p>

        <div class="space-y-4 mt-6">
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Nome</label>
                <input id="name" type="text" name="name"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">E-mail</label>
                <input id="email" type="email" name="email"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Senha</label>
                <input id="senha" type="password" name="senha"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Confirmar senha</label>
                <input id="senha2" type="password" name="senha2"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>

            <button type="submit" id="btn-criar"
                class="w-full rounded-lg bg-[#6366f1] py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                Criar conta
            </button>
        </div>

        <p class="text-center text-sm text-[#6b7280] mt-6">
            Já possuo conta. <a href="/login" class="text-[#6366f1] font-medium hover:underline">Entrar</a>
        </p>
    </form>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
