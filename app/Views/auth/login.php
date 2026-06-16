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
        <form action="/dashboard" method="GET" class="w-full max-w-sm">
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
                        value="ana@empresa.com"
                        class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"
                    >
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-[#1e1b4b]">Senha</label>
                        <a href="#" class="text-xs text-[#6366f1] hover:underline">Esqueci minha senha</a>
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        value="password"
                        class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"
                    >
                </div>

                <button
                    type="submit"
                    id="btn-entrar"
                    class="w-full rounded-lg bg-[#6366f1] py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Entrar
                </button>

                <div class="flex items-center gap-3 text-xs text-[#6b7280]">
                    <div class="h-px flex-1 bg-[#e5e7eb]"></div>
                    <span>ou</span>
                    <div class="h-px flex-1 bg-[#e5e7eb]"></div>
                </div>

                <a href="/dashboard"
                   class="w-full rounded-lg border border-[#e5e7eb] bg-white py-2.5 text-sm font-medium text-[#1e1b4b] hover:bg-[#f3f4f6] transition flex items-center justify-center gap-2">
                    <!-- Google icon -->
                    <svg width="16" height="16" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.83z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                    </svg>
                    Continuar com Google
                </a>
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
