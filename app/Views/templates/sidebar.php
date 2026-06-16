<?php
// $currentPath é definido pelo controller antes de incluir este template
$currentPath = $currentPath ?? $_SERVER['REQUEST_URI'];
$nav = [
    ['href' => '/dashboard',   'label' => 'Dashboard',       'icon' => 'layout-dashboard'],
    ['href' => '/pesquisas',   'label' => 'Minhas Pesquisas', 'icon' => 'file-text'],
    ['href' => '/configuracoes','label' => 'Configurações',   'icon' => 'settings'],
];
?>
<div class="min-h-screen flex bg-[#fafafa]">
    <!-- Sidebar desktop -->
    <aside class="hidden md:flex w-64 flex-col border-r border-[#e5e7eb] bg-[#fafafa] p-4 gap-1">
        <a href="/dashboard" class="flex items-center gap-2 px-2 py-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#6366f1] flex items-center justify-center">
                <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
            </div>
            <span class="font-semibold text-[#1e1b4b]">PesquisaIA</span>
        </a>

        <a href="/pesquisas/nova"
           class="mb-4 inline-flex items-center justify-center gap-2 rounded-lg bg-[#6366f1] px-3 py-2.5 text-sm font-medium text-white shadow-[0_20px_40px_-20px_rgb(99_102_241_/_0.35)] hover:opacity-90 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Nova pesquisa
        </a>

        <?php foreach ($nav as $item): ?>
            <?php $active = str_starts_with($currentPath, $item['href']); ?>
            <a href="<?= $item['href'] ?>"
               class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition <?= $active ? 'bg-[#eef2ff] text-[#4338ca] font-medium' : 'text-[#6b7280] hover:bg-[#f3f4f6] hover:text-[#1e1b4b]' ?>">
                <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>

        <div class="mt-auto border-t border-[#e5e7eb] pt-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-[#eef2ff] flex items-center justify-center text-sm font-medium text-[#6366f1]">
                    AC
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">Ana Costa</div>
                    <div class="text-xs text-[#6b7280] truncate">ana@empresa.com</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Conteúdo principal -->
    <main class="flex-1 min-w-0">
