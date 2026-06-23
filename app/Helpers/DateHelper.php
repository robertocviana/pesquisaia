<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    /**
     * Retorna a timezone ativa para o usuário atual (sessão) ou padrão (America/Sao_Paulo).
     */
    public static function getTimezone(): DateTimeZone
    {
        // Se a sessão não estiver ativa, tenta iniciar para buscar a timezone
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tz = $_SESSION['user_timezone'] ?? 'America/Sao_Paulo';
        return new DateTimeZone($tz);
    }

    /**
     * Converte uma data UTC (ou outra do BD) para a timezone do usuário e formata.
     */
    public static function format(?string $dateTimeStr, string $format = 'd/m/Y H:i', string $dbTimezone = 'UTC'): string
    {
        if (!$dateTimeStr) {
            return '';
        }

        try {
            $dt = new DateTime($dateTimeStr, new DateTimeZone($dbTimezone));
            $dt->setTimezone(self::getTimezone());
            return $dt->format($format);
        } catch (\Exception $e) {
            return $dateTimeStr;
        }
    }

    /**
     * Retorna a data atual no fuso horário do usuário no formato Y-m-d (útil para data limite).
     */
    public static function todayString(): string
    {
        try {
            $dt = new DateTime('now', self::getTimezone());
            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }

    /**
     * Retorna o timestamp de uma data convertida para o fuso do usuário.
     */
    public static function timestamp(?string $dateTimeStr, string $dbTimezone = 'UTC'): int
    {
        if (!$dateTimeStr) {
            return time();
        }

        try {
            $dt = new DateTime($dateTimeStr, new DateTimeZone($dbTimezone));
            $dt->setTimezone(self::getTimezone());
            return $dt->getTimestamp();
        } catch (\Exception $e) {
            return strtotime($dateTimeStr) ?: time();
        }
    }

    /**
     * Converte uma data/hora no fuso do usuário para UTC (formato Y-m-d H:i:s) para salvar no BD.
     */
    public static function toUtc(?string $localDateTimeStr): ?string
    {
        if (!$localDateTimeStr || trim($localDateTimeStr) === '') {
            return null;
        }

        try {
            $cleaned = str_replace('T', ' ', $localDateTimeStr);
            $dt = new DateTime($cleaned, self::getTimezone());
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
