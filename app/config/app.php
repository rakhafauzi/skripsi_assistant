<?php
declare(strict_types=1);

define('APP_NAME', 'AI Assistant Pembuat Dokumentasi Skripsi & Tugas Akhir');
define('APP_ENV', 'local');
define('APP_DEBUG', true);

define('APP_BASE_PATH', dirname(__DIR__, 2));
$envBase = rtrim((string)($_SERVER['APP_BASE_URL'] ?? ''), '/');
$autoBase = rtrim(str_replace('\\', '/', (string)dirname((string)($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$baseUrl = $envBase !== '' ? $envBase : ($autoBase === '' || $autoBase === '/' ? '' : $autoBase);
define('APP_BASE_URL', $baseUrl);

define('APP_SESSION_NAME', 'app_pembantu_skripsi_sess');
