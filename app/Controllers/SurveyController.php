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

        $userId       = Auth::id();
        $surveys      = Survey::findByUser($userId);
        $title        = 'Minhas Pesquisas';
        $currentPath  = '/pesquisas';
        $user         = Auth::user();
        $flashError   = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require BASE_PATH . '/app/Views/surveys/index.php';
    }

    // ─── GET /pesquisas/nova ─────────────────────────────────────────────────
    public function nova(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $user = Auth::user();
        $userPlan = $user['plan'] ?? 'trial';

        // Se o usuário quer criar explicitamente uma nova pesquisa, limpa a sessão e cria
        if (isset($_GET['new'])) {
            if ($userPlan === 'trial') {
                $surveys = Survey::findByUser($userId);
                if (count($surveys) >= 3) {
                    $_SESSION['flash_error'] = 'Você atingiu o limite de 3 pesquisas permitidas no plano Trial. Faça upgrade para o Pro para criar mais.';
                    header('Location: /pesquisas');
                    exit;
                }
            }
            $_SESSION['current_survey_id'] = Survey::create($userId);
            header('Location: /pesquisas/nova');
            exit;
        }

        // Criar pesquisa em rascunho se não houver uma em andamento
        if (empty($_SESSION['current_survey_id'])) {
            if ($userPlan === 'trial') {
                $surveys = Survey::findByUser($userId);
                if (count($surveys) >= 3) {
                    $_SESSION['flash_error'] = 'Você atingiu o limite de 3 pesquisas permitidas no plano Trial. Faça upgrade para o Pro para criar mais.';
                    header('Location: /pesquisas');
                    exit;
                }
            }
            $_SESSION['current_survey_id'] = Survey::create($userId);
        }

        $surveyId    = (int) $_SESSION['current_survey_id'];
        $survey      = Survey::findByIdForUser($surveyId, $userId);

        // Se a pesquisa foi concluída/publicada, criar nova
        if (!$survey || $survey['status'] !== 'rascunho') {
            if ($userPlan === 'trial') {
                $surveys = Survey::findByUser($userId);
                if (count($surveys) >= 3) {
                    $_SESSION['flash_error'] = 'Você atingiu o limite de 3 pesquisas permitidas no plano Trial. Faça upgrade para o Pro para criar mais.';
                    header('Location: /pesquisas');
                    exit;
                }
            }
            $_SESSION['current_survey_id'] = Survey::create($userId);
            $surveyId = (int) $_SESSION['current_survey_id'];
            $survey   = Survey::findByIdForUser($surveyId, $userId);
        }

        $title       = 'Nova pesquisa';
        $currentPath = '/pesquisas/nova';
        $user        = Auth::user();

        // Buscar histórico do chat para renderizar na tela
        $history = \App\Models\Conversation::findBySurvey($surveyId);
        if (empty($history)) {
            $welcomeMessage = "Olá! Vou te ajudar a criar sua pesquisa.\n\n**Qual tipo de pesquisa você deseja fazer?**\n\nExemplos:\n• Validar uma ideia de negócio\n• Entender uma dor de clientes\n• Avaliar uma nova funcionalidade\n• Entender comportamento de usuários\n• Testar uma proposta de valor\n• Outro\n\nEscreva o tipo de pesquisa que você quer criar.";
            \App\Models\Conversation::add($surveyId, 'assistant', $welcomeMessage);
            $history = \App\Models\Conversation::findBySurvey($surveyId);
        }

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

        $goalResponses = isset($_POST['goal_responses']) && $_POST['goal_responses'] !== '' ? (int)$_POST['goal_responses'] : null;
        $deadlineAt    = isset($_POST['deadline_at']) && $_POST['deadline_at'] !== '' ? \App\Helpers\DateHelper::toUtc($_POST['deadline_at']) : null;

        // Atualizar campos gerais
        Survey::update($id, $userId, [
            'name'           => $_POST['name']           ?? $survey['name'],
            'objective'      => $_POST['objective']      ?? $survey['objective'],
            'audience'       => $_POST['audience']       ?? $survey['audience'],
            'goal_responses' => $goalResponses,
            'deadline_at'    => $deadlineAt,
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

        // Garante ownership antes de qualquer outra checagem
        $survey = Survey::findByIdForUser($id, $userId);
        if (!$survey) {
            header('Location: /pesquisas');
            exit;
        }

        $existing = Report::findBySurvey($id);
        if ($existing) {
            $_SESSION['flash_error'] = 'Esta pesquisa já possui um relatório gerado. Não é permitido regenerar relatórios.';
            header('Location: /pesquisas/relatorio?id=' . $id);
            exit;
        }

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

        $user = Auth::user();
        $userPlan = $user['plan'] ?? 'trial';

        if ($userPlan === 'trial') {
            $_SESSION['flash_error'] = 'A exportação de relatórios e dados é um recurso exclusivo do plano Pro. Faça upgrade para liberar!';
            header('Location: /pesquisas/detalhe?id=' . $id);
            exit;
        }

        $service = new ExportService();

        if ($format === 'pdf') {
            $service->exportPdf($id, $userId);
        } else {
            $service->exportCsv($id, $userId);
        }
    }

    // ─── POST /pesquisas/duplicar ────────────────────────────────────────────
    public function handleDuplicar(): void
    {
        Auth::requireAuth();
        Csrf::validate();

        $userId = Auth::id();
        $id     = (int) ($_POST['survey_id'] ?? 0);

        $user = Auth::user();
        $userPlan = $user['plan'] ?? 'trial';

        if ($userPlan === 'trial') {
            $surveys = Survey::findByUser($userId);
            if (count($surveys) >= 3) {
                $_SESSION['flash_error'] = 'Você atingiu o limite de 3 pesquisas permitidas no plano Trial. Faça upgrade para o Pro para duplicar.';
                header('Location: /pesquisas');
                exit;
            }
        }

        try {
            $newId = Survey::duplicate($id, $userId);
            $_SESSION['current_survey_id'] = $newId;
            $_SESSION['flash_success'] = 'Pesquisa duplicada com sucesso!';
            header('Location: /pesquisas/revisao?id=' . $newId);
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao duplicar pesquisa: ' . $e->getMessage();
            header('Location: /pesquisas');
            exit;
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
