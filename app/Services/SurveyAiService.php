<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Survey;
use App\Models\Question;

/**
 * SurveyAiService — Gerencia o fluxo conversacional de criação de pesquisa com IA.
 *
 * Guia o usuário passo a passo através de 8 etapas para consolidar um escopo
 * de pesquisa de forma assistida.
 */
class SurveyAiService
{
    private AiService $ai;

    public function __construct()
    {
        $this->ai = new AiService('gpt-4o');
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Você é um assistente especializado em criar pesquisas conversacionais.
Seu objetivo é coletar as informações necessárias para criar uma pesquisa de forma amigável e conversacional.

Siga este fluxo OBRIGATÓRIO de etapas, uma por uma, de forma estrita:

1. tipo — Pergunte qual tipo de pesquisa o usuário deseja fazer. Apresente estes exemplos:
   - Validar uma ideia de negócio
   - Entender uma dor de clientes
   - Avaliar uma nova funcionalidade
   - Entender comportamento de usuários
   - Testar uma proposta de valor
   - Outro
   Pergunte: "Qual tipo de pesquisa você quer criar?"

2. objetivo — Pergunte qual é o objetivo principal da pesquisa. Pergunte: "O que você quer descobrir ou qual decisão você quer conseguir tomar com essa validação?"

3. publico — Pergunte quem será o público entrevistado (as pessoas a serem ouvidas). IMPORTANTE: Você DEVE sugerir 2 ou 3 exemplos práticos de público-alvo baseando-se no objetivo informado pelo usuário na etapa anterior.

4. hipotese — Pergunte qual é a hipótese principal que o usuário quer validar (o que ele acredita ser verdade hoje). Dê exemplos claros e curtos de hipóteses (por exemplo: "Empresários querem aprender IA, mas não sabem por onde começar"). Pergunte: "Qual é a principal hipótese que você quer validar?"

5. perguntas_previas — Pergunte se o usuário já tem algumas perguntas específicas que deseja incluir na pesquisa. Se tiver, peça para ele enviar.

6. perguntas_sugeridas — Se o usuário enviou perguntas prévias, valide-as. Sugira e elabore uma lista final de 4 a 6 perguntas da pesquisa (incluindo as prévias dele, se fizer sentido). Pergunte se ele deseja que você sugira mais algumas perguntas ou se ele prefere avançar.

7. meta_encerramento — Pergunte sobre a meta de encerramento da pesquisa, explicando estas 3 opções de forma simples:
   - Quantidade de respostas: A pesquisa encerra ao atingir um número de respostas (ex: 10 respostas).
   - Data máxima: Encerra em um dia e horário específico (ex: 20/06/2026 às 10:00).
   - Encerrar manualmente: O usuário clica para finalizar a qualquer momento (opção sempre disponível).
   Pergunte qual dessas opções ele prefere definir.

8. revisao_chat — Apresente um resumo detalhado e bem formatado em Markdown contendo:
   - Tipo de pesquisa
   - Objetivo principal
   - Público-alvo
   - Hipótese principal
   - Perguntas da pesquisa (numeradas)
   - Meta de encerramento
   E pergunte se está tudo correto ou se deseja alterar alguma coisa.
   IMPORTANTE: Se o usuário disser que quer alterar algo (ex: "mudar o público", "alterar a hipótese", "editar a pergunta 2"), você deve processar o ajuste solicitado, atualizar as informações e reapresentar o resumo atualizado na mesma resposta, perguntando novamente se está ok. Repita isso até que ele aprove.

9. finalizado — Quando o usuário aprovar explicitamente o resumo (ex: "sim", "tudo certo", "está ótimo", "pode avançar"), sugira um título curto e atraente para a pesquisa (ex: "Pesquisa de Clima Organizacional", "Validação de Ideia IA", etc.) e preencha-o no campo "name" dentro de "fields". Defina o estágio como "finalizado" e informe ao usuário que a pesquisa está pronta para ser revisada na tela final.

REGRAS DE CONDUÇÃO DA CONVERSA:
- Seja amigável, acolhedor, profissional e use português do Brasil.
- Faça apenas UMA pergunta por vez. Não acumule etapas em um único turno.
- A cada interação, retorne obrigatoriamente um JSON válido com esta estrutura:

{
  "message": "Texto legível que será exibido para o usuário (pode conter markdown)",
  "stage": "tipo|objetivo|publico|hipotese|perguntas_previas|perguntas_sugeridas|meta_encerramento|revisao_chat|finalizado",
  "fields": {
    "name": "Nome sugerido para a pesquisa (só preencher no final, no estágio finalizado)",
    "objective": "Objetivo principal da pesquisa (uma vez coletado)",
    "audience": "Público-alvo (uma vez coletado)",
    "goal_responses": 10, // número de respostas se o encerramento for por quantidade (null se não houver ou for manual)
    "deadline_at": "2026-06-20" // data formato YYYY-MM-DD se o encerramento for por data (null se não houver ou for manual)
  },
  "questions": ["Pergunta 1", "Pergunta 2"] // Lista de perguntas geradas. Só preencha a partir da etapa de perguntas_sugeridas
}

O campo "questions" deve conter todas as perguntas sugeridas/definidas a partir da etapa 6.
O campo "fields" deve conter os metadados da pesquisa coletados até o momento.
PROMPT;
    }

    /**
     * Processa uma mensagem do usuário.
     * Tenta a IA real; em caso de falha por quota/auth/rede, ativa o modo fallback manual (erro).
     *
     * @return array ['message', 'stage', 'fields', 'questions', 'questionsCount', 'fallback_mode']
     */
    public function chat(int $surveyId, string $userMessage): array
    {
        // Garantir que a mensagem de boas-vindas do assistente existe antes da resposta do usuário
        $history = Conversation::findBySurvey($surveyId);
        if (empty($history)) {
            $welcomeMessage = "Olá! Vou te ajudar a criar sua pesquisa.\n\n**Qual tipo de pesquisa você deseja fazer?**\n\nExemplos:\n• Validar uma ideia de negócio\n• Entender uma dor de clientes\n• Avaliar uma nova funcionalidade\n• Entender comportamento de usuários\n• Testar uma proposta de valor\n• Outro\n\nEscreva o tipo de pesquisa que você quer criar.";
            Conversation::add($surveyId, 'assistant', $welcomeMessage);
        }

        // Salvar mensagem do usuário
        Conversation::add($surveyId, 'user', $userMessage);

        // Tentar IA real
        if ($this->ai->hasKey()) {
            try {
                return $this->callAi($surveyId, $userMessage);
            } catch (AiException $e) {
                error_log('[PesquisaIA] AiService degraded: ' . $e->getErrorCode() . ' — ' . $e->getMessage());
                return $this->fallbackMode($surveyId, $userMessage, $e->getMessage());
            }
        }

        // Sem chave configurada: modo manual (erro de indisponibilidade)
        return $this->fallbackMode($surveyId, $userMessage, 'IA não configurada.');
    }

    /** Chama a API OpenAI e processa a resposta JSON. */
    private function callAi(int $surveyId, string $userMessage): array
    {
        $history  = Conversation::findBySurvey($surveyId);
        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $raw  = $this->ai->complete($messages, jsonMode: true, maxTokens: 2000);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = ['message' => $raw, 'stage' => 'tipo', 'fields' => [], 'questions' => []];
        }

        return $this->processAiResponse($surveyId, $data);
    }

    /**
     * Modo fallback manual — avisa que não é possível criar a pesquisa no momento.
     */
    private function fallbackMode(int $surveyId, string $userMessage, string $aiError): array
    {
        $message = "⚠️ O assistente de criação de pesquisas com IA está temporariamente indisponível no momento.\n\nNão é possível criar uma pesquisa conversacional sem o assistente. Por favor, tente novamente mais tarde.";

        // Salvar a resposta no histórico do chat
        Conversation::add($surveyId, 'assistant', $message);

        return [
            'message'        => $message,
            'stage'          => 'tipo',
            'fields'         => [],
            'questions'      => [],
            'questionsCount' => 0,
            'fallback_mode'  => true,
            'fallback_reason' => $aiError,
        ];
    }

    /** Processa a resposta da IA e persiste os dados no banco. */
    private function processAiResponse(int $surveyId, array $data): array
    {
        $message   = $data['message']   ?? 'Desculpe, não entendi. Pode repetir?';
        $stage     = $data['stage']     ?? 'tipo';
        $fields    = $data['fields']    ?? [];
        $questions = $data['questions'] ?? [];

        Conversation::add($surveyId, 'assistant', $message);

        $updateData = [];

        if (!empty($fields)) {
            if (isset($fields['name']) && !empty($fields['name'])) {
                $updateData['name'] = $fields['name'];
            }
            if (isset($fields['objective']) && !empty($fields['objective'])) {
                $updateData['objective'] = $fields['objective'];
            }
            if (isset($fields['audience']) && !empty($fields['audience'])) {
                $updateData['audience'] = $fields['audience'];
            }
            if (array_key_exists('goal_responses', $fields)) {
                $updateData['goal_responses'] = $fields['goal_responses'] ? (int) $fields['goal_responses'] : null;
            }
            if (array_key_exists('deadline_at', $fields)) {
                $updateData['deadline_at'] = $fields['deadline_at'] ?: null;
            }
        }

        // Atualizar o current_stage no banco
        $updateData['current_stage'] = $stage;

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        Survey::update($surveyId, $userId, $updateData);

        if (!empty($questions)) {
            Question::createBatch($surveyId, $questions);
        }

        return [
            'message'        => $message,
            'stage'          => $stage,
            'fields'         => $fields,
            'questions'      => $questions,
            'questionsCount' => count($questions),
            'fallback_mode'  => false,
        ];
    }
}
