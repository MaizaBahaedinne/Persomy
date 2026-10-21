<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\ErrorHandler;

if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    exit('PHP 8.2 or newer is required.');
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Core/helpers.php';

Env::load(BASE_PATH . '/.env');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Europe/Paris') ?? 'Europe/Paris');
mb_internal_encoding('UTF-8');
ErrorHandler::register();
