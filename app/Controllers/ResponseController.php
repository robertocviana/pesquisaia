<?php

namespace App\Controllers;

use App\Helpers\MockData;

class ResponseController
{
    public function index(): void
    {
        $id      = $_GET['id'] ?? 's-001';
        $survey  = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title   = 'Respostas — ' . $survey['name'];
        $currentPath = '/pesquisas';
        require BASE_PATH . '/app/Views/surveys/respostas.php';
    }

    public function show(): void
    {
        $id       = $_GET['id'] ?? 's-001';
        $rid      = $_GET['rid'] ?? 'r-001';
        $survey   = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $response = MockData::findResponse($survey, $rid) ?? $survey['responses'][0] ?? null;
        $title    = 'Detalhe da resposta';
        $currentPath = '/pesquisas';
        require BASE_PATH . '/app/Views/surveys/resposta.php';
    }
}
