<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-3xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/nova" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao chat
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Revisão da pesquisa</h1>
            <p class="text-sm text-[#6b7280] mt-1">Ajuste as informações antes de publicar.</p>
        </div>
    </div>

    <!-- Dados gerais -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <h2 class="font-semibold text-[#1e1b4b] mb-4">Dados gerais</h2>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Nome da pesquisa</label>
                <input type="text" id="survey-name-input" value="<?= htmlspecialchars($survey['name']) ?>"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Objetivo</label>
                <textarea id="survey-objective-input" rows="2"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]"><?= htmlspecialchars($survey['objective']) ?></textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-[#1e1b4b]">Público-alvo</label>
                <input type="text" id="survey-audience-input" value="<?= htmlspecialchars($survey['audience']) ?>"
                    class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            </div>
        </div>
    </section>

    <!-- Perguntas -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-[#1e1b4b]">Perguntas</h2>
            <button type="button" onclick="addQuestion()" class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-1.5 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Adicionar pergunta
            </button>
        </div>
        <ul class="space-y-2" id="questions-list">
            <!-- Renderizado dinamicamente via JS -->
        </ul>
    </section>

    <!-- Ações -->
    <div class="flex justify-end gap-2">
        <button type="button" id="save-btn" onclick="saveDraft(true)"
           class="inline-flex items-center gap-2 rounded-lg border border-[#e5e7eb] bg-white px-4 py-2.5 text-sm hover:bg-[#f3f4f6] transition text-[#1e1b4b]">
            <i data-lucide="save" class="w-4 h-4"></i> Salvar rascunho
        </button>
        <form id="publish-form" method="POST" action="/pesquisas/publicar" onsubmit="publishSurvey(event)">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="survey_id" value="<?= (int) $survey['id'] ?>">
            <button type="submit"
               class="inline-flex items-center gap-2 rounded-lg bg-[#6366f1] px-4 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                <i data-lucide="rocket" class="w-4 h-4"></i> Publicar pesquisa
            </button>
        </form>
    </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Inicializar perguntas a partir do PHP
let questions = <?= json_encode(array_map(fn($q) => ['text' => $q['text']], $questions)) ?>;

document.addEventListener('DOMContentLoaded', () => {
    renderQuestions();
    
    // Configurar reordenação drag-and-drop
    new Sortable(document.getElementById('questions-list'), {
        handle: '.grip-handle',
        animation: 150,
        ghostClass: 'bg-indigo-50',
        onEnd: () => {
            reorderQuestionsFromDom();
        }
    });
});

function escapeHtml(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function renderQuestions() {
    const list = document.getElementById('questions-list');
    list.innerHTML = '';
    
    if (questions.length === 0) {
        list.innerHTML = '<li class="text-sm text-center text-[#6b7280] py-6">Nenhuma pergunta ainda. Adicione acima.</li>';
        return;
    }
    
    questions.forEach((q, index) => {
        const li = document.createElement('li');
        li.className = 'flex items-start gap-2 rounded-lg border border-[#e5e7eb] p-3 group bg-white';
        li.dataset.index = index;
        li.innerHTML = `
            <i class="grip-handle w-4 h-4 text-[#9ca3af] mt-0.5 cursor-grab shrink-0 hover:text-[#6b7280] transition" data-lucide="grip-vertical"></i>
            <span class="text-xs text-[#6b7280] mt-0.5 w-6 shrink-0">${index + 1}.</span>
            <div class="flex-1 min-w-0 pr-2">
                <span class="q-text text-sm text-[#1e1b4b] break-words leading-relaxed">${escapeHtml(q.text)}</span>
                <input type="text" class="q-input hidden w-full rounded border border-[#e5e7eb] bg-white px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]" value="${escapeHtml(q.text)}">
            </div>
            <button type="button" class="btn-edit p-1 rounded hover:bg-[#f3f4f6] opacity-0 group-hover:opacity-100 transition" title="Editar" onclick="editQuestion(${index})">
                <i data-lucide="pencil" class="w-3.5 h-3.5 text-[#6b7280]"></i>
            </button>
            <button type="button" class="btn-delete p-1 rounded hover:bg-[#f3f4f6] opacity-0 group-hover:opacity-100 transition" title="Excluir" onclick="deleteQuestion(${index})">
                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-[#ef4444]"></i>
            </button>
        `;
        list.appendChild(li);
    });
    lucide.createIcons();
}

function addQuestion() {
    questions.push({ text: "Nova pergunta" });
    renderQuestions();
    editQuestion(questions.length - 1);
}

function editQuestion(index) {
    const li = document.querySelector(`li[data-index="${index}"]`);
    if (!li) return;
    const span = li.querySelector('.q-text');
    const input = li.querySelector('.q-input');
    
    span.classList.add('hidden');
    input.classList.remove('hidden');
    input.focus();
    input.select();
    
    let finished = false;
    const saveEdit = () => {
        if (finished) return;
        finished = true;
        const newText = input.value.trim();
        if (newText) {
            questions[index].text = newText;
        }
        renderQuestions();
    };
    
    input.onblur = saveEdit;
    input.onkeydown = (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveEdit();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            finished = true;
            renderQuestions();
        }
    };
}

function deleteQuestion(index) {
    if (confirm("Deseja realmente remover esta pergunta?")) {
        questions.splice(index, 1);
        renderQuestions();
    }
}

function reorderQuestionsFromDom() {
    const listItems = document.querySelectorAll('#questions-list li');
    const newQuestions = [];
    listItems.forEach(li => {
        const index = parseInt(li.dataset.index);
        if (!isNaN(index) && questions[index]) {
            newQuestions.push(questions[index]);
        }
    });
    questions = newQuestions;
    renderQuestions();
}

async function saveDraft(showFeedback = true) {
    const btn = document.getElementById('save-btn');
    const originalText = btn.innerHTML;
    
    if (showFeedback) {
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin border-2 border-indigo-600 border-t-transparent rounded-full w-4 h-4 mr-1.5 inline-block shrink-0"></span> Salvando...';
    }
    
    const name = document.getElementById('survey-name-input').value.trim();
    const objective = document.getElementById('survey-objective-input').value.trim();
    const audience = document.getElementById('survey-audience-input').value.trim();
    
    const formData = new FormData();
    formData.append('survey_id', <?= $survey['id'] ?>);
    formData.append('name', name);
    formData.append('objective', objective);
    formData.append('audience', audience);
    formData.append('questions', JSON.stringify(questions));
    formData.append('_csrf', '<?= \App\Helpers\Csrf::token() ?>');
    
    try {
        const response = await fetch('/pesquisas/revisao/salvar', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            if (showFeedback) {
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 text-[#22c55e]"><path d="M20 6 9 17l-5-5"/></svg> Salvo!';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    lucide.createIcons();
                }, 1500);
            }
            return true;
        } else {
            alert('Erro ao salvar rascunho: ' + (result.error || 'Erro desconhecido'));
        }
    } catch (err) {
        console.error(err);
        alert('Erro de rede ao salvar o rascunho.');
    }
    
    if (showFeedback) {
        btn.disabled = false;
        btn.innerHTML = originalText;
        lucide.createIcons();
    }
    return false;
}

async function publishSurvey(e) {
    e.preventDefault();
    const success = await saveDraft(false);
    if (success) {
        document.getElementById('publish-form').submit();
    } else {
        alert('Não foi possível salvar as alterações para publicar.');
    }
}
</script>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
