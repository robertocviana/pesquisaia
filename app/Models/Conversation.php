<?php

namespace App\Models;

use App\Database;

/**
 * Conversation — Histórico do chat de criação da pesquisa.
 */
class Conversation
{
    /** Retorna todo o histórico de uma pesquisa, ordenado cronologicamente. */
    public static function findBySurvey(int $surveyId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT role, content FROM conversations WHERE survey_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    /** Adiciona uma mensagem ao histórico. */
    public static function add(int $surveyId, string $role, string $content): void
    {
        Database::pdo()->prepare(
            'INSERT INTO conversations (survey_id, role, content) VALUES (?, ?, ?)'
        )->execute([$surveyId, $role, $content]);
    }

    /** Limpa o histórico de uma pesquisa (recomeço do chat). */
    public static function clear(int $surveyId): void
    {
        Database::pdo()->prepare(
            'DELETE FROM conversations WHERE survey_id = ?'
        )->execute([$surveyId]);
    }
}
