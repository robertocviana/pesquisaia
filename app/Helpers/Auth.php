<?php

namespace App\Helpers;

/**
 * Auth — Helpers de autenticação de sessão.
 */
class Auth
{
    /** Redireciona para /login se o usuário não estiver autenticado. */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /** Redireciona para /dashboard se o usuário JÁ estiver autenticado. */
    public static function requireGuest(): void
    {
        if (self::isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
    }

    /** Verifica se há um usuário autenticado na sessão. */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
    }

    /** Retorna o ID do usuário logado. */
    public static function id(): ?int
    {
        return self::isLoggedIn() ? (int) $_SESSION['user_id'] : null;
    }

    /** Retorna o array com os dados do usuário logado (name, email, plan, role). */
    public static function user(): ?array
    {
        if (!self::isLoggedIn()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'plan'  => $_SESSION['user_plan']  ?? 'trial',
            'role'  => $_SESSION['user_role']  ?? 'user',
        ];
    }

    /** Define a sessão do usuário após login bem-sucedido. */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_plan']  = $user['plan'] ?? 'trial';
        $_SESSION['user_role']  = $user['role'] ?? 'user';
    }

    /** Verifica se o usuário logado é um administrador. */
    public static function isAdmin(): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        // 1. Verificação por e-mail configurado no .env (alta prioridade, bypass rápido)
        $adminEmails = $_ENV['ADMIN_EMAILS'] ?? getenv('ADMIN_EMAILS') ?? '';
        if (!empty($adminEmails)) {
            $allowedEmails = array_map('trim', explode(',', $adminEmails));
            $userEmail = $_SESSION['user_email'] ?? '';
            if (in_array($userEmail, $allowedEmails, true)) {
                return true;
            }
        }

        // 2. Verificação pelo campo 'role' na sessão
        $role = $_SESSION['user_role'] ?? 'user';
        return $role === 'admin';
    }

    /** Bloqueia o acesso de não-administradores retornando 404 (para não revelar a rota secreta). */
    public static function requireAdmin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if (!self::isAdmin()) {
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>404 — PesquisaIA</title></head>';
            echo '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;">';
            echo '<div style="text-align:center"><h1 style="font-size:4rem;margin:0">404</h1><p>Página não encontrada.</p>';
            echo '<a href="/dashboard" style="color:#6366f1">Voltar ao início</a></div></body></html>';
            exit;
        }
    }

    /** Destrói a sessão do usuário. */
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
