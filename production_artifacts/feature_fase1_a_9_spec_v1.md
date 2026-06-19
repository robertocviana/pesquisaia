# Especificação Técnica — PesquisaIA Fases 1 a 9
**Versão:** v1  
**Data:** 2026-06-19  
**Status:** Aguardando aprovação

---

## Visão Geral

Implementação completa do PRD da plataforma de pesquisas conversacionais com IA, do zero ao produto funcional. O projeto já possui as **Views prontas** e um **roteador simples em PHP 8.3 via Lando**. O trabalho consiste em:

1. Adicionar MySQL ao Lando
2. Criar a camada de Models (PDO)
3. Implementar autenticação real
4. Conectar a IA (OpenAI) ao chat de criação e relatórios
5. Conectar todas as Views aos dados reais

---

## Stack Confirmada

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.3 (sem framework) |
| Banco | MySQL 8.0 via Lando |
| ORM | PDO nativo (sem ORM externo) |
| IA | OpenAI API (GPT-4o) |
| Frontend | Tailwind CDN (já nas views) + Vanilla JS |
| Deploy local | Lando (`pesquisaia.lndo.site`) |

---

## Estrutura de Arquivos a Criar/Modificar

### 🆕 Infraestrutura
```
.lando.yml                          → [MODIFICAR] Adicionar serviço database MySQL
app/bootstrap.php                   → [MODIFICAR] Adicionar .env loader + DB singleton
app/Database.php                    → [CRIAR] Singleton de conexão PDO
.env.example                        → [CRIAR] Template de variáveis de ambiente
database/
  migrations/
    001_create_users.sql
    002_create_surveys.sql
    003_create_questions.sql
    004_create_respondents.sql
    005_create_responses.sql
    006_create_conversations.sql
    007_create_reports.sql
  seeds/
    001_seed_users.sql
```

### 🆕 Models (camada de dados)
```
app/Models/
  User.php          → Cadastro, autenticação (password_hash/verify)
  Survey.php        → CRUD de pesquisas (com tenant isolation por user_id)
  Question.php      → CRUD de perguntas (vinculadas a survey)
  Respondent.php    → Controle de respondentes anônimos
  Response.php      → Respostas individuais por pergunta
  Conversation.php  → Histórico do chat de criação da pesquisa
  Report.php        → Cache do relatório gerado por IA
```

### 🆕 Services (lógica de negócio e IA)
```
app/Services/
  AuthService.php       → Login, registro, validação de sessão
  AiService.php         → Wrapper para OpenAI API (chat completion)
  SurveyAiService.php   → Lógica conversacional de criação da pesquisa
  ReportService.php     → Geração de relatório com IA
  ExportService.php     → Exportação CSV e PDF (HTML print)
```

### 🆕 Helpers novos
```
app/Helpers/
  Auth.php      → requireAuth(), currentUser(), isLoggedIn()
  Csrf.php      → generate(), validate() — proteção CSRF nos formulários POST
```

### 🔧 Controllers (reescrever para usar Models reais)
```
app/Controllers/
  AuthController.php         → handleLogin() e handleCadastro() reais (POST)
  DashboardController.php    → Survey::findByUser(user_id)
  SurveyController.php       → CRUD completo + publicar + encerrar
  ResponseController.php     → Listar/detalhar respostas reais
  RespondentController.php   → Fluxo real (intro/chat por slug/concluído)
  SettingsController.php     → Perfil do usuário logado
  AiController.php           → [CRIAR] Endpoint AJAX /pesquisas/nova/chat
```

### 🔧 Rotas a adicionar (public/index.php)
```
POST /login
POST /cadastro
GET  /r/{slug}                     → Rota pública respondente por slug
GET  /r/{slug}/chat
POST /r/responder                  → AJAX salvar resposta
POST /pesquisas/nova/chat          → AJAX chat IA
POST /pesquisas/nova/salvar        → Finalizar chat e salvar pesquisa
POST /pesquisas/revisao/salvar     → Salvar edições da revisão
POST /pesquisas/publicar           → Publicar pesquisa
POST /pesquisas/encerrar           → Encerrar pesquisa
POST /pesquisas/relatorio/gerar    → Gerar relatório com IA
GET  /pesquisas/exportar           → Export CSV ou PDF via ?format=csv|pdf
```

---

## Modelagem do Banco de Dados

### `users`
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(150) NOT NULL
email           VARCHAR(255) NOT NULL UNIQUE
password_hash   VARCHAR(255) NOT NULL
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

### `surveys`
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id         INT UNSIGNED NOT NULL      -- Tenant isolation
name            VARCHAR(255) NOT NULL
objective       TEXT NOT NULL
audience        VARCHAR(255)
status          ENUM('rascunho','ativa','encerrada') DEFAULT 'rascunho'
goal_responses  SMALLINT UNSIGNED DEFAULT NULL
deadline_at     DATE DEFAULT NULL
public_slug     VARCHAR(32) UNIQUE DEFAULT NULL   -- hash para URL pública
response_count  SMALLINT UNSIGNED DEFAULT 0
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

### `questions`
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
survey_id       INT UNSIGNED NOT NULL
order_index     TINYINT UNSIGNED DEFAULT 0
text            TEXT NOT NULL
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
```

### `conversations` (histórico do chat de criação)
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
survey_id       INT UNSIGNED NOT NULL
role            ENUM('user','assistant') NOT NULL
content         TEXT NOT NULL
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
```

### `respondents`
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
survey_id       INT UNSIGNED NOT NULL
token           VARCHAR(64) UNIQUE NOT NULL   -- UUID anônimo via cookie
name            VARCHAR(150) DEFAULT NULL     -- Perguntado no início da conversa
status          ENUM('em_andamento','concluida') DEFAULT 'em_andamento'
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
```

### `responses`
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
respondent_id   INT UNSIGNED NOT NULL
question_id     INT UNSIGNED NOT NULL
text_response   TEXT NOT NULL
answered_at     DATETIME DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (respondent_id) REFERENCES respondents(id) ON DELETE CASCADE
FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
```

### `reports` (cache do relatório gerado por IA)
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
survey_id       INT UNSIGNED NOT NULL UNIQUE
summary         TEXT
insights        JSON
generated_at    DATETIME DEFAULT CURRENT_TIMESTAMP
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
```

---

## Fluxo de Dados por Fase

### FASE 1 — Autenticação
```
GET  /login  → exibir view (já pronta)
POST /login  → AuthController::handleLogin()
               → AuthService::authenticate(email, password)
               → User::findByEmail() + password_verify()
               → $_SESSION['user_id'] = id
               → redirect /dashboard

GET  /cadastro → exibir view (já pronta)
POST /cadastro → AuthController::handleCadastro()
                → Validar campos → User::create()
                → Auto-login → redirect /dashboard
```

### FASE 2 — Criação da Pesquisa (Chat com IA)
```
GET  /pesquisas/nova
     → Criar survey rascunho no banco → salvar survey_id na sessão
     → Carregar histórico da conversa (se retomar)

POST /pesquisas/nova/chat (AJAX JSON)
     → AiController::chat()
     → Salvar mensagem do usuário em conversations
     → AiService::complete([histórico completo]) → GPT-4o
     → GPT retorna JSON: { message, fields?, questions? }
     → Salvar resposta assistant em conversations
     → Atualizar campos na survey (objetivo, público, etc.)
     → Retornar response para o frontend

POST /pesquisas/nova/salvar
     → Salvar perguntas geradas em questions
     → Redirecionar para /pesquisas/revisao?id=X
```

### FASE 3 — Revisão
```
GET  /pesquisas/revisao?id=X
     → Survey::findByIdForUser(id, user_id)  ← tenant isolation
     → Carregar questions ordenadas por order_index

POST /pesquisas/revisao/salvar
     → Atualizar campos da survey
     → Sync perguntas: INSERT novos, UPDATE editados, DELETE removidos
```

### FASE 4 — Publicação
```
POST /pesquisas/publicar
     → Validar: ao menos 1 pergunta
     → Survey::publish()
     → public_slug = bin2hex(random_bytes(8))
     → status = 'ativa'
     → URL pública: /r/{slug}
```

### FASE 5 — Respondente (área pública, sem login)
```
GET  /r/{slug}
     → Survey::findBySlug() → carregar dados e perguntas
     → Verificar status = 'ativa' (bloquear se encerrada)

GET  /r/{slug}/chat
     → Criar/retomar respondent via cookie token
     → Exibir perguntas sequencialmente via JS

POST /r/responder (AJAX)
     → Response::save(respondent_id, question_id, text)
     → Ao finalizar todas: respondent.status = 'concluida'
     → Survey::incrementResponseCount()
     → Verificar critérios automáticos de encerramento
```

### FASE 6 — Respostas
```
GET  /pesquisas/respostas?id=X
     → Survey::findByIdForUser() ← tenant isolation
     → Respondent::findBySurvey(survey_id) → listar

GET  /pesquisas/resposta?id=X&rid=Y
     → Response::findByRespondent(respondent_id) → exibir conversa
```

### FASE 7 — Encerramento
```
POST /pesquisas/encerrar
     → Survey::close() → status = 'encerrada'

[Automático — verificado no POST /r/responder]:
     → Se response_count >= goal_responses → Survey::close()
     → Se deadline_at <= today → Survey::close()
```

### FASE 8 — Relatório com IA
```
POST /pesquisas/relatorio/gerar
     → Verificar: pesquisa encerrada + ownership
     → ReportService::generate(survey_id)
       → Coletar todas as respostas do banco
       → Montar prompt com perguntas + respostas
       → GPT-4o → { summary, insights[] }
       → Salvar em reports (upsert)
     → Redirecionar para /pesquisas/relatorio?id=X
```

### FASE 9 — Exportação
```
GET /pesquisas/exportar?id=X&format=csv
    → Verificar ownership
    → ExportService::toCsv(survey_id)
    → header('Content-Type: text/csv')
    → header('Content-Disposition: attachment; filename="respostas.csv"')

GET /pesquisas/exportar?id=X&format=pdf
    → Renderizar view HTML especial para impressão
    → CSS @media print + window.print() no front
```

---

## Decisões Técnicas

| Decisão | Escolha | Justificativa |
|---------|---------|---------------|
| PDF | HTML + CSS `@media print` via `window.print()` | Zero dependência extra no Composer |
| CSRF | Token em `$_SESSION['csrf_token']` + input hidden | Simples, sem biblioteca |
| Contexto IA | Enviar histórico completo de `conversations` a cada mensagem | Coerência do chat |
| Perguntas IA | GPT-4o com `response_format: json_object` | Output estruturado e confiável |
| Tenant isolation | `WHERE id = ? AND user_id = ?` em **toda** query de survey | Nunca confiar no id da URL sem checagem |
| Config sensível | `.env` file lido no `bootstrap.php` + `.gitignore` | Não hardcodar chave OpenAI no código |
| Respondente anônimo | Cookie `pesquisaia_token` com UUID + tabela `respondents` | Permite retomar sem conta |
| Nome do respondente | Perguntado como primeiro step do chat do respondente | Melhor UX + identificação no painel |

---

## Ordem de Implementação (Sequencial)

```
1. Infraestrutura: .lando.yml + .env + Database.php + migrations
2. Auth: User.php + AuthService.php + AuthController.php + Auth.php helper + Csrf.php
3. Dashboard: DashboardController com Survey::findByUser()
4. Survey CRUD: Survey.php + Question.php + SurveyController básico
5. Chat IA: Conversation.php + AiService.php + SurveyAiService.php + AiController.php
6. Revisão: edição/reordenação de perguntas com AJAX
7. Publicação: slug + rotas públicas /r/{slug}
8. Respondente: Respondent.php + Response.php + RespondentController
9. Respostas: ResponseController com dados reais
10. Encerramento: manual + automático
11. Relatório: ReportService.php + integração IA
12. Exportação: ExportService.php CSV + PDF
13. Documentar: BUSINESS_RULES.md
14. Commit: via skill `commit`
```

---

## Perguntas em Aberto

> [!IMPORTANT]
> **Chave da OpenAI:** Precisa ser configurada em um arquivo `.env` na raiz do projeto (nunca commitada). Forneça sua chave antes de testar os recursos de IA.

> [!NOTE]
> **PDF:** Será implementado como view HTML com CSS `@media print` + botão `window.print()`. Se quiser geração server-side (PDF gerado no servidor sem clique), me avise para usar `wkhtmltopdf` via `shell_exec`.

> [!NOTE]
> **Nome do respondente:** O fluxo do respondente pedirá o nome como primeira mensagem do chat. Se preferir que seja opcional ou que não pergunte nome algum, me avise.

---

## Critério de Aprovação

Após aprovação deste documento, o arquivo `production_artifacts/feature_fase1_a_9_spec_v1.md` será salvo na pasta do projeto e a implementação começará criando um branch dedicado `feat/implementacao-completa-prd`.
