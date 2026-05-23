<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class JournalFavorite extends Model
{
    public function ensureFavorite(int $userId, int $journalId): bool
    {
        if ($userId <= 0 || $journalId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT id FROM journal_favorites WHERE user_id = :user_id AND journal_id = :journal_id LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'journal_id' => $journalId]);
        $existing = (int)($stmt->fetchColumn() ?: 0);
        if ($existing > 0) {
            return true;
        }

        $ins = $this->db->prepare('INSERT INTO journal_favorites (user_id, journal_id) VALUES (:user_id, :journal_id)');
        $ins->execute(['user_id' => $userId, 'journal_id' => $journalId]);
        return true;
    }

    public function toggle(int $userId, int $journalId): bool
    {
        if ($userId <= 0 || $journalId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT id FROM journal_favorites WHERE user_id = :user_id AND journal_id = :journal_id LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'journal_id' => $journalId]);
        $existing = (int)($stmt->fetchColumn() ?: 0);

        if ($existing > 0) {
            $del = $this->db->prepare('DELETE FROM journal_favorites WHERE id = :id LIMIT 1');
            $del->execute(['id' => $existing]);
            return false;
        }

        $ins = $this->db->prepare('INSERT INTO journal_favorites (user_id, journal_id) VALUES (:user_id, :journal_id)');
        $ins->execute(['user_id' => $userId, 'journal_id' => $journalId]);
        return true;
    }

    public function listByUser(int $userId, int $limit = 100): array
    {
        $stmt = $this->db->prepare('
            SELECT j.*,
                   f.created_at AS favorited_at
            FROM journal_favorites f
            JOIN journals j ON j.id = f.journal_id
            WHERE f.user_id = :user_id
            ORDER BY f.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', max(1, min(500, $limit)), \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
