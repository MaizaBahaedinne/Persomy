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
function format_content(string $text, bool $inline = false): string
{
    // $inline : gras (**texte**) et liens ([libellé](https://…) ou [libellé](/chemin)) ; tout est échappé d'abord.
    $fmt = $inline ? 'format_inline' : 'e';
    $html = '';
    $para = [];
    $list = [];
    $flushPara = static function () use (&$html, &$para, $fmt): void {
        if ($para) {
            $html .= '<p>' . implode('<br>', array_map($fmt, $para)) . '</p>';
            $para = [];
        }
    };
    $flushList = static function () use (&$html, &$list, $fmt): void {
        if ($list) {
            $html .= '<ul>' . implode('', array_map(static fn(string $i): string => '<li>' . $fmt($i) . '</li>', $list)) . '</ul>';
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

/**
 * Échappe un texte puis applique le format en ligne : **gras** et liens [libellé](https://…) / [libellé](/chemin).
 * Seuls http(s):// et les chemins internes sont acceptés comme cibles de lien (pas de javascript:, data:, //).
 * Le gras est traité en premier : une cible contenant « ** » n'est donc jamais transformée en lien.
 */
function format_inline(string $text): string
{
    $html = e($text);
    $html = preg_replace('/\*\*([^*\r\n]+)\*\*/u', '<strong>$1</strong>', $html) ?? $html;
    return preg_replace_callback(
        '/\[([^\[\]\r\n]+)\]\((https?:\/\/[^\s()<>"\']+|\/(?!\/)[^\s()<>"\']*)\)/u',
        static function (array $m): string {
            $external = str_starts_with($m[2], 'http');
            return '<a href="' . $m[2] . '"' . ($external ? ' rel="noopener noreferrer" target="_blank"' : '') . '>' . $m[1] . '</a>';
        },
        $html
    ) ?? $html;
}

/** Temps de lecture estimé (≈ 200 mots/minute), au minimum 1 minute. */
function reading_minutes(string $text): int
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return max(1, (int) ceil(count($words) / 200));
}

/** Date longue localisée (ex. « 21 octobre 2026 »), avec repli si l'extension intl est absente. */
function format_day(?string $date, ?string $locale = null): string
{
    $ts = $date ? strtotime($date) : false;
    if (!$ts) {
        return '';
    }
    $locale ??= Lang::locale();
    if (class_exists(\IntlDateFormatter::class)) {
        $map = ['fr' => 'fr_FR', 'en' => 'en_GB', 'ar' => 'ar'];
        $formatter = new \IntlDateFormatter($map[$locale] ?? 'fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $out = $formatter->format($ts);
        if (is_string($out) && $out !== '') {
            return $out;
        }
    }
    return date('d/m/Y', $ts);
}

/**
 * Découpe un contenu administrable en une introduction (texte avant le premier « ## »)
 * et une liste de sections [titre, corps], pour les afficher en blocs.
 *
 * @return array{intro: string, sections: list<array{title: string, body: string}>}
 */
function split_sections(string $content): array
{
    $parts = preg_split('/^##[ \t]+(.+)$/mu', trim($content), -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    $intro = trim((string) array_shift($parts));
    $sections = [];
    for ($i = 0; $i + 1 < count($parts); $i += 2) {
        $sections[] = ['title' => trim($parts[$i]), 'body' => trim($parts[$i + 1])];
    }
    return ['intro' => $intro, 'sections' => $sections];
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
        'portfolio' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'blog' => '<path d="M4 4h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/><path d="M18 8h2a0 0 0 0 1 0 0v10a2 2 0 0 1-2 2"/><path d="M8 9h6M8 13h6M8 17h4"/>',
        'target' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
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
