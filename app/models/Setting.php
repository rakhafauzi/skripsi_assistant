<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Setting extends Model
{
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }

    public function set(string $key, string $value): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
        ');
        return $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT `key`, `value` FROM settings');
        $rows = $stmt->fetchAll() ?: [];
        $map = [];
        foreach ($rows as $r) {
            $map[$r['key']] = $r['value'];
        }
        return $map;
    }
}

