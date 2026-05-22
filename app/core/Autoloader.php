<?php
declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    private string $baseDir;

    public function __construct(string $appDir)
    {
        $this->baseDir = rtrim($appDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (!str_starts_with($class, 'App\\')) {
                return;
            }

            $relative = substr($class, 4);
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $file = $this->baseDir . $relativePath;

            if (is_file($file)) {
                require $file;
                return;
            }

            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            if ($parts && isset($parts[0])) {
                $parts[0] = strtolower($parts[0]);
                $fallback = $this->baseDir . implode(DIRECTORY_SEPARATOR, $parts);
                if (is_file($fallback)) {
                    require $fallback;
                }
            }
        });
    }
}
