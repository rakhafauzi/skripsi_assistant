<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAuthByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsEmail(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $name, string $email, string $password, string $role = 'mahasiswa'): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'hash' => $hash,
            'role' => $role,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listUsers(int $limit = 200): array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        return $stmt->execute(['role' => $role, 'id' => $id]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function updateProfile(int $id, string $name, ?string $newPassword = null): bool
    {
        if ($newPassword !== null && $newPassword !== '') {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('UPDATE users SET name = :name, password_hash = :hash WHERE id = :id');
            return $stmt->execute(['name' => $name, 'hash' => $hash, 'id' => $id]);
        }
        $stmt = $this->db->prepare('UPDATE users SET name = :name WHERE id = :id');
        return $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM users');
        return (int)$stmt->fetchColumn();
    }
}
