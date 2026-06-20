<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Response;
use App\Services\ResponseGeneratorService;

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
        
        $flashError   = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require BASE_PATH . '/app/Views/surveys/respostas.php';
    }

    // ─── POST /pesquisas/respostas/gerar ─────────────────────────────────────
    public function handleGenerateResponses(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId   = Auth::id();
        $surveyId = (int) ($_POST['survey_id'] ?? 0);
        $count    = (int) ($_POST['count'] ?? 10);
        // Whitelist para evitar valores arbitrários no parâmetro de estratégia
        $strategyInput = $_POST['strategy'] ?? 'hybrid';
        $strategy = in_array($strategyInput, ['hybrid', 'local'], true) ? $strategyInput : 'local';

        // Garante ownership da pesquisa
        $survey = Survey::findByIdForUser($surveyId, $userId);
        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        if ($survey['status'] !== 'ativa') {
            $_SESSION['flash_error'] = 'Você só pode gerar respostas para pesquisas ativas.';
            header('Location: /pesquisas/respostas?id=' . $surveyId);
            exit;
        }

        // Valida limites
        if ($count < 1 || $count > 100) {
            $_SESSION['flash_error'] = 'Você pode gerar entre 1 e 100 respostas por vez.';
            header('Location: /pesquisas/respostas?id=' . $surveyId);
            exit;
        }

        try {
            $generator = new ResponseGeneratorService();
            $result = $generator->generate($surveyId, $count, $strategy);
            
            $msg = "Sucesso! Foram geradas {$result['count']} respostas fictícias para esta pesquisa.";
            if ($result['fallback']) {
                $msg .= " (Utilizado fallback local sem consumo de cota de IA)";
            } else {
                $msg .= " (Respostas enriquecidas com IA)";
            }
            $_SESSION['flash_success'] = $msg;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao gerar respostas: ' . $e->getMessage();
        }

        header('Location: /pesquisas/respostas?id=' . $surveyId);
        exit;
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
        $questions   = Question::findBySurvey($id);
        $survey['questions'] = $questions;

        $firstAnswer = reset($answers);
        $lastAnswer  = end($answers);
        $durationMin = 1;
        if ($firstAnswer && $lastAnswer) {
            $durationSeconds = strtotime($lastAnswer['answered_at']) - strtotime($firstAnswer['answered_at']);
            $durationMin = max(1, (int) round($durationSeconds / 60));
        }

        $response = [
            'respondent'  => $respondent['name'] ?: 'Respondente Anônimo',
            'date'        => $respondent['created_at'],
            'durationMin' => $durationMin,
            'answers'     => array_map(fn($a) => [
                'questionId' => (int) $a['question_id'],
                'text'       => $a['text_response']
            ], $answers)
        ];

        $title       = 'Detalhe da resposta';
        $currentPath = '/pesquisas';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/surveys/resposta.php';
    }
}
