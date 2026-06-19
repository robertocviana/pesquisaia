<?php

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Services\SurveyAiService;
use App\Helpers\Auth;

/**
 * AiController — Endpoints AJAX para o chat de criação de pesquisa.
 */
class AiController
{
    /**
     * POST /pesquisas/nova/chat
     * Recebe { survey_id, message } e retorna resposta da IA.
     */
    public function chat(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');

        $input    = json_decode(file_get_contents('php://input'), true) ?? [];
        $surveyId = (int) ($input['survey_id'] ?? $_SESSION['current_survey_id'] ?? 0);
        $message  = trim($input['message'] ?? '');

        if (!$surveyId || !$message) {
            http_response_code(400);
            echo json_encode(['error' => 'survey_id e message são obrigatórios.']);
            exit;
        }

        // Validar CSRF pelo header
        $csrfToken   = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionCsrf = $_SESSION['csrf_token'] ?? '';
        if (!hash_equals($sessionCsrf, $csrfToken)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token inválido.']);
            exit;
        }

        try {
            $service  = new SurveyAiService();
            $result   = $service->chat($surveyId, $message);
            echo json_encode($result);
        } catch (\RuntimeException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit;
    }
}
