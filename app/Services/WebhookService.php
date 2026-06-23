<?php

namespace App\Services;

class WebhookService
{
    /**
     * Envia um payload em formato JSON para o webhook configurado no .env.
     */
    public static function send(string $event, array $data): bool
    {
        $url = $_ENV['PESQUISAI_WEBHOOKS'] ?? '';
        if (empty($url)) {
            return false;
        }

        $payload = [
            'event'     => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'data'      => $data
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
