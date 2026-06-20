# Especificação Técnica — Duplicação de Pesquisas

**Versão:** v1  
**Funcionalidade:** Duplicar Pesquisa  
**Objetivo:** Permitir ao usuário duplicar uma pesquisa existente (inclusive suas perguntas) e redirecioná-lo para a tela de revisão para validar as perguntas antes da publicação.

---

## 1. Arquitetura e Fluxo (MVC)

### Rota (Router)
- **Método**: `POST`
- **Caminho**: `/pesquisas/duplicar`
- **Controlador**: `App\Controllers\SurveyController::handleDuplicar()`
- **Segurança**: Requer Autenticação + Proteção contra CSRF.

### Controller
`App\Controllers\SurveyController` adicionará o seguinte método:
- `handleDuplicar()`:
  - Executa `Auth::requireAuth()`.
  - Executa `Csrf::validate()`.
  - Obtém `survey_id` via `POST`.
  - Invoca `Survey::duplicate($surveyId, $userId)`.
  - Armazena o novo ID retornado em `$_SESSION['current_survey_id']` (opcional, para alinhar o rascunho ativo atual).
  - Redireciona o usuário para `/pesquisas/revisao?id={new_id}`.
  - Em caso de falha, armazena o erro em `$_SESSION['flash_error']` e redireciona de volta para `/pesquisas`.

### Model (Survey)
`App\Models\Survey` receberá o método:
- `duplicate(int $id, int $userId): int`:
  - Obtém a pesquisa original via `Survey::findByIdForUser($id, $userId)` (Tenant Isolation).
  - Caso não exista, lança uma exceção.
  - Inicia uma transação com o banco de dados via `Database::pdo()->beginTransaction()`.
  - Insere a nova pesquisa duplicada:
    - `name`: Nome original + `" (Cópia)"`
    - `objective`: Copiado da original.
    - `audience`: Copiado da original.
    - `status`: `'rascunho'` (garante que não seja publicada imediatamente).
    - `current_stage`: `'finalizado'` (estágio do assistente de IA concluído).
    - `goal_responses`: Copiado da original.
    - `deadline_at`: Copiado da original.
    - `public_slug`: `NULL` (será gerado na publicação).
    - `response_count`: `0`.
  - Obtém todas as perguntas originais via `Question::findBySurvey($id)`.
  - Insere as perguntas na nova pesquisa com a mesma ordem (`order_index`) e texto (`text`).
  - Commita a transação.
  - Retorna o ID da nova pesquisa gerada.

### View
`app/Views/surveys/index.php`:
- O botão de placeholder "Duplicar" será envelopado por um formulário de envio POST:
  ```html
  <form method="POST" action="/pesquisas/duplicar" class="inline-flex">
      <?= \App\Helpers\Csrf::field() ?>
      <input type="hidden" name="survey_id" value="<?= (int) $s['id'] ?>">
      <button type="submit" title="Duplicar" class="p-2 rounded-lg border border-[#e5e7eb] bg-white hover:bg-[#f3f4f6] transition">
          <i data-lucide="copy" class="w-4 h-4 text-[#6b7280]"></i>
      </button>
  </form>
  ```
- Exibição de alertas `flash_error` e `flash_success` no início do container principal.

---

## 2. Segurança e Validações

1. **Tenant Isolation**: `Survey::findByIdForUser($id, $userId)` garante que o usuário só consiga duplicar pesquisas criadas por ele mesmo.
2. **Prevenção de CSRF**: O formulário de envio POST incluirá o campo oculto com o token CSRF (`Csrf::field()`), validado no controller por `Csrf::validate()`.
3. **Integridade de Dados**: Uso de transação SQL (`beginTransaction`, `commit`, `rollBack`) garante que a pesquisa duplicada e suas perguntas sejam criadas de forma atômica ou nenhuma delas seja criada.
