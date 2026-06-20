<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Report;
use App\Services\ReportService;
use App\Services\ExportService;

class SurveyController
{
    // ─── GET /pesquisas ──────────────────────────────────────────────────────
    public function index(): void
    {
        Auth::requireAuth();

        $userId      = Auth::id();
        $surveys     = Survey::findByUser($userId);
        $title       = 'Minhas Pesquisas';
        $currentPath = '/pesquisas';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/surveys/index.php';
    }

    // ─── GET /pesquisas/nova ─────────────────────────────────────────────────
    public function nova(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();

        // Se o usuário quer criar explicitamente uma nova pesquisa, limpa a sessão e cria
        if (isset($_GET['new'])) {
            $_SESSION['current_survey_id'] = Survey::create($userId);
            header('Location: /pesquisas/nova');
            exit;
        }

        // Criar pesquisa em rascunho se não houver uma em andamento
        if (empty($_SESSION['current_survey_id'])) {
            $_SESSION['current_survey_id'] = Survey::create($userId);
        }

        $surveyId    = (int) $_SESSION['current_survey_id'];
        $survey      = Survey::findByIdForUser($surveyId, $userId);

        // Se a pesquisa foi concluída/publicada, criar nova
        if (!$survey || $survey['status'] !== 'rascunho') {
            $_SESSION['current_survey_id'] = Survey::create($userId);
            $surveyId = (int) $_SESSION['current_survey_id'];
            $survey   = Survey::findByIdForUser($surveyId, $userId);
        }

        $title       = 'Nova pesquisa';
        $currentPath = '/pesquisas/nova';
        $user        = Auth::user();

        // Buscar histórico do chat para renderizar na tela
        $history = \App\Models\Conversation::findBySurvey($surveyId);

        // Determinar a etapa atual para o progresso inicial do JS
        $currentStage = $survey['current_stage'] ?? 'tipo';

        require BASE_PATH . '/app/Views/surveys/nova.php';
    }

    // ─── GET /pesquisas/detalhe ──────────────────────────────────────────────
    public function detalhe(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $id     = (int) ($_GET['id'] ?? 0);
        $survey = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $questions    = Question::findBySurvey($id);
        $title        = $survey['name'];
        $currentPath  = '/pesquisas';
        $user         = Auth::user();
        $link         = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'pesquisaia.lndo.site') . '/r/' . ($survey['public_slug'] ?? '');
        $progress     = $survey['goal_responses']
            ? min(100, (int) round(($survey['response_count'] / $survey['goal_responses']) * 100))
            : 0;

        require BASE_PATH . '/app/Views/surveys/detalhe.php';
    }

    // ─── GET /pesquisas/revisao ──────────────────────────────────────────────
    public function revisao(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $id     = (int) ($_GET['id'] ?? $_SESSION['current_survey_id'] ?? 0);
        $survey = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $questions   = Question::findBySurvey($id);
        $title       = 'Revisão — ' . $survey['name'];
        $currentPath = '/pesquisas';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/surveys/revisao.php';
    }

    // ─── POST /pesquisas/revisao/salvar ──────────────────────────────────────
    public function handleRevisaoSalvar(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId   = Auth::id();
        $id       = (int) ($_POST['survey_id'] ?? 0);
        $survey   = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            exit;
        }

        // Atualizar campos gerais
        Survey::update($id, $userId, [
            'name'           => $_POST['name']           ?? $survey['name'],
            'objective'      => $_POST['objective']      ?? $survey['objective'],
            'audience'       => $_POST['audience']       ?? $survey['audience'],
            'goal_responses' => $_POST['goal_responses'] ?? null,
            'deadline_at'    => $_POST['deadline_at']    ?? null,
        ]);

        // Sincronizar perguntas
        $questions = json_decode($_POST['questions'] ?? '[]', true);
        if (is_array($questions)) {
            Question::sync($id, $questions);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // ─── POST /pesquisas/publicar ────────────────────────────────────────────
    public function handlePublicar(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId = Auth::id();
        $id     = (int) ($_POST['survey_id'] ?? 0);
        $survey = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $questions = Question::findBySurvey($id);
        if (count($questions) === 0) {
            $_SESSION['flash_error'] = 'A pesquisa deve ter pelo menos uma pergunta antes de ser publicada.';
            header('Location: /pesquisas/revisao?id=' . $id);
            exit;
        }

        Survey::publish($id, $userId);
        unset($_SESSION['current_survey_id']);

        header('Location: /pesquisas/detalhe?id=' . $id);
        exit;
    }

    // ─── POST /pesquisas/encerrar ────────────────────────────────────────────
    public function handleEncerrar(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId = Auth::id();
        $id     = (int) ($_POST['survey_id'] ?? 0);

        Survey::close($id, $userId);

        header('Location: /pesquisas/detalhe?id=' . $id);
        exit;
    }

    // ─── GET /pesquisas/relatorio ────────────────────────────────────────────
    public function relatorio(): void
    {
        Auth::requireAuth();

        $userId  = Auth::id();
        $id      = (int) ($_GET['id'] ?? 0);
        $survey  = Survey::findByIdForUser($id, $userId);

        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $questions = Question::findBySurvey($id);
        $report    = Report::findBySurvey($id);
        $title     = 'Relatório — ' . $survey['name'];
        $currentPath = '/pesquisas';
        $user        = Auth::user();
        $flashError  = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        require BASE_PATH . '/app/Views/surveys/relatorio.php';
    }

    // ─── POST /pesquisas/relatorio/gerar ─────────────────────────────────────
    public function handleRelatorioGerar(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId = Auth::id();
        $id     = (int) ($_POST['survey_id'] ?? 0);

        try {
            $service = new ReportService();
            $service->generate($id, $userId);
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: /pesquisas/relatorio?id=' . $id);
        exit;
    }

    // ─── GET /pesquisas/exportar ─────────────────────────────────────────────
    public function exportar(): void
    {
        Auth::requireAuth();

        $userId  = Auth::id();
        $id      = (int) ($_GET['id'] ?? 0);
        $format  = $_GET['format'] ?? 'csv';
        $service = new ExportService();

        if ($format === 'pdf') {
            $service->exportPdf($id, $userId);
        } else {
            $service->exportCsv($id, $userId);
        }
    }

    // ─── POST /pesquisas/excluir ─────────────────────────────────────────────
    public function handleExcluir(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId = Auth::id();
        $id     = (int) ($_POST['survey_id'] ?? 0);

        $survey = Survey::findByIdForUser($id, $userId);
        if ($survey) {
            Survey::delete($id, $userId);

            if (isset($_SESSION['current_survey_id']) && (int)$_SESSION['current_survey_id'] === $id) {
                unset($_SESSION['current_survey_id']);
            }
        }

        header('Location: /pesquisas');
        exit;
    }
}
