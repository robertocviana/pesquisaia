# Technical Specification - Plano Trial e Limitações do Sistema

## Objetivo
Implementar um sistema de planos e assinaturas ("Trial" vs "Pro") na plataforma PesquisaIA. O plano default para novos cadastros será "Trial" (Gratuito). Limitações específicas serão aplicadas ao plano Trial para incentivar o upgrade para o plano "Pro", o qual será totalmente ilimitado. Uma funcionalidade de simulação/troca de planos será embutida na tela de Configurações para facilitar testes tanto pelo administrador quanto pelos usuários.

---

## 1. Regras de Limitações Propostas (Plano Trial)

A tabela abaixo resume as limitações ativas para contas com o plano `trial` vs o plano `pro`:

| Funcionalidade | Limite no Plano Trial | Plano Pro (Ilimitado) | Ação ao Atingir o Limite |
| :--- | :--- | :--- | :--- |
| **Pesquisas Criadas** | **Máximo de 3** pesquisas totais | Ilimitado | Bloqueia novas criações e duplicações (exibe banner de upgrade) |
| **Respostas por Pesquisa** | **Máximo de 20** respostas concluídas | Ilimitado | Encerra a pesquisa automaticamente ao atingir 20 respostas; impede novas respostas de participantes |
| **Exportação de Dados** | **Bloqueada** (CSV/PDF) | Liberado | Exibe mensagem de erro flash no painel com link de upgrade |
| **Relatórios de IA** | **Máximo de 1 geração** (Sem direito a regenerar) | Ilimitado | Oculta/bloqueia o botão de regenerar relatório |
| **Gerador de Respostas (Testes)** | **Máximo de 5 respostas** por envio e **apenas estratégia local** | Até 100 respostas e estratégia híbrida (IA) | Validação no backend com fallback/sanitização de entrada |

---

## 2. Modelagem do Banco de Dados

Criação de uma nova migration SQL para adicionar a coluna de plano à tabela de usuários.

### Arquivo: `[NEW] database/migrations/011_add_plan_to_users.sql`
```sql
-- Migration 011: add plan to users
ALTER TABLE `users` ADD COLUMN `plan` ENUM('trial', 'pro') NOT NULL DEFAULT 'trial';
```

---

## 3. Alterações nos Modelos (Models)

### 3.1 `[MODIFY] app/Models/User.php`
- Atualizar as consultas de leitura de usuário para selecionar o campo `plan`.
- Adicionar o método `updatePlan(int $id, string $plan)`.

#### Método `User::findByEmail`
```php
public static function findByEmail(string $email): ?array
{
    $stmt = Database::pdo()->prepare(
        'SELECT id, name, email, password_hash, plan FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}
```

#### Método `User::findById`
```php
public static function findById(int $id): ?array
{
    $stmt = Database::pdo()->prepare(
        'SELECT id, name, email, plan FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}
```

#### Novo Método `User::updatePlan`
```php
public static function updatePlan(int $id, string $plan): void
{
    if (!in_array($plan, ['trial', 'pro'], true)) {
        throw new \InvalidArgumentException('Plano inválido.');
    }
    $stmt = Database::pdo()->prepare(
        'UPDATE users SET plan = ? WHERE id = ?'
    );
    $stmt->execute([$plan, $id]);
}
```

---

## 4. Alterações nos Auxiliares (Helpers)

### 4.1 `[MODIFY] app/Helpers/Auth.php`
- Salvar a informação do plano do usuário na sessão no momento do login.
- Adicionar o plano ao array retornado no método `user()`.

#### Método `Auth::login`
```php
public static function login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_plan']  = $user['plan'] ?? 'trial';
}
```

#### Método `Auth::user`
```php
public static function user(): ?array
{
    if (!self::isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'plan'  => $_SESSION['user_plan']  ?? 'trial',
    ];
}
```

---

## 5. Alterações nos Controllers (Lógica de Negócios)

### 5.1 `[MODIFY] app/Controllers/SettingsController.php`
- Adicionar lógica para lidar com a alteração simulada de plano.

#### Novo Método `handleUpdatePlan`
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

    try {
        \App\Models\User::updatePlan($userId, $plan);
        $_SESSION['user_plan'] = $plan; // Atualiza a sessão ativa
        $_SESSION['flash_success'] = 'Plano atualizado com sucesso! (Simulação ativa)';
    } catch (\Exception $e) {
        $_SESSION['flash_error'] = 'Erro ao atualizar plano: ' . $e->getMessage();
    }

    header('Location: /configuracoes');
    exit;
}
```

### 5.2 `[MODIFY] app/Controllers/SurveyController.php`
- **Validação de Criação de Pesquisa**: No método `nova()`, se o plano for `trial`, contar as pesquisas existentes do usuário logado. Se a quantidade de pesquisas já existentes for maior ou igual a 3 (e a pesquisa da sessão atual não for rascunho ativo), impedir a criação de um novo rascunho e redirecionar para a listagem `/pesquisas` com um alerta `flash_error`.
- **Validação de Duplicação**: No método `handleDuplicar()`, se o plano for `trial`, contar se o usuário já tem >= 3 pesquisas. Se sim, impedir a duplicação.
- **Validação de Exportação**: No método `exportar()`, validar se o plano do usuário logado é `pro`. Se for `trial`, redirecionar para `/pesquisas/detalhe?id=X` com mensagem `flash_error` avisando que exportações são exclusivas Pro.
- **Limitação de Relatórios**:
  - No método `handleRelatorioGerar()`, se o plano for `trial` e já existir um relatório salvo no banco para a pesquisa, impedir nova geração (lançar erro flash "Seu plano Trial permite apenas 1 geração de relatório").

### 5.3 `[MODIFY] app/Controllers/ResponseController.php`
- **Limitação do Gerador**: No método `handleGenerateResponses()`, obter o plano do usuário.
  - Se o plano for `trial`:
    - Forçar a estratégia para `'local'` (independente do selecionado).
    - Se a quantidade selecionada `$count` for maior que 5, forçar para `5`.

### 5.4 `[MODIFY] app/Controllers/RespondentController.php`
- **Limitação do Respondente**: Ao carregar a página da pesquisa (`intro` ou `chat`), precisamos verificar se o dono da pesquisa (`user_id`) é um usuário do plano `trial` e se o número de respostas atuais (`response_count`) já atingiu 20.
  - Se sim, e o participante for novo ou não tiver finalizado, bloquear a pesquisa exibindo uma tela amigável informando que a pesquisa atingiu o limite máximo do plano gratuito do organizador.
- **Fechamento Automático**: Atualizar o método `checkAutoClose` na classe `Survey` para também monitorar o limite de 20 respostas caso o dono da pesquisa seja `trial`.

---

## 6. Alterações nas Visualizações (Views)

### 6.1 `[MODIFY] app/Views/settings/index.php`
- Adicionar uma nova aba chamada **Plano / Assinatura**.
- Dentro desta aba, mostrar o plano atual do usuário ("Gratuito (Trial)" ou "Profissional (Pro)").
- Exibir uma tabela ou lista comparativa e bonita dos limites de cada plano.
- Exibir um formulário POST simulando um upgrade/downgrade instantâneo (com botão estilizado) para facilitar testes.

### 6.2 `[MODIFY] app/Views/templates/sidebar.php`
- Se o usuário logado estiver no plano `trial`, mostrar um pequeno card elegante na parte inferior (acima do card de perfil) informando "Plano Gratuito (Trial)" com um link/botão rápido para fazer upgrade.

### 6.3 `[MODIFY] app/Views/surveys/relatorio.php`
- Ocultar o botão "Regenerar Relatório" ou desativá-lo com um badge se o usuário for do plano `trial` e o relatório já tiver sido gerado uma vez.

### 6.4 `[MODIFY] app/Views/surveys/respostas.php`
- Se o usuário for `trial`, mostrar um pequeno banner informativo perto do gerador de respostas avisando que no plano gratuito a geração é limitada a 5 respondentes locais (sem IA) por vez.

---

## 7. Roteamento (`public/index.php`)
Adicionar a nova rota POST no front controller:
```php
} elseif ($uri === '/configuracoes/plano' && $method === 'POST') {
    $c = new \App\Controllers\SettingsController();
    $c->handleUpdatePlan();
```

---

## 8. Plano de Validação e Testes
1. **Verificação de Migrations**: Rodar `lando php database/migrate.php` e validar se a coluna `plan` foi injetada corretamente na tabela `users`.
2. **Upgrade/Downgrade**: Acessar `/configuracoes`, clicar na nova aba **Plano** e testar a transição entre Trial e Pro. Validar se o card na sidebar atualiza dinamicamente.
3. **Limite de 3 Pesquisas**: 
   - No plano Trial, criar pesquisas até atingir o limite de 3. Tentar criar a 4ª ou duplicar uma das 3 e verificar o bloqueio.
   - Mudar para o plano Pro e validar se é possível criar mais de 3 pesquisas.
4. **Limite de 20 Respostas**:
   - Criar uma pesquisa no plano Trial. Simular/gerar 20 respostas.
   - Tentar acessar o link público do respondente e confirmar o bloqueio de novas participações.
5. **Bloqueio de Exportação**:
   - No plano Trial, tentar forçar a rota de exportar PDF/CSV e garantir que ocorre redirecionamento com erro flash.
6. **IA Report / Response Generator**:
   - No plano Trial, testar o gerador de respostas para ver se limita a 5 locais.
   - Testar o bloqueio de regeneração do relatório da IA.
