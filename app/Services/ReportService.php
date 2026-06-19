<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use App\Models\Report;

/**
 * ReportService — Geração de relatório analítico com IA.
 */
class ReportService
{
    private AiService $ai;

    public function __construct()
    {
        $this->ai = new AiService('gpt-4o');
    }

    /**
     * Gera (ou regenera) o relatório de uma pesquisa encerrada.
     * @throws \RuntimeException se a pesquisa não estiver encerrada ou sem respostas.
     */
    public function generate(int $surveyId, int $userId): array
    {
        $survey = Survey::findByIdForUser($surveyId, $userId);

        if (!$survey) {
            throw new \RuntimeException('Pesquisa não encontrada.');
        }

        if ($survey['status'] !== 'encerrada') {
            throw new \RuntimeException('O relatório só pode ser gerado para pesquisas encerradas.');
        }

        $questions = Question::findBySurvey($surveyId);
        $responses = Response::findBySurvey($surveyId);

        if (empty($responses)) {
            throw new \RuntimeException('Não há respostas suficientes para gerar um relatório.');
        }

        // Montar o prompt com os dados da pesquisa
        $prompt = $this->buildPrompt($survey, $questions, $responses);

        try {
            $raw = $this->ai->complete([
                ['role' => 'system', 'content' => 'Você é um especialista em análise de pesquisas qualitativas. Responda sempre em JSON válido.'],
                ['role' => 'user',   'content' => $prompt],
            ], jsonMode: true, maxTokens: 2000);
        } catch (AiException $e) {
            if ($e->isQuotaError()) {
                throw new \RuntimeException(
                    'Não foi possível gerar o relatório: cota da OpenAI esgotada. ' .
                    'Verifique seu plano em platform.openai.com/account/billing e tente novamente.'
                );
            }
            if ($e->isAuthError()) {
                throw new \RuntimeException(
                    'Não foi possível gerar o relatório: chave de API inválida. ' .
                    'Verifique o valor de OPENAI_API_KEY no .env.'
                );
            }
            // Outros erros (timeout, rede): mensagem genérica retryable
            throw new \RuntimeException($e->getMessage());
        }

        $data = json_decode($raw, true);

        $summary  = $data['summary']  ?? 'Resumo não disponível.';
        $insights = $data['insights'] ?? [];

        Report::upsert($surveyId, $summary, $insights);

        return ['summary' => $summary, 'insights' => $insights];
    }

    private function buildPrompt(array $survey, array $questions, array $responses): string
    {
        $qMap = [];
        foreach ($questions as $q) {
            $qMap[$q['id']] = $q['text'];
        }

        // Agrupar respostas por pergunta
        $grouped = [];
        foreach ($responses as $r) {
            $qText = $r['question_text'] ?? 'Pergunta';
            $grouped[$qText][] = $r['text_response'];
        }

        $dataBlock = '';
        foreach ($grouped as $question => $answers) {
            $dataBlock .= "\n**Pergunta:** {$question}\n";
            foreach ($answers as $i => $answer) {
                $dataBlock .= "  - Respondente " . ($i + 1) . ": {$answer}\n";
            }
        }

        $totalRespondents = count(array_unique(array_column($responses, 'respondent_id')));

        return <<<PROMPT
Analise os dados desta pesquisa e gere um relatório executivo em JSON.

**Pesquisa:** {$survey['name']}
**Objetivo:** {$survey['objective']}
**Público-alvo:** {$survey['audience']}
**Total de respondentes:** {$totalRespondents}

**Dados coletados:**
{$dataBlock}

Retorne um JSON com esta estrutura:
{
  "summary": "Resumo executivo de 3 a 5 parágrafos com os principais achados",
  "insights": [
    {
      "title": "Título do insight",
      "description": "Descrição detalhada do insight baseada nas respostas",
      "type": "positive|negative|neutral|opportunity"
    }
  ]
}

Gere de 3 a 6 insights relevantes. Seja específico e baseie-se nos dados reais.
PROMPT;
    }
}
