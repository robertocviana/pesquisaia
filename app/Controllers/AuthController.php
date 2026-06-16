<?php

namespace App\Controllers;

class AuthController
{
    public function login(): void
    {
        $title = 'Entrar';
        $description = 'Acesse sua conta PesquisaIA para criar pesquisas inteligentes em minutos.';
        require BASE_PATH . '/app/Views/auth/login.php';
    }

    public function cadastro(): void
    {
        $title = 'Criar conta';
        $description = 'Crie sua conta PesquisaIA e comece a fazer pesquisas inteligentes.';
        require BASE_PATH . '/app/Views/auth/cadastro.php';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
