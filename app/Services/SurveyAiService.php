<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Survey;
use App\Models\Question;

/**
 * SurveyAiService — Gerencia o fluxo conversacional de criação de pesquisa com IA.
 *
 * Possui modo fallback (sem IA) que guia o usuário pela coleta manual de dados
 * quando a API da OpenAI está indisponível (quota esgotada, chave inválida, etc).
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

Siga este fluxo OBRIGATÓRIO:
1. Se ainda não tiver o OBJETIVO da pesquisa, pergunte sobre ele.
2. Se ainda não tiver o PÚBLICO-ALVO, pergunte sobre ele.
3. Se ainda não tiver o NOME da pesquisa, sugira um nome baseado no objetivo.
4. Se ainda não tiver a META DE RESPOSTAS (quantidade desejada), pergunte (aceite "não definido").
5. Quando tiver objetivo e público, GERE 4 a 6 perguntas abertas para a pesquisa.
6. Quando as perguntas estiverem prontas, comunique ao usuário que está pronto para revisar.

REGRAS IMPORTANTES:
- Seja amigável, objetivo e use português do Brasil.
- Faça UMA pergunta por vez.
- IMPORTANTE: Assim que você receber ou definir a META DE RESPOSTAS, você já terá coletado todos os dados obrigatórios (objetivo, público, nome, meta). Você deve gerar as 4 a 6 perguntas abertas IMEDIATAMENTE nessa mesma resposta! Defina o campo "stage" como "finalizado", preencha o array "questions" com as perguntas geradas e inclua-as de forma clara na "message" para o usuário revisar. Não divida isso em dois turnos.
- Sempre retorne JSON com esta estrutura:

{
  "message": "Texto da sua resposta para o usuário",
  "stage": "objetivo|publico|nome|meta|perguntas|finalizado",
  "fields": {
    "name": "Nome da pesquisa (quando definido)",
    "objective": "Objetivo (quando coletado)",
    "audience": "Público-alvo (quando coletado)",
    "goal_responses": null
  },
  "questions": ["Pergunta 1", "Pergunta 2"]
}

O campo "questions" só deve ser preenchido quando as perguntas forem geradas.
O campo "fields" deve conter apenas os campos que JÁ foram coletados nesta conversa.
PROMPT;
    }

    /**
     * Processa uma mensagem do usuário.
     * Tenta a IA real; em caso de falha por quota/auth, ativa o modo fallback manual.
     *
     * @return array ['message', 'stage', 'fields', 'questions', 'questionsCount', 'fallback_mode']
     */
    public function chat(int $surveyId, string $userMessage): array
    {
        // Salvar mensagem do usuário
        Conversation::add($surveyId, 'user', $userMessage);

        // Tentar IA real
        if ($this->ai->hasKey()) {
            try {
                return $this->callAi($surveyId, $userMessage);
            } catch (AiException $e) {
                // Para quota e auth: ativar modo manual (problema de configuração, sem retry)
                if ($e->isQuotaError() || $e->isAuthError()) {
                    error_log('[PesquisaIA] AiService degraded: ' . $e->getErrorCode() . ' — ' . $e->getMessage());
                    return $this->fallbackMode($surveyId, $userMessage, $e->getMessage());
                }

                // Para timeout/rede: relançar para o controller exibir mensagem de retry
                throw $e;
            }
        }

        // Sem chave configurada: modo manual direto
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

        $raw  = $this->ai->complete($messages, jsonMode: true, maxTokens: 1500);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = ['message' => $raw, 'stage' => 'objetivo', 'fields' => [], 'questions' => []];
        }

        return $this->processAiResponse($surveyId, $data);
    }

    /**
     * Modo fallback manual — coleta informações sem IA via fluxo determinístico.
     * Analisa o histórico da conversa para saber em qual etapa o usuário está.
     */
    private function fallbackMode(int $surveyId, string $userMessage, string $aiError): array
    {
        $history  = Conversation::findBySurvey($surveyId);
        $survey   = Survey::findById($surveyId);

        // Contar turnos do usuário (excluindo a mensagem atual que acabou de ser adicionada)
        $userTurns = count(array_filter($history, fn($h) => $h['role'] === 'user'));

        // Determinar etapa pelo que já temos coletado
        $hasObjective = !empty($survey['objective']);
        $hasAudience  = !empty($survey['audience']);
        $hasName      = !empty($survey['name']) && $survey['name'] !== 'Nova pesquisa';
        $hasQuestions = count(Question::findBySurvey($surveyId)) > 0;

        // ─── Processar a resposta do usuário para salvar no banco ───────────
        $fields   = [];
        $questions = [];
        $stage    = 'objetivo';

        if (!$hasObjective) {
            // Primeira resposta = objetivo
            $fields = ['objective' => $userMessage];
            $stage  = 'publico';
            $message = "✅ Objetivo registrado!\n\nPara quem é destinada esta pesquisa? (Ex: clientes da loja, funcionários, estudantes)";

        } elseif (!$hasAudience) {
            // Segunda resposta = público-alvo
            $fields = ['audience' => $userMessage];
            $stage  = 'nome';
            $sugName = $this->suggestName($survey['objective']);
            $fields['name'] = $sugName;
            $message = "✅ Público registrado!\n\nSugestão de nome para a pesquisa: **\"{$sugName}\"**\n\nDeseja usar este nome ou quer mudar? (Responda com o nome que prefere ou \"ok\" para confirmar)";

        } elseif (!$hasName) {
            // Terceira resposta = nome
            $nome    = (strtolower(trim($userMessage)) === 'ok') ? $survey['name'] : $userMessage;
            $fields  = ['name' => $nome];
            $stage   = 'meta';
            $message = "✅ Nome definido: **\"{$nome}\"**\n\nQual é a meta de respostas? (Ex: 50, 100 — ou \"não definido\" para não ter limite)";

        } elseif (!$hasQuestions) {
            // Quarta resposta = meta + gerar perguntas
            $goalRaw = strtolower(trim($userMessage));
            $goal    = is_numeric($goalRaw) ? (int) $goalRaw : null;
            if ($goal) $fields = ['goal_responses' => $goal];

            // Gerar perguntas baseadas no objetivo e público
            $questions = $this->generateQuestions($survey['objective'], $survey['audience'] ?? $userMessage);
            $stage     = 'finalizado';

            $qList   = implode("\n", array_map(fn($i, $q) => ($i+1).". {$q}", array_keys($questions), $questions));
            $message = "✅ Perfeito! Gerei {count($questions)} perguntas para sua pesquisa:\n\n{$qList}\n\nClique em **Revisar e publicar** para ajustar as perguntas antes de publicar.";
            $message = str_replace('{count($questions)}', (string) count($questions), $message);

        } else {
            // Pesquisa já completa
            $stage   = 'finalizado';
            $message = "Sua pesquisa já está pronta para publicação! 🎉 Clique em **Revisar e publicar** para ajustar as perguntas.";
        }

        // Salvar campos coletados no banco
        if (!empty($fields)) {
            $userId     = (int) ($_SESSION['user_id'] ?? 0);
            $updateData = array_filter($fields);
            if (!empty($updateData)) {
                Survey::update($surveyId, $userId, $updateData);
            }
        }

        // Salvar perguntas geradas
        if (!empty($questions)) {
            Question::createBatch($surveyId, array_values($questions));
        }

        // Salvar resposta no histórico
        Conversation::add($surveyId, 'assistant', $message);

        return [
            'message'        => $message,
            'stage'          => $stage,
            'fields'         => $fields,
            'questions'      => array_values($questions),
            'questionsCount' => count($questions),
            'fallback_mode'  => true,
            'fallback_reason' => $aiError,
        ];
    }

    /** Processa a resposta da IA e persiste os dados no banco. */
    private function processAiResponse(int $surveyId, array $data): array
    {
        $message   = $data['message']   ?? 'Desculpe, não entendi. Pode repetir?';
        $stage     = $data['stage']     ?? 'objetivo';
        $fields    = $data['fields']    ?? [];
        $questions = $data['questions'] ?? [];

        // Redundância / Safety Net: se a IA transitou para o estágio de perguntas/finalizado 
        // mas o array de perguntas veio vazio, geramos as perguntas localmente usando o modo determinístico
        // para que o usuário não fique preso na conversa sem botões/perguntas.
        if (($stage === 'perguntas' || $stage === 'finalizado') && empty($questions)) {
            $survey = Survey::findById($surveyId);
            $objective = $fields['objective'] ?? $survey['objective'] ?? '';
            $audience = $fields['audience'] ?? $survey['audience'] ?? '';
            if (!empty($objective) && !empty($audience)) {
                $questions = $this->generateQuestions($objective, $audience);
                $stage = 'finalizado';
                $qList = implode("\n", array_map(fn($i, $q) => ($i+1).". {$q}", array_keys($questions), $questions));
                $message .= "\n\n(Gerado automaticamente):\n{$qList}";
            }
        }

        Conversation::add($surveyId, 'assistant', $message);

        if (!empty($fields)) {
            $updateData = array_filter([
                'name'           => $fields['name']           ?? null,
                'objective'      => $fields['objective']      ?? null,
                'audience'       => $fields['audience']       ?? null,
                'goal_responses' => isset($fields['goal_responses']) && $fields['goal_responses']
                    ? (int) $fields['goal_responses'] : null,
            ]);
            if (!empty($updateData)) {
                $userId = (int) ($_SESSION['user_id'] ?? 0);
                Survey::update($surveyId, $userId, $updateData);
            }
        }

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

    /** Sugere um nome baseado no objetivo da pesquisa. */
    private function suggestName(string $objective): string
    {
        $obj = strtolower(trim($objective));

        // Mapeamentos simples de palavras-chave → nomes sugeridos
        $keywords = [
            'satisfa' => 'Pesquisa de Satisfação',
            'atendim' => 'Avaliação de Atendimento',
            'produto' => 'Avaliação de Produto',
            'serviço' => 'Avaliação de Serviço',
            'func'    => 'Pesquisa de Clima Organizacional',
            'client'  => 'Pesquisa de Satisfação de Clientes',
            'event'   => 'Avaliação de Evento',
            'treinam' => 'Avaliação de Treinamento',
            'nps'     => 'Net Promoter Score',
        ];

        foreach ($keywords as $key => $name) {
            if (str_contains($obj, $key)) {
                return $name;
            }
        }

        // Fallback: usar as primeiras 4 palavras do objetivo
        $words = array_slice(explode(' ', ucwords($objective)), 0, 4);
        return 'Pesquisa — ' . implode(' ', $words);
    }

    /**
     * Gera perguntas abertas baseadas no objetivo e público (sem IA).
     * Retorna 5 perguntas genéricas, mas contextualizadas.
     */
    private function generateQuestions(string $objective, string $audience): array
    {
        $obj = strtolower($objective);
        $pub = strtolower($audience);

        // Perguntas base que funcionam para qualquer pesquisa
        return [
            "Como você avalia sua experiência geral com {$objective}?",
            "O que mais te agradou nesta experiência?",
            "O que poderia ser melhorado?",
            "Em uma escala de 0 a 10, qual a probabilidade de você recomendar para um amigo ou colega?",
            "Tem mais algum comentário ou sugestão que gostaria de compartilhar?",
        ];
    }
}
