# Especificação Técnica: Admin de Gerenciamento de Contas e Engajamento (v1)

Esta especificação descreve a arquitetura e o plano de implementação do painel de administração privado da plataforma PesquisaIA. O painel permitirá monitorar o engajamento dos usuários, gerenciar seus planos e papéis de acesso, de forma altamente segura.

---

## 1. Requisitos de Negócio e Segurança

1. **Segredo por Ofuscação (URL Oculta)**: A URL do painel admin não será fixa (ex: `/admin`). Ela será configurada via variável de ambiente `.env` (ex: `ADMIN_ROUTE=minha-url-super-secreta-2026`).
2. **Dupla Camada de Segurança**:
   - **Banco de Dados**: Nova coluna `role` na tabela `users` (valores: `user`, `admin`).
   - **Bypass Seguros no `.env`**: Variável `ADMIN_EMAILS` no `.env` contendo uma lista de e-mails de administradores (facilitando acesso de desenvolvedor sem precisar alterar banco diretamente).
3. **Tratamento de Acesso Não Autorizado**:
   - Usuário não autenticado: Redirecionado para `/login`.
   - Usuário autenticado que NÃO é administrador: Retorna **HTTP 404 (Página Não Encontrada)** em vez de 403, ocultando totalmente a existência da rota secreta.
4. **Métricas de Engajamento por Usuário**:
   - Plano atual (`trial` ou `pro`).
   - Data de cadastro.
   - Total de pesquisas criadas e distribuição por status (rascunhos, ativas, encerradas).
   - Última pesquisa criada (título e data).
   - Total de respondentes (iniciados e concluídos).
   - Total de respostas individuais no banco de dados.
   - Data da última resposta recebida nas pesquisas do usuário (indicador crucial de atividade recente).
5. **Ações Administrativas**:
   - Alteração instantânea do plano do usuário (`trial` <-> `pro`). Isso é necessário para atender às solicitações de upgrade enviadas via webhook.
   - Promoção/Demissão do papel de administrador (`user` <-> `admin`), com proteção para evitar autodespromoção.

---

## 2. Alterações no Banco de Dados

Criaremos a migration `database/migrations/012_add_role_to_users.sql` para adicionar a coluna `role` e atualizar o usuário padrão.

```sql
-- Migration 012: add role to users
ALTER TABLE `users` ADD COLUMN `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user';

-- Define o usuário padrão do seed como admin para fins de teste local
UPDATE `users` SET `role` = 'admin' WHERE `email` = 'dev@pesquisaia.com';
```

---

## 3. Estrutura de Arquivos e Componentes

### 3.1. Roteador (`public/index.php`)
[MODIFY] [index.php](file:///Users/robertoviana/dreamhost/pesquisaia/public/index.php)

Detectar dinamicamente a rota secreta configurada no `.env`:
```php
$adminRoute = $_ENV['ADMIN_ROUTE'] ?? getenv('ADMIN_ROUTE') ?: 'admin-controle';
$adminPrefix = '/' . $adminRoute;

if ($uri === $adminPrefix) {
    $c = new \App\Controllers\AdminController();
    $c->index();
} elseif ($uri === $adminPrefix . '/update-plan') {
    $c = new \App\Controllers\AdminController();
    $c->handleUpdatePlan();
} elseif ($uri === $adminPrefix . '/update-role') {
    $c = new \App\Controllers\AdminController();
    $c->handleUpdateRole();
} elseif ($uri === $adminPrefix . '/user-surveys') {
    $c = new \App\Controllers\AdminController();
    $c->userSurveys();
}
```

### 3.2. Helpers de Segurança (`app/Helpers/Auth.php`)
[MODIFY] [Auth.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Helpers/Auth.php)

Adicionar métodos para validar o papel de administrador e processar o login:
- `Auth::isAdmin()`: Verifica se o e-mail do usuário está listado em `ADMIN_EMAILS` (do `.env`) ou se `role` na sessão é `'admin'`.
- `Auth::requireAdmin()`: Executa `requireAuth()`, verifica `isAdmin()`. Se falso, limpa buffers e renderiza tela 404 padrão do sistema.
- Atualizar `Auth::login($user)` e `Auth::user()` para incluir a coluna `role`.

### 3.3. Modelo do Usuário (`app/Models/User.php`)
[MODIFY] [User.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Models/User.php)

- Atualizar consultas de `findByEmail` e `findById` para selecionar o campo `role`.
- Adicionar método `User::updateRole(int $id, string $role)` para atualizar o papel do usuário.

### 3.4. Controle do Administrador (`app/Controllers/AdminController.php` - NOVO)
[NEW] [AdminController.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Controllers/AdminController.php)

Responsabilidades:
- `index()`: Carrega dados agregados de engajamento do banco de dados. Suporta busca (por nome/e-mail) e filtros (por plano).
- `handleUpdatePlan()`: Processa via POST a alteração de plano de um usuário.
- `handleUpdateRole()`: Processa via POST a alteração de papel de acesso de um usuário (com validação para o usuário logado não remover a si mesmo).
- `userSurveys()`: Endpoint AJAX que retorna dados JSON detalhados sobre as pesquisas de um usuário específico para alimentar a interface dinâmica.

#### Query de Engajamento Otimizada (Single Pass)
```sql
SELECT 
    u.id, 
    u.name, 
    u.email, 
    u.plan, 
    u.role, 
    u.created_at,
    COALESCE(s.total_surveys, 0) AS total_surveys,
    COALESCE(s.surveys_draft, 0) AS surveys_draft,
    COALESCE(s.surveys_active, 0) AS surveys_active,
    COALESCE(s.surveys_closed, 0) AS surveys_closed,
    s.last_survey_at,
    COALESCE(r.total_respondents, 0) AS total_respondents,
    COALESCE(r.respondents_completed, 0) AS respondents_completed,
    COALESCE(r.respondents_in_progress, 0) AS respondents_in_progress,
    COALESCE(res.total_answers, 0) AS total_answers,
    res.last_response_at
FROM users u
LEFT JOIN (
    SELECT 
        user_id,
        COUNT(*) AS total_surveys,
        SUM(CASE WHEN status = 'rascunho' THEN 1 ELSE 0 END) AS surveys_draft,
        SUM(CASE WHEN status = 'ativa' THEN 1 ELSE 0 END) AS surveys_active,
        SUM(CASE WHEN status = 'encerrada' THEN 1 ELSE 0 END) AS surveys_closed,
        MAX(created_at) AS last_survey_at
    FROM surveys
    GROUP BY user_id
) s ON s.user_id = u.id
LEFT JOIN (
    SELECT 
        s.user_id,
        COUNT(*) AS total_respondents,
        SUM(CASE WHEN r.status = 'concluida' THEN 1 ELSE 0 END) AS respondents_completed,
        SUM(CASE WHEN r.status = 'em_andamento' THEN 1 ELSE 0 END) AS respondents_in_progress
    FROM respondents r
    JOIN surveys s ON r.survey_id = s.id
    GROUP BY s.user_id
) r ON r.user_id = u.id
LEFT JOIN (
    SELECT 
        s.user_id,
        COUNT(*) AS total_answers,
        MAX(res.answered_at) AS last_response_at
    FROM responses res
    JOIN respondents r ON res.respondent_id = r.id
    JOIN surveys s ON r.survey_id = s.id
    GROUP BY s.user_id
) res ON res.user_id = u.id
-- Filtros e busca serão concatenados dinamicamente
ORDER BY u.created_at DESC
```

### 3.5. Barra Lateral de Navegação (`app/Views/templates/sidebar.php`)
[MODIFY] [sidebar.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/templates/sidebar.php)

Se `Auth::isAdmin()` for verdadeiro, renderizar um botão adicional na barra lateral apontando para a rota dinâmica configurada:
```php
if (\App\Helpers\Auth::isAdmin()) {
    $adminRoute = $_ENV['ADMIN_ROUTE'] ?? getenv('ADMIN_ROUTE') ?: 'admin-controle';
    $nav[] = ['href' => '/' . $adminRoute, 'label' => 'Painel Admin', 'icon' => 'shield'];
}
```

### 3.6. Visão do Administrador (`app/Views/admin/index.php` - NOVO)
[NEW] [index.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/admin/index.php)

Uma interface rica com o tema escuro/claro nativo do sistema contendo:
- **Cards de Métricas Globais**:
  - Usuários Cadastrados
  - Total de Pesquisas (Ativas vs Rascunho vs Encerradas)
  - Total de Respostas Recebidas
- **Controles de Busca e Filtro**:
  - Busca por nome/e-mail (input de texto)
  - Filtro por Plano (`Todos`, `Trial`, `Pro`)
  - Filtro por Papel (`Todos`, `Usuário`, `Administrador`)
- **Tabela de Usuários**:
  - Nome, e-mail e data de cadastro.
  - Plan Badge (Modificável via formulário inline/seletor).
  - Role Badge (Modificável com aviso de confirmação).
  - Estatísticas de engajamento resumidas.
  - Botão de expansão para listar pesquisas daquele usuário.
- **Linha Expansível (Sub-tabela de Pesquisas)**:
  - Carregada assincronamente via AJAX quando clicada.
  - Exibe: Nome da Pesquisa, Status, Meta de Respostas, Respostas Concluídas e Data de Criação.
- **Segurança Front-End**:
  - Validação CSRF inclusa em todas as requisições AJAX e POST.

---

## 4. Plano de Verificação

### Testes Manuais
1. **Segurança de Acesso**:
   - Tentar acessar a rota secreta sem fazer login (deve redirecionar para `/login`).
   - Tentar acessar com um usuário comum (deve retornar HTTP 404).
   - Acessar com usuário Admin (deve abrir o painel com sucesso).
2. **Funcionamento das Ações**:
   - Alterar plano de um usuário e validar se a restrição de plano (ex: limite de pesquisas) muda imediatamente para ele.
   - Tentar demitir o próprio usuário admin logado (deve retornar erro impedindo a ação).
   - Promover outro usuário a Admin e validar se ele agora ganha acesso ao painel e a opção de menu lateral correspondente.
3. **Métricas de Engajamento**:
   - Validar se a contagem de pesquisas, respondentes e respostas bate exatamente com as páginas de dashboard e relatórios daquele usuário.
   - Expandir a linha de um usuário e validar se a lista de pesquisas corresponde aos dados reais.

---
