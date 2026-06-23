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

        // Verificar limite do plano Trial do criador da pesquisa
        $owner = \App\Models\User::findById((int) $survey['user_id']);
        $ownerPlan = $owner['plan'] ?? 'trial';

        if ($ownerPlan === 'trial' && (int) $survey['response_count'] >= 10) {
            http_response_code(403);
            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Limite Atingido — PesquisaIA</title></head>';
            echo '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background-color:#f9fafb;margin:0;padding:2rem;">';
            echo '<div style="max-w-md;text-align:center;background:white;padding:2.5rem;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">';
            echo '<div style="width:3.5rem;height:3.5rem;background:#fee2e2;color:#ef4444;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">';
            echo '<svg style="width:2rem;height:2rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
            echo '</div>';
            echo '<h1 style="font-size:1.25rem;font-weight:600;color:#111827;margin-bottom:0.75rem;">Limite de Respostas Atingido</h1>';
            echo '<p style="font-size:0.875rem;color:#4b5563;line-height:1.5;margin-bottom:1.5rem;">Esta pesquisa foi criada em uma conta gratuita (plano Trial) e já atingiu o limite máximo de 10 respostas permitidas.</p>';
            echo '<span style="font-size:0.75rem;color:#9ca3af;">PesquisaIA</span>';
            echo '</div></body></html>';
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

        // Verificar limite do plano Trial do criador da pesquisa
        $owner = \App\Models\User::findById((int) $survey['user_id']);
        $ownerPlan = $owner['plan'] ?? 'trial';

        if ($ownerPlan === 'trial' && (int) $survey['response_count'] >= 10) {
            header('Location: /r/' . $survey['public_slug']);
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
