<?php
declare(strict_types=1);

define('OPENAI_API_KEY', $_SERVER['OPENAI_API_KEY'] ?? ($_SERVER['OPENROUTER_API_KEY'] ?? ''));
define('OPENAI_BASE_URL', rtrim((string)($_SERVER['OPENAI_BASE_URL'] ?? 'https://openrouter.ai/api/v1'), '/'));
define('OPENAI_MODEL', $_SERVER['OPENAI_MODEL'] ?? 'openai/gpt-4o-mini');
define('OPENAI_TEMPERATURE', (float)($_SERVER['OPENAI_TEMPERATURE'] ?? 0.7));
define('OPENAI_MAX_TOKENS', (int)($_SERVER['OPENAI_MAX_TOKENS'] ?? 1200));

define('OPENROUTER_HTTP_REFERER', $_SERVER['OPENROUTER_HTTP_REFERER'] ?? '');
define('OPENROUTER_APP_TITLE', $_SERVER['OPENROUTER_APP_TITLE'] ?? APP_NAME);
