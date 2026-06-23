# BUSINESS_RULES.md — PesquisaIA
> Última atualização: 2026-06-21
> Branch principal: `main`

---

> [!NOTE]
> **Fluxo de branches:** `main` é o branch estável e base de todo desenvolvimento.
> Para novas features: `git checkout main && git checkout -b feat/nome-da-feature`
> Para correções: `git checkout main && git checkout -b fix/nome-do-bug`

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
│   ├── Helpers/                  # Auth, Csrf, MockData, DateHelper
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
- Chave de unicidade composta: `(survey_id, token)`, permitindo que o mesmo cookie/respondente participe de diferentes pesquisas
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
- Ao acessar `/pesquisas/nova?new=1` (ou se a sessão `current_survey_id` estiver vazia), uma nova pesquisa em `rascunho` é criada automaticamente no banco, seu ID é salvo em `$_SESSION['current_survey_id']`, e o usuário é redirecionado de volta para a rota limpa `/pesquisas/nova` (técnica Post-Redirect-Get para evitar duplicação no recarregamento de página/F5).
- Acessar `/pesquisas/nova` sem parâmetros recupera o rascunho de pesquisa ativo da sessão.
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
- **Por data**: `Survey::checkAutoClose()` verifica `deadline_at <= DateHelper::todayString()` (fuso horário local)
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

## 11. Tratamento de Erros de IA — Modo Fallback

> **Regra adicionada em:** 2026-06-19 — fix/openai-quota-error-handling
> **Motivação:** Conta OpenAI com quota esgotada travava completamente o fluxo de criação de pesquisas.

### Hierarquia de erros (`AiException`)

Todos os erros da API OpenAI são agora encapsulados em `App\Services\AiException` com código tipado:

| Código (`getErrorCode()`) | Causa | Comportamento |
|--------------------------|-------|---------------|
| `quota_exceeded` | Cota de uso esgotada (HTTP 429 + "quota") | Ativa modo fallback manual |
| `auth_failed` | Chave de API inválida (HTTP 401) | Ativa modo fallback manual |
| `rate_limit` | Muitas requisições (HTTP 429 sem quota) | Retorna erro retryable ao frontend |
| `timeout` | CURL timeout > 60s | Retorna erro retryable ao frontend |
| `network_error` | CURL error de conexão | Retorna erro retryable ao frontend |

### Modo Fallback Manual (`SurveyAiService`)

Quando a IA está indisponível por `quota_exceeded` ou `auth_failed`:
- `SurveyAiService::fallbackMode()` é ativado automaticamente
- O fluxo coleta objetivo → público → nome → meta → gera 5 perguntas genéricas
- O usuário vê um banner amarelo de aviso (não um erro)
- **Os dados são sempre salvos no banco** — modo fallback é transparente para o banco
- A pesquisa pode ser publicada normalmente após o fluxo manual

### Preservação de Estado e Redundância (Safety Net)

Para garantir que o fluxo de criação nunca fique travado ou em estado inconsistente:
- **Restauração de Histórico**: Ao carregar ou recarregar `/pesquisas/nova`, o histórico completo da conversa é renderizado e a etapa ativa (`currentStage`) é calculada dinamicamente com base nos dados salvos no banco, sincronizando a interface do usuário.
- **Geração Redundante (Safety Net)**: Se a IA responder com estágio `perguntas` ou `finalizado` mas a lista de perguntas vier vazia, o backend intercepta o retorno e gera as perguntas locais de modo determinístico, salvando-as no banco e liberando o botão de revisão no frontend.
- **Geração Imediata**: O prompt do sistema foi otimizado para que a IA gere as perguntas no exato momento em que coleta o campo da meta de respostas, sem postergar a ação para um turno extra.

### Relatório sem IA (`ReportService`)

Quando a IA está indisponível ao gerar relatório:
- O erro é capturado e convertido em `RuntimeException` com mensagem amigável em português
- O `flash_error` é exibido na página de relatório
- O usuário pode tentar novamente quando a cota for recarregada

### Frontend (nova.php)

- Erros retryable: exibem mensagem com botão "Tentar novamente"
- Erros definitivos (quota/auth): sem botão de retry (não adianta tentar de novo)
- Modo fallback: banner amarelo no topo do chat (exibido apenas uma vez)

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
| POST | `/pesquisas/respostas/gerar` | ResponseController::handleGenerateResponses |
| GET | `/pesquisas/resposta?id=X&rid=Y` | ResponseController::show |
| GET | `/configuracoes` | SettingsController::index |
| GET | `/r/{slug}` | RespondentController::intro |
| GET | `/r/{slug}/chat` | RespondentController::chat |
| GET | `/r/{slug}/concluido` | RespondentController::concluido |
| POST | `/r/responder` | RespondentController::responder (AJAX) |

---

## 12. Gerador de Respostas de Pesquisa (Ferramenta de Teste)

> **Implementado em:** 2026-06-20 — feat/gerador-respostas
> **Objetivo:** Facilitar o teste da funcionalidade de geração de relatórios por IA, gerando dados fictícios coerentes para pesquisas ativas.

### Regras de Negócio

- **Pré-condição:** A pesquisa deve estar com status `ativa`. Pesquisas em `rascunho` ou `encerradas` são bloqueadas com erro.
- **Limite por chamada:** De 1 a 100 respondentes por requisição. Validação no Controller antes de chamar o Service.
- **Ownership obrigatório:** `Survey::findByIdForUser(id, userId)` garante tenant isolation antes de qualquer geração.
- **Encerramento automático:** Após a geração, `Survey::checkAutoClose($surveyId)` é invocado — se o total gerado atingir `goal_responses`, a pesquisa é encerrada automaticamente.
- **Respondentes marcados como `concluida`** com datas retroativas aleatórias (0 a 10 dias atrás) para que relatórios e listagens reflitam distribuição temporal natural.

### Estratégias de Geração

| Estratégia | Comportamento | Custo de API |
|------------|---------------|--------------|
| `hybrid` | Tenta IA para gerar 5 perfis ricos e contextualizados; fallback automático para `local` se a IA falhar | Cota OpenAI por chamada |
| `local` | Gera 5 perfis determinísticos baseados em tons pré-definidos (entusiasta, crítico, neutro, detalhista, exigente), com mapeamento semântico por palavras-chave nas perguntas | Zero (sem custo) |

- A estratégia selecionada pelo usuário é validada por **whitelist** no Controller (`in_array(['hybrid', 'local'])`).
- Independente da estratégia, para cada respondente gerado, as respostas são sorteadas aleatoriamente entre os perfis base (cross-pollination), garantindo variedade mesmo com poucos perfis de template.

### Arquitetura (MVC)

- **Service:** `app/Services/ResponseGeneratorService.php` — toda a lógica de geração, chamada à IA e fallback local.
- **Controller:** `app/Controllers/ResponseController::handleGenerateResponses()` — validações HTTP, CSRF, ownership, limites. Retorna redirect com flash message.
- **View:** `app/Views/surveys/respostas.php` — botão "Gerar" com seletor de quantidade e estratégia, visível apenas para pesquisas `ativas`.
- **Rota:** `POST /pesquisas/respostas/gerar`

### Segurança

| Proteção | Implementação |
|----------|---------------|
| Tenant Isolation | `Survey::findByIdForUser(id, userId)` antes de qualquer geração |
| CSRF | `Csrf::validate()` no início do handler |
| Whitelist de Strategy | `in_array(['hybrid', 'local'], true)` para evitar valores arbitrários |
| Limite de contagem | `$count < 1 || $count > 100` antes de chamar o Service |
| SQL Injection | PDO prepared statements com `?` em todos os INSERTs |
| Sanitização da IA | Array de respondentes validado e sanitizado antes de ser usado |

---

## 13. Duplicação de Pesquisas

> **Implementado em:** 2026-06-20 — feat/duplicar-pesquisa
> **Objetivo:** Permitir ao usuário clonar uma pesquisa (e todas as suas perguntas) e redirecioná-lo para a tela de revisão para validação antes de publicá-la.

### Regras de Negócio
- **Segurança e Tenant Isolation**: Só é possível duplicar pesquisas pertencentes ao próprio usuário (`Survey::findByIdForUser`).
- **Estado Inicial**: A pesquisa duplicada é gerada com status `rascunho`, `current_stage = 'finalizado'`, `response_count = 0` e `public_slug = NULL`. Seu nome recebe o sufixo `" (Cópia)"`.
- **Cópia de Perguntas**: Todas as perguntas associadas à pesquisa original são duplicadas mantendo os textos e a ordenação (`order_index`).
- **Atomicidade**: Todo o processo de criação da nova pesquisa e cópia das perguntas é executado sob uma transação SQL (`beginTransaction()`).
- **Redirecionamento**: O usuário é redirecionado diretamente para `/pesquisas/revisao?id=NEW_ID`.

---

## 14. Redesign UX — Chat do Respondente (One-Question-at-a-Time e Design Lovable)

> **Implementado em:** 2026-06-22 — feat/redesign-respondente-lovable
> **Objetivo:** Substituir a área do respondente por um design moderno e elegante extraído do Lovable (welcome, chat, concluído), em CSS/JS puro sem Tailwind/Lucide externo.

### Mudanças de Comportamento e Design

- **Intro (`/r/{slug}`)**: Design com gradientes suaves, estatísticas da pesquisa e CTA "Iniciar pesquisa".
- **Chat (`/r/{slug}/chat`)**: Fluxo sequencial estilo "typeform" com barra de progresso no topo, transições animadas na troca de perguntas e botões dinâmicos de navegação. Sem o botão voltar, com foco total no avanço.
- **Concluído (`/r/{slug}/concluido`)**: Feedback adicional simulado na UI local e animação de fade-in.
- **Independência de dependências**: As views foram convertidas para HTML/CSS/JS standalone, deixando de incluir o `header.php` global que carrega Tailwind CDN e Lucide JS.

### Arquivos Modificados

- **`app/Views/respondent/intro.php`** — Layout de boas-vindas
- **`app/Views/respondent/chat.php`** — Interface de chat do respondente
- **`app/Views/respondent/concluido.php`** — Tela de conclusão
- Sincronização AJAX com `/r/responder` e persistência no banco de dados mantidas integralmente.

---

## 15. Adequação de Fuso Horário (GMT-3 São Paulo e suporte dinâmico)

> **Implementado em:** 2026-06-23 — feat/timezone-adequacao
> **Objetivo:** Adequar a exibição de todas as datas e horas do sistema e regras de negócio de fechamento automático para respeitar o fuso de São Paulo (GMT-3), preparando o sistema para fusos customizados futuros.

### Regras de Negócio
- **Armazenamento consistente**: O banco de dados MySQL continua armazenando todos os campos de data e hora (`created_at`, `answered_at`, `generated_at`) no padrão UTC/servidor.
- **Conversão sob demanda**: A exibição na interface do painel (detalhes, relatórios, respostas, exportações de PDF e CSV) é convertida para o fuso do usuário via `DateHelper::format()`.
- **Fuso Padrão e Dinâmico**: O fuso horário padrão é `America/Sao_Paulo`. O sistema tenta obter o fuso horário da sessão do usuário (`$_SESSION['user_timezone']`), facilitando a customização por usuário no futuro.
- **Fechamento Automático**: A verificação de expiração de pesquisas (`deadline_at`) compara a data/hora com o horário atual UTC (`gmdate('Y-m-d H:i:s')`), garantindo que o encerramento ocorra exatamente no fuso/horário configurados.

---

## 16. Critérios de Encerramento na Revisão (Meta de Respostas e Data/Hora Limite)

> **Implementado em:** 2026-06-23 — feat/criterios-encerramento-revisao
> **Objetivo:** Permitir ao usuário visualizar e alterar os critérios de encerramento da pesquisa (meta de respostas e data/hora limite) na tela de revisão de rascunhos antes de publicá-la.

### Regras de Negócio e Alterações
- **Formulário de Revisão**: Exibe uma nova seção com inputs para "Meta de respostas" (tipo numérico, >= 1) e "Data e hora limite" (tipo `datetime-local`).
- **Conversão de Timezone no Salvamento**: O input local de data e hora do usuário (ex: `2026-06-25T15:30`) é convertido para UTC (ex: `2026-06-25 18:30:00`) via `DateHelper::toUtc()` antes de ser salvo no banco.
- **Conversão de Timezone na Exibição**: O valor em UTC do banco é convertido de volta para o fuso local do usuário usando `DateHelper::format($survey['deadline_at'], 'Y-m-d\TH:i')` ao preencher o campo na tela de revisão, e em `'d/m/Y H:i'` na tela de detalhes.
- **Verificação de Expiração Precisa**: A função `Survey::checkAutoClose()` compara a data e hora limite UTC (`deadline_at`) diretamente com a data/hora atual em UTC (`gmdate('Y-m-d H:i:s')`), permitindo fechamento no minuto correto configurado pelo usuário.
- **Alteração do Banco de Dados**: A coluna `deadline_at` na tabela `surveys` foi alterada de `DATE` para `DATETIME` para permitir precisão de hora e minuto.



