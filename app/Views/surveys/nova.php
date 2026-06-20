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
        <a href="/pesquisas/nova?new=1" class="mb-4 inline-flex items-center justify-center gap-2 rounded-lg bg-[#6366f1] px-3 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
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
                    <?php if (empty($history)): ?>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="text-sm leading-relaxed text-[#1e1b4b] pt-1">
                                Olá! Vou te ajudar a criar sua pesquisa.<br><br>
                                <strong>Qual tipo de pesquisa você deseja fazer?</strong><br><br>
                                Exemplos:<br>
                                • Validar uma ideia de negócio<br>
                                • Entender uma dor de clientes<br>
                                • Avaliar uma nova funcionalidade<br>
                                • Entender comportamento de usuários<br>
                                • Testar uma proposta de valor<br>
                                • Outro<br><br>
                                Escreva o tipo de pesquisa que você quer criar.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($history as $msg): ?>
                            <?php if ($msg['role'] === 'assistant'): 
                                $msgHtml = htmlspecialchars($msg['content']);
                                $msgHtml = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $msgHtml);
                                $msgHtml = nl2br($msgHtml);
                            ?>
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                                    </div>
                                    <div class="text-sm leading-relaxed text-[#1e1b4b] pt-1">
                                        <?= $msgHtml ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="flex justify-end">
                                    <div class="max-w-[80%] rounded-2xl rounded-tr-sm bg-[#6366f1] px-4 py-2.5 text-sm text-white">
                                        <?= htmlspecialchars($msg['content']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($currentStage === 'finalizado'): ?>
                            <div class="flex gap-3">
                                <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                                </div>
                                <div class="text-sm leading-relaxed text-[#1e1b4b] pt-1">
                                    <a href="/pesquisas/revisao?id=<?= $surveyId ?>" class="inline-flex items-center gap-1.5 mt-2 rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition">Revisar e publicar →</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
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
                <div data-step="tipo" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Tipo de Pesquisa
                </div>
                <div data-step="objetivo" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Objetivo
                </div>
                <div data-step="publico" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Público-alvo
                </div>
                <div data-step="hipotese" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Hipóteses
                </div>
                <div data-step="perguntas" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Perguntas
                </div>
                <div data-step="meta" class="flex items-center gap-2.5 text-sm text-[#6b7280]">
                    <span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> Encerramento
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

// ─── Configuração real da IA ──────────────────────────────────────────────────
const SURVEY_ID  = <?= (int) ($survey['id'] ?? 0) ?>;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let currentStage    = '<?= $currentStage ?>';
let questionsCount  = <?= count($questions ?? []) ?>;
let isSending       = false;

// Executar atualização de progresso no carregamento da página
document.addEventListener('DOMContentLoaded', () => {
    updateProgress(currentStage);
    scrollToEnd();
});

function scrollToEnd() {
    document.getElementById('chat-end').scrollIntoView({ behavior: 'smooth' });
}

function setLoading(loading) {
    isSending = loading;
    const btn = document.getElementById('chat-send');
    const inp = document.getElementById('chat-input');
    btn.disabled = loading;
    inp.disabled = loading;
    btn.style.opacity = loading ? '0.5' : '1';
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

function addTypingIndicator() {
    const container = document.getElementById('messages-container');
    const div = document.createElement('div');
    div.id = 'typing-indicator';
    div.className = 'flex gap-3';
    div.innerHTML = `
        <div class="w-8 h-8 shrink-0 rounded-lg bg-[#6366f1] flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
        </div>
        <div class="text-sm leading-relaxed text-[#6b7280] pt-2 flex gap-1 items-center">
            <span class="w-2 h-2 bg-[#6b7280] rounded-full animate-bounce" style="animation-delay:0ms"></span>
            <span class="w-2 h-2 bg-[#6b7280] rounded-full animate-bounce" style="animation-delay:150ms"></span>
            <span class="w-2 h-2 bg-[#6b7280] rounded-full animate-bounce" style="animation-delay:300ms"></span>
        </div>`;
    container.appendChild(div);
    scrollToEnd();
}

function removeTypingIndicator() {
    document.getElementById('typing-indicator')?.remove();
}

function updateProgress(stage) {
    const stageOrder = [
        'tipo', 
        'objetivo', 
        'publico', 
        'hipotese', 
        'perguntas_previas', 
        'perguntas_sugeridas', 
        'meta_encerramento', 
        'revisao_chat', 
        'finalizado'
    ];
    const stepKeys = ['tipo', 'objetivo', 'publico', 'hipotese', 'perguntas', 'meta', 'revisao'];

    let currentStepIdx = 0;
    if (stage === 'objetivo') currentStepIdx = 1;
    else if (stage === 'publico') currentStepIdx = 2;
    else if (stage === 'hipotese') currentStepIdx = 3;
    else if (stage === 'perguntas_previas' || stage === 'perguntas_sugeridas') currentStepIdx = 4;
    else if (stage === 'meta_encerramento') currentStepIdx = 5;
    else if (stage === 'revisao_chat') currentStepIdx = 6;
    else if (stage === 'finalizado') currentStepIdx = 7;

    const stageIdx = stageOrder.indexOf(stage);
    const totalStages = stageOrder.length - 1;
    const pct = Math.min(100, Math.round((stageIdx / totalStages) * 100));

    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('progress-label').textContent = pct + '%';

    stepKeys.forEach((key, i) => {
        const el = document.querySelector(`[data-step="${key}"]`);
        if (!el) return;
        
        let label = el.innerText.trim();
        
        if (i < currentStepIdx) {
            el.className = 'flex items-center gap-2.5 text-sm text-[#22c55e]';
            el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> ${label}`;
        } else if (i === currentStepIdx) {
            el.className = 'flex items-center gap-2.5 text-sm text-[#1e1b4b] font-medium';
            el.innerHTML = `<span class="w-4 h-4 rounded-full border-2 border-[#6366f1]"></span> ${label}`;
        } else {
            el.className = 'flex items-center gap-2.5 text-sm text-[#6b7280]';
            el.innerHTML = `<span class="w-4 h-4 rounded-full border-2 border-[#9ca3af]"></span> ${label}`;
        }
    });

    // Atualizar nome da pesquisa na sidebar
    const nameField = document.getElementById('survey-name');
    if (nameField && window._surveyName) {
        nameField.textContent = window._surveyName;
    }
}

async function send() {
    if (isSending) return;
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    addMessage('user', text);
    setLoading(true);
    addTypingIndicator();

    try {
        const res = await fetch('/pesquisas/nova/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ survey_id: SURVEY_ID, message: text }),
        });

        const data = await res.json();
        removeTypingIndicator();

        if (data.error) {
            // Erro retryable (timeout, rede): mostrar botão de tentar novamente
            if (data.retryable) {
                addErrorMessage(data.error, true);
            } else {
                addErrorMessage(data.error, false);
            }
            setLoading(false);
            return;
        }

        // Exibir banner de aviso se estiver no modo fallback (IA indisponível)
        if (data.warning) {
            showFallbackBanner(data.warning.message);
        }

        // Atualizar nome da pesquisa se disponível
        if (data.fields?.name) {
            window._surveyName = data.fields.name;
        }

        currentStage = data.stage ?? 'objetivo';
        updateProgress(currentStage);

        // Renderizar markdown simples (negrito, quebras de linha)
        let msgHtml = data.message
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        // Se as perguntas foram geradas, adicionar link para revisão
        if (data.stage === 'finalizado' || data.questionsCount > 0) {
            msgHtml += `<br><br><a href="/pesquisas/revisao?id=${SURVEY_ID}" class="inline-flex items-center gap-1.5 mt-2 rounded-lg bg-[#6366f1] px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition">Revisar e publicar →</a>`;
        }

        addMessage('assistant', msgHtml);

    } catch (err) {
        removeTypingIndicator();
        addErrorMessage('Sem conexão com o servidor. Verifique sua internet e tente novamente.', true);
    }

    setLoading(false);
}

function addErrorMessage(msg, retryable = false) {
    const container = document.getElementById('messages-container');
    const div = document.createElement('div');
    div.className = 'flex gap-3';
    const retryBtn = retryable
        ? `<button onclick="document.getElementById('chat-input').focus()" class="mt-2 text-xs text-[#6366f1] underline hover:no-underline">Tentar novamente</button>`
        : '';
    div.innerHTML = `
        <div class="rounded-2xl rounded-tl-sm border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700 max-w-[85%]">
            <div class="flex items-center gap-1.5 font-medium mb-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Ocorreu um problema
            </div>
            <div>${msg}</div>
            ${retryBtn}
        </div>`;
    container.appendChild(div);
    document.getElementById('chat-end').scrollIntoView({ behavior: 'smooth' });
}

let fallbackBannerShown = false;
function showFallbackBanner(msg) {
    if (fallbackBannerShown) return;
    fallbackBannerShown = true;
    const banner = document.createElement('div');
    banner.className = 'mx-auto max-w-xl mb-2';
    banner.innerHTML = `
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-2.5 text-xs text-yellow-800 flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 mt-0.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>${msg} O assistente está operando em modo manual — suas respostas ainda serão salvas normalmente.</span>
        </div>`;
    document.getElementById('messages-container').insertBefore(
        banner,
        document.getElementById('messages-container').firstChild
    );
}

document.getElementById('chat-send').addEventListener('click', send);
document.getElementById('chat-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
});


</script>
</body>
</html>
