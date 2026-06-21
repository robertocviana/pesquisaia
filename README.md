# PesquisaIA

Plataforma SaaS de pesquisas conversacionais criadas com IA (OpenAI GPT-4o).

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

## Fluxo de Branches

O `main` é o **branch principal e estável** do projeto. Todo desenvolvimento de features e correções é feito em branches separadas e integrado via merge ao `main`.

```bash
# ✅ Criar nova feature
git checkout main
git checkout -b feat/nome-da-feature

# ✅ Criar correção de bug
git checkout main
git checkout -b fix/nome-do-bug

# ✅ Ao finalizar, merge de volta para main
git checkout main
git merge feat/nome-da-feature
```

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

## Primeiros Passos

### Pré-requisitos

- [Lando](https://lando.dev/) instalado
- Acesso ao MySQL externo (`host.docker.internal:3308`)
- Chave de API da OpenAI

### Setup

```bash
# 1. Clone e entre no projeto
git clone https://github.com/robertocviana/pesquisaia.git
cd pesquisaia

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

## Comandos Úteis

```bash
lando info                              # Informações do ambiente
lando php database/migrate.php          # Rodar migrations
lando php database/migrate.php --seed   # Migrations + seeds
lando logs -s appserver                 # Logs do PHP
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
