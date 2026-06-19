<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Response;

class ResponseController
{
    // ─── GET /pesquisas/respostas?id=X ───────────────────────────────────────
    public function index(): void
    {
        Auth::requireAuth();

        $userId  = Auth::id();
        $id      = (int) ($_GET['id'] ?? 0);
        $survey  = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $respondents = Respondent::findBySurvey($id);
        $title       = 'Respostas — ' . $survey['name'];
        $currentPath = '/pesquisas';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/surveys/respostas.php';
    }

    // ─── GET /pesquisas/resposta?id=X&rid=Y ──────────────────────────────────
    public function show(): void
    {
        Auth::requireAuth();

        $userId      = Auth::id();
        $id          = (int) ($_GET['id'] ?? 0);
        $rid         = (int) ($_GET['rid'] ?? 0);
        $survey      = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $respondent = Respondent::findById($rid);

        // Verificar que o respondente pertence à pesquisa
        if (!$respondent || (int) $respondent['survey_id'] !== $id) {
            header('Location: /pesquisas/respostas?id=' . $id);
            exit;
        }

        $answers     = Response::findByRespondent($rid);
        $title       = 'Detalhe da resposta';
        $currentPath = '/pesquisas';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/surveys/resposta.php';
    }
}
