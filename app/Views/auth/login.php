<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>

<div class="min-h-screen grid lg:grid-cols-2">
    <!-- Painel esquerdo — ilustração / branding -->
    <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-[#eef2ff] via-[#fafafa] to-[#eef2ff]">
        <a href="/login" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-[#6366f1] flex items-center justify-center">
                <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
            </div>
            <span class="font-semibold text-lg text-[#1e1b4b]">PesquisaIA</span>
        </a>
        <div class="max-w-md">
            <h2 class="text-3xl font-semibold tracking-tight text-[#1e1b4b]">Pesquisas inteligentes em minutos.</h2>
            <p class="mt-3 text-[#6b7280]">Crie pesquisas conversando com a IA, colete respostas e veja relatórios automáticos com insights.</p>
        </div>
        <p class="text-xs text-[#6b7280]">© 2026 PesquisaIA</p>
    </div>

    <!-- Painel direito — formulário -->
    <div class="flex items-center justify-center p-6 sm:p-12">
        <?php if (!empty($error)): ?>
        <div class="absolute top-6 right-6 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <form action="/login" method="POST" class="w-full max-w-sm">
            <?= \App\Helpers\Csrf::field() ?>
            <!-- Logo mobile -->
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <div class="w-9 h-9 rounded-xl bg-[#6366f1] flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-semibold text-lg text-[#1e1b4b]">PesquisaIA</span>
            </div>

            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Entrar</h1>
            <p class="text-sm text-[#6b7280] mt-1">Bem-vindo de volta.</p>

            <div class="space-y-4 mt-8">
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">E-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"
                        placeholder="seu@email.com"
                    >
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-[#1e1b4b]">Senha</label>
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Mínimo 8 caracteres"
                        class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"
                    >
                </div>

                <button
                    type="submit"
                    id="btn-entrar"
                    class="w-full rounded-lg bg-[#6366f1] py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Entrar
                </button>
            </div>

            <p class="text-center text-sm text-[#6b7280] mt-8">
                Não tem conta? <a href="/cadastro" class="text-[#6366f1] font-medium hover:underline">Criar conta</a>
            </p>
        </form>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
