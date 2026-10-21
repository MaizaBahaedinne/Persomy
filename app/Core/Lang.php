<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;

final class Lang
{
    private static string $locale = 'fr';
    private static ?string $default = null;
    /** @var array<string,array<string,string>> */
    private static array $cache = [];

    public static function init(Request $request): void
    {
        $first = explode('/', trim($request->path, '/'))[0] ?? '';
        self::$locale = self::isSupported($first) ? $first : self::defaultLocale();
    }

    public static function set(string $locale): void
    {
        if (self::isSupported($locale)) {
            self::$locale = $locale;
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function dir(?string $locale = null): string
    {
        return (string) Config::get('locales.' . ($locale ?? self::$locale) . '.dir', 'ltr');
    }

    /** @return list<string> */
    public static function supported(): array
    {
        return array_keys(Config::get('locales', []));
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    public static function defaultLocale(): string
    {
        if (self::$default === null) {
            $default = (string) Config::get('default_locale', 'fr');
            try {
                $fromDb = Setting::get('default_locale');
                if ($fromDb !== null && self::isSupported($fromDb)) {
                    $default = $fromDb;
                }
            } catch (\Throwable) {
                // Base indisponible : on garde la valeur du .env.
            }
            self::$default = self::isSupported($default) ? $default : 'fr';
        }
        return self::$default;
    }

    public static function t(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale ??= self::$locale;
        $text = self::load($locale)[$key] ?? self::load(self::defaultLocaleSafe())[$key] ?? self::load('fr')[$key] ?? $key;
        foreach ($replace as $name => $value) {
            $text = str_replace('{' . $name . '}', (string) $value, $text);
        }
        return $text;
    }

    private static function defaultLocaleSafe(): string
    {
        return self::$default ?? (string) Config::get('default_locale', 'fr');
    }

    /** @return array<string,string> */
    private static function load(string $locale): array
    {
        if (!isset(self::$cache[$locale])) {
            $file = BASE_PATH . '/app/Languages/' . $locale . '.php';
            $data = (self::isSupported($locale) && is_file($file)) ? require $file : [];
            self::$cache[$locale] = self::flatten($data);
        }
        return self::$cache[$locale];
    }

    /** @return array<string,string> */
    public static function flatten(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $out += self::flatten($value, $path);
            } else {
                $out[$path] = (string) $value;
            }
        }
        return $out;
    }
}
