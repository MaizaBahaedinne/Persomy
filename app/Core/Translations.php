<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Sélection d'une traduction avec repli explicite.
 * Une traduction est considérée présente si son champ principal est non vide.
 */
final class Translations
{
    /**
     * @param array<string,array> $byLocale  [locale => ligne]
     * @return array|null ligne + _locale + _fallback
     */
    public static function pick(array $byLocale, string $locale, string $mainField): ?array
    {
        $order = array_unique(array_merge([$locale, Lang::defaultLocale()], Lang::supported()));
        foreach ($order as $candidate) {
            $row = $byLocale[$candidate] ?? null;
            if ($row !== null && trim((string) ($row[$mainField] ?? '')) !== '') {
                $row['_locale'] = $candidate;
                $row['_fallback'] = $candidate !== $locale;
                return $row;
            }
        }
        return null;
    }
}
