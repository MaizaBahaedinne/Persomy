<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** Autorise Google Analytics dans la CSP (activé par le layout public uniquement). */
    public static bool $allowAnalytics = false;

    /** @param array<string,string> $headers */
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = []
    ) {
    }

    public static function redirect(string $url, int $status = 302): self
    {
        // Évite les redirections ouvertes : uniquement des chemins internes.
        if ($url === '' || $url[0] !== '/' || str_starts_with($url, '//') || str_contains($url, "\n") || str_contains($url, "\r")) {
            $url = '/';
        }
        return new self('', $status, ['Location' => $url]);
    }

    public static function text(string $body, string $contentType = 'text/plain; charset=UTF-8', int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => $contentType]);
    }

    /** Politique CSP stricte ; ouvre uniquement les domaines Google Analytics si demandé. */
    public static function csp(bool $analytics = false): string
    {
        $script = "'self'";
        $img = "'self' data:";
        $connect = "'self'";
        if ($analytics) {
            $script .= ' https://www.googletagmanager.com';
            $img .= ' https://www.google-analytics.com https://*.google-analytics.com https://www.googletagmanager.com';
            $connect .= ' https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com';
        }
        return "default-src 'self'; img-src $img; style-src 'self'; script-src $script; connect-src $connect; "
            . "font-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";
    }

    public function noCache(): self
    {
        $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, private';
        return $this;
    }

    public function send(bool $headOnly = false): void
    {
        http_response_code($this->status);
        header_remove('X-Powered-By');

        $headers = $this->headers + [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Content-Security-Policy' => self::csp(self::$allowAnalytics),
        ];
        if (Request::isHttps()) {
            $headers += ['Strict-Transport-Security' => 'max-age=31536000'];
        }
        if (!headers_sent()) {
            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
        if (!$headOnly) {
            echo $this->body;
        }
    }
}
