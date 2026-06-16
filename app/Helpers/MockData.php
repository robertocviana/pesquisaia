<?php
/**
 * Mock Data Helper
 * Substitui o hook useSurveys() do React com dados estáticos para prototipagem.
 */

namespace App\Helpers;

class MockData
{
    public static function surveys(): array
    {
        return [
            [
                'id'        => 's-001',
                'name'      => 'Satisfação de Atendimento Q2',
                'status'    => 'ativa',
                'objective' => 'Medir a satisfação dos clientes com nosso time de suporte no segundo trimestre.',
                'audience'  => 'Clientes que abriram chamado nos últimos 30 dias',
                'goal'      => 50,
                'createdAt' => '2026-05-10',
                'questions' => [
                    ['id' => 'q1', 'text' => 'Como você descreveria sua experiência geral com o atendimento?'],
                    ['id' => 'q2', 'text' => 'O que mais te agradou no suporte que recebeu?'],
                    ['id' => 'q3', 'text' => 'O que poderíamos ter feito diferente ou melhor?'],
                    ['id' => 'q4', 'text' => 'Você nos recomendaria a um amigo ou colega? Por quê?'],
                ],
                'responses' => [
                    [
                        'id'          => 'r-001',
                        'respondent'  => 'Maria Silva',
                        'date'        => '2026-06-01',
                        'durationMin' => 4,
                        'status'      => 'concluída',
                        'answers'     => [
                            ['questionId' => 'q1', 'text' => 'Fui muito bem atendida, resolveram rapidinho!'],
                            ['questionId' => 'q2', 'text' => 'A rapidez e a simpatia do atendente.'],
                            ['questionId' => 'q3', 'text' => 'O tempo de espera inicial poderia ser menor.'],
                            ['questionId' => 'q4', 'text' => 'Com certeza recomendaria. Experiência ótima.'],
                        ],
                    ],
                    [
                        'id'          => 'r-002',
                        'respondent'  => 'João Pereira',
                        'date'        => '2026-06-03',
                        'durationMin' => 3,
                        'status'      => 'concluída',
                        'answers'     => [
                            ['questionId' => 'q1', 'text' => 'Boa, mas demorou um pouco para resolver.'],
                            ['questionId' => 'q2', 'text' => 'A equipe foi educada e prestativa.'],
                            ['questionId' => 'q3', 'text' => 'Melhorar a comunicação sobre o status do pedido.'],
                            ['questionId' => 'q4', 'text' => 'Provavelmente sim, com algumas ressalvas.'],
                        ],
                    ],
                    [
                        'id'          => 'r-003',
                        'respondent'  => 'Ana Costa',
                        'date'        => '2026-06-05',
                        'durationMin' => 5,
                        'status'      => 'concluída',
                        'answers'     => [
                            ['questionId' => 'q1', 'text' => 'Excelente! Tudo funcionou como esperado.'],
                            ['questionId' => 'q2', 'text' => 'A resolução na primeira tentativa foi incrível.'],
                            ['questionId' => 'q3', 'text' => 'Nada a reclamar, foi perfeito.'],
                            ['questionId' => 'q4', 'text' => 'De olhos fechados! Recomendo muito.'],
                        ],
                    ],
                ],
            ],
            [
                'id'        => 's-002',
                'name'      => 'NPS Produto — Maio 2026',
                'status'    => 'encerrada',
                'objective' => 'Coletar o Net Promoter Score dos usuários do produto principal.',
                'audience'  => 'Usuários ativos há mais de 3 meses',
                'goal'      => 100,
                'createdAt' => '2026-05-01',
                'questions' => [
                    ['id' => 'q1', 'text' => 'Em uma escala de 0 a 10, o quanto você nos recomendaria?'],
                    ['id' => 'q2', 'text' => 'Qual o principal motivo da sua nota?'],
                    ['id' => 'q3', 'text' => 'Tem alguma sugestão para melhorarmos?'],
                ],
                'responses' => array_map(fn($i) => [
                    'id'          => 'r-n' . $i,
                    'respondent'  => 'Respondente ' . $i,
                    'date'        => '2026-05-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'durationMin' => rand(2, 6),
                    'status'      => 'concluída',
                    'answers'     => [],
                ], range(1, 18)),
            ],
            [
                'id'        => 's-003',
                'name'      => 'Onboarding Experience',
                'status'    => 'rascunho',
                'objective' => 'Avaliar a experiência de novos usuários durante o onboarding.',
                'audience'  => 'Usuários que se cadastraram nos últimos 7 dias',
                'goal'      => 30,
                'createdAt' => '2026-06-10',
                'questions' => [],
                'responses' => [],
            ],
        ];
    }

    public static function findSurvey(string $id): ?array
    {
        foreach (self::surveys() as $s) {
            if ($s['id'] === $id) return $s;
        }
        return null;
    }

    public static function findResponse(array $survey, string $rid): ?array
    {
        foreach ($survey['responses'] as $r) {
            if ($r['id'] === $rid) return $r;
        }
        return null;
    }

    public static function formatDate(string $date): string
    {
        $ts = strtotime($date);
        return date('d/m/Y', $ts);
    }

    public static function stats(array $surveys): array
    {
        $total     = count($surveys);
        $ativas    = count(array_filter($surveys, fn($s) => $s['status'] === 'ativa'));
        $encerradas = count(array_filter($surveys, fn($s) => $s['status'] === 'encerrada'));
        $respostas = array_sum(array_map(fn($s) => count($s['responses']), $surveys));
        return compact('total', 'ativas', 'encerradas', 'respostas');
    }
}
