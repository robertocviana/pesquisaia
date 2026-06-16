<?php

namespace App\Controllers;

use App\Helpers\MockData;

class DashboardController
{
    public function index(): void
    {
        $surveys     = MockData::surveys();
        $stats       = MockData::stats($surveys);
        $recent      = array_slice($surveys, 0, 5);
        $title       = 'Dashboard';
        $currentPath = '/dashboard';

        require BASE_PATH . '/app/Views/dashboard/index.php';
    }
}
