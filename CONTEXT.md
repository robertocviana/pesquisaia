# CONTEXT.md — PesquisaIA
> Lido automaticamente por qualquer agente ao iniciar trabalho neste projeto.

---

## ⚡ Stack do Projeto

Este é um projeto **PHP 8.3 puro** com Lando. Não é React, não é Vite, não é Node.

| Camada | Tecnologia |
|--------|------------|
| Backend | **PHP 8.3** (sem framework, PSR-4 manual) |
| Frontend | **Tailwind CSS CDN** + Lucide Icons + Vanilla JS |
| Banco | **MySQL 8.0** (via `host.docker.internal:3308`) |
| Ambiente | **Lando** (webroot: `/public`, URL: `pesquisaia.lndo.site`) |
| IA | OpenAI API (GPT-4o) via cURL |

---

## 🌿 Fluxo de Branches

O `main` é o **branch principal e estável**. Sempre crie novas branches a partir dele.

```bash
# Criar nova feature
git checkout main
git checkout -b feat/nome-da-feature

# Criar correção de bug
git checkout main
git checkout -b fix/nome-do-bug
```

### Como verificar que você está no ambiente correto

```bash
# A raiz do projeto deve conter:
# .lando.yml, app/, public/, composer.json, README.md

# E NÃO deve conter:
# package.json, vite.config.ts, bun.lock, src/
```

---

## 📖 Documentação de referência

- Regras de negócio completas → `production_artifacts/BUSINESS_RULES.md`
- PRD original → `production_artifacts/PRD.md`
- Specs de features → `production_artifacts/*.md`
