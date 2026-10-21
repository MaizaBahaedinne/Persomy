<?php

declare(strict_types=1);

namespace App\Core;

final class ErrorHandler
{
    public static function register(): void
    {
        $debug = (bool) Config::get('debug', false);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        ini_set('error_log', $dir . '/php-error.log');
        error_reporting(E_ALL);

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(\Throwable $e): void
    {
        if (!$e instanceof HttpException) {
            Logger::error(get_class($e) . ': ' . $e->getMessage(), ['file' => basename($e->getFile()), 'line' => $e->getLine()]);
        }
        $status = $e instanceof HttpException ? $e->status : 500;
        try {
            ErrorPage::response($status, $e)->send();
        } catch (\Throwable) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            echo 'Error';
        }
    }
}
