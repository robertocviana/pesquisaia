<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\Auth;

/**
 * AuthService — Lógica de negócio de autenticação.
 */
class AuthService
{
    /**
     * Autentica o usuário. Retorna o array do usuário ou lança RuntimeException.
     */
    public function authenticate(string $email, string $password): array
    {
        $email = trim(strtolower($email));

        if (empty($email) || empty($password)) {
            throw new \RuntimeException('E-mail e senha são obrigatórios.');
        }

        $user = User::findByEmail($email);

        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            throw new \RuntimeException('Credenciais inválidas. Verifique seu e-mail e senha.');
        }

        Auth::login($user);
        return $user;
    }

    /**
     * Registra um novo usuário. Lança RuntimeException em caso de erro.
     */
    public function register(string $name, string $email, string $password, string $confirm): array
    {
        $name     = trim($name);
        $email    = trim(strtolower($email));
        $password = trim($password);
        $confirm  = trim($confirm);

        if (empty($name)) {
            throw new \RuntimeException('O nome é obrigatório.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('E-mail inválido.');
        }

        if (strlen($password) < 8) {
            throw new \RuntimeException('A senha deve ter no mínimo 8 caracteres.');
        }

        if ($password !== $confirm) {
            throw new \RuntimeException('As senhas não coincidem.');
        }

        try {
            $id = User::create($name, $email, $password);
        } catch (\RuntimeException $e) {
            throw $e; // E-mail duplicado
        }

        $user = User::findById($id);
        Auth::login($user);
        return $user;
    }
}
