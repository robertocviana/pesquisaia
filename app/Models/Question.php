<?php

namespace App\Models;

use App\Database;

/**
 * Question — CRUD de perguntas vinculadas a uma pesquisa.
 */
class Question
{
    /** Retorna as perguntas de uma pesquisa ordenadas por order_index. */
    public static function findBySurvey(int $surveyId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM questions WHERE survey_id = ? ORDER BY order_index ASC, id ASC'
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    /** Retorna uma pergunta pelo ID. */
    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM questions WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Cria uma pergunta e retorna o ID. */
    public static function create(int $surveyId, string $text, int $orderIndex = 0): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO questions (survey_id, text, order_index) VALUES (?, ?, ?)'
        );
        $stmt->execute([$surveyId, $text, $orderIndex]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** Insere múltiplas perguntas de uma vez (batch insert). */
    public static function createBatch(int $surveyId, array $texts): void
    {
        // Limpa perguntas anteriores da pesquisa (para substituição completa)
        Database::pdo()->prepare('DELETE FROM questions WHERE survey_id = ?')->execute([$surveyId]);

        foreach ($texts as $i => $text) {
            self::create($surveyId, trim($text), $i);
        }
    }

    /** Atualiza o texto de uma pergunta. */
    public static function update(int $id, int $surveyId, string $text): void
    {
        Database::pdo()->prepare(
            'UPDATE questions SET text = ? WHERE id = ? AND survey_id = ?'
        )->execute([$text, $id, $surveyId]);
    }

    /** Remove uma pergunta. */
    public static function delete(int $id, int $surveyId): void
    {
        Database::pdo()->prepare(
            'DELETE FROM questions WHERE id = ? AND survey_id = ?'
        )->execute([$id, $surveyId]);
    }

    /** Substitui todas as perguntas da pesquisa pelos arrays fornecidos. */
    public static function sync(int $surveyId, array $questions): void
    {
        // $questions = [ ['id' => ..., 'text' => ...], ... ] ou [ ['text' => ...] ]
        Database::pdo()->prepare('DELETE FROM questions WHERE survey_id = ?')->execute([$surveyId]);
        foreach ($questions as $i => $q) {
            self::create($surveyId, trim($q['text'] ?? ''), $i);
        }
    }
}
