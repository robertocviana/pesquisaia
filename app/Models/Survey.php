<?php

namespace App\Models;

use App\Database;

/**
 * Survey — Model de pesquisas com tenant isolation obrigatório.
 */
class Survey
{
    // ─── Leitura ─────────────────────────────────────────────────────────────

    /** Todas as pesquisas do usuário, ordenadas pela mais recente. */
    public static function findByUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM surveys WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca uma pesquisa pelo ID garantindo que pertence ao usuário.
     * Tenant isolation: nunca retorna pesquisa de outro usuário.
     */
    public static function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM surveys WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Busca pesquisa pelo slug público (sem autenticação). */
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM surveys WHERE public_slug = ? AND status = 'ativa' LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Busca por ID simples (sem checar user_id — usar com cuidado). */
    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM surveys WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ─── Estatísticas ────────────────────────────────────────────────────────

    /** Estatísticas do dashboard para o usuário. */
    public static function stats(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'ativa') AS ativas,
                SUM(status = 'encerrada') AS encerradas,
                SUM(response_count) AS respostas
             FROM surveys
             WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    // ─── Criação / Atualização ───────────────────────────────────────────────

    /** Cria uma nova pesquisa em rascunho e retorna o ID. */
    public static function create(int $userId): int
    {
        $stmt = Database::pdo()->prepare(
            "INSERT INTO surveys (user_id, name, objective, status, current_stage) VALUES (?, 'Nova pesquisa', '', 'rascunho', 'tipo')"
        );
        $stmt->execute([$userId]);
        return (int) Database::pdo()->lastInsertId();
    }

    /** Atualiza os metadados da pesquisa (nome, objetivo, público-alvo, meta, prazo). */
    public static function update(int $id, int $userId, array $data): void
    {
        $fields = [];
        $params = [];

        $allowed = ['name', 'objective', 'audience', 'goal_responses', 'deadline_at', 'current_stage'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "`{$col}` = ?";
                $params[]  = $data[$col] ?: null;
            }
        }

        if (empty($fields)) return;

        $params[] = $id;
        $params[] = $userId;

        $sql = 'UPDATE surveys SET ' . implode(', ', $fields) . ' WHERE id = ? AND user_id = ?';
        Database::pdo()->prepare($sql)->execute($params);
    }

    // ─── Publicação / Encerramento ───────────────────────────────────────────

    /** Publica a pesquisa gerando um slug único. */
    public static function publish(int $id, int $userId): string
    {
        $slug = bin2hex(random_bytes(8)); // 16 chars hex

        $stmt = Database::pdo()->prepare(
            "UPDATE surveys SET status = 'ativa', public_slug = ? WHERE id = ? AND user_id = ? AND status = 'rascunho'"
        );
        $stmt->execute([$slug, $id, $userId]);

        if ($stmt->rowCount() === 0) {
            // Pesquisa já ativa — retornar slug existente
            $row = self::findByIdForUser($id, $userId);
            return $row['public_slug'] ?? '';
        }

        return $slug;
    }

    /** Encerra a pesquisa. */
    public static function close(int $id, int $userId): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE surveys SET status = 'encerrada' WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$id, $userId]);
    }

    /** Encerra a pesquisa apenas pelo ID (usado internamente pelo respondente). */
    public static function closeById(int $id): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE surveys SET status = 'encerrada' WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    /** Incrementa o contador de respostas. */
    public static function incrementResponseCount(int $id): void
    {
        Database::pdo()->prepare(
            'UPDATE surveys SET response_count = response_count + 1 WHERE id = ?'
        )->execute([$id]);
    }

    /** Verifica e aplica critérios de encerramento automático. */
    public static function checkAutoClose(int $id): void
    {
        $survey = self::findById($id);
        if (!$survey || $survey['status'] !== 'ativa') return;

        $shouldClose = false;

        if ($survey['goal_responses'] && $survey['response_count'] >= $survey['goal_responses']) {
            $shouldClose = true;
        }

        if ($survey['deadline_at'] && $survey['deadline_at'] <= \App\Helpers\DateHelper::todayString()) {
            $shouldClose = true;
        }

        if ($shouldClose) {
            self::closeById($id);
        }
    }

    /**
     * Duplica uma pesquisa existente e todas as suas perguntas.
     * Retorna o ID da nova pesquisa duplicada.
     */
    public static function duplicate(int $id, int $userId): int
    {
        $original = self::findByIdForUser($id, $userId);
        if (!$original) {
            throw new \InvalidArgumentException("Pesquisa não encontrada ou acesso negado.");
        }

        $db = Database::pdo();
        $db->beginTransaction();

        try {
            // 1. Inserir a nova pesquisa duplicada
            $newName = $original['name'] . ' (Cópia)';
            $stmt = $db->prepare(
                "INSERT INTO surveys (user_id, name, objective, audience, status, current_stage, goal_responses, deadline_at)
                 VALUES (?, ?, ?, ?, 'rascunho', 'finalizado', ?, ?)"
            );
            $stmt->execute([
                $userId,
                $newName,
                $original['objective'],
                $original['audience'],
                $original['goal_responses'],
                $original['deadline_at']
            ]);
            $newId = (int)$db->lastInsertId();

            // 2. Copiar as perguntas
            $questions = Question::findBySurvey($id);
            foreach ($questions as $q) {
                $stmtQ = $db->prepare(
                    "INSERT INTO questions (survey_id, text, order_index) VALUES (?, ?, ?)"
                );
                $stmtQ->execute([$newId, $q['text'], $q['order_index']]);
            }

            $db->commit();
            return $newId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Exclui uma pesquisa e todos os seus dados vinculados. */
    public static function delete(int $id, int $userId): void
    {
        $stmt = Database::pdo()->prepare(
            'DELETE FROM surveys WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
    }
}
