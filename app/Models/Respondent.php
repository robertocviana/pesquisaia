<?php

namespace App\Models;

use App\Database;

/**
 * Respondent — Respondentes anônimos identificados por token UUID.
 */
class Respondent
{
    /** Busca ou cria um respondente pelo token de cookie. */
    public static function findOrCreate(int $surveyId, string $token): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM respondents WHERE survey_id = ? AND token = ? LIMIT 1'
        );
        $stmt->execute([$surveyId, $token]);
        $row = $stmt->fetch();

        if ($row) return $row;

        Database::pdo()->prepare(
            "INSERT INTO respondents (survey_id, token, status) VALUES (?, ?, 'em_andamento')"
        )->execute([$surveyId, $token]);

        return [
            'id'       => (int) Database::pdo()->lastInsertId(),
            'survey_id'=> $surveyId,
            'token'    => $token,
            'name'     => null,
            'status'   => 'em_andamento',
        ];
    }

    /** Busca pelo token apenas. */
    public static function findByToken(string $token): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM respondents WHERE token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Busca pelo ID. */
    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM respondents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Atualiza o nome do respondente. */
    public static function setName(int $id, string $name): void
    {
        Database::pdo()->prepare(
            'UPDATE respondents SET name = ? WHERE id = ?'
        )->execute([$name, $id]);
    }

    /** Marca o respondente como concluído. */
    public static function complete(int $id): void
    {
        Database::pdo()->prepare(
            "UPDATE respondents SET status = 'concluida' WHERE id = ?"
        )->execute([$id]);
    }

    /** Lista todos os respondentes de uma pesquisa. */
    public static function findBySurvey(int $surveyId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM respondents WHERE survey_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    /** Gera um token UUID v4 simplificado. */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /** Conta respondentes concluídos de uma pesquisa. */
    public static function countCompleted(int $surveyId): int
    {
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM respondents WHERE survey_id = ? AND status = 'concluida'"
        );
        $stmt->execute([$surveyId]);
        return (int) $stmt->fetchColumn();
    }
}
