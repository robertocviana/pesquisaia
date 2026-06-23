<?php

namespace App\Controllers;

use App\Models\Survey;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Response;

/**
 * RespondentController — Fluxo do respondente (área pública, sem login).
 */
class RespondentController
{
    // ─── GET /r/{slug} — Tela de boas-vindas ────────────────────────────────
    public function intro(): void
    {
        $slug   = $_GET['slug'] ?? '';
        $survey = Survey::findBySlug($slug);

        if (!$survey) {
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="pt-BR"><body style="font-family:sans-serif;text-align:center;padding:4rem">';
            echo '<h1>Pesquisa não encontrada</h1>';
            echo '<p>Este link pode ter expirado ou a pesquisa foi encerrada.</p>';
            echo '</body></html>';
            exit;
        }

        $title = 'Participar — ' . $survey['name'];
        require BASE_PATH . '/app/Views/respondent/intro.php';
    }

    // ─── GET /r/{slug}/chat — Chat com o respondente ─────────────────────────
    public function chat(): void
    {
        $slug   = $_GET['slug'] ?? '';
        $survey = Survey::findBySlug($slug);

        if (!$survey) {
            header('Location: /');
            exit;
        }

        // Gerenciar token anônimo via cookie
        $token = $_COOKIE['pesquisaia_token'] ?? null;
        if (!$token) {
            $token = Respondent::generateToken();
            setcookie('pesquisaia_token', $token, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $respondent = Respondent::findOrCreate((int) $survey['id'], $token);
        $questions  = Question::findBySurvey((int) $survey['id']);
        $answered   = Response::countByRespondent((int) $respondent['id']);

        // Se o respondente já concluiu ou já respondeu todas as perguntas existentes, redireciona para a página de conclusão
        if (count($questions) > 0 && ($respondent['status'] === 'concluida' || $answered >= count($questions))) {
            header('Location: /r/' . $survey['public_slug'] . '/concluido');
            exit;
        }

        $title = $survey['name'];
        require BASE_PATH . '/app/Views/respondent/chat.php';
    }

    // ─── POST /r/responder — Salvar resposta (AJAX) ──────────────────────────
    public function responder(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input        = json_decode(file_get_contents('php://input'), true) ?? [];
        $surveySlug   = $input['slug']        ?? '';
        $questionId   = (int) ($input['question_id'] ?? 0);
        $textResponse = trim($input['answer']  ?? '');
        $name         = trim($input['name']    ?? '');

        $survey = Survey::findBySlug($surveySlug);
        if (!$survey) {
            http_response_code(404);
            echo json_encode(['error' => 'Pesquisa não encontrada.']);
            exit;
        }

        $token = $_COOKIE['pesquisaia_token'] ?? null;
        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'Token de respondente não encontrado.']);
            exit;
        }

        $respondent = Respondent::findOrCreate((int) $survey['id'], $token);
        $surveyId   = (int) $survey['id'];

        // Atualizar nome se fornecido
        if ($name && !$respondent['name']) {
            Respondent::setName((int) $respondent['id'], $name);
        }

        // Salvar resposta (se não for apenas o nome)
        if ($questionId && $textResponse) {
            if (!Response::exists((int) $respondent['id'], $questionId)) {
                Response::save((int) $respondent['id'], $questionId, $textResponse);
            }
        }

        $questions = Question::findBySurvey($surveyId);
        $answered  = Response::countByRespondent((int) $respondent['id']);
        $total     = count($questions);
        $completed = $answered >= $total;

        // Finalizar respondente
        if ($completed) {
            Respondent::complete((int) $respondent['id']);
            Survey::incrementResponseCount($surveyId);
            Survey::checkAutoClose($surveyId);
        }

        echo json_encode([
            'success'    => true,
            'answered'   => $answered,
            'total'      => $total,
            'completed'  => $completed,
        ]);
        exit;
    }

    // ─── GET /r/{slug}/concluido ─────────────────────────────────────────────
    public function concluido(): void
    {
        $slug   = $_GET['slug'] ?? '';
        $survey = Survey::findBySlug($slug) ?? Survey::findById(0); // fallback graceful
        $title  = 'Obrigado!';
        require BASE_PATH . '/app/Views/respondent/concluido.php';
    }
}
