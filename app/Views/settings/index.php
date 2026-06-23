<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-4xl mx-auto p-6 sm:p-10">
    
    <!-- Flash Messages -->
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
            <?php
            $parts    = explode(' ', $user['name'] ?? '');
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            ?>
            <form id="tab-perfil" action="/configuracoes/perfil" method="POST" class="tab-panel space-y-5">
                <?= \App\Helpers\Csrf::field() ?>
                <h3 class="font-semibold text-[#1e1b4b]">Perfil</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#eef2ff] flex items-center justify-center text-xl font-semibold text-[#6366f1]">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Nome</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" required>
                </div>
                <button type="submit" class="rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Salvar alterações
                </button>
            </form>

            <!-- Segurança -->
            <form id="tab-seguranca" action="/configuracoes/seguranca" method="POST" class="tab-panel hidden space-y-5">
                <?= \App\Helpers\Csrf::field() ?>
                <h3 class="font-semibold text-[#1e1b4b]">Segurança</h3>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Senha atual</label>
                    <input type="password" name="current_password" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Nova senha</label>
                    <input type="password" name="new_password" placeholder="Mínimo 8 caracteres" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Confirmar nova senha</label>
                    <input type="password" name="confirm_password" placeholder="Repita a nova senha" class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" required>
                </div>
                <button type="submit" class="rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                    Alterar senha
                </button>
            </form>

            <!-- Preferências -->
            <div id="tab-preferencias" class="tab-panel hidden space-y-5">
                <h3 class="font-semibold text-[#1e1b4b]">Preferências</h3>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b]">Idioma</label>
                    <select class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none">
                        <option>Português (BR)</option>
                        <option disabled>English (Em breve)</option>
                        <option disabled>Español (Em breve)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-[#1e1b4b] block mb-2">Tema</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button id="btn-claro" onclick="setTheme('claro')"
                            class="flex items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition">
                            <i data-lucide="sun" class="w-4 h-4"></i> Claro
                        </button>
                        <button id="btn-escuro" onclick="setTheme('escuro')"
                            class="flex items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition">
                            <i data-lucide="moon" class="w-4 h-4"></i> Escuro
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    
    // Restaurar aba ativa ou padrão 'perfil'
    const activeTab = localStorage.getItem('active_settings_tab') || 'perfil';
    activateTab(activeTab);

    // Restaurar tema ativo
    const savedTheme = localStorage.getItem('theme') || 'claro';
    setTheme(savedTheme);
});

// Tabs
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');

function activateTab(key) {
    localStorage.setItem('active_settings_tab', key);
    
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

// Tema
function setTheme(t) {
    localStorage.setItem('theme', t);
    document.documentElement.classList.toggle('dark', t === 'escuro');
    
    const btnClaro = document.getElementById('btn-claro');
    const btnEscuro = document.getElementById('btn-escuro');
    
    if (t === 'claro') {
        btnClaro.className = 'flex items-center justify-center gap-2 rounded-lg border border-[#6366f1] bg-[#eef2ff] text-[#4338ca] font-medium px-3 py-2.5 text-sm transition';
        btnEscuro.className = 'flex items-center justify-center gap-2 rounded-lg border border-[#e5e7eb] bg-white text-[#6b7280] hover:bg-[#f3f4f6] px-3 py-2.5 text-sm transition';
    } else {
        btnClaro.className = 'flex items-center justify-center gap-2 rounded-lg border border-[#e5e7eb] bg-white text-[#6b7280] hover:bg-[#f3f4f6] px-3 py-2.5 text-sm transition';
        btnEscuro.className = 'flex items-center justify-center gap-2 rounded-lg border border-[#6366f1] bg-[#eef2ff] text-[#4338ca] font-medium px-3 py-2.5 text-sm transition';
    }
}
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
