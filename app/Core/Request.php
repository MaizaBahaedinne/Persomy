<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $post = [],
        public readonly array $files = []
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        return new self($method, self::normalizePath((string) ($_SERVER['REQUEST_URI'] ?? '/')), $_GET, $_POST, $_FILES);
    }

    public static function normalizePath(string $uri): string
    {
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');
        $path = rawurldecode($path);
        if (!mb_check_encoding($path, 'UTF-8') || str_contains($path, "\0")) {
            return '/__invalid__';
        }
        $path = '/' . trim((string) preg_replace('#/+#', '/', $path), '/');
        return $path === '' ? '/' : $path;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** Valeur texte nettoyée (trim) d'un champ POST. */
    public function input(string $key, string $default = ''): string
    {
        $value = $this->post[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public function query(string $key, string $default = ''): string
    {
        $value = $this->query[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /** Champ POST de type tableau (ex. tr[fr][title]). */
    public function arrayInput(string $key): array
    {
        $value = $this->post[$key] ?? [];
        return is_array($value) ? $value : [];
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        return is_array($file) && isset($file['error']) && !is_array($file['error']) ? $file : null;
    }

    public function ip(): string
    {
        if (Config::get('trust_proxy') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $first = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** Empreinte non réversible de l'IP (pas de stockage d'adresse brute). */
    public function ipHash(): string
    {
        return hash_hmac('sha256', $this->ip(), (string) Config::get('key', '') ?: 'persomy');
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        return Config::get('trust_proxy') && strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }
}
