<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Page extends TranslatedModel
{
    protected const TABLE = 'pages';
    protected const TR_TABLE = 'page_translations';
    protected const FK = 'page_id';
    protected const FIELDS = ['title', 'meta_title', 'meta_description', 'intro', 'content'];
    protected const MAIN = 'title';

    /** Page active avec sa traduction résolue (repli explicite). */
    public static function get(string $slug, string $locale): ?array
    {
        $row = Db::one('SELECT * FROM pages WHERE slug = :slug AND is_active = 1', ['slug' => $slug]);
        if ($row === null) {
            return null;
        }
        $localized = self::localize([$row], $locale);
        if (!$localized) {
            return null;
        }
        return $localized[0]['t'] + ['slug' => $slug];
    }

    /** @return list<array> */
    public static function all(): array
    {
        return Db::all('SELECT * FROM pages ORDER BY id');
    }

    public static function save(int $id, bool $active, array $translations): void
    {
        Db::transaction(static function () use ($id, $active, $translations): void {
            Db::run('UPDATE pages SET is_active = :a WHERE id = :id', ['a' => $active ? 1 : 0, 'id' => $id]);
            self::saveTranslations($id, $translations);
        });
    }
}
