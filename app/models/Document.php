<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Document extends Model
{
    public function create(int $userId, string $type, string $title, string $content, string $format = 'text'): int
    {
        $stmt = $this->db->prepare('INSERT INTO documents (user_id, type, title, content, content_format) VALUES (:user_id, :type, :title, :content, :format)');
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'format' => $format,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $title, string $content, string $format = 'text'): bool
    {
        $stmt = $this->db->prepare('UPDATE documents SET title = :title, content = :content, content_format = :format WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'format' => $format,
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM documents WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listByUser(int $userId, int $limit = 200): array
    {
        $stmt = $this->db->prepare('SELECT id, type, title, content_format, created_at, updated_at FROM documents WHERE user_id = :user_id ORDER BY updated_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function statsByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT type, COUNT(*) AS total FROM documents WHERE user_id = :user_id GROUP BY type');
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll() ?: [];
        $map = [];
        foreach ($rows as $r) {
            $map[$r['type']] = (int)$r['total'];
        }
        return $map;
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM documents');
        return (int)$stmt->fetchColumn();
    }

    public function listAll(int $limit = 200): array
    {
        $stmt = $this->db->prepare('
            SELECT d.id, d.user_id, u.name AS user_name, u.email AS user_email, d.type, d.title, d.created_at, d.updated_at
            FROM documents d
            JOIN users u ON u.id = d.user_id
            ORDER BY d.updated_at DESC, d.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function findByIdAny(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT d.*, u.name AS user_name, u.email AS user_email
            FROM documents d
            JOIN users u ON u.id = d.user_id
            WHERE d.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
