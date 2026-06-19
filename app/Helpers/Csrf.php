<?php

namespace App\Helpers;

/**
 * Csrf — Proteção CSRF simples via token de sessão.
 */
class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /** Gera (ou retorna o existente) token CSRF. */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** Renderiza o input hidden com o token CSRF. */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }

    /** Valida o token enviado. Aborta com 403 se inválido. */
    public static function validate(): void
    {
        $token   = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session = $_SESSION[self::SESSION_KEY] ?? '';

        if (!hash_equals($session, $token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token inválido.']));
        }
    }

    /** Regenera o token (use após submit bem-sucedido). */
    public static function regenerate(): void
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}
