<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/detalhe?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar à pesquisa
    </a>

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

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Respostas</h1>
            <p class="text-sm text-[#6b7280] mt-1"><?= count($respondents) ?> respostas em "<?= htmlspecialchars($survey['name']) ?>"</p>
        </div>
        
        <?php if ($survey['status'] === 'ativa'): ?>
        <div>
            <form action="/pesquisas/respostas/gerar" method="POST" class="flex flex-wrap items-center gap-2 bg-white border border-[#e5e7eb] rounded-lg p-2 shadow-sm">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
                
                <span class="text-xs text-[#6b7280] font-medium pl-1.5 hidden sm:inline">Simular:</span>
                <select name="count" class="rounded-md border border-[#e5e7eb] px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-[#6366f1] bg-white">
                    <option value="5">5 respostas</option>
                    <option value="15" selected>15 respostas</option>
                    <option value="30">30 respostas</option>
                    <option value="50">50 respostas</option>
                    <option value="100">100 respostas</option>
                </select>
                


                <button type="submit" onclick="const btn = this; setTimeout(function(){ btn.disabled = true; btn.innerHTML = '<span class=\'animate-spin border-2 border-white border-t-transparent rounded-full w-3.5 h-3.5 mr-1.5 inline-block\'></span> Gerando...'; }, 10);"
                    class="inline-flex items-center justify-center rounded-md bg-[#6366f1] hover:bg-[#4f46e5] text-white px-3 py-1.5 text-xs font-semibold shadow-sm transition">
                    <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i> Gerar
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="rounded-xl border border-[#e5e7eb] bg-white shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] overflow-hidden">
        <!-- Toolbar de filtros -->
        <div class="px-4 py-3 border-b border-[#e5e7eb] flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#9ca3af] pointer-events-none"></i>
                <input id="search-respondent" type="text" placeholder="Buscar respondente..."
                    class="w-full rounded-lg border border-[#e5e7eb] bg-white pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>

            <!-- Filtro de Data -->
            <div class="relative" id="date-filter-wrapper">
                <button id="date-filter-btn" onclick="toggleDropdown('date-dropdown')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span id="date-filter-label">Data</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 text-[#9ca3af]"></i>
                </button>
                <div id="date-dropdown" class="hidden absolute right-0 top-full mt-1 bg-white border border-[#e5e7eb] rounded-lg shadow-lg z-20 min-w-[200px] p-3">
                    <p class="text-xs font-medium text-[#6b7280] mb-2">Filtrar por período</p>
                    <div class="flex flex-col gap-1.5">
                        <button onclick="setDateFilter('', 'Qualquer data')" class="date-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">Qualquer data</button>
                        <button onclick="setDateFilter('today', 'Hoje')" class="date-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">Hoje</button>
                        <button onclick="setDateFilter('7d', 'Últimos 7 dias')" class="date-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">Últimos 7 dias</button>
                        <button onclick="setDateFilter('30d', 'Últimos 30 dias')" class="date-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">Últimos 30 dias</button>
                    </div>
                </div>
            </div>

            <!-- Filtro de Status -->
            <div class="relative" id="status-filter-wrapper">
                <button id="status-filter-btn" onclick="toggleDropdown('status-dropdown')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span id="status-filter-label">Status</span>
                    <i data-lucide="chevron-down" class="w-3 h-3 text-[#9ca3af]"></i>
                </button>
                <div id="status-dropdown" class="hidden absolute right-0 top-full mt-1 bg-white border border-[#e5e7eb] rounded-lg shadow-lg z-20 min-w-[180px] p-3">
                    <p class="text-xs font-medium text-[#6b7280] mb-2">Filtrar por status</p>
                    <div class="flex flex-col gap-1.5">
                        <button onclick="setStatusFilter('', 'Status')" class="status-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">Todos</button>
                        <button onclick="setStatusFilter('concluida', 'Concluída')" class="status-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">
                            <span class="inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#22c55e] inline-block"></span>Concluída</span>
                        </button>
                        <button onclick="setStatusFilter('em_andamento', 'Em andamento')" class="status-opt text-left text-sm px-2 py-1.5 rounded hover:bg-[#f3f4f6] text-[#1e1b4b]">
                            <span class="inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#eab308] inline-block"></span>Em andamento</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Badge de resultados visíveis -->
            <span id="filter-results-badge" class="ml-auto text-xs text-[#6b7280] hidden"></span>
        </div>

        <!-- Tabela -->
        <table class="w-full text-sm" id="responses-table">
            <thead class="bg-[#f3f4f6]/40 text-xs uppercase text-[#6b7280]">
                <tr>
                    <th class="text-left px-4 py-2.5 font-medium">Respondente</th>
                    <th class="text-left px-4 py-2.5 font-medium">Data</th>
                    <th class="text-left px-4 py-2.5 font-medium">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e7eb]" id="responses-body">
                <?php foreach ($respondents as $r):
                    $statusStyle = $r['status'] === 'concluida'
                        ? 'bg-[#22c55e]/10 text-[#22c55e]'
                        : 'bg-[#eab308]/10 text-[#713f12]';
                    $statusLabel = $r['status'] === 'concluida' ? 'concluída' : 'em andamento';
                    // Timestamp em ms para o filtro de data via JS
                    $timestampMs = strtotime($r['created_at']) * 1000;
                ?>
                <tr class="hover:bg-[#f3f4f6]/30 transition response-row"
                    data-name="<?= htmlspecialchars(strtolower($r['name'] ?? 'anônimo')) ?>"
                    data-status="<?= htmlspecialchars($r['status']) ?>"
                    data-ts="<?= $timestampMs ?>">
                    <td class="px-4 py-3 font-medium text-[#1e1b4b]"><?= htmlspecialchars($r['name'] ?? 'Anônimo') ?></td>
                    <td class="px-4 py-3 text-[#6b7280]"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full <?= $statusStyle ?> px-2 py-0.5 text-xs font-medium capitalize">
                            <?= $statusLabel ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="/pesquisas/resposta?id=<?= $survey['id'] ?>&rid=<?= $r['id'] ?>"
                           class="inline-flex items-center gap-1.5 text-[#6366f1] text-sm hover:underline">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Visualizar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($respondents)): ?>
                <tr id="empty-state-initial">
                    <td colspan="4" class="px-4 py-12 text-center text-[#6b7280]">
                        Nenhuma resposta encontrada ainda.
                    </td>
                </tr>
                <?php endif; ?>
                <!-- Linha de estado vazio para filtro ativo -->
                <tr id="empty-state-filter" class="hidden">
                    <td colspan="4" class="px-4 py-12 text-center text-[#6b7280]">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="search-x" class="w-8 h-8 text-[#d1d5db]"></i>
                            <p>Nenhuma resposta encontrada para os filtros selecionados.</p>
                            <button onclick="clearAllFilters()" class="text-[#6366f1] text-sm hover:underline mt-1">Limpar filtros</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
lucide.createIcons();

// ── Estado dos filtros ─────────────────────────────────────────────────────
let activeSearch = '';
let activeDateFilter = '';
let activeStatusFilter = '';

// ── Dropdown toggle ────────────────────────────────────────────────────────
function toggleDropdown(id) {
    const all = ['date-dropdown', 'status-dropdown'];
    all.forEach(d => {
        if (d !== id) document.getElementById(d).classList.add('hidden');
    });
    document.getElementById(id).classList.toggle('hidden');
}

// Fechar dropdowns ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('#date-filter-wrapper') && !e.target.closest('#status-filter-wrapper')) {
        document.getElementById('date-dropdown').classList.add('hidden');
        document.getElementById('status-dropdown').classList.add('hidden');
    }
});

// ── Filtros ────────────────────────────────────────────────────────────────
function setDateFilter(value, label) {
    activeDateFilter = value;
    const btn = document.getElementById('date-filter-btn');
    const labelEl = document.getElementById('date-filter-label');
    labelEl.textContent = label;
    if (value) {
        btn.classList.add('border-[#6366f1]', 'text-[#6366f1]', 'bg-[#eef2ff]');
        btn.classList.remove('border-[#e5e7eb]', 'bg-white');
    } else {
        btn.classList.remove('border-[#6366f1]', 'text-[#6366f1]', 'bg-[#eef2ff]');
        btn.classList.add('border-[#e5e7eb]', 'bg-white');
    }
    document.getElementById('date-dropdown').classList.add('hidden');
    applyFilters();
}

function setStatusFilter(value, label) {
    activeStatusFilter = value;
    const btn = document.getElementById('status-filter-btn');
    const labelEl = document.getElementById('status-filter-label');
    labelEl.textContent = label;
    if (value) {
        btn.classList.add('border-[#6366f1]', 'text-[#6366f1]', 'bg-[#eef2ff]');
        btn.classList.remove('border-[#e5e7eb]', 'bg-white');
    } else {
        btn.classList.remove('border-[#6366f1]', 'text-[#6366f1]', 'bg-[#eef2ff]');
        btn.classList.add('border-[#e5e7eb]', 'bg-white');
    }
    document.getElementById('status-dropdown').classList.add('hidden');
    applyFilters();
}

function clearAllFilters() {
    document.getElementById('search-respondent').value = '';
    setDateFilter('', 'Data');
    setStatusFilter('', 'Status');
    activeSearch = '';
    applyFilters();
}

// ── Aplicação combinada de filtros ─────────────────────────────────────────
function applyFilters() {
    const rows = document.querySelectorAll('.response-row');
    const now = Date.now();

    const cutoffMs = {
        'today': now - 86400000,
        '7d':    now - (7 * 86400000),
        '30d':   now - (30 * 86400000),
        '':      0,
    };

    let visibleCount = 0;

    rows.forEach(row => {
        const name   = row.dataset.name   || '';
        const status = row.dataset.status || '';
        const ts     = parseInt(row.dataset.ts, 10) || 0;

        const matchName   = !activeSearch || name.includes(activeSearch);
        const matchStatus = !activeStatusFilter || status === activeStatusFilter;
        const matchDate   = !activeDateFilter || ts >= cutoffMs[activeDateFilter];

        const visible = matchName && matchStatus && matchDate;
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    // Atualiza o badge de resultados
    const badge = document.getElementById('filter-results-badge');
    const hasFilter = activeSearch || activeDateFilter || activeStatusFilter;
    if (hasFilter) {
        badge.textContent = visibleCount + ' resultado' + (visibleCount !== 1 ? 's' : '');
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }

    // Mostra linha de estado vazio para filtro ativo
    const emptyFilter = document.getElementById('empty-state-filter');
    if (emptyFilter) emptyFilter.classList.toggle('hidden', visibleCount > 0 || !hasFilter);
}

// ── Busca por nome ─────────────────────────────────────────────────────────
document.getElementById('search-respondent').addEventListener('input', function() {
    activeSearch = this.value.toLowerCase().trim();
    applyFilters();
});
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>

