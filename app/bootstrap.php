<?php
declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Security;

require __DIR__ . '/core/Autoloader.php';

$autoloader = new Autoloader(__DIR__);
$autoloader->register();

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/openai.php';
require __DIR__ . '/helpers/view_helper.php';
require __DIR__ . '/helpers/auth_helper.php';

Security::startSession();
Security::ensureCsrfToken();

// #region debug-point B:shutdown-fatal
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if (!$e || !isset($e['type'])) {
        return;
    }
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array((int)$e['type'], $fatal, true)) {
        return;
    }

    $envFile = APP_BASE_PATH . '/.dbg/journals-search-http200.env';
    if (!is_file($envFile)) {
        return;
    }
    $env = @file_get_contents($envFile);
    if (!is_string($env) || $env === '') {
        return;
    }
    $url = (preg_match('/^DEBUG_SERVER_URL=(.+)$/m', $env, $m) ? trim((string)$m[1]) : '');
    $sid = (preg_match('/^DEBUG_SESSION_ID=(.+)$/m', $env, $m2) ? trim((string)$m2[1]) : 'journals-search-http200');
    if ($url === '') {
        return;
    }

    $payload = json_encode([
        'sessionId' => $sid,
        'runId' => 'pre-fix',
        'hypothesisId' => 'B',
        'location' => 'app/bootstrap.php:shutdown',
        'msg' => '[DEBUG] Fatal error captured',
        'data' => [
            'type' => (int)($e['type'] ?? 0),
            'message' => (string)($e['message'] ?? ''),
            'file' => (string)($e['file'] ?? ''),
            'line' => (int)($e['line'] ?? 0),
            'route' => (string)($_GET['r'] ?? ''),
        ],
        'ts' => (int)(microtime(true) * 1000),
    ], JSON_UNESCAPED_UNICODE);
    if (!is_string($payload) || $payload === '') {
        return;
    }

    @file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $payload,
            'timeout' => 0.7,
        ],
    ]));
});
// #endregion

// #region debug-point B:php-warning-notice
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    $route = (string)($_GET['r'] ?? '');
    if ($route !== 'journals/api-search') {
        return false;
    }

    static $sent = 0;
    if ($sent >= 5) {
        return false;
    }

    $envFile = APP_BASE_PATH . '/.dbg/journals-search-http200.env';
    if (!is_file($envFile)) {
        return false;
    }
    $env = @file_get_contents($envFile);
    if (!is_string($env) || $env === '') {
        return false;
    }
    $url = (preg_match('/^DEBUG_SERVER_URL=(.+)$/m', $env, $m) ? trim((string)$m[1]) : '');
    $sid = (preg_match('/^DEBUG_SESSION_ID=(.+)$/m', $env, $m2) ? trim((string)$m2[1]) : 'journals-search-http200');
    if ($url === '') {
        return false;
    }

    $payload = json_encode([
        'sessionId' => $sid,
        'runId' => 'pre-fix',
        'hypothesisId' => 'B',
        'location' => 'app/bootstrap.php:error_handler',
        'msg' => '[DEBUG] PHP warning/notice emitted',
        'data' => [
            'errno' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        ],
        'ts' => (int)(microtime(true) * 1000),
    ], JSON_UNESCAPED_UNICODE);
    if (is_string($payload) && $payload !== '') {
        @file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 0.7,
            ],
        ]));
        $sent++;
    }

    return false;
});
// #endregion
