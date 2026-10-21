<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Générateurs purs (sans base ni requête) pour le référencement classique (SEO)
 * et pour les moteurs de réponse IA (GEO) : robots.txt, llms.txt, données structurées JSON-LD.
 */
final class Seo
{
    /** Politique d'accès des robots IA : clé => libellé (réglage « ai_crawlers » de l'administration). */
    public const AI_MODES = [
        'allow' => 'Autoriser (recommandé pour être cité par les assistants IA) : recherche IA et entraînement',
        'search_only' => 'Recherche IA seulement : autoriser les assistants, bloquer les robots d’entraînement',
        'block' => 'Bloquer tous les robots IA (Googlebot et Bingbot restent autorisés)',
    ];

    /** Robots qui récupèrent une page pour répondre à une question ou alimenter une recherche IA. */
    private const AI_SEARCH_BOTS = ['OAI-SearchBot', 'ChatGPT-User', 'Claude-SearchBot', 'Claude-User', 'PerplexityBot', 'Perplexity-User'];

    /** Robots collectant des données d'entraînement de modèles. */
    private const AI_TRAINING_BOTS = ['GPTBot', 'ClaudeBot', 'Google-Extended', 'Applebot-Extended', 'CCBot', 'Meta-ExternalAgent', 'Bytespider'];

    /** Content-Signal (Cloudflare) : search / ai-input / ai-train. */
    private const SIGNALS = [
        'allow' => 'search=yes, ai-input=yes, ai-train=yes',
        'search_only' => 'search=yes, ai-input=yes, ai-train=no',
        'block' => 'search=yes, ai-input=no, ai-train=no',
    ];

    public static function robotsTxt(string $mode, string $sitemapUrl, string $llmsUrl): string
    {
        $mode = isset(self::AI_MODES[$mode]) ? $mode : 'allow';

        $out = "# robots.txt — généré par l'application\n"
            . "User-agent: *\n"
            . "Disallow: /admin\n"
            . 'Content-Signal: ' . self::SIGNALS[$mode] . "\n";

        // Un groupe spécifique remplace le groupe « * » : /admin est donc répété.
        $group = static function (array $bots, bool $allowed): string {
            $block = '';
            foreach ($bots as $bot) {
                $block .= 'User-agent: ' . $bot . "\n";
            }
            return $block . ($allowed ? "Allow: /\nDisallow: /admin\n" : "Disallow: /\n");
        };

        $out .= "\n# Assistants et moteurs de réponse IA (récupération à la demande)\n"
            . $group(self::AI_SEARCH_BOTS, $mode !== 'block');
        $out .= "\n# Robots d'entraînement de modèles IA\n"
            . $group(self::AI_TRAINING_BOTS, $mode === 'allow');

        return $out . "\nSitemap: " . $sitemapUrl . "\n# Résumé du site pour les IA : " . $llmsUrl . "\n";
    }

    /**
     * @param array{name:string,summary:string,notes:list<string>,sections:array<string,list<array{title:string,url:string,desc?:string,body?:string}>>} $d
     */
    public static function llmsTxt(array $d, bool $full = false): string
    {
        $out = '# ' . self::line($d['name']) . "\n\n";
        if ($d['summary'] !== '') {
            $out .= '> ' . self::line($d['summary']) . "\n\n";
        }
        foreach ($d['notes'] as $note) {
            $out .= self::line($note) . "\n\n";
        }
        foreach ($d['sections'] as $title => $items) {
            if (!$items) {
                continue;
            }
            $out .= '## ' . $title . "\n\n";
            foreach ($items as $item) {
                $desc = self::line($item['desc'] ?? '');
                if ($full) {
                    $out .= '### ' . self::line($item['title']) . "\n\nSource: " . $item['url'] . "\n\n";
                    if ($desc !== '') {
                        $out .= $desc . "\n\n";
                    }
                    $body = trim((string) ($item['body'] ?? ''));
                    if ($body !== '') {
                        // Les titres du contenu passent sous le niveau « ### » de l'élément.
                        $out .= preg_replace('/^(#{2,3}) /m', '##$1 ', str_replace(["\r\n", "\r"], "\n", $body)) . "\n\n";
                    }
                } else {
                    $out .= '- [' . self::line($item['title']) . '](' . $item['url'] . ')' . ($desc !== '' ? ': ' . $desc : '') . "\n";
                }
            }
            if (!$full) {
                $out .= "\n";
            }
        }
        return rtrim($out) . "\n";
    }

    /** Une seule ligne, sans crochets (qui casseraient la syntaxe des liens Markdown). */
    private static function line(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', str_replace(['[', ']'], '', $text)));
    }

    /** @param list<array{0:string,1:string}> $items [[nom, url absolue], …] */
    public static function breadcrumbs(array $items): array
    {
        $list = [];
        foreach (array_values($items) as $i => [$name, $url]) {
            $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => $url];
        }
        return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    /** @param list<array> $nodes */
    public static function graph(array $nodes): array
    {
        return ['@context' => 'https://schema.org', '@graph' => array_values($nodes)];
    }

    /**
     * Organisation + site web (page d'accueil).
     *
     * @param array{name:string,url:string,logo:string,description:string,locales:list<string>,email:?string,phone:?string,linkedin:?string,services:list<string>,offices:list<array{0:string,1:string,2:?string}>} $d
     */
    public static function siteNodes(array $d): array
    {
        $orgId = $d['url'] . '#organization';
        $org = [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => $d['name'],
            'url' => $d['url'],
            'logo' => ['@type' => 'ImageObject', 'url' => $d['logo'], 'width' => 512, 'height' => 512],
        ];
        if ($d['description'] !== '') {
            $org['description'] = $d['description'];
        }
        if ($d['email']) {
            $org['email'] = $d['email'];
        }
        if ($d['phone']) {
            $org['telephone'] = $d['phone'];
        }
        if ($d['linkedin']) {
            $org['sameAs'] = [$d['linkedin']];
        }
        if ($d['services']) {
            $org['knowsAbout'] = $d['services'];
        }
        $places = [];
        foreach ($d['offices'] as [$city, $country, $street]) {
            $address = ['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressCountry' => $country];
            if ($street) {
                $address['streetAddress'] = $street;
            }
            $places[] = ['@type' => 'Place', 'name' => $city, 'address' => $address];
        }
        if ($places) {
            $org['location'] = $places;
        }

        $site = [
            '@type' => 'WebSite',
            '@id' => $d['url'] . '#website',
            'url' => $d['url'],
            'name' => $d['name'],
            'inLanguage' => $d['locales'],
            'publisher' => ['@id' => $orgId],
        ];
        return [$org, $site];
    }

    /** @param list<array{0:string,1:string}> $services [[nom, description], …] */
    public static function serviceList(array $services, string $providerName): array
    {
        $items = [];
        foreach (array_values($services) as $i => [$name, $description]) {
            $service = ['@type' => 'Service', 'name' => $name, 'provider' => ['@type' => 'Organization', 'name' => $providerName]];
            if ($description !== '') {
                $service['description'] = $description;
            }
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $service];
        }
        return ['@type' => 'ItemList', 'itemListElement' => $items];
    }
}
