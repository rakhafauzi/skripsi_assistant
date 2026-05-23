<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class JournalSearchHistory extends Model
{
    public function create(int $userId, array $query, int $totalResults): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO journal_search_history (user_id, query_json, total_results)
            VALUES (:user_id, :query_json, :total_results)
        ');
        $stmt->execute([
            'user_id' => $userId,
            'query_json' => json_encode($query, JSON_UNESCAPED_UNICODE),
            'total_results' => max(0, $totalResults),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function latestByUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare('
            SELECT id, query_json, total_results, created_at
            FROM journal_search_history
            WHERE user_id = :user_id
            ORDER BY id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', max(1, min(200, $limit)), \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as &$r) {
            $decoded = json_decode((string)($r['query_json'] ?? ''), true);
            $r['query'] = is_array($decoded) ? $decoded : [];
        }
        unset($r);
        return $rows;
    }
}

