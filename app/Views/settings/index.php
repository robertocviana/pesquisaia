<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-4xl mx-auto p-6 sm:p-10">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Configurações</h1>
            <p class="text-sm text-[#6b7280] mt-1">Gerencie sua conta e preferências.</p>
        </div>
    </div>

    <div class="grid md:grid-cols-[200px_1fr] gap-6">
        <!-- Tabs de navegação -->
        <nav class="space-y-1" id="settings-nav">
            <?php
            $tabs = [
                ['key' => 'perfil',       'label' => 'Perfil',        'icon' => 'user'],
                ['key' => 'seguranca',    'label' => 'Segurança',      'icon' => 'lock'],
                ['key' => 'assinatura',   'label' => 'Assinatura',     'icon' => 'credit-card'],
                ['key' => 'preferencias', 'label' => 'Preferências',   'icon' => 'settings'],
            ];
            foreach ($tabs as $t): ?>
            <button data-tab="<?= $t['key'] ?>"
                class="tab-btn w-full flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#1e1b4b]">
                <i data-lucide="<?= $t['icon'] ?>" class="w-4 h-4"></i> <?= $t['label'] ?>
            </button>
            <?php endforeach; ?>
        </nav>

        <!-- Painéis -->
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">

            <!-- Perfil -->
            <div id="tab-perfil" class="tab-panel space-y-5">
                <h3 class="font-semibold text-[#1e1b4b]">Perfil</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#eef2ff] flex items-center justify-center text-xl font-semibold text-[#6366f1]">AC</div>
                    <button class="rounded-lg border border-[#e5e7eb] bg-white px-3 py-1.5 text-sm hover:bg-[#f3f4f6] transition">Alterar foto</button>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Nome</label>
                    <input type="text" value="Ana Costa" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">E-mail</label>
                    <input type="email" value="ana@empresa.com" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
                <button class="rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Salvar alterações
                </button>
            </div>

            <!-- Segurança -->
            <div id="tab-seguranca" class="tab-panel hidden space-y-5">
                <h3 class="font-semibold text-[#1e1b4b]">Segurança</h3>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Senha atual</label>
                    <input type="password" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Nova senha</label>
                    <input type="password" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Confirmar nova senha</label>
                    <input type="password" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
                <button class="rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Alterar senha
                </button>
            </div>

            <!-- Assinatura -->
            <div id="tab-assinatura" class="tab-panel hidden space-y-5">
                <h3 class="font-semibold text-[#1e1b4b]">Assinatura</h3>
                <div class="rounded-lg border border-[#e5e7eb] bg-gradient-to-br from-[#eef2ff] to-white p-5">
                    <div class="text-xs uppercase tracking-wide text-[#6b7280]">Plano atual</div>
                    <div class="mt-1 text-2xl font-semibold text-[#1e1b4b]">Pro</div>
                    <p class="text-sm text-[#6b7280] mt-1">Pesquisas ilimitadas · Relatórios com IA · R$ 89/mês</p>
                    <button class="mt-4 rounded-lg border border-[#e5e7eb] bg-white px-3 py-1.5 text-sm hover:bg-[#f3f4f6] transition">
                        Gerenciar plano
                    </button>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-[#1e1b4b] mb-2">Histórico</h4>
                    <ul class="divide-y divide-[#e5e7eb] rounded-lg border border-[#e5e7eb]">
                        <?php foreach (['Nov 2026', 'Out 2026', 'Set 2026'] as $m): ?>
                        <li class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-[#1e1b4b]"><?= $m ?></span>
                            <span class="text-[#6b7280]">R$ 89,00</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Preferências -->
            <div id="tab-preferencias" class="tab-panel hidden space-y-5">
                <h3 class="font-semibold text-[#1e1b4b]">Preferências</h3>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Idioma</label>
                    <select class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm">
                        <option>Português (BR)</option>
                        <option>English</option>
                        <option>Español</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b] block mb-2">Tema</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button id="btn-claro" onclick="setTheme('claro')"
                            class="flex items-center gap-2 rounded-lg border border-[#6366f1] bg-[#eef2ff] px-3 py-2.5 text-sm transition">
                            <i data-lucide="sun" class="w-4 h-4"></i> Claro
                        </button>
                        <button id="btn-escuro" onclick="setTheme('escuro')"
                            class="flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm hover:bg-[#f3f4f6] transition">
                            <i data-lucide="moon" class="w-4 h-4"></i> Escuro
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// Tabs
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');

function activateTab(key) {
    tabBtns.forEach(btn => {
        const active = btn.dataset.tab === key;
        btn.className = `tab-btn w-full flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition ${
            active ? 'bg-[#eef2ff] text-[#4338ca] font-medium' : 'text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#1e1b4b]'
        }`;
    });
    tabPanels.forEach(panel => {
        panel.classList.toggle('hidden', panel.id !== 'tab-' + key);
    });
}

tabBtns.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));
activateTab('perfil');

// Tema
function setTheme(t) {
    document.documentElement.classList.toggle('dark', t === 'escuro');
    document.getElementById('btn-claro').className = `flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition ${t === 'claro' ? 'border-[#6366f1] bg-[#eef2ff]' : 'border-[#e5e7eb] bg-white hover:bg-[#f3f4f6]'}`;
    document.getElementById('btn-escuro').className = `flex items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition ${t === 'escuro' ? 'border-[#6366f1] bg-[#eef2ff]' : 'border-[#e5e7eb] bg-white hover:bg-[#f3f4f6]'}`;
}
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
