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
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ─── Área Pública — Auth ────────────────────────────────────────────────────
if ($uri === '/' || $uri === '/index.php') {
    header('Location: /login');
    exit;

} elseif ($uri === '/login') {
    $c = new \App\Controllers\AuthController();
    $c->login();

} elseif ($uri === '/cadastro') {
    $c = new \App\Controllers\AuthController();
    $c->cadastro();

} elseif ($uri === '/logout') {
    $c = new \App\Controllers\AuthController();
    $c->logout();

// ─── Dashboard ──────────────────────────────────────────────────────────────
} elseif ($uri === '/dashboard') {
    $c = new \App\Controllers\DashboardController();
    $c->index();

// ─── Pesquisas ──────────────────────────────────────────────────────────────
} elseif ($uri === '/pesquisas' || $uri === '/pesquisas/') {
    $c = new \App\Controllers\SurveyController();
    $c->index();

} elseif ($uri === '/pesquisas/nova') {
    $c = new \App\Controllers\SurveyController();
    $c->nova();

} elseif ($uri === '/pesquisas/detalhe') {
    $c = new \App\Controllers\SurveyController();
    $c->detalhe();

} elseif ($uri === '/pesquisas/revisao') {
    $c = new \App\Controllers\SurveyController();
    $c->revisao();

} elseif ($uri === '/pesquisas/relatorio') {
    $c = new \App\Controllers\SurveyController();
    $c->relatorio();

// ─── Respostas ───────────────────────────────────────────────────────────────
} elseif ($uri === '/pesquisas/respostas') {
    $c = new \App\Controllers\ResponseController();
    $c->index();

} elseif ($uri === '/pesquisas/resposta') {
    $c = new \App\Controllers\ResponseController();
    $c->show();

// ─── Configurações ───────────────────────────────────────────────────────────
} elseif ($uri === '/configuracoes') {
    $c = new \App\Controllers\SettingsController();
    $c->index();

// ─── Área Pública — Respondente ──────────────────────────────────────────────
} elseif ($uri === '/r/intro') {
    $c = new \App\Controllers\RespondentController();
    $c->intro();

} elseif ($uri === '/r/chat') {
    $c = new \App\Controllers\RespondentController();
    $c->chat();

} elseif ($uri === '/r/concluido') {
    $c = new \App\Controllers\RespondentController();
    $c->concluido();

// ─── 404 ─────────────────────────────────────────────────────────────────────
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>404 — PesquisaIA</title></head>';
    echo '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;">';
    echo '<div style="text-align:center"><h1 style="font-size:4rem;margin:0">404</h1><p>Página não encontrada.</p>';
    echo '<a href="/dashboard" style="color:#6366f1">Voltar ao início</a></div></body></html>';
}
