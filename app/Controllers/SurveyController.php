<?php

namespace App\Controllers;

use App\Helpers\MockData;

class SurveyController
{
    public function index(): void
    {
        $surveys     = MockData::surveys();
        $title       = 'Minhas Pesquisas';
        $currentPath = '/pesquisas';
        require BASE_PATH . '/app/Views/surveys/index.php';
    }

    public function nova(): void
    {
        $title       = 'Nova pesquisa';
        $currentPath = '/pesquisas/nova';
        require BASE_PATH . '/app/Views/surveys/nova.php';
    }

    public function detalhe(): void
    {
        $id      = $_GET['id'] ?? 's-001';
        $survey  = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title   = $survey['name'];
        $currentPath = '/pesquisas';
        $link    = 'https://pesquisaia.lndo.site/r/intro?id=' . $survey['id'];
        $progress = min(100, (int) round((count($survey['responses']) / max(1, $survey['goal'])) * 100));
        require BASE_PATH . '/app/Views/surveys/detalhe.php';
    }

    public function revisao(): void
    {
        $id      = $_GET['id'] ?? 's-001';
        $survey  = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title   = 'Revisão — ' . $survey['name'];
        $currentPath = '/pesquisas';
        require BASE_PATH . '/app/Views/surveys/revisao.php';
    }

    public function relatorio(): void
    {
        $id      = $_GET['id'] ?? 's-001';
        $survey  = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title   = 'Relatório — ' . $survey['name'];
        $currentPath = '/pesquisas';
        require BASE_PATH . '/app/Views/surveys/relatorio.php';
    }
}
