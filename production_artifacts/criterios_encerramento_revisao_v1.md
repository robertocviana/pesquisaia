# Especificação Técnica: Critérios de Encerramento na Revisão

Esta especificação detalha a inclusão dos campos de encerramento da pesquisa ("Meta de respostas" e "Data limite") na tela de revisão da pesquisa, permitindo que o usuário visualize e altere essas configurações antes de publicá-la.

---

## 1. Contexto & Motivação

Atualmente, ao criar uma pesquisa conversacional, a IA ou o fluxo manual coleta a meta de respostas (`goal_responses`) e a data limite (`deadline_at`). No entanto, na página de revisão (`/pesquisas/revisao?id=X`), estes critérios de encerramento não são exibidos nem podem ser alterados pelo usuário antes de efetuar a publicação.

Esta funcionalidade visa adicionar os campos correspondentes na seção de dados gerais ou em uma nova seção na tela de revisão, garantindo que o usuário possa configurá-los/ajustá-los.

---

## 2. Componentes Afetados

### 2.1 View: `app/Views/surveys/revisao.php`
- Adição de uma nova seção visual no HTML para os critérios de encerramento (opcionais), utilizando classes de estilo consistentes com o design system (Tailwind).
- Atualização do script JS (`saveDraft`) para extrair os valores dos novos inputs (`goal_responses` e `deadline_at`) e anexá-los ao `FormData` enviado via POST.

### 2.2 Controller: `app/Controllers/SurveyController.php`
- O controller `handleRevisaoSalvar()` já aceita e mapeia `goal_responses` e `deadline_at`. Nenhuma mudança lógica severa é necessária no backend, mas faremos a homologação para garantir que strings vazias sejam tratadas corretamente como `NULL` na persistência do banco.

---

## 3. Detalhes da Interface (UI/UX)

Propõe-se a inclusão de uma seção "Critérios de encerramento (opcional)" logo abaixo da seção "Dados gerais", mantendo a consistência do design limpo e moderno da aplicação:

```html
<!-- Critérios de encerramento -->
<section class="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_0_rgb(15_23_42_/_0.04)] mb-6">
    <h2 class="font-semibold text-[#1e1b4b] mb-4">Critérios de encerramento (opcional)</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-[#1e1b4b]">Meta de respostas</label>
            <input type="number" id="survey-goal-responses-input" min="1" value="<?= htmlspecialchars($survey['goal_responses'] ?? '') ?>" placeholder="Ex: 100"
                class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            <p class="text-xs text-[#6b7280] mt-1">A pesquisa será encerrada automaticamente ao atingir este número.</p>
        </div>
        <div>
            <label class="text-sm font-medium text-[#1e1b4b]">Data limite</label>
            <input type="date" id="survey-deadline-input" value="<?= htmlspecialchars($survey['deadline_at'] ?? '') ?>"
                class="mt-1.5 w-full rounded-lg border border-[#e5e7eb] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#6366f1]">
            <p class="text-xs text-[#6b7280] mt-1">A pesquisa será encerrada automaticamente ao final desta data.</p>
        </div>
    </div>
</section>
```

---

## 4. Plano de Testes e Validação

1. **Validação de Exibição**: Carregar `/pesquisas/revisao?id=X` e garantir que os campos aparecem preenchidos caso a pesquisa já possua dados, ou vazios caso contrário.
2. **Validação de Atualização**:
   - Alterar a meta de respostas para `50` e a data limite para uma data futura, clicar em "Salvar rascunho" e verificar se as informações persistem no banco.
   - Limpar ambos os campos, salvar o rascunho, e garantir que no banco passem a valer `NULL`.
3. **Validação de Publicação**: Efetuar a publicação da pesquisa revisada e validar se no detalhe (`/pesquisas/detalhe?id=X`) as metas e prazos são mostrados corretamente.
