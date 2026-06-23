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

    public function handleUpdatePerfil(): void
    {
        Auth::requireAuth();
        \App\Helpers\Csrf::validate();

        $userId = Auth::id();
        $name   = trim($_POST['name'] ?? '');
        $email  = trim(strtolower($_POST['email'] ?? ''));

        if (empty($name) || empty($email)) {
            $_SESSION['flash_error'] = 'Nome e e-mail são obrigatórios.';
            header('Location: /configuracoes');
            exit;
        }

        $existing = \App\Models\User::findByEmail($email);
        if ($existing && (int) $existing['id'] !== $userId) {
            $_SESSION['flash_error'] = 'Este e-mail já está em uso por outro usuário.';
            header('Location: /configuracoes');
            exit;
        }

        try {
            \App\Models\User::update($userId, $name, $email);
            
            $user = \App\Helpers\Auth::user();
            $user['name'] = $name;
            $user['email'] = $email;
            $_SESSION['user'] = $user;

            $_SESSION['flash_success'] = 'Perfil atualizado com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
        }

        header('Location: /configuracoes');
        exit;
    }

    public function handleUpdateSeguranca(): void
    {
        Auth::requireAuth();
        \App\Helpers\Csrf::validate();

        $userId      = Auth::id();
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $_SESSION['flash_error'] = 'Todos os campos de senha são obrigatórios.';
            header('Location: /configuracoes');
            exit;
        }

        if ($newPass !== $confirmPass) {
            $_SESSION['flash_error'] = 'A nova senha e a confirmação não coincidem.';
            header('Location: /configuracoes');
            exit;
        }

        if (strlen($newPass) < 8) {
            $_SESSION['flash_error'] = 'A nova senha deve ter pelo menos 8 caracteres.';
            header('Location: /configuracoes');
            exit;
        }

        $user = \App\Helpers\Auth::user();
        $userFull = \App\Models\User::findByEmail($user['email']);

        if (!$userFull || !\App\Models\User::verifyPassword($currentPass, $userFull['password_hash'])) {
            $_SESSION['flash_error'] = 'A senha atual informada está incorreta.';
            header('Location: /configuracoes');
            exit;
        }

        try {
            \App\Models\User::updatePassword($userId, $newPass);
            $_SESSION['flash_success'] = 'Senha atualizada com sucesso!';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar senha: ' . $e->getMessage();
        }

        header('Location: /configuracoes');
        exit;
    }
}
