<?php
$slug      = $_GET['slug'] ?? '';
$questions = Question::findBySurvey((int) $survey['id']);
$total     = count($questions);
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
            <div id="question-counter" class="text-xs text-[#6b7280]">Iniciando...</div>
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

const SURVEY_SLUG = '<?= htmlspecialchars($slug) ?>';
const SURVEY_ID   = <?= (int) $survey['id'] ?>;
const RESPONDENT_ID = <?= (int) ($respondent['id'] ?? 0) ?>;

const questions = <?= json_encode(array_values(array_map(fn($q) => ['id' => (int)$q['id'], 'text' => $q['text']], $questions))) ?>;
const total     = questions.length;
const answered  = <?= (int) ($answered ?? 0) ?>;

// Descobrir a primeira pergunta não respondida
let step = answered;

// Controle de nome
let nameCollected = <?= ($respondent['name'] ? 'true' : 'false') ?>;
let pendingName   = false;

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

function updateProgress(currentStep) {
    const pct = total > 0 ? Math.min(100, Math.round((currentStep / total) * 100)) : 0;
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('question-counter').textContent =
        currentStep < total ? `Pergunta ${currentStep + 1} de ${total}` : 'Concluindo...';
}

async function saveAnswer(questionId, text, name = null) {
    try {
        await fetch('/r/responder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                slug:        SURVEY_SLUG,
                question_id: questionId,
                answer:      text,
                name:        name,
            }),
        });
    } catch (e) {
        console.error('Erro ao salvar resposta:', e);
    }
}

async function send() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    input.disabled = true;

    addMessage('user', text);

    // Coletar nome primeiro
    if (!nameCollected && pendingName) {
        nameCollected = true;
        pendingName   = false;
        await saveAnswer(0, '', text); // Salvar nome
        setTimeout(askQuestion, 500);
        input.disabled = false;
        return;
    }

    // Salvar resposta da pergunta atual
    if (step < total) {
        await saveAnswer(questions[step].id, text);
        step++;
    }

    updateProgress(step);

    if (step >= total) {
        // Finalizar
        addMessage('assistant', 'Obrigado pelas suas respostas! 🎉');
        setTimeout(() => {
            window.location.href = '/r/' + SURVEY_SLUG + '/concluido';
        }, 1500);
    } else {
        setTimeout(askQuestion, 500);
    }

    input.disabled = false;
}

function askQuestion() {
    if (step < total) {
        addMessage('assistant', questions[step].text);
        updateProgress(step);
    }
}

// Iniciar conversa
if (total > 0) {
    setTimeout(() => {
        if (!nameCollected) {
            addMessage('assistant', 'Olá! Obrigado por participar 😊 Antes de começar, qual é o seu nome?');
            pendingName = true;
        } else {
            addMessage('assistant', 'Olá! Obrigado por participar 😊 Vamos começar!');
            setTimeout(askQuestion, 800);
        }
    }, 400);
} else {
    addMessage('assistant', 'Esta pesquisa ainda não tem perguntas.');
}

document.getElementById('chat-send').addEventListener('click', send);
document.getElementById('chat-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
});

updateProgress(step);
</script>
</body>
</html>
