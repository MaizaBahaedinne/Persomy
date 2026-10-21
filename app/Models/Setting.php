<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Setting
{
    private static ?array $all = null;

    /** @return array<string,string> */
    public static function all(): array
    {
        if (self::$all === null) {
            $rows = Db::all('SELECT setting_key, setting_value FROM site_settings');
            self::$all = [];
            foreach ($rows as $row) {
                self::$all[$row['setting_key']] = (string) $row['setting_value'];
            }
        }
        return self::$all;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        Db::run(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v1)
             ON DUPLICATE KEY UPDATE setting_value = :v2',
            ['k' => $key, 'v1' => $value, 'v2' => $value]
        );
        self::$all = null;
    }
}
