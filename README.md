# PesquisaIA

Plataforma SaaS de pesquisas conversacionais criadas com IA (OpenAI GPT-4o).

---

## ⚠️ LEIA ANTES DE QUALQUER COISA — Arquitetura de Branches

> [!CAUTION]
> O branch **`main`** deste repositório contém um template legado **React/Vite/TanStack** que **NÃO É** o projeto real e **NÃO TEM** `.lando.yml`. Ele existe apenas como ponto de partida histórico.

### Regra de ouro para novos trabalhos:

```bash
# ✅ SEMPRE comece aqui — branch PHP mais atualizada
git checkout feat/duplicar-pesquisa

# ✅ Crie novas features a partir dela
git checkout -b feat/nome-da-feature

# ❌ NUNCA faça isso
git checkout main
```

### Mapa de branches

| Branch | Conteúdo | Status |
|--------|----------|--------|
| `main` | ❌ Template React/Vite/TanStack (legado) | Não usar |
| `feat/implementacao-completa-prd` | ✅ PHP base — Fases 1–9 completas | Histórico |
| `feat/migrate-to-php` | ✅ Migração inicial para PHP | Histórico |
| `feat/gerador-respostas` | ✅ Gerador de respostas fictícias | Histórico |
| `feat/duplicar-pesquisa` | ✅ **Base principal atual** | Usar como base |
| `feat/chat-respondente-ux` | ✅ Redesign UX do chat | Em desenvolvimento |

---

## Stack Técnica

| Camada | Tecnologia |
|--------|------------|
| **Backend** | PHP 8.3 (sem framework, PSR-4 manual) |
| **Banco de dados** | MySQL 8.0 (externo via `host.docker.internal:3308`) |
| **ORM** | PDO nativo |
| **IA** | OpenAI API (GPT-4o / GPT-4o-mini) via cURL |
| **Frontend** | Tailwind CSS CDN + Lucide Icons + Vanilla JS |
| **Servidor local** | Lando (PHP 8.3, webroot: `/public`) |
| **Configuração** | `.env` na raiz (não commitado, use `.env.example`) |

---

## Estrutura do Projeto

```
pesquisaia/
├── .env.example              # Template de variáveis de ambiente
├── .lando.yml                # Config Lando (PHP 8.3)
├── composer.json             # PSR-4 autoloader (sem dependências externas)
├── public/
│   ├── index.php             # Router principal (front controller)
│   └── .htaccess             # Rewrite rules
├── app/
│   ├── bootstrap.php         # Carrega .env + autoloader
│   ├── Database.php          # PDO Singleton
│   ├── Controllers/          # HTTP Controllers (GET + POST)
│   ├── Models/               # Data Access Layer (PDO)
│   ├── Services/             # Lógica de negócio e IA
│   ├── Helpers/              # Auth, Csrf, etc.
│   └── Views/                # PHP views (Tailwind CSS)
│       ├── templates/        # header.php, sidebar.php, etc.
│       ├── auth/             # login.php, cadastro.php
│       ├── dashboard/        # index.php
│       ├── surveys/          # nova, revisao, detalhe, relatorio, etc.
│       ├── respondent/       # intro.php, chat.php, concluido.php
│       └── settings/         # index.php
├── database/
│   ├── migrations/*.sql      # Migrations versionadas
│   ├── seeds/*.sql           # Seeds de desenvolvimento
│   └── migrate.php           # Runner de migrations
└── production_artifacts/     # Documentação técnica e specs
    ├── BUSINESS_RULES.md     # ← Hipocampo do projeto. Leia sempre.
    ├── PRD.md
    └── *.md                  # Specs das features
```

---

## Primeiros passos

### Pré-requisitos

- [Lando](https://lando.dev/) instalado
- Acesso ao MySQL externo (`host.docker.internal:3308`)
- Chave de API da OpenAI

### Setup

```bash
# 1. Clone e vá para a branch PHP correta
git clone <repo>
cd pesquisaia
git checkout feat/duplicar-pesquisa

# 2. Configure variáveis de ambiente
cp .env.example .env
# Edite .env com suas credenciais

# 3. Suba o ambiente
lando start

# 4. Execute migrations
lando php database/migrate.php --seed

# 5. Acesse
# http://pesquisaia.lndo.site/
# Usuário: dev@pesquisaia.com | Senha: password
```

---

## Comandos úteis

```bash
lando info                      # Informações do ambiente
lando php database/migrate.php  # Rodar migrations
lando php database/migrate.php --seed  # Migrations + seeds
lando logs -s appserver         # Logs do PHP
```

---

## Documentação

Toda a documentação de regras de negócio, decisões arquiteturais e specs de features está em [`production_artifacts/BUSINESS_RULES.md`](production_artifacts/BUSINESS_RULES.md).

**Leia esse arquivo antes de qualquer desenvolvimento.**

---

## Segurança

- CSRF token em `$_SESSION['csrf_token']` — validado em todos os POSTs
- Tenant isolation: toda query de survey usa `WHERE id = ? AND user_id = ?`
- PDO prepared statements em todas as queries
- `htmlspecialchars()` em todos os outputs PHP
- Cookies: `httponly=true`, `samesite=Lax`
