<?php

namespace App\Models;

use App\Database;

/**
 * Response — Respostas individuais por pergunta.
 */
class Response
{
    /** Salva uma resposta. */
    public static function save(int $respondentId, int $questionId, string $text): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO responses (respondent_id, question_id, text_response) VALUES (?, ?, ?)'
        );
        $stmt->execute([$respondentId, $questionId, $text]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** Retorna todas as respostas de um respondente com o texto da pergunta. */
    public static function findByRespondent(int $respondentId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT r.*, q.text AS question_text, q.order_index
             FROM responses r
             JOIN questions q ON q.id = r.question_id
             WHERE r.respondent_id = ?
             ORDER BY q.order_index ASC, q.id ASC'
        );
        $stmt->execute([$respondentId]);
        return $stmt->fetchAll();
    }

    /** Retorna todas as respostas de uma pesquisa inteira (para relatório). */
    public static function findBySurvey(int $surveyId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT r.*, q.text AS question_text, q.order_index,
                    rsp.name AS respondent_name, rsp.token AS respondent_token
             FROM responses r
             JOIN questions q ON q.id = r.question_id
             JOIN respondents rsp ON rsp.id = r.respondent_id
             WHERE q.survey_id = ?
             ORDER BY rsp.id ASC, q.order_index ASC'
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    /** Verifica se uma pergunta já foi respondida por um respondente. */
    public static function exists(int $respondentId, int $questionId): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM responses WHERE respondent_id = ? AND question_id = ?'
        );
        $stmt->execute([$respondentId, $questionId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Conta quantas perguntas o respondente já respondeu. */
    public static function countByRespondent(int $respondentId): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM responses WHERE respondent_id = ?'
        );
        $stmt->execute([$respondentId]);
        return (int) $stmt->fetchColumn();
    }
}
