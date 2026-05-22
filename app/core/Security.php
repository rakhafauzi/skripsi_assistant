<?php
declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function startSession(): void
    {
        // Session security: cookie HttpOnly, SameSite, dan regenerate ID untuk mencegah session fixation.
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (defined('APP_SESSION_NAME') && APP_SESSION_NAME !== '') {
            session_name(APP_SESSION_NAME);
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $path = '/';
        if (defined('APP_BASE_URL') && APP_BASE_URL !== '') {
            $path = rtrim(APP_BASE_URL, '/') . '/';
        }
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $path,
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
        ]);

        session_start();

        if (!isset($_SESSION['_init'])) {
            $_SESSION['_init'] = true;
            session_regenerate_id(true);
        }
    }

    public static function ensureCsrfToken(): void
    {
        // CSRF sederhana: token acak disimpan di session dan diverifikasi di setiap POST/AJAX.
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
    }

    public static function csrfToken(): string
    {
        self::ensureCsrfToken();
        return (string)$_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        if (!$token || empty($_SESSION['_csrf'])) {
            return false;
        }
        return hash_equals((string)$_SESSION['_csrf'], $token);
    }

    public static function e(?string $value): string
    {
        // Sanitasi output (XSS protection) untuk semua data yang ditampilkan ke HTML.
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
