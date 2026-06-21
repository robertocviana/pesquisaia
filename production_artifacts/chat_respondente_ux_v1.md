# Especificação Técnica — Chat do Respondente: Redesign UX
> Versão: v1 | Data: 2026-06-21 | Branch alvo: `feat/chat-respondente-ux`

---

## 1. Resumo Executivo

A página `/r/{slug}/chat` atualmente funciona como um **chat contínuo com histórico acumulado**, o que causa problemas de UX especialmente no mobile:

- O histórico ocupa a tela e empurra a pergunta atual para cima
- No mobile, ao clicar no input, o teclado virtual sobe e o botão de envio desaparece
- A barra de progresso some da viewport quando o usuário rola a tela

### Objetivo

Transformar o chat em um **fluxo de perguntas sequenciais estilo "typeform"**: uma pergunta por vez, com transição de slide/fade, sem histórico visível — mantendo a lógica de AJAX e salvamento existente intacta.

---

## 2. Análise do Estado Atual

### Arquivo afetado (único)
- **`app/Views/respondent/chat.php`** — única view a ser modificada
- Nenhum Controller, Model ou rota precisa mudar
- A lógica de AJAX (`/r/responder`) permanece inalterada

### Problemas identificados

| Problema | Causa Raiz |
|----------|-----------|
| Histórico acumula na tela | `addMessage()` faz append no container, nunca limpa |
| Botão some no mobile | O container do input está fora do viewport quando teclado virtual sobe |
| Barra de progresso não fixa | Está em fluxo normal do documento, sem posição fixada |
| Layout não é mobile-first | Sem `dvh` (dynamic viewport height) para considerar o teclado virtual |

---

## 3. Design Direction

### Aesthetic Direction
**"Focus Mode" — Luxury Minimal com Motion Intencional**
Inspiração: Typeform, Linear — interfaces que fazem o usuário sentir que a pergunta merece atenção total.

### Design System
- **Fonte**: `Plus Jakarta Sans` (display) via CDN + `Inter` (já no projeto)
- **Color Story**: Dominante `#6366f1` (indigo) já estabelecido no design system
- **Motion**: Slide-in da direita ao avançar; fade-out ao sair. 400ms cubic-bezier
- **Âncora Memorável**: Pergunta central com tipografia grande (`clamp(1.5rem, 4vw, 2.5rem)`), ocupando espaço generoso — sensação de foco total

---

## 4. Novo Layout — Especificação

### Estrutura de Telas (3 zonas fixas)

```
┌─────────────────────────────────────┐  ← position: fixed, top: 0
│  [Logo/Nome]   Pergunta X de N      │  Header
│  ████████████░░░░░░░░░░░ 40%        │  Progress bar integrada
├─────────────────────────────────────┤
│                                     │
│   (área central — flex center)      │  Zona de pergunta
│                                     │
│   "Qual sua opinião sobre..."       │  ← texto grande, centralizado
│                                     │
├─────────────────────────────────────┤  ← position: fixed, bottom: 0
│  [Digite sua resposta............]  │  Input zone
│  [                    [Enviar →]  ] │  Botão SEMPRE visível
└─────────────────────────────────────┘
```

### Comportamento de Transição
1. Usuário digita resposta e clica "Enviar"
2. A resposta é salva via AJAX (mantido)
3. A pergunta atual faz **fade-out + slide-left** (`translateX(-40px), opacity: 0`)
4. A nova pergunta aparece **fade-in + slide-right** (`translateX(40px) → 0`)
5. O contador e barra de progresso atualizam com animação suave

### Mobile-specific
- Usar `height: 100dvh` para considerar o teclado virtual do iOS/Android
- Input zone com `position: fixed; bottom: 0` + `padding-bottom: env(safe-area-inset-bottom)`
- Botão de envio DENTRO do mesmo container fixo do input — **nunca some**
- Texto da pergunta com `font-size: clamp(1.25rem, 4vw, 2rem)` — responsivo

---

## 5. Especificação JS

### Remover
- `addMessage()` — não mais necessário (sem chat bubbles)
- `scrollToEnd()` — não mais necessário
- `#messages-container` / `#chat-scroll` — elementos removidos

### Adicionar
- `showQuestion(step)` — atualiza o texto central com animação
- `transitionToNext()` — orquestra fade-out → save → fade-in

### Estados de UI
| Estado | Descrição |
|--------|-----------|
| `idle` | Pergunta visível, input habilitado |
| `sending` | Input desabilitado, botão mostra spinner |
| `transitioning` | Animação em andamento, input desabilitado |
| `done` | Último step — redirect para `/concluido` |

---

## 6. Arquivo a Modificar

### [MODIFY] `app/Views/respondent/chat.php`
**Substituição total** da view (HTML + CSS + JS).
- Backend PHP permanece inalterado
- Variáveis PHP preservadas: `$survey`, `$questions`, `$respondent`, `$answered`, `$slug`
- Rotas AJAX preservadas: `POST /r/responder`

---

## 7. Regras de Negócio Preservadas

- ✅ Coleta de nome antes da primeira pergunta (mantida como primeiro "step")
- ✅ Salvamento AJAX via `POST /r/responder` (inalterado)
- ✅ Controle de `step` para perguntas já respondidas (`answered`)
- ✅ Redirect para `/r/{slug}/concluido` ao finalizar
- ✅ Sem nenhuma mudança em banco de dados, rotas ou controllers

---

## 8. Não-Objetivos (YAGNI)

- ❌ Não implementar "voltar pergunta anterior"
- ❌ Não mudar o fluxo de autenticação do respondente
- ❌ Não alterar a tela de intro ou concluído
- ❌ Não mudar nenhuma rota ou controller

---

## 9. Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Teclado virtual iOS causa jump de layout | `100dvh` + `env(safe-area-inset-bottom)` |
| Animação trava em dispositivos lentos | `prefers-reduced-motion` desativa animações |
| Input perde foco após transição | Re-focar input via JS após animação completar |
