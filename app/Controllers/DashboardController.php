<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Survey;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();

        $userId      = Auth::id();
        $surveys     = Survey::findByUser($userId);
        $stats       = Survey::stats($userId);
        $recent      = array_slice($surveys, 0, 5);
        $title       = 'Dashboard';
        $currentPath = '/dashboard';
        $user        = Auth::user();

        require BASE_PATH . '/app/Views/dashboard/index.php';
    }
}
