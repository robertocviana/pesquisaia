# Technical Specification - Alertas via Webhook (v1)

## Objetivo
Implementar dois alertas automatizados no sistema PesquisaIA que enviam notificações em formato JSON via cURL para o webhook configurado na variável de ambiente `PESQUISAI_WEBHOOKS`.

### Alertas Requeridos:
1. **Cadastro de Nova Conta**: Enviado ao finalizar com sucesso o registro de um usuário. Deve conter o **nome**, **e-mail** e **senha** (em texto plano/conforme inserido no cadastro).
2. **Solicitação de Plano Pro**: Enviado quando um usuário logado tenta atualizar seu plano para "Pro" na aba de Plano/Assinatura em Configurações. Deve conter o **ID**, **nome** e **e-mail** do usuário.

### Regra Especial para o Plano Pro:
- Quando o usuário tentar selecionar e atualizar para o plano **Pro**, o sistema **não** deve realizar a alteração de plano no banco de dados. 
- Em vez disso, deve enviar o webhook, manter o plano atual e exibir uma mensagem de sucesso na tela informando que o administrador já foi notificado e que entrará em contato. O plano continuará inalterado.

---

## 1. Variáveis de Ambiente
A URL do webhook será obtida a partir da variável `PESQUISAI_WEBHOOKS` no arquivo `.env`.
Exemplo:
```env
PESQUISAI_WEBHOOKS=https://n8n2.uaizap.io/webhook/pesquisai_webhooks
```

---

## 2. Proposta de Implementação (MVC)

### 2.1 Novo Serviço de Webhook: `[NEW] app/Services/WebhookService.php`
Criar uma classe utilitária responsável pelo envio assíncrono/síncrono das requisições POST com payload JSON.

```php
<?php

namespace App\Services;

class WebhookService
{
    /**
     * Envia um payload em formato JSON para o webhook configurado no .env.
     */
    public static function send(string $event, array $data): bool
    {
        $url = $_ENV['PESQUISAI_WEBHOOKS'] ?? '';
        if (empty($url)) {
            return false;
        }

        $payload = [
            'event'     => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'data'      => $data
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
```

---

### 2.2 Alerta 1: Cadastro de Nova Conta
Modificar o método `register` em `app/Services/AuthService.php` para disparar o webhook de criação de conta logo após a inserção no banco de dados e antes do retorno.

#### Arquivo: `[MODIFY] app/Services/AuthService.php`
```php
        try {
            $id = User::create($name, $email, $password);
        } catch (\RuntimeException $e) {
            throw $e; // E-mail duplicado
        }

        // Dispara o alerta de cadastro de nova conta
        \App\Services\WebhookService::send('user.register', [
            'name'     => $name,
            'email'    => $email,
            'password' => $password
        ]);

        $user = User::findById($id);
        Auth::login($user);
        return $user;
```

---

### 2.3 Alerta 2: Solicitação de Plano Pro e Bloqueio de Upgrade Direto
Modificar o método `handleUpdatePlan` em `app/Controllers/SettingsController.php` para interceptar quando o plano solicitado for `'pro'`.

#### Arquivo: `[MODIFY] app/Controllers/SettingsController.php`
```php
    public function handleUpdatePlan(): void
    {
        Auth::requireAuth();
        \App\Helpers\Csrf::validate();

        $userId = Auth::id();
        $plan   = trim($_POST['plan'] ?? 'trial');

        if (!in_array($plan, ['trial', 'pro'], true)) {
            $_SESSION['flash_error'] = 'Plano inválido.';
            header('Location: /configuracoes');
            exit;
        }

        // Regra especial para solicitação do plano Pro
        if ($plan === 'pro') {
            $user = User::findById($userId);
            if ($user) {
                \App\Services\WebhookService::send('plan.upgrade_request', [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email']
                ]);
            }
            $_SESSION['flash_success'] = 'O administrador já foi avisado e entrará em contato.';
            header('Location: /configuracoes');
            exit;
        }

        // Fluxo normal para outros planos (ex: rebaixamento para Trial ou testes)
        try {
            User::updatePlan($userId, $plan);
            $_SESSION['user_plan'] = $plan;
            $_SESSION['flash_success'] = 'Plano atualizado com sucesso! (Simulação ativa)';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Erro ao atualizar plano: ' . $e->getMessage();
        }

        header('Location: /configuracoes');
        exit;
    }
```

---

## 3. Plano de Validação e Testes
1. **Teste de Cadastro**:
   - Ir para a página `/cadastro`.
   - Realizar o cadastro de um novo usuário (ex: `Nome Teste`, `teste_webhook@pesquisaia.com`, `senha1234`).
   - Validar se o webhook recebe o payload `user.register` contendo o nome, e-mail e a senha digitada.
2. **Teste de Solicitação do Plano Pro**:
   - Logar com o usuário recém-criado.
   - Ir para a página `/configuracoes`, clicar na aba **Plano / Assinatura**.
   - Escolher a opção **Pro (Profissional)** e clicar em **Atualizar Plano**.
   - Validar que a tela atualiza e exibe a mensagem: *"O administrador já foi avisado e entrará em contato."*
   - Validar que o plano na barra lateral e na visualização continua como **Trial (Gratuito)**.
   - Validar se o webhook recebe o payload `plan.upgrade_request` com ID, nome e e-mail do usuário logado.
