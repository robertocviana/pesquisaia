<?php

namespace App\Controllers;

use App\Helpers\MockData;

class RespondentController
{
    public function intro(): void
    {
        $id     = $_GET['id'] ?? 's-001';
        $survey = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title  = 'Participar da pesquisa';
        require BASE_PATH . '/app/Views/respondent/intro.php';
    }

    public function chat(): void
    {
        $id     = $_GET['id'] ?? 's-001';
        $survey = MockData::findSurvey($id) ?? MockData::surveys()[0];
        $title  = $survey['name'];
        require BASE_PATH . '/app/Views/respondent/chat.php';
    }

    public function concluido(): void
    {
        $title = 'Obrigado!';
        require BASE_PATH . '/app/Views/respondent/concluido.php';
    }
}
