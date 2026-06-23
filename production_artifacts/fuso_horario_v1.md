# Technical Specification - Adequação de Fuso Horário (GMT-3 São Paulo)

## Objetivo
Implementar a adequação do sistema de exibição de datas, horas e checagem de regras de negócios para respeitar o fuso horário `America/Sao_Paulo` (GMT-3), mantendo o banco de dados em UTC/horário do servidor. A solução prepara a plataforma para suportar fusos horários customizados por usuário de forma transparente no futuro.

---

## 1. Nova Classe: `DateHelper`
Criação da classe helper `App\Helpers\DateHelper` para gerenciar todas as conversões e formatações de data e hora.

### Arquivo: `[NEW] app/Helpers/DateHelper.php`
```php
<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class DateHelper
{
    /**
     * Retorna a timezone activa para o usuário atual (sessão) ou padrão (America/Sao_Paulo).
     */
    public static function getTimezone(): DateTimeZone
    {
        // Se houver timezone definida na sessão do usuário logado, usa ela.
        // Se não, usa America/Sao_Paulo por padrão.
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
     * Retorna a data atual no fuso horário do usuário no formato Y-m-d (para data limite).
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
}
```

---

## 2. Alterações nos Arquivos Existentes

### 2.1 [MODIFY] [Survey.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Models/Survey.php)
Modificar a função `checkAutoClose` para fazer a comparação da data de expiração (`deadline_at`) usando a data atual convertida para o fuso horário de São Paulo (ou o fuso ativo).
* **Antes**: `if ($survey['deadline_at'] && $survey['deadline_at'] <= date('Y-m-d'))`
* **Depois**: `if ($survey['deadline_at'] && $survey['deadline_at'] <= \App\Helpers\DateHelper::todayString())`

### 2.2 [MODIFY] [relatorio.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/surveys/relatorio.php)
* **Antes**: `Gerado em <?= date('d/m/Y H:i', strtotime($report['generated_at'])) ?>`
* **Depois**: `Gerado em <?= \App\Helpers\DateHelper::format($report['generated_at'], 'd/m/Y H:i') ?>`

### 2.3 [MODIFY] [detalhe.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/surveys/detalhe.php)
* **Antes**: `Criada em <?= date('d/m/Y', strtotime($survey['created_at'])) ?>`
* **Depois**: `Criada em <?= \App\Helpers\DateHelper::format($survey['created_at'], 'd/m/Y') ?>`
* **Antes**: `<?= $survey['deadline_at'] ? date('d/m/Y', strtotime($survey['deadline_at'])) : '...' ?>`
* **Depois**: `<?= $survey['deadline_at'] ? \App\Helpers\DateHelper::format($survey['deadline_at'], 'd/m/Y') : '...' ?>`

### 2.4 [MODIFY] [export_pdf.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/surveys/export_pdf.php)
* **Antes**: `Gerado em <?= date('d/m/Y H:i') ?>`
* **Depois**: `Gerado em <?= \App\Helpers\DateHelper::format('now', 'd/m/Y H:i') ?>`
* **Antes**: `<?= date('d/m/Y', strtotime($rsp['created_at'])) ?>`
* **Depois**: `<?= \App\Helpers\DateHelper::format($rsp['created_at'], 'd/m/Y') ?>`

### 2.5 [MODIFY] [respostas.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Views/surveys/respostas.php)
* **Antes**: `$timestampMs = strtotime($r['created_at']) * 1000;`
* **Depois**: `$timestampMs = \App\Helpers\DateHelper::timestamp($r['created_at']) * 1000;`
* **Antes**: `<?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>`
* **Depois**: `<?= \App\Helpers\DateHelper::format($r['created_at'], 'd/m/Y H:i') ?>`

### 2.6 [MODIFY] [ExportService.php](file:///Users/robertoviana/dreamhost/pesquisaia/app/Services/ExportService.php)
* **Antes**: `date('d/m/Y H:i', strtotime($rsp['created_at'])),`
* **Depois**: `\App\Helpers\DateHelper::format($rsp['created_at'], 'd/m/Y H:i'),`

---

## 3. Plano de Validação e Testes
* **Testes Manuais**:
  1. Acessar a tela de listagem de pesquisas e verificar se a data de criação está formatada corretamente no padrão brasileiro.
  2. Acessar o detalhe de uma pesquisa e verificar a data de criação.
  3. Adicionar uma data limite (`deadline_at`) para hoje e verificar se o fechamento automático funciona corretamente considerando o fuso GMT-3.
  4. Gerar relatórios e exportações (PDF/CSV) e verificar se as datas refletem a timezone de São Paulo (`America/Sao_Paulo`).
  5. Simular a alteração temporária da timezone na sessão para testar o suporte a fusos dinâmicos e verificar se as exibições mudam correspondentemente.
