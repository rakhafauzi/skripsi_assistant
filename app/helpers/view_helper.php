<?php
declare(strict_types=1);

use App\Core\Security;

function url(string $route, array $query = []): string
{
    $base = APP_BASE_URL !== '' ? APP_BASE_URL : '';
    $qs = array_merge(['r' => $route], $query);
    return $base . '/index.php?' . http_build_query($qs);
}

function asset(string $path): string
{
    $base = APP_BASE_URL !== '' ? APP_BASE_URL : '';
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

function csrf_token(): string
{
    return Security::csrfToken();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . Security::e(csrf_token()) . '">';
}

function e(?string $value): string
{
    return Security::e($value);
}

