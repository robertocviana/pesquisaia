# Especificação Técnica — Gerador de Respostas de Pesquisa (v1)

## 1. Visão Geral

Para testar a funcionalidade de geração de relatórios de forma realista e automatizada, precisamos de um mecanismo capaz de gerar dados fictícios coerentes e abundantes para qualquer pesquisa cadastrada na plataforma. O objetivo é simular respostas completas fornecidas por respondentes reais para todas as perguntas da pesquisa, salvando-as no banco de dados com data/hora e atualizando os contadores adequadamente.

---

## 2. Perguntas de Design para o Usuário (Open Questions)

Antes de iniciarmos a implementação final do código, precisamos alinhar os seguintes pontos:

> [!IMPORTANT]
> **1. Onde você prefere acionar o gerador?**
> * **Opção A (Apenas Interface Web)**: Um botão ou painel no painel de controle da pesquisa (ex: na listagem de respostas `/pesquisas/respostas?id=X` ou no detalhe `/pesquisas/detalhe?id=X`) onde você digita a quantidade desejada e o sistema gera via AJAX.
> * **Opção B (Apenas CLI/Terminal)**: Um script que você roda via Lando no terminal, por exemplo: `lando php database/generate-responses.php --survey=ID --count=30`.
> * **Opção C (Ambas - Recomendado)**: Um helper de serviço reutilizável no backend, exposto tanto em um comando CLI de conveniência quanto em um botão visível no painel web apenas em ambiente local/desenvolvimento.

> [!IMPORTANT]
> **2. Como as respostas simuladas devem ser formadas?**
> * **Opção A (Foco em IA - Respostas Ricas)**: Fazer uma requisição em lote para a API da OpenAI passando o nome, objetivo, público e perguntas da pesquisa. A IA retorna uma lista de `N` perfis de respondentes com respostas super coerentes e realistas para as perguntas. (Nota: Excelente para testar a qualidade dos insights dos relatórios de IA, mas consome cota/tokens da API a cada geração de teste).
> * **Opção B (Foco em Performance - Mock Inteligente Sem Custos)**: Uma lógica em PHP no backend com um dicionário local de respostas comuns/gerais baseadas no tipo de pergunta, ou respostas variadas e nomes fictícios randomizados. (Nota: Super rápido e custo zero de API, mas os relatórios finais da IA podem ter insights genéricos).
> * **Opção C (Híbrida - Recomendado)**: Criamos uma lógica rápida que tenta usar IA para gerar uma base de 5-10 respostas ricas diferentes de exemplo (ou usa templates de resposta dependendo do objetivo), e replica/varia essas respostas localmente de forma determinística para atingir volumes maiores (ex: 50 ou 100 respondentes), mantendo boa variedade sem estourar o limite de tokens.

> [!IMPORTANT]
> **3. Regras de Encerramento e Status:**
> * Devemos permitir gerar respostas para pesquisas com qualquer status (`rascunho`, `ativa`, `encerrada`)?
> * O correto para testar relatórios de IA é que as pesquisas estejam `encerradas`. Se o gerador for rodado em um `rascunho`, devemos forçar a pesquisa a se tornar `ativa` ou `encerrada` ao fim do processo?

---

## 3. Arquitetura Proposta (MVC)

### 3.1 Camada de Serviço (Model/Service)

Criaremos o `app/Services/ResponseGeneratorService.php` que conterá a lógica principal de negócio.

```php
namespace App\Services;

class ResponseGeneratorService {
    /**
     * Gera respondentes e respostas para a pesquisa informada.
     * 
     * @param int $surveyId ID da pesquisa
     * @param int $count Quantidade de respondentes a simular
     * @param string $strategy 'ia' ou 'local'
     * @return int Quantidade de respondentes inseridos com sucesso
     */
    public function generate(int $surveyId, int $count, string $strategy = 'local'): int;
}
```

Essa lógica irá:
1. Buscar os detalhes da pesquisa e suas perguntas (`Question::findBySurvey`).
2. Criar `N` registros na tabela `respondents` com token UUID aleatório e status `concluida`.
3. Para cada respondente, criar respostas para cada pergunta da pesquisa na tabela `responses`.
4. Incrementar adequadamente o contador `response_count` na tabela `surveys` para cada respondente concluído.
5. Invocar `Survey::checkAutoClose($surveyId)` para simular o encerramento automático caso atinja a meta.

### 3.2 Interface Web / Rota (Controller/View)

Adicionaremos uma nova rota no roteador:
* **POST** `/pesquisas/respostas/gerar` que aponta para `ResponseController::handleGenerateResponses`.

Essa rota receberá por POST:
* `survey_id`: ID da pesquisa
* `count`: Quantidade (ex: de 1 a 100)
* `strategy`: 'local' ou 'ia'
* `csrf_token`

Um botão bonito de "Simular Respostas" com carregamento (spinner) será adicionado na interface web das pesquisas (por exemplo, na aba de respostas `/pesquisas/respostas?id=X` ou no `/pesquisas/detalhe?id=X`).

### 3.3 CLI / Script de Conveniência (Command)

Criaremos o script `database/generate_responses.php` para rodar diretamente via terminal pelo Lando:

```bash
lando php database/generate_responses.php --survey=12 --count=50 --strategy=local
```

---

## 4. Plano de Verificação

### Testes Manuais
1. Criar uma nova pesquisa e publicá-la.
2. Usar o gerador para criar 30 respostas simuladas.
3. Verificar na listagem de respostas `/pesquisas/respostas?id=X` se os respondentes e as respostas foram criados corretamente.
4. Encerrar a pesquisa e gerar o relatório da IA para verificar se o relatório consegue resumir e extrair insights corretos baseados nos dados simulados.
5. Verificar se os contadores (`response_count`) no banco de dados estão consistentes.
