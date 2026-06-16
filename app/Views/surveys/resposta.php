<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-3xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/respostas?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Detalhe da resposta</h1>
            <p class="text-sm text-[#6b7280] mt-1">Entrevista completa em "<?= htmlspecialchars($survey['name']) ?>"</p>
        </div>
    </div>

    <?php if ($response): ?>
    <!-- Meta info -->
    <div class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <div class="flex flex-wrap gap-6 text-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-[#6b7280]"></i>
                <div>
                    <div class="text-xs text-[#6b7280]">Respondente</div>
                    <div class="font-medium text-[#1e1b4b]"><?= htmlspecialchars($response['respondent']) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-[#6b7280]"></i>
                <div>
                    <div class="text-xs text-[#6b7280]">Data</div>
                    <div class="font-medium text-[#1e1b4b]"><?= \App\Helpers\MockData::formatDate($response['date']) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#6b7280]"></i>
                <div>
                    <div class="text-xs text-[#6b7280]">Duração</div>
                    <div class="font-medium text-[#1e1b4b]"><?= $response['durationMin'] ?> min</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversa Q&A -->
    <div class="space-y-5">
        <?php foreach ($survey['questions'] as $q):
            $answer = null;
            foreach ($response['answers'] as $a) {
                if ($a['questionId'] === $q['id']) { $answer = $a; break; }
            }
        ?>
        <div class="space-y-2">
            <!-- Pergunta (IA) -->
            <div class="flex gap-3">
                <div class="w-8 h-8 shrink-0 rounded-lg bg-[#f3f4f6] flex items-center justify-center text-xs font-medium text-[#6b7280]">
                    IA
                </div>
                <div class="max-w-[80%] rounded-2xl rounded-tl-sm bg-[#f3f4f6] px-4 py-2.5 text-sm text-[#1e1b4b]">
                    <?= htmlspecialchars($q['text']) ?>
                </div>
            </div>
            <!-- Resposta (usuário) -->
            <?php if ($answer): ?>
            <div class="flex justify-end">
                <div class="max-w-[80%] rounded-2xl rounded-tr-sm bg-[#6366f1] px-4 py-2.5 text-sm text-white">
                    <?= htmlspecialchars($answer['text']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="rounded-xl border border-[#e5e7eb] bg-white p-10 text-center text-[#6b7280]">
        Resposta não encontrada.
    </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
