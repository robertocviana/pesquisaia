<?php require BASE_PATH . '/app/Views/templates/header.php'; ?>
<?php require BASE_PATH . '/app/Views/templates/sidebar.php'; ?>

<div class="max-w-5xl mx-auto p-6 sm:p-10">
    <a href="/pesquisas/detalhe?id=<?= $survey['id'] ?>" class="inline-flex items-center gap-1.5 text-sm text-[#6b7280] hover:text-[#1e1b4b] mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar à pesquisa
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#1e1b4b]">Relatório</h1>
            <p class="text-sm text-[#6b7280] mt-1">Análise consolidada — <?= htmlspecialchars($survey['name']) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <button class="inline-flex items-center gap-1.5 rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm hover:bg-[#f3f4f6] transition">
                <i data-lucide="share-2" class="w-4 h-4"></i> Compartilhar
            </button>
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-[#6366f1] px-3 py-2 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
                <i data-lucide="download" class="w-4 h-4"></i> Exportar PDF
            </button>
        </div>
    </div>

    <!-- Resumo executivo -->
    <section class="rounded-xl border border-[#e5e7eb] bg-gradient-to-br from-[#eef2ff]/60 to-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#6366f1]"></i>
            <h2 class="font-semibold text-[#1e1b4b]">Resumo executivo</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-4 mb-4">
            <?php
            $kpis = [
                ['label' => 'Total de respostas',  'value' => max(count($survey['responses']), 47)],
                ['label' => 'Taxa de conclusão',    'value' => '87%'],
                ['label' => 'Satisfação média',     'value' => '8.4 / 10'],
            ];
            foreach ($kpis as $kpi): ?>
            <div class="rounded-lg bg-white border border-[#e5e7eb] p-4">
                <div class="text-xs uppercase tracking-wide text-[#6b7280]"><?= $kpi['label'] ?></div>
                <div class="mt-1.5 text-2xl font-semibold tracking-tight text-[#1e1b4b]"><?= $kpi['value'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-sm text-[#6b7280] leading-relaxed">
            A maioria dos respondentes está satisfeita com o atendimento, destacando a velocidade do suporte.
            A principal oportunidade está em melhorar a comunicação sobre status de entregas, mencionada em
            cerca de 19% das respostas.
        </p>
    </section>

    <!-- Temas identificados -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
        <h2 class="font-semibold text-[#1e1b4b] mb-4">Temas identificados</h2>
        <ul class="space-y-2">
            <?php
            $insights = [
                ['title' => 'Atendimento é o ponto forte mais citado',              'count' => 14],
                ['title' => 'Tempo de entrega aparece como principal fricção',       'count' => 9],
                ['title' => 'Recomendação espontânea é alta entre usuários >30 dias','count' => 7],
            ];
            foreach ($insights as $i): ?>
            <li class="flex items-center gap-3 p-3 rounded-lg border border-[#e5e7eb]">
                <i data-lucide="trending-up" class="w-4 h-4 text-[#6366f1]"></i>
                <span class="flex-1 text-sm text-[#1e1b4b]"><?= htmlspecialchars($i['title']) ?></span>
                <span class="text-xs text-[#6b7280]"><?= $i['count'] ?> menções</span>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Gráficos -->
    <div class="grid lg:grid-cols-2 gap-5 mb-6">
        <!-- Distribuição -->
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-4">Distribuição das respostas</h3>
            <div class="space-y-3">
                <?php
                $distribution = [
                    ['label' => 'Muito satisfeito', 'value' => 52],
                    ['label' => 'Satisfeito',        'value' => 28],
                    ['label' => 'Neutro',            'value' => 12],
                    ['label' => 'Insatisfeito',      'value' => 8],
                ];
                foreach ($distribution as $d): ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-[#1e1b4b]"><?= $d['label'] ?></span>
                        <span class="text-[#6b7280]"><?= $d['value'] ?>%</span>
                    </div>
                    <div class="h-2 rounded-full bg-[#f3f4f6] overflow-hidden">
                        <div class="h-full bg-[#6366f1]" style="width:<?= $d['value'] ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Evolução -->
        <div class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
            <h3 class="font-semibold text-[#1e1b4b] mb-4">Evolução das respostas</h3>
            <?php $evolution = [3, 5, 8, 7, 12, 14, 10, 16, 19, 22, 18, 25]; $maxV = max($evolution); ?>
            <div class="h-40 flex items-end gap-1.5">
                <?php foreach ($evolution as $v): ?>
                <div class="flex-1 rounded-t bg-[#6366f1]/80 hover:bg-[#6366f1] transition cursor-pointer"
                     style="height:<?= round(($v / $maxV) * 100) ?>%"
                     title="<?= $v ?> respostas"></div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 text-xs text-[#6b7280]">Últimos 12 dias</div>
        </div>
    </div>

    <!-- Comentários relevantes -->
    <section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)]">
        <h2 class="font-semibold text-[#1e1b4b] mb-4">Comentários relevantes</h2>
        <div class="space-y-3">
            <?php
            $highlights = [
                ['who' => 'Maria Silva',  'quote' => 'Suporte resolveu na primeira tentativa, foi rápido e simpático.'],
                ['who' => 'João Pereira', 'quote' => 'Produto bom, mas a comunicação sobre o status do pedido precisa melhorar.'],
                ['who' => 'Ana Costa',    'quote' => 'Tudo funcionou como esperado, recomendo de olhos fechados.'],
            ];
            foreach ($highlights as $h): ?>
            <div class="flex gap-3 p-4 rounded-lg bg-[#f3f4f6]/40">
                <i data-lucide="quote" class="w-4 h-4 text-[#6366f1] shrink-0 mt-1"></i>
                <div>
                    <p class="text-sm leading-relaxed text-[#1e1b4b]">"<?= htmlspecialchars($h['quote']) ?>"</p>
                    <p class="text-xs text-[#6b7280] mt-2">— <?= htmlspecialchars($h['who']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require BASE_PATH . '/app/Views/templates/footer.php'; ?>
