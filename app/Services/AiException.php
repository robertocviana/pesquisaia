<?php

namespace App\Services;

/**
 * AiException — Exceção tipada para erros da API de IA.
 * Carrega um código de erro legível para tratamento no upstream.
 */
class AiException extends \RuntimeException
{
    public function __construct(string $message, private readonly string $errorCode = 'unknown')
    {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function isQuotaError(): bool
    {
        return $this->errorCode === AiService::ERR_QUOTA;
    }

    public function isAuthError(): bool
    {
        return $this->errorCode === AiService::ERR_AUTH;
    }

    public function isNetworkError(): bool
    {
        return in_array($this->errorCode, [AiService::ERR_TIMEOUT, AiService::ERR_NETWORK], true);
    }
}
