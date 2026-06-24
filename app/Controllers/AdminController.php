<?php

namespace App\Controllers;

use App\Database;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\DateHelper;
use App\Models\User;

/**
 * AdminController — Gerenciamento de contas e engajamento da plataforma.
 */
class AdminController
{
    public function __construct()
    {
        // Enforçar segurança de administrador antes de qualquer ação
        Auth::requireAdmin();
    }

    // ─── GET /admin ──────────────────────────────────────────────────────────
    public function index(): void
    {
        $adminRoute = $_ENV['ADMIN_ROUTE'] ?? getenv('ADMIN_ROUTE') ?: 'admin-controle';
        $title = 'Painel Admin';

        // 1. Estatísticas Globais
        $totalUsers = (int) Database::pdo()->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $proUsers = (int) Database::pdo()->query("SELECT COUNT(*) FROM users WHERE plan = 'pro'")->fetchColumn();
        $totalSurveys = (int) Database::pdo()->query("SELECT COUNT(*) FROM surveys")->fetchColumn();
        $totalAnswers = (int) Database::pdo()->query("SELECT COUNT(*) FROM responses")->fetchColumn();
        $totalReports = (int) Database::pdo()->query("SELECT COUNT(*) FROM reports")->fetchColumn();

        // 2. Busca e Filtros
        $search = trim($_GET['search'] ?? '');
        $planFilter = trim($_GET['plan'] ?? 'all');
        $roleFilter = trim($_GET['role'] ?? 'all');

        // 3. Query de Engajamento por Usuário
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.plan, 
                    u.role, 
                    u.created_at,
                    COALESCE(s.total_surveys, 0) AS total_surveys,
                    COALESCE(s.surveys_draft, 0) AS surveys_draft,
                    COALESCE(s.surveys_active, 0) AS surveys_active,
                    COALESCE(s.surveys_closed, 0) AS surveys_closed,
                    s.last_survey_at,
                    COALESCE(r.total_respondents, 0) AS total_respondents,
                    COALESCE(r.respondents_completed, 0) AS respondents_completed,
                    COALESCE(r.respondents_in_progress, 0) AS respondents_in_progress,
                    COALESCE(res.total_answers, 0) AS total_answers,
                    res.last_response_at,
                    COALESCE(rep.total_reports, 0) AS total_reports
                FROM users u
                LEFT JOIN (
                    SELECT 
                        user_id,
                        COUNT(*) AS total_surveys,
                        SUM(CASE WHEN status = 'rascunho' THEN 1 ELSE 0 END) AS surveys_draft,
                        SUM(CASE WHEN status = 'ativa' THEN 1 ELSE 0 END) AS surveys_active,
                        SUM(CASE WHEN status = 'encerrada' THEN 1 ELSE 0 END) AS surveys_closed,
                        MAX(created_at) AS last_survey_at
                    FROM surveys
                    GROUP BY user_id
                ) s ON s.user_id = u.id
                LEFT JOIN (
                    SELECT 
                        s.user_id,
                        COUNT(*) AS total_respondents,
                        SUM(CASE WHEN r.status = 'concluida' THEN 1 ELSE 0 END) AS respondents_completed,
                        SUM(CASE WHEN r.status = 'em_andamento' THEN 1 ELSE 0 END) AS respondents_in_progress
                    FROM respondents r
                    JOIN surveys s ON r.survey_id = s.id
                    GROUP BY s.user_id
                ) r ON r.user_id = u.id
                LEFT JOIN (
                    SELECT 
                        s.user_id,
                        COUNT(*) AS total_answers,
                        MAX(res.answered_at) AS last_response_at
                    FROM responses res
                    JOIN respondents r ON res.respondent_id = r.id
                    JOIN surveys s ON r.survey_id = s.id
                    GROUP BY s.user_id
                ) res ON res.user_id = u.id
                LEFT JOIN (
                    SELECT 
                        s.user_id,
                        COUNT(rep.id) AS total_reports
                    FROM reports rep
                    JOIN surveys s ON rep.survey_id = s.id
                    GROUP BY s.user_id
                ) rep ON rep.user_id = u.id";

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (in_array($planFilter, ['trial', 'pro'], true)) {
            $where[] = "u.plan = ?";
            $params[] = $planFilter;
        }

        if (in_array($roleFilter, ['user', 'admin'], true)) {
            $where[] = "u.role = ?";
            $params[] = $roleFilter;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        // 4. Carrega Mensagens Flash
        $successMsg = $_SESSION['admin_success'] ?? null;
        $errorMsg = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_success'], $_SESSION['admin_error']);

        require BASE_PATH . '/app/Views/admin/index.php';
    }

    // ─── POST /admin/update-plan ─────────────────────────────────────────────
    public function handleUpdatePlan(): void
    {
        Csrf::validate();
        $adminRoute = $_ENV['ADMIN_ROUTE'] ?? getenv('ADMIN_ROUTE') ?: 'admin-controle';

        $userId = (int) ($_POST['user_id'] ?? 0);
        $plan = $_POST['plan'] ?? '';

        if ($userId <= 0 || !in_array($plan, ['trial', 'pro'], true)) {
            $_SESSION['admin_error'] = 'Dados inválidos para alteração de plano.';
            header("Location: /$adminRoute");
            exit;
        }

        try {
            User::updatePlan($userId, $plan);
            $_SESSION['admin_success'] = 'Plano do usuário atualizado com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = 'Erro ao atualizar plano: ' . $e->getMessage();
        }

        header("Location: /$adminRoute");
        exit;
    }

    // ─── POST /admin/update-role ─────────────────────────────────────────────
    public function handleUpdateRole(): void
    {
        Csrf::validate();
        $adminRoute = $_ENV['ADMIN_ROUTE'] ?? getenv('ADMIN_ROUTE') ?: 'admin-controle';

        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';

        if ($userId <= 0 || !in_array($role, ['user', 'admin'], true)) {
            $_SESSION['admin_error'] = 'Dados inválidos para alteração de papel de acesso.';
            header("Location: /$adminRoute");
            exit;
        }

        // Impedir autodespromoção
        if ($userId === (int) Auth::id()) {
            $_SESSION['admin_error'] = 'Operação negada: você não pode remover o seu próprio acesso de administrador.';
            header("Location: /$adminRoute");
            exit;
        }

        try {
            User::updateRole($userId, $role);
            $_SESSION['admin_success'] = 'Papel de acesso do usuário atualizado com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = 'Erro ao atualizar papel: ' . $e->getMessage();
        }

        header("Location: /$adminRoute");
        exit;
    }

    // ─── GET /admin/user-surveys ─────────────────────────────────────────────
    public function userSurveys(): void
    {
        $userId = (int) ($_GET['user_id'] ?? 0);

        if ($userId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID do usuário inválido.']);
            exit;
        }

        try {
            $stmt = Database::pdo()->prepare(
                "SELECT s.id, s.name, s.status, s.goal_responses, s.response_count, s.created_at, s.deadline_at,
                        CASE WHEN rep.id IS NOT NULL THEN 1 ELSE 0 END AS has_report
                 FROM surveys s
                 LEFT JOIN reports rep ON rep.survey_id = s.id
                 WHERE s.user_id = ? 
                 ORDER BY s.created_at DESC"
            );
            $stmt->execute([$userId]);
            $surveys = $stmt->fetchAll();

            // Formatar datas para o fuso local do usuário logado
            foreach ($surveys as &$survey) {
                $survey['created_at_formatted'] = DateHelper::format($survey['created_at'], 'd/m/Y H:i');
                $survey['deadline_at_formatted'] = $survey['deadline_at'] ? DateHelper::format($survey['deadline_at'], 'd/m/Y H:i') : 'Sem prazo';
                $survey['has_report'] = (int) $survey['has_report'];
                
                // Tradução amigável do status
                $statusMap = [
                    'rascunho' => 'Rascunho',
                    'ativa' => 'Ativa',
                    'encerrada' => 'Encerrada'
                ];
                $survey['status_label'] = $statusMap[$survey['status']] ?? ucfirst($survey['status']);
            }

            header('Content-Type: application/json');
            echo json_encode($surveys);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao obter pesquisas: ' . $e->getMessage()]);
            exit;
        }
    }
}
