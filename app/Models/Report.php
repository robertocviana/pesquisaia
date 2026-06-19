<?php

namespace App\Models;

use App\Database;

/**
 * Report — Cache dos relatórios gerados por IA.
 */
class Report
{
    /** Busca o relatório de uma pesquisa. */
    public static function findBySurvey(int $surveyId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM reports WHERE survey_id = ? LIMIT 1'
        );
        $stmt->execute([$surveyId]);
        $row = $stmt->fetch();
        if (!$row) return null;

        // Decodifica o JSON dos insights
        if (isset($row['insights'])) {
            $row['insights'] = json_decode($row['insights'], true) ?? [];
        }

        return $row;
    }

    /** Salva ou atualiza o relatório (UPSERT). */
    public static function upsert(int $surveyId, string $summary, array $insights): void
    {
        $insightsJson = json_encode($insights, JSON_UNESCAPED_UNICODE);

        Database::pdo()->prepare(
            'INSERT INTO reports (survey_id, summary, insights)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE summary = VALUES(summary), insights = VALUES(insights), generated_at = NOW()'
        )->execute([$surveyId, $summary, $insightsJson]);
    }
}
