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

## 🚨 ARMADILHA CRÍTICA — Branches Git

```
⛔ O branch `main` = Template React/Vite/TanStack LEGADO (sem .lando.yml)
✅ O branch base real = feat/duplicar-pesquisa (PHP completo)
```

### Regra obrigatória para criar qualquer nova feature ou fix:

```bash
# SEMPRE partir daqui:
git checkout feat/duplicar-pesquisa
git checkout -b feat/nome-da-feature
```

### Mapa de branches PHP (todas baseadas em feat/implementacao-completa-prd)

| Branch | Descrição |
|--------|-----------|
| `feat/implementacao-completa-prd` | Base PHP — Fases 1–9 |
| `feat/gerador-respostas` | +Gerador de respostas fictícias |
| `feat/duplicar-pesquisa` | **+Duplicar pesquisa (BASE ATUAL)** |
| `feat/chat-respondente-ux` | +Redesign UX do chat respondente |

---

## 🔍 Como verificar se você está no branch certo

```bash
# Deve mostrar .lando.yml, app/, public/, composer.json
ls

# NUNCA deve mostrar: package.json, vite.config.ts, bun.lock, src/
# Se mostrar qualquer um desses → você está no main React. Mude de branch!
```

---

## 📖 Documentação de referência

- Regras de negócio completas → `production_artifacts/BUSINESS_RULES.md`
- PRD original → `production_artifacts/PRD.md`
- Specs de features → `production_artifacts/*.md`
