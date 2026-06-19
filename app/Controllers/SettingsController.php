<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\User;

class SettingsController
{
    public function index(): void
    {
        Auth::requireAuth();

        $userId      = Auth::id();
        $user        = User::findById($userId);
        $title       = 'Configurações';
        $currentPath = '/configuracoes';
        $flashError  = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require BASE_PATH . '/app/Views/settings/index.php';
    }
}
