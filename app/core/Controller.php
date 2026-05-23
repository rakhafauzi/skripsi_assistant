<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function wantsJson(): bool
    {
        $route = strtolower((string)($_GET['r'] ?? ''));
        if ($route !== '' && (str_starts_with($route, 'api/') || str_contains($route, '/api') || str_contains($route, 'api-'))) {
            return true;
        }
        $xhr = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        if ($xhr === 'xmlhttprequest') {
            return true;
        }
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        return str_contains($accept, 'application/json');
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            if ($this->wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Session habis. Silakan login ulang.'], 401);
                exit;
            }
            Flash::error('Silakan login terlebih dahulu.');
            Response::redirect(url('auth/login'));
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (!Auth::isAdmin()) {
            if ($this->wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Akses ditolak.'], 403);
                exit;
            }
            Flash::error('Akses ditolak.');
            Response::redirect(url('dashboard/index'));
        }
    }
}
