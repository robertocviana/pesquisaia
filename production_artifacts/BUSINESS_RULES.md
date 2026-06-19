# BUSINESS_RULES.md — PesquisaIA
> Última atualização: 2026-06-19
> Branch: feat/implementacao-completa-prd

---

## 1. Visão Geral do Sistema

PesquisaIA é uma plataforma SaaS de pesquisas conversacionais criadas com auxílio de IA (OpenAI GPT-4o). O usuário cria pesquisas conversando com o assistente, publica um link público e analisa as respostas com relatórios gerados automaticamente por IA.

---

## 2. Stack Técnica

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.3 (sem framework, PSR-4 manual) |
| Banco de dados | MySQL 8.0 (externo via host.docker.internal:3308) |
| ORM | PDO nativo |
| IA | OpenAI API (GPT-4o) via cURL |
| Frontend | Tailwind CSS CDN + Lucide Icons + Vanilla JS |
| Servidor | Lando (appserver PHP 8.3, webroot: /public) |
| Configuração | `.env` na raiz (não commitado) |

---

## 3. Arquitetura de Arquivos

```
pesquisaia/
├── .env                          # Variáveis de ambiente (não commitado)
├── .lando.yml                    # Config Lando (PHP 8.3)
├── public/
│   ├── index.php                 # Router principal (front controller)
│   └── .htaccess                 # Rewrite rules
├── app/
│   ├── bootstrap.php             # Loader .env + autoloader PSR-4
│   ├── Database.php              # PDO Singleton
│   ├── Controllers/              # HTTP Controllers (GET + POST)
│   ├── Models/                   # Data Access Layer (PDO)
│   ├── Services/                 # Lógica de negócio e IA
│   ├── Helpers/                  # Auth, Csrf, MockData
│   └── Views/                    # PHP views (Tailwind CSS)
├── database/
│   ├── migrations/*.sql          # 7 migrations
│   ├── seeds/*.sql               # Seeds de desenvolvimento
│   └── migrate.php               # Runner de migrations
└── production_artifacts/         # Documentação técnica
```

---

## 4. Modelo de Dados

### 4.1 `users`
- `id`, `name`, `email` (UNIQUE), `password_hash` (bcrypt cost 12), `created_at`, `updated_at`
- Autenticação via `password_verify()`. Nunca armazena senha em plaintext.

### 4.2 `surveys` — **Tenant isolation: SEMPRE filtrar por `user_id`**
- `id`, `user_id` (FK users), `name`, `objective`, `audience`
- `status`: `rascunho` | `ativa` | `encerrada`
- `goal_responses`: meta de respostas (nullable)
- `deadline_at`: data limite (nullable)
- `public_slug`: 16 chars hex, gerado em `Survey::publish()` via `bin2hex(random_bytes(8))`
- `response_count`: contador de respondentes concluídos (incrementado atômico no banco)

### 4.3 `questions`
- `id`, `survey_id` (FK), `order_index`, `text`
- Ordenação: `ORDER BY order_index ASC, id ASC`

### 4.4 `conversations`
- Histórico completo do chat de criação: `role` (user|assistant), `content`
- Enviado integralmente à OpenAI a cada turno para manter contexto

### 4.5 `respondents`
- Identificação anônima por `token` (64 chars hex via `random_bytes(32)`)
- Token armazenado em cookie `pesquisaia_token` (30 dias, httponly, samesite=Lax)
- `status`: `em_andamento` | `concluida`
- `name`: coletado como primeiro step do chat (opcional)

### 4.6 `responses`
- `respondent_id` (FK), `question_id` (FK), `text_response`, `answered_at`
- Verificação de duplicidade antes de salvar (`Response::exists()`)

### 4.7 `reports`
- Um por pesquisa (UNIQUE survey_id)
- `summary` (TEXT) + `insights` (JSON array de objetos)
- Regenerável via UPSERT (`ON DUPLICATE KEY UPDATE`)

---

## 5. Regras de Negócio por Fase

### FASE 1 — Autenticação
- Email deve ser único (constraint UNIQUE no banco)
- Senha mínima de 8 caracteres, validada no `AuthService`
- Após login/cadastro bem-sucedido, `session_regenerate_id(true)` é chamado
- CSRF token em `$_SESSION['csrf_token']` protege todos os formulários POST
- Rota `/logout` destroi sessão completamente

### FASE 2 — Criação de Pesquisa com IA
- Ao acessar `/pesquisas/nova`, uma pesquisa em `rascunho` é criada automaticamente no banco e seu ID salvo em `$_SESSION['current_survey_id']`
- O chat chama `POST /pesquisas/nova/chat` via AJAX com CSRF no header `X-CSRF-TOKEN`
- A IA recebe o histórico completo de `conversations` a cada mensagem
- A IA retorna JSON estruturado: `{message, stage, fields, questions}`
- Campos coletados são salvos na tabela `surveys` automaticamente
- Perguntas geradas são salvas via `Question::createBatch()`

### FASE 3 — Revisão
- A revisão usa `Survey::findByIdForUser(id, user_id)` — tenant isolation obrigatório
- `POST /pesquisas/revisao/salvar` aceita `questions` como JSON no body
- `Question::sync()` deleta e recria todas as perguntas (operação idempotente)

### FASE 4 — Publicação
- Validação obrigatória: pesquisa deve ter ≥ 1 pergunta
- `public_slug` = `bin2hex(random_bytes(8))` — 16 chars hex
- URL pública: `/r/{slug}` (regex: `[a-f0-9]{16}`)
- Status muda para `ativa` apenas neste momento

### FASE 5 — Respondente (Área Pública)
- **Nenhum login necessário**
- Token anônimo via cookie `pesquisaia_token` (httponly, samesite=Lax)
- `Respondent::findOrCreate(survey_id, token)` — cria ou retoma o respondente
- Perguntas exibidas sequencialmente via JS
- Nome coletado como primeiro step (salvo via `Respondent::setName()`)
- Cada resposta salva via `POST /r/responder` (AJAX)
- Duplicação prevenida por `Response::exists(respondent_id, question_id)`

### FASE 6 — Respostas
- `ResponseController` verifica ownership da pesquisa E pertencimento do respondente
- Detalhe da resposta verifica `respondent['survey_id'] === survey['id']`

### FASE 7 — Encerramento
- **Manual**: `POST /pesquisas/encerrar` com CSRF
- **Por quantidade**: `Survey::checkAutoClose()` verifica `response_count >= goal_responses`
- **Por data**: `Survey::checkAutoClose()` verifica `deadline_at <= date('Y-m-d')`
- Após encerramento: status = `encerrada`, novas respostas bloqueadas (`Survey::findBySlug()` retorna apenas `ativa`)

### FASE 8 — Relatório
- **Condição obrigatória**: pesquisa `encerrada`
- `ReportService::generate()` agrupa respostas por pergunta e envia à IA
- IA retorna `{summary: string, insights: [{title, description, type}]}`
- `type` possíveis: `positive`, `negative`, `neutral`, `opportunity`
- Relatório salvo via `Report::upsert()` (regenerável)

### FASE 9 — Exportação
- CSV: BOM UTF-8 + separador `;` (compatível com Excel BR)
- PDF: HTML view com CSS `@media print` + botão `window.print()`
- Ambos verificam ownership via `Survey::findByIdForUser()`

---

## 6. Segurança

| Proteção | Implementação |
|----------|--------------|
| CSRF | Token de sessão `$_SESSION['csrf_token']`, validado em todos os POST. AJAX via header `X-CSRF-TOKEN` |
| Tenant Isolation | Toda query de survey usa `WHERE id = ? AND user_id = ?` |
| SQL Injection | PDO preparado com `execute([])` em todas as queries |
| XSS | `htmlspecialchars()` em todos os outputs PHP |
| Senha | `password_hash()` bcrypt cost 12, `password_verify()` |
| Sessão | `session_regenerate_id(true)` após login |
| Headers HTTP | X-Frame-Options: DENY, X-Content-Type-Options: nosniff, X-XSS-Protection |
| Cookie | httponly=true, samesite=Lax, secure detectado automaticamente |

---

## 7. Chaves e Configuração

O arquivo `.env` (não commitado, adicionado ao `.gitignore`) deve conter:
```
DB_HOST=host.docker.internal
DB_PORT=3308
DB_USER=dream
DB_PASSWORD=dream
DB_NAME=pesquisai
OPENAI_API_KEY=sk-...
```

---

## 8. Usuário de Desenvolvimento

- **Email**: `dev@pesquisaia.com`
- **Senha**: `password`
- Criado via seed `database/seeds/001_seed_users.sql`

---

## 9. Como Executar Migrations

```bash
lando php database/migrate.php          # Apenas migrations
lando php database/migrate.php --seed   # Migrations + seeds
```

---

## 10. Rotas Completas

| Método | Rota | Controller::método |
|--------|------|--------------------|
| GET | `/login` | AuthController::login |
| POST | `/login` | AuthController::handleLogin |
| GET | `/cadastro` | AuthController::cadastro |
| POST | `/cadastro` | AuthController::handleCadastro |
| GET | `/logout` | AuthController::logout |
| GET | `/dashboard` | DashboardController::index |
| GET | `/pesquisas` | SurveyController::index |
| GET | `/pesquisas/nova` | SurveyController::nova |
| POST | `/pesquisas/nova/chat` | AiController::chat (AJAX) |
| GET | `/pesquisas/detalhe?id=X` | SurveyController::detalhe |
| GET | `/pesquisas/revisao?id=X` | SurveyController::revisao |
| POST | `/pesquisas/revisao/salvar` | SurveyController::handleRevisaoSalvar |
| POST | `/pesquisas/publicar` | SurveyController::handlePublicar |
| POST | `/pesquisas/encerrar` | SurveyController::handleEncerrar |
| GET | `/pesquisas/relatorio?id=X` | SurveyController::relatorio |
| POST | `/pesquisas/relatorio/gerar` | SurveyController::handleRelatorioGerar |
| GET | `/pesquisas/exportar?id=X&format=csv\|pdf` | SurveyController::exportar |
| GET | `/pesquisas/respostas?id=X` | ResponseController::index |
| GET | `/pesquisas/resposta?id=X&rid=Y` | ResponseController::show |
| GET | `/configuracoes` | SettingsController::index |
| GET | `/r/{slug}` | RespondentController::intro |
| GET | `/r/{slug}/chat` | RespondentController::chat |
| GET | `/r/{slug}/concluido` | RespondentController::concluido |
| POST | `/r/responder` | RespondentController::responder (AJAX) |
