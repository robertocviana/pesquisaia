<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Survey;
use App\Models\Question;

/**
 * SurveyAiService — Gerencia o fluxo conversacional de criação de pesquisa com IA.
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
- Quando gerar as perguntas, inclua-as na resposta de forma clara.
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
     * Processa uma mensagem do usuário e retorna a resposta da IA.
     *
     * @return array ['message'=>string, 'stage'=>string, 'fields'=>array, 'questions'=>array]
     */
    public function chat(int $surveyId, string $userMessage): array
    {
        // Salvar mensagem do usuário
        Conversation::add($surveyId, 'user', $userMessage);

        // Montar histórico completo para enviar à IA
        $history = Conversation::findBySurvey($surveyId);
        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        // Chamar a IA em JSON mode
        $raw = $this->ai->complete($messages, jsonMode: true, maxTokens: 1500);

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: tratar como mensagem simples
            $data = ['message' => $raw, 'stage' => 'objetivo', 'fields' => [], 'questions' => []];
        }

        $message   = $data['message']   ?? 'Desculpe, não entendi. Pode repetir?';
        $stage     = $data['stage']     ?? 'objetivo';
        $fields    = $data['fields']    ?? [];
        $questions = $data['questions'] ?? [];

        // Salvar resposta do assistente
        Conversation::add($surveyId, 'assistant', $message);

        // Atualizar campos coletados na pesquisa
        if (!empty($fields)) {
            $updateData = array_filter([
                'name'           => $fields['name']          ?? null,
                'objective'      => $fields['objective']     ?? null,
                'audience'       => $fields['audience']      ?? null,
                'goal_responses' => $fields['goal_responses'] ? (int)$fields['goal_responses'] : null,
            ]);
            if (!empty($updateData)) {
                $userId = (int) ($_SESSION['user_id'] ?? 0);
                Survey::update($surveyId, $userId, $updateData);
            }
        }

        // Salvar perguntas geradas
        if (!empty($questions)) {
            Question::createBatch($surveyId, $questions);
        }

        return [
            'message'        => $message,
            'stage'          => $stage,
            'fields'         => $fields,
            'questions'      => $questions,
            'questionsCount' => count($questions),
        ];
    }
}
