<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Auth;
use App\Helpers\Csrf;

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    // ─── GET /login ──────────────────────────────────────────────────────────
    public function login(): void
    {
        Auth::requireGuest();
        $title       = 'Entrar';
        $description = 'Acesse sua conta PesquisaIA para criar pesquisas inteligentes em minutos.';
        $error       = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        require BASE_PATH . '/app/Views/auth/login.php';
    }

    // ─── POST /login ─────────────────────────────────────────────────────────
    public function handleLogin(): void
    {
        Auth::requireGuest();
        Csrf::validate();

        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $this->service->authenticate($email, $password);
            Csrf::regenerate();
            header('Location: /dashboard');
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['auth_error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }
    }

    // ─── GET /cadastro ───────────────────────────────────────────────────────
    public function cadastro(): void
    {
        Auth::requireGuest();
        $title       = 'Criar conta';
        $description = 'Crie sua conta PesquisaIA e comece a fazer pesquisas inteligentes.';
        $error       = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        require BASE_PATH . '/app/Views/auth/cadastro.php';
    }

    // ─── POST /cadastro ──────────────────────────────────────────────────────
    public function handleCadastro(): void
    {
        Auth::requireGuest();
        Csrf::validate();

        $name     = $_POST['name']     ?? '';
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        try {
            $this->service->register($name, $email, $password, $confirm);
            Csrf::regenerate();
            header('Location: /dashboard');
            exit;
        } catch (\RuntimeException $e) {
            $_SESSION['auth_error'] = $e->getMessage();
            header('Location: /cadastro');
            exit;
        }
    }

    // ─── GET /logout ─────────────────────────────────────────────────────────
    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
