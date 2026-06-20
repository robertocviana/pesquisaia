<?php

namespace App\Services;

use App\Database;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Response;

class ResponseGeneratorService
{
    private AiService $ai;

    public function __construct()
    {
        $this->ai = new AiService('gpt-4o');
    }

    /**
     * Gera respondentes e respostas para a pesquisa informada.
     * Estratégia Híbrida: Obtém 5 perfis ricos via IA (ou fallback) e multiplica localmente.
     *
     * @param int $surveyId ID da pesquisa
     * @param int $count Quantidade de respondentes a simular
     * @param string $strategy 'hybrid' | 'local'
     * @return array Contendo a contagem gerada e se usou fallback
     */
    public function generate(int $surveyId, int $count, string $strategy = 'hybrid'): array
    {
        // 1. Validar e buscar dados da pesquisa
        $survey = Survey::findById($surveyId);
        if (!$survey) {
            throw new \InvalidArgumentException("Pesquisa não encontrada.");
        }

        if ($survey['status'] !== 'ativa') {
            throw new \InvalidArgumentException("O gerador só pode ser executado para pesquisas ativas.");
        }

        $questions = Question::findBySurvey($surveyId);
        if (empty($questions)) {
            throw new \InvalidArgumentException("A pesquisa não possui perguntas cadastradas.");
        }

        $baseRespondents = [];
        $usedFallback = false;

        // 2. Obter base de perfis de respostas
        if ($strategy === 'hybrid' && $this->ai->hasKey()) {
            try {
                $baseRespondents = $this->fetchBaseFromAi($survey, $questions);
            } catch (AiException $e) {
                error_log('[PesquisaIA] ResponseGeneratorService degraded to local fallback: ' . $e->getMessage());
                $usedFallback = true;
                $baseRespondents = $this->generateLocalBase($questions);
            }
        } else {
            $usedFallback = true;
            $baseRespondents = $this->generateLocalBase($questions);
        }

        // Se por algum motivo o array veio vazio, garantir fallback
        if (empty($baseRespondents)) {
            $baseRespondents = $this->generateLocalBase($questions);
        }

        // 3. Multiplicar e salvar os dados no banco
        $db = Database::pdo();
        $generatedCount = 0;

        $firstNames = ['João', 'Maria', 'Pedro', 'Ana', 'Lucas', 'Julia', 'Mateus', 'Beatriz', 'Gabriel', 'Larissa', 'Bruno', 'Camila', 'Felipe', 'Letícia', 'Gustavo', 'Amanda', 'Rodrigo', 'Bruna', 'Thiago', 'Fernanda', 'Rafael', 'Mariana', 'Daniel', 'Carolina', 'Vinícius', 'Gabriela', 'Leonardo', 'Luana', 'Diego', 'Isabela', 'Eduardo', 'Juliana', 'Marcos', 'Aline', 'André', 'Patrícia', 'Ricardo', 'Renata', 'Fabio', 'Vanessa'];
        $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes', 'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Almeida', 'Lopes', 'Soares', 'Fernandes', 'Vieira', 'Barbosa', 'Rocha', 'Dias', 'Nascimento', 'Moreira', 'Andrade', 'Nunes', 'Cardoso', 'Teixeira', 'Araújo', 'Melo'];

        for ($i = 0; $i < $count; $i++) {
            // Sorteia um nome aleatório
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = "{$firstName} {$lastName}";

            // Gera token único e insere respondente
            $token = Respondent::generateToken();
            
            // Inserir respondente na base diretamente para evitar concorrência ou bugs
            $stmt = $db->prepare(
                "INSERT INTO respondents (survey_id, token, name, status, created_at) VALUES (?, ?, ?, 'concluida', ?)"
            );
            
            // Retroagir datas para que os gráficos e listagens de relatórios fiquem naturais
            $daysAgo = rand(0, 10);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);
            $createdAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days -{$hoursAgo} hours -{$minutesAgo} minutes"));

            $stmt->execute([$surveyId, $token, $fullName, $createdAt]);
            $respondentId = (int) $db->lastInsertId();

            // Salva as respostas
            foreach ($questions as $qIndex => $q) {
                // Sorteia uma das respostas dos perfis base para esta pergunta específica
                // Isso cria variações/combinações ricas misturadas entre os respondentes modelo
                $randomProfile = $baseRespondents[array_rand($baseRespondents)];
                $answerText = $randomProfile['answers'][$qIndex] ?? 'Sim, gostei bastante.';

                // Salva a resposta individual
                $ansStmt = $db->prepare(
                    "INSERT INTO responses (respondent_id, question_id, text_response, answered_at) VALUES (?, ?, ?, ?)"
                );
                
                // Responder alguns segundos/minutos depois da criação do respondente
                $answeredAt = date('Y-m-d H:i:s', strtotime($createdAt . " + " . rand(30, 300) . " seconds"));
                $ansStmt->execute([$respondentId, $q['id'], $answerText, $answeredAt]);
            }

            // Incrementa contador de respostas na pesquisa
            Survey::incrementResponseCount($surveyId);
            $generatedCount++;
        }

        // Verifica encerramento automático
        Survey::checkAutoClose($surveyId);

        return [
            'count' => $generatedCount,
            'fallback' => $usedFallback
        ];
    }

    /**
     * Consulta a OpenAI para gerar 5 perfis base realistas e ricos.
     */
    private function fetchBaseFromAi(array $survey, array $questions): array
    {
        $questionsFormatted = "";
        foreach ($questions as $index => $q) {
            $questionsFormatted .= ($index + 1) . ". " . $q['text'] . "\n";
        }

        $systemPrompt = <<<PROMPT
Você é um gerador de dados sintéticos para testes de pesquisa.
Seu papel é criar 5 perfis de respondentes com nomes brasileiros completos fictícios e suas respectivas respostas para cada pergunta da pesquisa informada. 

As respostas devem ser extremamente realistas, simulando a linguagem e a digitação de uma pessoa em um chat de suporte ou formulário online. Devem ser coerentes com o objetivo, o público-alvo e o tema da pesquisa. Evite respostas genéricas iguais; dê personalidades variadas aos respondentes (alguns muito satisfeitos, alguns neutros, alguns com críticas construtivas e sugestões reais).

Retorne estritamente um JSON válido no seguinte formato de objeto, sem markdown adicional ou tags extras:
{
  "respondents": [
    {
      "name": "Nome Completo",
      "answers": [
        "Texto de resposta para a pergunta 1",
        "Texto de resposta para a pergunta 2"
      ]
    }
  ]
}
PROMPT;

        $userPrompt = <<<PROMPT
Pesquisa:
Nome: {$survey['name']}
Objetivo: {$survey['objective']}
Público-alvo: {$survey['audience']}

Perguntas da Pesquisa:
{$questionsFormatted}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $raw = $this->ai->complete($messages, jsonMode: true, maxTokens: 2500);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['respondents']) || !is_array($data['respondents'])) {
            throw new AiException("Resposta JSON inválida da OpenAI.");
        }

        // Sanitiza a estrutura de retorno para garantir que o número de respostas bate com o de perguntas
        $sanitized = [];
        foreach ($data['respondents'] as $resp) {
            if (!isset($resp['name']) || !isset($resp['answers']) || !is_array($resp['answers'])) {
                continue;
            }
            $sanitized[] = [
                'name' => $resp['name'],
                'answers' => $resp['answers']
            ];
        }

        return empty($sanitized) ? throw new AiException("Nenhum respondente sanitizado.") : $sanitized;
    }

    /**
     * Lógica local determinística de fallback inteligente para simular respostas em lote.
     */
    private function generateLocalBase(array $questions): array
    {
        $profiles = [];
        
        // Vamos criar 5 perfis base com tons diferentes:
        // 1. Super Positivo e Entusiasta
        // 2. Crítico Construtivo (Pontos fortes + melhorias)
        // 3. Neutro / Direto ao ponto
        // 4. Detalhado com foco em usabilidade/UX
        // 5. Exigente / Insatisfeito suave

        $tones = [
            // Perfil 1: Entusiasta
            [
                'name' => 'Felipe Castanhari',
                'general_positive' => 'Achei simplesmente sensacional! Atendeu a todas as minhas necessidades.',
                'general_suggestions' => 'Por enquanto nada, achei a experiência impecável e muito fácil de usar.',
                'general_score' => 'Nota 10, com certeza indicaria para todo mundo.',
                'general_generic' => 'Tudo excelente, gostei muito da proposta.'
            ],
            // Perfil 2: Crítico Construtivo
            [
                'name' => 'Renata Vasconcellos',
                'general_positive' => 'O serviço funciona bem e a equipe é muito prestativa na hora de ajudar.',
                'general_suggestions' => 'O tempo de carregamento inicial poderia ser melhorado, e talvez ter mais formas de pagamento.',
                'general_score' => 'Daria um 8. Recomendo sim, tem muito potencial.',
                'general_generic' => 'Atendeu minhas expectativas, mas tem espaço para crescer.'
            ],
            // Perfil 3: Neutro
            [
                'name' => 'Claudio Ramos',
                'general_positive' => 'Razoável, resolveu meu problema rápido.',
                'general_suggestions' => 'Interface um pouco confusa no início, podia ter um tutorial guiando.',
                'general_score' => 'Nota 7. Talvez recomende para quem precisa de algo simples.',
                'general_generic' => 'Normal, nada de muito inovador mas funciona.'
            ],
            // Perfil 4: Detalhista
            [
                'name' => 'Ana Beatriz Nogueira',
                'general_positive' => 'Gostei da facilidade de acesso às principais opções e do design clean da interface.',
                'general_suggestions' => 'Falta integração nativa com outras ferramentas de mercado e exportação de dados mais flexível.',
                'general_score' => 'Nota 9. É uma das melhores soluções que já utilizei recentemente.',
                'general_generic' => 'Utilizo quase todo dia e facilita muito meu fluxo de trabalho.'
            ],
            // Perfil 5: Exigente
            [
                'name' => 'Roberto Carlos',
                'general_positive' => 'O atendimento ao cliente é bom, mas o produto em si tem bugs chatos.',
                'general_suggestions' => 'Corrigir as falhas de sincronização e melhorar a documentação de ajuda.',
                'general_score' => 'Nota 6. Recomendo com ressalvas até corrigirem os bugs.',
                'general_generic' => 'Precisa de mais polimento técnico para ser de nível profissional.'
            ]
        ];

        foreach ($tones as $tone) {
            $answers = [];
            foreach ($questions as $q) {
                $text = mb_strtolower($q['text']);
                
                // Mapeia termos da pergunta para as respostas do perfil correspondente
                if (str_contains($text, 'melhorar') || str_contains($text, 'sugestão') || str_contains($text, 'diferente') || str_contains($text, 'mudar') || str_contains($text, 'crítica')) {
                    $answers[] = $tone['general_suggestions'];
                } elseif (str_contains($text, 'recomenda') || str_contains($text, 'indicar') || str_contains($text, 'nota') || str_contains($text, 'escala') || str_contains($text, 'quant')) {
                    $answers[] = $tone['general_score'];
                } elseif (str_contains($text, 'gostou') || str_contains($text, 'positivo') || str_contains($text, 'ponto forte') || str_contains($text, 'experiência')) {
                    $answers[] = $tone['general_positive'];
                } else {
                    $answers[] = $tone['general_generic'];
                }
            }

            $profiles[] = [
                'name' => $tone['name'],
                'answers' => $answers
            ];
        }

        return $profiles;
    }
}
