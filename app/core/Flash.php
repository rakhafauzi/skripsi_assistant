<?php
declare(strict_types=1);

namespace App\Core;

final class Flash
{
    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('danger', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    private static function set(string $type, string $message): void
    {
        $_SESSION['_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function pull(): ?array
    {
        if (empty($_SESSION['_flash'])) {
            return null;
        }
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return is_array($flash) ? $flash : null;
    }
}

