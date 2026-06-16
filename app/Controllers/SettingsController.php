<?php

namespace App\Controllers;

class SettingsController
{
    public function index(): void
    {
        $title       = 'Configurações';
        $currentPath = '/configuracoes';
        require BASE_PATH . '/app/Views/settings/index.php';
    }
}
