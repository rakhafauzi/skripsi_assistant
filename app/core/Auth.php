<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $cachedUser = null;

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function name(): ?string
    {
        if (!self::check()) {
            return null;
        }
        if (!empty($_SESSION['user_name'])) {
            return (string)$_SESSION['user_name'];
        }
        $user = self::user();
        return $user ? (string)($user['name'] ?? '') : null;
    }

    public static function role(): ?string
    {
        if (!self::check()) {
            return null;
        }
        if (!empty($_SESSION['role'])) {
            return (string)$_SESSION['role'];
        }
        $user = self::user();
        return $user ? (string)($user['role'] ?? '') : null;
    }

    public static function id(): ?int
    {
        return self::check() ? (int)$_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $model = new User();
        $user = $model->findById((int)$_SESSION['user_id']);
        self::$cachedUser = $user ?: null;
        return self::$cachedUser;
    }

    public static function isAdmin(): bool
    {
        $role = (string)(self::role() ?? '');
        return $role === 'admin';
    }

    public static function isMahasiswa(): bool
    {
        $role = (string)(self::role() ?? '');
        return $role === 'mahasiswa' || $role === 'user';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)($user['id'] ?? 0);
        $_SESSION['user_name'] = (string)($user['name'] ?? '');
        $_SESSION['role'] = (string)($user['role'] ?? 'mahasiswa');
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}
