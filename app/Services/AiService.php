<?php

namespace App\Services;

/**
 * AiService — Wrapper para OpenAI Chat Completions API.
 */
class AiService
{
    private string $apiKey;
    private string $model;

    // Códigos de erro tipados para tratamento no upstream
    public const ERR_QUOTA    = 'quota_exceeded';
    public const ERR_AUTH     = 'auth_failed';
    public const ERR_TIMEOUT  = 'timeout';
    public const ERR_NETWORK  = 'network_error';
    public const ERR_NO_KEY   = 'no_api_key';

    public function __construct(string $model = 'gpt-4o')
    {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model  = $model;
    }

    /** Verifica se a chave de API está configurada. */
    public function hasKey(): bool
    {
        return !empty($this->apiKey) && str_starts_with($this->apiKey, 'sk-');
    }

    /**
     * Envia um array de mensagens para a API e retorna o conteúdo da resposta.
     *
     * @param array  $messages   Array no formato [['role'=>'...','content'=>'...'], ...]
     * @param bool   $jsonMode   Se true, usa response_format: json_object
     * @param int    $maxTokens  Máximo de tokens na resposta
     * @throws AiException com código tipado em caso de erro
     */
    public function complete(array $messages, bool $jsonMode = false, int $maxTokens = 1024): string
    {
        if (!$this->hasKey()) {
            throw new AiException(
                'A chave da OpenAI não está configurada no .env',
                self::ERR_NO_KEY
            );
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
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        $curlErrno    = curl_errno($ch);
        curl_close($ch);

        // Erro de rede / timeout
        if ($curlError) {
            $code = ($curlErrno === CURLE_OPERATION_TIMEDOUT) ? self::ERR_TIMEOUT : self::ERR_NETWORK;
            throw new AiException(
                "Não foi possível conectar à OpenAI. Verifique sua conexão e tente novamente.",
                $code
            );
        }

        $data = json_decode($responseBody, true);

        // Tratar erros HTTP específicos com mensagens amigáveis
        if ($httpCode !== 200) {
            $apiMsg = $data['error']['message'] ?? '';
            $apiCode = $data['error']['code'] ?? $data['error']['type'] ?? '';

            // 429 — Quota / Rate limit
            if ($httpCode === 429) {
                $isQuota = str_contains($apiMsg, 'quota') || str_contains($apiCode, 'quota');
                $code    = $isQuota ? self::ERR_QUOTA : 'rate_limit';
                $userMsg = $isQuota
                    ? 'Sua cota da OpenAI foi esgotada. Verifique seu plano em platform.openai.com/account/billing.'
                    : 'Muitas requisições em pouco tempo. Aguarde alguns segundos e tente novamente.';
                throw new AiException($userMsg, $code);
            }

            // 401 — Chave inválida
            if ($httpCode === 401) {
                throw new AiException(
                    'Chave de API inválida. Verifique o valor de OPENAI_API_KEY no .env.',
                    self::ERR_AUTH
                );
            }

            // Outros erros
            throw new AiException(
                "Erro temporário na IA (HTTP {$httpCode}). Tente novamente em instantes.",
                'api_error_' . $httpCode
            );
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new AiException('Resposta inesperada da API. Tente novamente.', 'unexpected_response');
        }

        return $data['choices'][0]['message']['content'];
    }
}
