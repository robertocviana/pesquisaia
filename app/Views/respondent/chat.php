<?php
$surveyId = htmlspecialchars($_GET['id'] ?? 's-001');
$questions = $survey['questions'];
$total = count($questions);
require BASE_PATH . '/app/Views/templates/header.php';
?>

<div class="min-h-screen flex flex-col bg-[#f3f4f6]/30">
    <!-- Header -->
    <header class="bg-white border-b border-[#e5e7eb] px-5 py-3 flex items-center gap-3 shrink-0">
        <div class="w-9 h-9 rounded-full bg-[#6366f1] flex items-center justify-center">
            <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm text-[#1e1b4b] truncate"><?= htmlspecialchars($survey['name']) ?></div>
            <div id="question-counter" class="text-xs text-[#6b7280]">Pergunta 1 de <?= $total ?></div>
        </div>
    </header>

    <!-- Barra de progresso -->
    <div class="h-1 bg-[#e5e7eb] shrink-0">
        <div id="progress-bar" class="h-full bg-[#6366f1] transition-all" style="width:0%"></div>
    </div>

    <!-- Mensagens -->
    <div id="chat-scroll" class="flex-1 overflow-y-auto p-4">
        <div class="max-w-xl mx-auto space-y-3" id="messages-container"></div>
        <div id="chat-end"></div>
    </div>

    <!-- Input -->
    <div class="border-t border-[#e5e7eb] bg-white p-3 shrink-0">
        <div class="max-w-xl mx-auto flex items-end gap-2">
            <textarea id="chat-input" rows="1" placeholder="Digite sua resposta..."
                class="flex-1 resize-none rounded-full border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"></textarea>
            <button id="chat-send" class="rounded-full bg-[#6366f1] p-3 text-white hover:opacity-90 transition">
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

const questions = <?= json_encode(array_values(array_column($questions, 'text'))) ?>;
const total = questions.length;
const surveyId = '<?= $surveyId ?>';
let step = 0;

function scrollToEnd() {
    document.getElementById('chat-end').scrollIntoView({ behavior: 'smooth' });
}

function addMessage(role, text) {
    const container = document.getElementById('messages-container');
    const div = document.createElement('div');
    if (role === 'assistant') {
        div.className = 'flex justify-start';
        div.innerHTML = `<div class="max-w-[80%] rounded-2xl rounded-tl-sm bg-white border border-[#e5e7eb] px-4 py-2.5 text-sm shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] text-[#1e1b4b]">${text}</div>`;
    } else {
        div.className = 'flex justify-end';
        div.innerHTML = `<div class="max-w-[80%] rounded-2xl rounded-tr-sm bg-[#6366f1] px-4 py-2.5 text-sm text-white">${text}</div>`;
    }
    container.appendChild(div);
    scrollToEnd();
}

function updateProgress() {
    const pct = total > 0 ? Math.min(100, Math.round((step / total) * 100)) : 0;
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('question-counter').textContent = `Pergunta ${Math.min(step + 1, total)} de ${total}`;
}

function send() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    addMessage('user', text);

    setTimeout(() => {
        const nextStep = step + 1;
        if (nextStep >= total) {
            window.location.href = '/r/concluido?id=' + surveyId;
            return;
        }
        step = nextStep;
        addMessage('assistant', questions[step]);
        updateProgress();
    }, 500);
}

document.getElementById('chat-send').addEventListener('click', send);
document.getElementById('chat-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
});

// Inicia com as primeiras mensagens da IA
if (total > 0) {
    setTimeout(() => { addMessage('assistant', 'Oi! Obrigada por participar 😊'); }, 300);
    setTimeout(() => { addMessage('assistant', questions[0]); updateProgress(); }, 800);
} else {
    addMessage('assistant', 'Esta pesquisa não tem perguntas ainda.');
}
</script>
</body>
</html>
