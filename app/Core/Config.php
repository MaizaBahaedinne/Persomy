<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static ?array $config = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        self::$config ??= require BASE_PATH . '/config/app.php';
        $value = self::$config;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}
