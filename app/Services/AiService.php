<?php

namespace App\Services;

/**
 * AiService — Wrapper para OpenAI Chat Completions API.
 */
class AiService
{
    private string $apiKey;
    private string $model;

    public function __construct(string $model = 'gpt-4o')
    {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model  = $model;
    }

    /**
     * Envia um array de mensagens para a API e retorna o conteúdo da resposta.
     *
     * @param array  $messages       Array no formato [['role'=>'...','content'=>'...'], ...]
     * @param bool   $jsonMode       Se true, usa response_format: json_object
     * @param int    $maxTokens      Máximo de tokens na resposta
     */
    public function complete(array $messages, bool $jsonMode = false, int $maxTokens = 1024): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY não configurada no .env');
        }

        $payload = [
            'model'      => $this->model,
            'messages'   => $messages,
            'max_tokens' => $maxTokens,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException("Erro de rede ao chamar OpenAI: {$curlError}");
        }

        $data = json_decode($responseBody, true);

        if ($httpCode !== 200 || !isset($data['choices'][0]['message']['content'])) {
            $msg = $data['error']['message'] ?? "HTTP {$httpCode}";
            throw new \RuntimeException("Erro na API OpenAI: {$msg}");
        }

        return $data['choices'][0]['message']['content'];
    }
}
