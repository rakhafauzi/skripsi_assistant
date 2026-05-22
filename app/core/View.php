<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewPath = APP_BASE_PATH . '/app/views/' . $view . '.php';
        $layoutPath = APP_BASE_PATH . '/app/views/layouts/' . $layout . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View tidak ditemukan: ' . Security::e($view);
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (is_file($layoutPath)) {
            require $layoutPath;
            return;
        }

        echo $content;
    }
}

