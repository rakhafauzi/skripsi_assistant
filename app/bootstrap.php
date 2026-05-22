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
