<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>

<div class="min-h-screen flex bg-[#fafafa]">
    <!-- Sidebar desktop (repetida inline pois o layout desta tela é especial) -->
    <aside class="hidden md:flex w-64 flex-col border-r border-[#e5e7eb] bg-[#fafafa] p-4 gap-1 shrink-0">
        <a href="/dashboard" class="flex items-center gap-2 px-2 py-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
                <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
            </div>
            <span class="font-semibold text-[#1e1b4b]">PesquisaIA</span>
        </a>
        <a href="/pesquisas/nova" class="mb-4 inline-flex items-center justify-center gap-2 rounded-lg bg-[#6366f1] px-3 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Nova pesquisa
        </a>
        <?php
        $nav = [
            ['href' => '/dashboard',    'label' => 'Dashboard',       'icon' => 'layout-dashboard'],
            ['href' => '/pesquisas',    'label' => 'Minhas Pesquisas', 'icon' => 'file-text'],
            ['href' => '/configuracoes','label' => 'Configurações',    'icon' => 'settings'],
        ];
        foreach ($nav as $item):
            $active = str_starts_with($currentPath ?? '', $item['href']);
        ?>
        <a href="<?= $item['href'] ?>" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition <?= $active ? 'bg-[#eef2ff] text-[#4338ca] font-medium' : 'text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#1e1b4b]' ?>">
            <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4"></i>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
        <div class="mt-auto border-t border-[#e5e7eb] pt-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-[#eef2ff] flex items-center justify-center text-sm font-medium text-[#6366f1]">AC</div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">Ana Costa</div>
                    <div class="text-xs text-[#6b7280] truncate">ana@empresa.com</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Área principal com layout de grid -->
    <div class="flex-1 grid lg:grid-cols-[1fr_320px] min-h-screen">
        <!-- Chat principal -->
        <div class="flex flex-col" style="height:100vh; overflow:hidden;">
            <div class="border-b border-[#e5e7eb] px-6 py-4 flex items-center gap-3 shrink-0">
                <div class="w-9 h-9 rounded-lg bg-[#6366f1] flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <div class="font-semibold text-[#1e1b4b]">Assistente de pesquisa</div>
                    <div class="text-xs text-[#6b7280]">Vamos criar sua pesquisa juntos.</div>
                </div>
            </div>

            <div id="chat-scroll" class="flex-1 overflow-y-auto px-6 py-8">
                <div class="max-w-2xl mx-auto space-y-5" id="messages-container">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                        </div>
                        <div class="text-sm leading-relaxed text-[#1e1b4b] pt-1">
                            Olá! Vou te ajudar a criar sua pesquisa. Qual é o <strong>objetivo</strong> dela? O que você quer descobrir ou medir?
                        </div>
                    </div>
                </div>
                <div id="chat-end"></div>
            </div>

            <div class="border-t border-[#e5e7eb] p-4 shrink-0">
                <div class="max-w-2xl mx-auto flex items-end gap-2">
                    <textarea id="chat-input" rows="1" placeholder="Escreva sua resposta..."
                        class="flex-1 resize-none rounded-lg border border-[#e5e7eb] bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"></textarea>
                    <button id="chat-send" class="rounded-lg bg-[#6366f1] p-2.5 text-white hover:opacity-90 transition">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar de progresso -->
        <aside class="hidden lg:block border-l border-[#e5e7eb] bg-[#fafafa] p-6">
            <div class="text-xs uppercase tracking-wide text-[#6b7280] mb-2">Pesquisa</div>
            <div id="survey-name" class="font-semibold text-[#1e1b4b] mb-6">Nova pesquisa</div>

            <div class="mb-6">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="font-medium text-[#1e1b4b]">Progresso</span>
                    <span id="progress-label" class="text-[#6b7280]">0%</span>
                </div>
                <div class="h-2 rounded-full bg-[#f3f4f6] overflow-hidden">
                    <div id="progress-bar" class="h-full bg-[#6366f1] transition-all" style="width:0%"></div>
                </div>
            </div>

            <div class="space-y-2" id="steps-list">
                <div data-step="objetivo" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Objetivo
                </div>
                <div data-step="publico" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Público-alvo
                </div>
                <div data-step="perguntas" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Perguntas
                </div>
                <div data-step="revisao" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Revisão
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
lucide.createIcons();

const aiFlow = [
    { ask: "Perfeito! Qual público vai responder essa pesquisa?", step: "objetivo" },
    { ask: "Excelente. Quantas perguntas você gostaria? Posso sugerir 4 baseadas no seu objetivo.", step: "publico" },
    { ask: "Pronto! Gerei 4 perguntas para você. Clique abaixo para ir à revisão e ajustar antes de publicar.", step: "perguntas" },
];

let step = 0;

function scrollToEnd() {
    document.getElementById('chat-end').scrollIntoView({ behavior: 'smooth' });
}

function addMessage(role, html) {
    const container = document.getElementById('messages-container');
    const div = document.createElement('div');
    if (role === 'assistant') {
        div.className = 'flex gap-3';
        div.innerHTML = `
            <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
            </div>
            <div class="text-sm leading-relaxed text-[#1e1b4b] pt-1">${html}</div>`;
    } else {
        div.className = 'flex justify-end';
        div.innerHTML = `<div class="max-w-[80%] rounded-2xl rounded-tr-sm bg-[#6366f1] px-4 py-2.5 text-sm text-white">${html}</div>`;
    }
    container.appendChild(div);
    scrollToEnd();
}

function updateProgress() {
    const pct = Math.min(100, Math.round((step / aiFlow.length) * 100));
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-label').textContent = pct + '%';

    const stepKeys = ['objetivo', 'publico', 'perguntas', 'revisao'];
    stepKeys.forEach((key, i) => {
        const el = document.querySelector(`[data-step="${key}"]`);
        if (!el) return;
        if (i < step) {
            el.className = 'flex items-center gap-2.5 text-sm text-[#22c55e]';
            el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> ${el.textContent.trim()}`;
        } else if (i === step) {
            el.className = 'flex items-center gap-2.5 text-sm text-[#1e1b4b] font-medium';
            el.innerHTML = `<span class="w-4 h-4 rounded-full border-2 border-[#6366f1]"></span> ${el.textContent.trim()}`;
        }
    });
}

function send() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    addMessage('user', text);

    setTimeout(() => {
        if (step >= aiFlow.length) {
            addMessage('assistant', 'Ótimo! <a href="/pesquisas/revisao?id=s-001" class="text-[#6366f1] underline font-medium">Clique aqui para revisar e publicar sua pesquisa →</a>');
            return;
        }
        const current = aiFlow[step];
        addMessage('assistant', current.ask);
        step++;
        updateProgress();
    }, 600);
}

document.getElementById('chat-send').addEventListener('click', send);
document.getElementById('chat-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
});

updateProgress();
</script>
</body>
</html>
