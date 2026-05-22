<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    public function dispatch(): void
    {
        $route = (string)($_GET['r'] ?? 'dashboard/index');
        $route = trim($route, '/');

        [$controllerName, $action] = array_pad(explode('/', $route, 2), 2, 'index');
        $controllerName = $controllerName ?: 'dashboard';
        $action = $action ?: 'index';

        $controllerClass = 'App\\Controllers\\' . $this->studly($controllerName) . 'Controller';
        $method = $this->camel($action);

        if (!class_exists($controllerClass)) {
            Response::notFound('Controller tidak ditemukan.');
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            Response::notFound('Action tidak ditemukan.');
            return;
        }

        $controller->$method();
    }

    private function studly(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', strtolower($value));
        $value = ucwords($value);
        return str_replace(' ', '', $value);
    }

    private function camel(string $value): string
    {
        $studly = $this->studly($value);
        return lcfirst($studly);
    }
}

