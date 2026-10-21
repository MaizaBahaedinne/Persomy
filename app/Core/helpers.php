<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Lang;
use App\Models\Setting;

/** Échappement HTML (contexte texte et attributs). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function t(string $key, array $replace = []): string
{
    return Lang::t($key, $replace);
}

function locale(): string
{
    return Lang::locale();
}

/** URL localisée : lurl('about') => /fr/about ; lurl('') => /fr/ */
function lurl(string $path = '', ?string $locale = null): string
{
    return '/' . ($locale ?? Lang::locale()) . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

function abs_url(string $path = '/'): string
{
    $base = (string) Config::get('url', '');
    if ($base === '') {
        $scheme = \App\Core\Request::isHttps() ? 'https' : 'http';
        $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        $base = $scheme . '://' . $host;
    }
    return $base . '/' . ltrim($path, '/');
}

function setting(string $key, ?string $default = null): ?string
{
    return Setting::get($key, $default);
}

/** Vrai si la valeur est vide ou contient un marqueur « à compléter ». */
function is_placeholder(?string $value): bool
{
    $value = trim((string) $value);
    return $value === '' || (bool) preg_match('/\[(À COMPLÉTER|TO BE COMPLETED|يُستكمل لاحقًا)/u', $value);
}

function has_placeholder(?string $value): bool
{
    return (bool) preg_match('/\[(À COMPLÉTER|TO BE COMPLETED|يُستكمل لاحقًا)/u', (string) $value);
}

function csrf_field(): string
{
    return Csrf::field();
}

/** Attributs lang/dir pour un contenu affiché dans une langue de repli. */
function lang_attrs(array $row): string
{
    if (empty($row['_fallback']) || empty($row['_locale'])) {
        return '';
    }
    return ' lang="' . e($row['_locale']) . '" dir="' . e(Lang::dir($row['_locale'])) . '"';
}

function truncate(string $text, int $max = 160): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    return mb_strlen($text) > $max ? rtrim(mb_substr($text, 0, $max - 1)) . '…' : $text;
}

function slugify(string $text): string
{
    $text = trim($text);
    if (function_exists('transliterator_transliterate')) {
        $converted = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        if (is_string($converted)) {
            $text = $converted;
        }
    }
    $text = strtolower($text);
    $text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function is_valid_slug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) && strlen($slug) <= 120;
}

/**
 * Mini-format sûr pour les contenus administrables :
 * paragraphes (ligne vide), "## " titre, "### " sous-titre, "- " liste.
 * Tout le texte est échappé : aucun HTML brut n'est interprété.
 */
function format_content(string $text): string
{
    $html = '';
    $para = [];
    $list = [];
    $flushPara = static function () use (&$html, &$para): void {
        if ($para) {
            $html .= '<p>' . implode('<br>', array_map('e', $para)) . '</p>';
            $para = [];
        }
    };
    $flushList = static function () use (&$html, &$list): void {
        if ($list) {
            $html .= '<ul>' . implode('', array_map(static fn(string $i): string => '<li>' . e($i) . '</li>', $list)) . '</ul>';
            $list = [];
        }
    };

    $lines = preg_split('/\R/u', trim($text)) ?: [];
    foreach ($lines as $line) {
        $line = rtrim($line);
        if ($line === '') {
            $flushPara();
            $flushList();
        } elseif (str_starts_with($line, '### ')) {
            $flushPara();
            $flushList();
            $html .= '<h3>' . e(substr($line, 4)) . '</h3>';
        } elseif (str_starts_with($line, '## ')) {
            $flushPara();
            $flushList();
            $html .= '<h2>' . e(substr($line, 3)) . '</h2>';
        } elseif (str_starts_with($line, '- ')) {
            $flushPara();
            $list[] = substr($line, 2);
        } else {
            $flushList();
            $para[] = $line;
        }
    }
    $flushPara();
    $flushList();
    return $html;
}

/** Icônes SVG simples (traits) identifiées par un nom. */
function icon(string $name): string
{
    $paths = [
        'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'cloud' => '<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>',
        'ai' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/>',
        'automation' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'consulting' => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
    ];
    $inner = $paths[$name] ?? $paths['code'];
    return '<svg class="icon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y H:i', $ts) : '';
}
