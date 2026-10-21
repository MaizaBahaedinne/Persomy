<?php

declare(strict_types=1);

/**
 * Amorçage commun des scripts en ligne de commande (SSH).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

restore_exception_handler();
ini_set('display_errors', '1');
