<?php

require __DIR__ . '/../app/bootstrap.php';

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Session
$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params([
    'lifetime' => 2592000,
    'path'     => '/',
    'domain'   => null,
    'secure'   => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Router
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// ─── Raiz ────────────────────────────────────────────────────────────────────
if ($uri === '/' || $uri === '/index.php') {
    header('Location: /login');
    exit;
}

// ─── Auth ────────────────────────────────────────────────────────────────────
if ($uri === '/login') {
    $c = new \App\Controllers\AuthController();
    $method === 'POST' ? $c->handleLogin() : $c->login();

} elseif ($uri === '/cadastro') {
    $c = new \App\Controllers\AuthController();
    $method === 'POST' ? $c->handleCadastro() : $c->cadastro();

} elseif ($uri === '/logout') {
    $c = new \App\Controllers\AuthController();
    $c->logout();

// ─── Dashboard ───────────────────────────────────────────────────────────────
} elseif ($uri === '/dashboard') {
    $c = new \App\Controllers\DashboardController();
    $c->index();

// ─── Pesquisas ───────────────────────────────────────────────────────────────
} elseif ($uri === '/pesquisas' || $uri === '/pesquisas/') {
    $c = new \App\Controllers\SurveyController();
    $c->index();

} elseif ($uri === '/pesquisas/nova') {
    $c = new \App\Controllers\SurveyController();
    $c->nova();

} elseif ($uri === '/pesquisas/nova/chat') {
    $c = new \App\Controllers\AiController();
    $c->chat();

} elseif ($uri === '/pesquisas/detalhe') {
    $c = new \App\Controllers\SurveyController();
    $c->detalhe();

} elseif ($uri === '/pesquisas/revisao') {
    $c = new \App\Controllers\SurveyController();
    $c->revisao();

} elseif ($uri === '/pesquisas/revisao/salvar') {
    $c = new \App\Controllers\SurveyController();
    $c->handleRevisaoSalvar();

} elseif ($uri === '/pesquisas/publicar') {
    $c = new \App\Controllers\SurveyController();
    $c->handlePublicar();

} elseif ($uri === '/pesquisas/encerrar') {
    $c = new \App\Controllers\SurveyController();
    $c->handleEncerrar();

} elseif ($uri === '/pesquisas/duplicar') {
    $c = new \App\Controllers\SurveyController();
    $c->handleDuplicar();

} elseif ($uri === '/pesquisas/excluir') {
    $c = new \App\Controllers\SurveyController();
    $c->handleExcluir();

} elseif ($uri === '/pesquisas/relatorio') {
    $c = new \App\Controllers\SurveyController();
    $c->relatorio();

} elseif ($uri === '/pesquisas/relatorio/gerar') {
    $c = new \App\Controllers\SurveyController();
    $c->handleRelatorioGerar();

} elseif ($uri === '/pesquisas/exportar') {
    $c = new \App\Controllers\SurveyController();
    $c->exportar();

// ─── Respostas ────────────────────────────────────────────────────────────────
} elseif ($uri === '/pesquisas/respostas') {
    $c = new \App\Controllers\ResponseController();
    $c->index();

} elseif ($uri === '/pesquisas/respostas/gerar') {
    $c = new \App\Controllers\ResponseController();
    $c->handleGenerateResponses();

} elseif ($uri === '/pesquisas/resposta') {
    $c = new \App\Controllers\ResponseController();
    $c->show();

// ─── Configurações ────────────────────────────────────────────────────────────
} elseif ($uri === '/configuracoes') {
    $c = new \App\Controllers\SettingsController();
    $c->index();

} elseif ($uri === '/configuracoes/perfil' && $method === 'POST') {
    $c = new \App\Controllers\SettingsController();
    $c->handleUpdatePerfil();

} elseif ($uri === '/configuracoes/seguranca' && $method === 'POST') {
    $c = new \App\Controllers\SettingsController();
    $c->handleUpdateSeguranca();

// ─── Área Pública — Respondente (rotas dinâmicas /r/{slug}) ──────────────────
} elseif (preg_match('#^/r/([a-f0-9]{16})$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    $c = new \App\Controllers\RespondentController();
    $c->intro();

} elseif (preg_match('#^/r/([a-f0-9]{16})/chat$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    $c = new \App\Controllers\RespondentController();
    $c->chat();

} elseif (preg_match('#^/r/([a-f0-9]{16})/concluido$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    $c = new \App\Controllers\RespondentController();
    $c->concluido();

} elseif ($uri === '/r/responder') {
    $c = new \App\Controllers\RespondentController();
    $c->responder();

// ─── 404 ─────────────────────────────────────────────────────────────────────
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>404 — PesquisaIA</title></head>';
    echo '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;">';
    echo '<div style="text-align:center"><h1 style="font-size:4rem;margin:0">404</h1><p>Página não encontrada.</p>';
    echo '<a href="/dashboard" style="color:#6366f1">Voltar ao início</a></div></body></html>';
}
