<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use App\Core\Lang;
use App\Core\Translations;

/**
 * Base pour les entités avec une table principale et une table de traductions.
 * Les constantes (noms de tables/colonnes) sont fixes : jamais issues d'une saisie utilisateur.
 */
abstract class TranslatedModel
{
    protected const TABLE = '';
    protected const TR_TABLE = '';
    protected const FK = '';
    /** @var list<string> */
    protected const FIELDS = [];
    protected const MAIN = 'title';

    /** @return list<string> */
    public static function fields(): array
    {
        return static::FIELDS;
    }

    public static function mainField(): string
    {
        return static::MAIN;
    }

    public static function find(int $id): ?array
    {
        $row = Db::one('SELECT * FROM ' . static::TABLE . ' WHERE id = :id', ['id' => $id]);
        if ($row === null) {
            return null;
        }
        $row['tr'] = static::translationsFor([$id])[$id] ?? [];
        return $row;
    }

    /** @return array<int,array<string,array>> [id => [locale => ligne]] */
    public static function translationsFor(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $rows = Db::all('SELECT * FROM ' . static::TR_TABLE . ' WHERE ' . static::FK . ' IN (' . $in . ')', $ids);
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row[static::FK]][$row['locale']] = $row;
        }
        return $out;
    }

    /**
     * Ajoute la clé 't' (traduction résolue avec repli) et ignore les lignes sans aucune traduction.
     *
     * @return list<array>
     */
    public static function localize(array $rows, string $locale): array
    {
        $translations = static::translationsFor(array_column($rows, 'id'));
        $out = [];
        foreach ($rows as $row) {
            $t = Translations::pick($translations[(int) $row['id']] ?? [], $locale, static::MAIN);
            if ($t !== null) {
                $row['t'] = $t;
                $out[] = $row;
            }
        }
        return $out;
    }

    /** @param array<string,array<string,string>> $data [locale => [champ => valeur]] */
    public static function saveTranslations(int $id, array $data): void
    {
        $fields = static::FIELDS;
        $insert = 'INSERT INTO ' . static::TR_TABLE . ' (' . static::FK . ', locale, ' . implode(', ', $fields) . ') VALUES (:id, :loc, '
            . implode(', ', array_map(static fn(string $f): string => ':' . $f, $fields)) . ') ON DUPLICATE KEY UPDATE '
            . implode(', ', array_map(static fn(string $f): string => $f . ' = VALUES(' . $f . ')', $fields));

        foreach (Lang::supported() as $locale) {
            $values = $data[$locale] ?? [];
            $filled = false;
            $params = ['id' => $id, 'loc' => $locale];
            foreach ($fields as $field) {
                $value = trim((string) ($values[$field] ?? ''));
                $params[$field] = $value;
                $filled = $filled || $value !== '';
            }
            if ($filled) {
                Db::run($insert, $params);
            } else {
                Db::run('DELETE FROM ' . static::TR_TABLE . ' WHERE ' . static::FK . ' = :id AND locale = :loc', ['id' => $id, 'loc' => $locale]);
            }
        }
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM ' . static::TABLE . ' WHERE id = :id', ['id' => $id]);
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Db::val(
            'SELECT id FROM ' . static::TABLE . ' WHERE slug = :slug AND id <> :id LIMIT 1',
            ['slug' => $slug, 'id' => $exceptId ?? 0]
        ) !== null;
    }

    /**
     * Nettoie les traductions postées et retourne [données, erreur|null].
     * Règle : au moins une langue avec le champ principal ; un champ rempli exige le champ principal.
     *
     * @return array{0:array<string,array<string,string>>,1:?string}
     */
    public static function cleanTranslations(array $posted, array $limits = []): array
    {
        $clean = [];
        $error = null;
        $hasMain = false;
        foreach (Lang::supported() as $locale) {
            $row = is_array($posted[$locale] ?? null) ? $posted[$locale] : [];
            $filled = false;
            foreach (static::FIELDS as $field) {
                $value = isset($row[$field]) && is_string($row[$field]) ? trim($row[$field]) : '';
                $max = $limits[$field] ?? 5000;
                if (mb_strlen($value) > $max) {
                    $error ??= 'Un champ dépasse la longueur maximale (' . $max . ' caractères) en ' . strtoupper($locale) . '.';
                    $value = mb_substr($value, 0, $max);
                }
                $clean[$locale][$field] = $value;
                $filled = $filled || $value !== '';
            }
            if ($filled && $clean[$locale][static::MAIN] === '') {
                $error ??= 'Le champ principal est obligatoire dès qu’une traduction est renseignée (' . strtoupper($locale) . ').';
            }
            $hasMain = $hasMain || $clean[$locale][static::MAIN] !== '';
        }
        if (!$hasMain) {
            $error ??= 'Renseignez au moins une langue.';
        }
        return [$clean, $error];
    }
}
