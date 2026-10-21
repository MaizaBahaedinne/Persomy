<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Journal applicatif. Ne jamais y passer de secrets ni de contenu de messages.
 */
final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return;
        }
        $parts = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $parts[] = $key . '=' . self::clean((string) $value, 200);
            }
        }
        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            self::clean($message, 500),
            $parts ? ' ' . implode(' ', $parts) : ''
        );
        @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    private static function clean(string $value, int $max): string
    {
        $value = preg_replace('/[\r\n\t]+/', ' ', $value) ?? '';
        return mb_substr($value, 0, $max);
    }
}
