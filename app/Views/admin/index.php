<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    
    <!-- Alertas de Feedback (Sucesso/Erro) -->
    <?php if (isset($errorMsg) && $errorMsg): ?>
        <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-300 text-sm border border-red-200 dark:border-red-900/50 flex items-start gap-2.5 shadow-sm animate-fade-in">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5 text-red-600 dark:text-red-400"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($successMsg) && $successMsg): ?>
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-900/50 flex items-start gap-2.5 shadow-sm animate-fade-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5 text-emerald-600 dark:text-emerald-400"></i>
            <div><?= htmlspecialchars($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <!-- Título do Painel -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1e1b4b] dark:text-white flex items-center gap-2">
                <i data-lucide="shield" class="w-7 h-7 text-[#6366f1] fill-[#6366f1]/10"></i> Painel de Administração
            </h1>
            <p class="text-sm text-[#6b7280] dark:text-gray-400 mt-1">
                Monitore o engajamento geral e gerencie as contas do sistema.
            </p>
        </div>
    </div>

    <!-- Cards de Estatísticas Globais -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        
        <!-- Card 1: Usuários -->
        <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-[#6366f1] flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-[#6b7280] dark:text-gray-400 block">Usuários Cadastrados</span>
                <span class="text-2xl font-bold text-[#1e1b4b] dark:text-white block mt-0.5"><?= $totalUsers ?></span>
                <span class="text-[11px] text-gray-500 mt-1 block">
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400"><?= $proUsers ?> Pro</span> / <?= ($totalUsers - $proUsers) ?> Trial
                </span>
            </div>
        </div>

        <!-- Card 2: Pesquisas -->
        <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-[#6366f1] flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-[#6b7280] dark:text-gray-400 block">Total de Pesquisas</span>
                <span class="text-2xl font-bold text-[#1e1b4b] dark:text-white block mt-0.5"><?= $totalSurveys ?></span>
                <span class="text-[11px] text-gray-500 mt-1 block">Criadas por todos os usuários</span>
            </div>
        </div>

        <!-- Card 3: Respostas Coletadas -->
        <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-[#6366f1] flex items-center justify-center shrink-0">
                <i data-lucide="message-square" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-[#6b7280] dark:text-gray-400 block">Respostas no Banco</span>
                <span class="text-2xl font-bold text-[#1e1b4b] dark:text-white block mt-0.5"><?= $totalAnswers ?></span>
                <span class="text-[11px] text-gray-500 mt-1 block">Turnos de conversa respondidos</span>
            </div>
        </div>

        <!-- Card 4: Relatórios IA -->
        <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-[#6366f1] flex items-center justify-center shrink-0">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-[#6b7280] dark:text-gray-400 block">Relatórios com IA</span>
                <span class="text-2xl font-bold text-[#1e1b4b] dark:text-white block mt-0.5"><?= $totalReports ?></span>
                <span class="text-[11px] text-gray-500 mt-1 block">Relatórios de IA gerados</span>
            </div>
        </div>

        <!-- Card 5: Taxa de Resposta -->
        <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-[#6366f1] flex items-center justify-center shrink-0">
                <i data-lucide="zap" class="w-6 h-6 fill-[#6366f1]/10"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-[#6b7280] dark:text-gray-400 block">Pesquisas por Usuário</span>
                <span class="text-2xl font-bold text-[#1e1b4b] dark:text-white block mt-0.5">
                    <?= $totalUsers > 0 ? number_format($totalSurveys / $totalUsers, 1) : 0 ?>
                </span>
                <span class="text-[11px] text-gray-500 mt-1 block">Média de engajamento na criação</span>
            </div>
        </div>

    </div>

    <!-- Filtros de Busca e Listagem -->
    <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm mb-6">
        <form method="GET" action="/<?= $adminRoute ?>" class="flex flex-wrap items-center gap-4">
            
            <!-- Campo de texto (Busca) -->
            <div class="flex-1 min-w-[240px]">
                <label class="text-xs font-semibold text-[#1e1b4b] dark:text-gray-300 block mb-1">Buscar Usuário</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nome ou e-mail..."
                           class="w-full rounded-lg border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                </div>
            </div>

            <!-- Filtro Plano -->
            <div class="w-[150px]">
                <label class="text-xs font-semibold text-[#1e1b4b] dark:text-gray-300 block mb-1">Filtrar por Plano</label>
                <select name="plan" class="w-full rounded-lg border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                    <option value="all" <?= $planFilter === 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="trial" <?= $planFilter === 'trial' ? 'selected' : '' ?>>Trial</option>
                    <option value="pro" <?= $planFilter === 'pro' ? 'selected' : '' ?>>Pro</option>
                </select>
            </div>

            <!-- Filtro Papel -->
            <div class="w-[150px]">
                <label class="text-xs font-semibold text-[#1e1b4b] dark:text-gray-300 block mb-1">Filtrar por Acesso</label>
                <select name="role" class="w-full rounded-lg border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
                    <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>Usuário comum</option>
                    <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <!-- Botões -->
            <div class="flex items-end gap-2 pt-5">
                <button type="submit" class="rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition">
                    Filtrar
                </button>
                <?php if ($search !== '' || $planFilter !== 'all' || $roleFilter !== 'all'): ?>
                    <a href="/<?= $adminRoute ?>" class="rounded-lg border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-950 px-4 py-2 text-sm font-semibold text-[#6b7280] hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                        Limpar
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Tabela Principal de Usuários -->
    <div class="rounded-xl border border-[#e5e7eb] dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-950/40 text-xs font-semibold text-[#6b7280] dark:text-gray-400 border-b border-[#e5e7eb] dark:border-gray-800">
                        <th class="p-4 w-10"></th>
                        <th class="p-4">Usuário</th>
                        <th class="p-4 text-center">Plano</th>
                        <th class="p-4 text-center">Papel</th>
                        <th class="p-4 text-center">Pesquisas</th>
                        <th class="p-4 text-center">Respondentes</th>
                        <th class="p-4 text-center">Relatórios IA</th>
                        <th class="p-4 text-center">Respostas (Conversa)</th>
                        <th class="p-4">Última Atividade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e7eb] dark:divide-gray-800 text-sm text-[#1e1b4b] dark:text-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="9" class="p-8 text-center text-[#6b7280]">
                                Nenhum usuário encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-950/20 transition-colors">
                                <!-- Botão de Expansão -->
                                <td class="p-4 text-center">
                                    <button onclick="toggleSurveys(<?= $u['id'] ?>)" 
                                            id="btn-expand-<?= $u['id'] ?>"
                                            title="Ver pesquisas" 
                                            class="p-1 rounded hover:bg-indigo-50 dark:hover:bg-indigo-950/30 text-[#6b7280] hover:text-[#6366f1] transition">
                                        <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200" id="icon-expand-<?= $u['id'] ?>"></i>
                                    </button>
                                </td>

                                <!-- Detalhes do Usuário -->
                                <td class="p-4">
                                    <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="text-xs text-[#6b7280] dark:text-gray-400 mt-0.5"><?= htmlspecialchars($u['email']) ?></div>
                                    <div class="text-[10px] text-gray-400 mt-1">Cadastro: <?= \App\Helpers\DateHelper::format($u['created_at'], 'd/m/Y H:i') ?></div>
                                </td>

                                <!-- Seletor de Plano -->
                                <td class="p-4 text-center align-middle">
                                    <form action="/<?= $adminRoute ?>/update-plan" method="POST" class="inline-block" onchange="this.submit()">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <select name="plan" class="text-xs font-semibold rounded px-2.5 py-1.5 cursor-pointer focus:outline-none ring-offset-2 focus:ring-2 focus:ring-[#6366f1] <?= $u['plan'] === 'pro' ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50' : 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50' ?>">
                                            <option value="trial" <?= $u['plan'] === 'trial' ? 'selected' : '' ?>>Trial</option>
                                            <option value="pro" <?= $u['plan'] === 'pro' ? 'selected' : '' ?>>Pro</option>
                                        </select>
                                    </form>
                                </td>

                                <!-- Seletor de Papel de Acesso -->
                                <td class="p-4 text-center align-middle">
                                    <form action="/<?= $adminRoute ?>/update-role" method="POST" class="inline-block" onchange="confirmRoleChange(this, '<?= htmlspecialchars($u['name']) ?>', '<?= $u['role'] ?>')">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <select name="role" class="text-xs font-semibold rounded px-2.5 py-1.5 cursor-pointer focus:outline-none ring-offset-2 focus:ring-2 focus:ring-[#6366f1] <?= $u['role'] === 'admin' ? 'bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50' : 'bg-gray-50 dark:bg-gray-950/40 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800' ?>">
                                            <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Usuário</option>
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>

                                <!-- Pesquisas criadas -->
                                <td class="p-4 text-center">
                                    <span class="font-bold text-gray-900 dark:text-white"><?= $u['total_surveys'] ?></span>
                                    <div class="text-[10px] text-gray-400 mt-1">
                                        <?= $u['surveys_active'] ?> Ativas / <?= $u['surveys_closed'] ?> Enc.
                                    </div>
                                </td>

                                <!-- Respondentes totais -->
                                <td class="p-4 text-center">
                                    <span class="font-bold text-gray-900 dark:text-white"><?= $u['total_respondents'] ?></span>
                                    <div class="text-[10px] text-gray-400 mt-1">
                                        <?= $u['respondents_completed'] ?> Concl.
                                    </div>
                                </td>

                                <!-- Relatórios gerados -->
                                <td class="p-4 text-center font-semibold text-gray-800 dark:text-gray-300">
                                    <?= $u['total_reports'] ?>
                                </td>

                                <!-- Respostas individuais -->
                                <td class="p-4 text-center font-semibold text-gray-800 dark:text-gray-300">
                                    <?= $u['total_answers'] ?>
                                </td>

                                <!-- Última atividade -->
                                <td class="p-4">
                                    <?php if ($u['last_response_at']): ?>
                                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Resposta coletada</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5"><?= \App\Helpers\DateHelper::format($u['last_response_at'], 'd/m/Y H:i') ?></div>
                                    <?php elseif ($u['last_survey_at']): ?>
                                        <div class="text-xs text-[#6366f1] font-medium">Pesquisa criada</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5"><?= \App\Helpers\DateHelper::format($u['last_survey_at'], 'd/m/Y H:i') ?></div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Sem atividade</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Linha de detalhamento das pesquisas do usuário (Inicialmente oculta) -->
                            <tr id="surveys-row-<?= $u['id'] ?>" class="hidden bg-gray-50/40 dark:bg-gray-950/10">
                                <td colspan="9" class="p-4 border-t border-b border-[#e5e7eb] dark:border-gray-800">
                                    <div class="pl-14 pr-4 py-2">
                                        <h4 class="font-semibold text-xs text-[#1e1b4b] dark:text-white uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#6366f1]"></i> Pesquisas Criadas por <?= htmlspecialchars($u['name']) ?>
                                        </h4>
                                        <div id="surveys-container-<?= $u['id'] ?>" class="text-xs">
                                            <!-- Preenchido via JS AJAX -->
                                            <div class="flex items-center gap-2 text-[#6b7280] py-3">
                                                <div class="animate-spin w-4 h-4 border-2 border-[#6366f1] border-t-transparent rounded-full"></div>
                                                Carregando pesquisas do usuário...
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
});

// Cache para evitar requisições AJAX desnecessárias ao expandir/recolher
const surveysCache = {};

function toggleSurveys(userId) {
    const row = document.getElementById(`surveys-row-${userId}`);
    const icon = document.getElementById(`icon-expand-${userId}`);
    const container = document.getElementById(`surveys-container-${userId}`);
    
    if (row.classList.contains('hidden')) {
        // Expandir
        row.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
        
        // Se já está no cache, usar cache
        if (surveysCache[userId]) {
            renderSurveys(container, surveysCache[userId]);
        } else {
            // Carregar via AJAX
            fetch(`/<?= $adminRoute ?>/user-surveys?user_id=${userId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Falha ao buscar pesquisas');
                    return res.json();
                })
                .then(data => {
                    surveysCache[userId] = data;
                    renderSurveys(container, data);
                })
                .catch(err => {
                    container.innerHTML = `<div class="text-red-500 py-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">error</span> Erro ao carregar pesquisas.
                    </div>`;
                });
        }
    } else {
        // Recolher
        row.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function renderSurveys(container, surveys) {
    if (surveys.length === 0) {
        container.innerHTML = `<div class="text-gray-400 py-3 italic">Nenhuma pesquisa criada por este usuário.</div>`;
        return;
    }

    let html = `<div class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-lg bg-white dark:bg-gray-950 shadow-sm">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-800">
                    <th class="p-2.5">Nome da Pesquisa</th>
                    <th class="p-2.5 text-center">Status</th>
                    <th class="p-2.5 text-center">Respostas / Meta</th>
                    <th class="p-2.5 text-center">Relatório IA</th>
                    <th class="p-2.5">Criada em</th>
                    <th class="p-2.5">Prazo Limite</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] text-gray-600 dark:text-gray-300">`;

    surveys.forEach(s => {
        let statusBadge = '';
        if (s.status === 'ativa') {
            statusBadge = `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-medium text-emerald-700 border border-emerald-100">Ativa</span>`;
        } else if (s.status === 'encerrada') {
            statusBadge = `<span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[9px] font-medium text-red-700 border border-red-100">Encerrada</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[9px] font-medium text-gray-600 border border-gray-200">Rascunho</span>`;
        }

        let reportBadge = '';
        if (s.has_report === 1) {
            reportBadge = `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-medium text-emerald-700 border border-emerald-100">Gerado</span>`;
        } else {
            reportBadge = `<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[9px] font-medium text-gray-400 border border-gray-200">Não gerado</span>`;
        }

        const goalStr = s.goal_responses ? s.goal_responses : '∞';
        const progressPercent = s.goal_responses ? Math.min(Math.round((s.response_count / s.goal_responses) * 100), 100) : 100;
        
        let progressHtml = `<div>
            <div class="flex items-center justify-center gap-1.5">
                <span class="font-semibold text-gray-800 dark:text-white">${s.response_count}</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-500">${goalStr}</span>
            </div>`;
        
        if (s.goal_responses) {
            progressHtml += `<div class="w-20 bg-gray-100 dark:bg-gray-800 h-1 rounded-full mx-auto mt-1 overflow-hidden">
                <div class="bg-[#6366f1] h-1 rounded-full" style="width: ${progressPercent}%"></div>
            </div>`;
        }
        progressHtml += `</div>`;

        html += `<tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10">
            <td class="p-2.5 font-medium text-gray-900 dark:text-white">${escapeHtml(s.name)}</td>
            <td class="p-2.5 text-center">${statusBadge}</td>
            <td class="p-2.5 text-center">${progressHtml}</td>
            <td class="p-2.5 text-center">${reportBadge}</td>
            <td class="p-2.5 text-gray-500">${s.created_at_formatted}</td>
            <td class="p-2.5 text-gray-500">${escapeHtml(s.deadline_at_formatted)}</td>
        </tr>`;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
    lucide.createIcons();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
}

function confirmRoleChange(selectElement, userName, currentRole) {
    const newRole = selectElement.value;
    if (newRole === currentRole) return;

    let warningText = `Deseja realmente alterar o papel de "${userName}" para ${newRole === 'admin' ? 'Administrador' : 'Usuário comum'}?`;
    if (newRole === 'admin') {
        warningText += `\n\nATENÇÃO: Isso concederá acesso total a este painel administrativo e a todas as configurações de controle do sistema!`;
    }

    if (confirm(warningText)) {
        selectElement.form.submit();
    } else {
        // Reverter seleção
        selectElement.value = currentRole;
    }
}
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
