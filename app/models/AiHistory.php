<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AiHistory extends Model
{
    public function create(
        int $userId,
        string $feature,
        string $prompt,
        string $response,
        string $model,
        int $inputTokens,
        int $outputTokens,
        int $totalTokens
    ): int {
        $stmt = $this->db->prepare('
            INSERT INTO ai_history (user_id, feature, prompt, response, model, input_tokens, output_tokens, total_tokens)
            VALUES (:user_id, :feature, :prompt, :response, :model, :in, :out, :total)
        ');
        $stmt->execute([
            'user_id' => $userId,
            'feature' => $feature,
            'prompt' => $prompt,
            'response' => $response,
            'model' => $model,
            'in' => $inputTokens,
            'out' => $outputTokens,
            'total' => $totalTokens,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function latestByUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare('
            SELECT id, feature, model, total_tokens, created_at
            FROM ai_history
            WHERE user_id = :user_id
            ORDER BY id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ai_history WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM ai_history');
        return (int)$stmt->fetchColumn();
    }

    public function sumTotalTokens(): int
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(total_tokens), 0) FROM ai_history');
        return (int)$stmt->fetchColumn();
    }

    public function latestAll(int $limit = 50): array
    {
        $stmt = $this->db->prepare('
            SELECT h.id, h.user_id, u.name AS user_name, u.email AS user_email, h.feature, h.model, h.total_tokens, h.created_at
            FROM ai_history h
            JOIN users u ON u.id = h.user_id
            ORDER BY h.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function findByIdAny(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT h.*, u.name AS user_name, u.email AS user_email
            FROM ai_history h
            JOIN users u ON u.id = h.user_id
            WHERE h.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
