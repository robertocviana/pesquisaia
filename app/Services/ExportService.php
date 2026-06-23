<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use App\Models\Respondent;

/**
 * ExportService — Exportação de dados de pesquisa em CSV e PDF.
 */
class ExportService
{
    /**
     * Envia o CSV como download para o navegador.
     */
    public function exportCsv(int $surveyId, int $userId): void
    {
        $survey = Survey::findByIdForUser($surveyId, $userId);
        if (!$survey) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $questions   = Question::findBySurvey($surveyId);
        $respondents = Respondent::findBySurvey($surveyId);
        $responses   = Response::findBySurvey($surveyId);

        // Indexar respostas por [respondent_id][question_id]
        $indexed = [];
        foreach ($responses as $r) {
            $indexed[$r['respondent_id']][$r['question_id']] = $r['text_response'];
        }

        $filename = 'respostas_' . preg_replace('/[^a-z0-9]/i', '_', $survey['name']) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM para UTF-8 no Excel
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // Cabeçalho
        $header = ['Respondente', 'Data', 'Status'];
        foreach ($questions as $q) {
            $header[] = $q['text'];
        }
        fputcsv($out, $header, ';');

        // Linhas
        foreach ($respondents as $rsp) {
            $row = [
                $rsp['name'] ?? ('Respondente #' . $rsp['id']),
                \App\Helpers\DateHelper::format($rsp['created_at'], 'd/m/Y H:i'),
                $rsp['status'] === 'concluida' ? 'Concluída' : 'Em andamento',
            ];
            foreach ($questions as $q) {
                $row[] = $indexed[$rsp['id']][$q['id']] ?? '';
            }
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }

    /**
     * Renderiza a view de impressão para PDF via window.print().
     */
    public function exportPdf(int $surveyId, int $userId): void
    {
        $survey = Survey::findByIdForUser($surveyId, $userId);
        if (!$survey) {
            http_response_code(403);
            exit('Acesso negado.');
        }

        $questions   = Question::findBySurvey($surveyId);
        $respondents = Respondent::findBySurvey($surveyId);
        $responses   = Response::findBySurvey($surveyId);

        // Indexar respostas
        $indexed = [];
        foreach ($responses as $r) {
            $indexed[$r['respondent_id']][$r['question_id']] = $r['text_response'];
        }

        require BASE_PATH . '/app/Views/surveys/export_pdf.php';
        exit;
    }
}
