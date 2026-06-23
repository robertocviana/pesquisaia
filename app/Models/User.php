<?php

namespace App\Models;

use App\Database;

/**
 * User — Model de usuário com autenticação segura.
 */
class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, password_hash, plan FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, name, email, plan FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Cria um novo usuário.
     * @return int ID do usuário criado.
     * @throws \RuntimeException se o e-mail já existir.
     */
    public static function create(string $name, string $email, string $password): int
    {
        if (self::findByEmail($email)) {
            throw new \RuntimeException('E-mail já cadastrado.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** Verifica a senha em texto plano contra o hash armazenado. */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /** Atualiza nome e/ou e-mail do usuário. */
    public static function update(int $id, string $name, string $email): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE users SET name = ?, email = ? WHERE id = ?'
        );
        $stmt->execute([$name, $email, $id]);
    }

    /** Atualiza a senha do usuário. */
    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = Database::pdo()->prepare(
            'UPDATE users SET password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$hash, $id]);
    }

    /** Atualiza o plano do usuário. */
    public static function updatePlan(int $id, string $plan): void
    {
        if (!in_array($plan, ['trial', 'pro'], true)) {
            throw new \InvalidArgumentException('Plano inválido.');
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE users SET plan = ? WHERE id = ?'
        );
        $stmt->execute([$plan, $id]);
    }
}
