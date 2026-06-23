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

    /** Retorna o array com os dados do usuário logado (name, email, plan). */
    public static function user(): ?array
    {
        if (!self::isLoggedIn()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name']  ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'plan'  => $_SESSION['user_plan']  ?? 'trial',
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
    }

    /** Destrói a sessão do usuário. */
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
