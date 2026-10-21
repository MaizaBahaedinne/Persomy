<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use App\Core\Translations;

final class Category extends TranslatedModel
{
    protected const TABLE = 'project_categories';
    protected const TR_TABLE = 'project_category_translations';
    protected const FK = 'category_id';
    protected const FIELDS = ['name'];
    protected const MAIN = 'name';

    public static function localized(string $locale): array
    {
        return self::localize(Db::all('SELECT * FROM project_categories ORDER BY sort_order, id'), $locale);
    }

    /** Liste d'administration avec nom par défaut et nombre de projets. */
    public static function adminList(string $locale): array
    {
        $rows = Db::all(
            'SELECT c.*, (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) AS projects_count
             FROM project_categories c ORDER BY c.sort_order, c.id'
        );
        $translations = self::translationsFor(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $t = Translations::pick($translations[(int) $row['id']] ?? [], $locale, 'name');
            $row['name'] = $t['name'] ?? '(sans nom)';
        }
        return $rows;
    }

    public static function save(?int $id, string $slug, int $sort, array $translations): int
    {
        return (int) Db::transaction(static function () use ($id, $slug, $sort, $translations): int {
            if ($id === null) {
                $id = Db::insert(
                    'INSERT INTO project_categories (slug, sort_order) VALUES (:slug, :sort)',
                    ['slug' => $slug, 'sort' => $sort]
                );
            } else {
                Db::run(
                    'UPDATE project_categories SET slug = :slug, sort_order = :sort WHERE id = :id',
                    ['slug' => $slug, 'sort' => $sort, 'id' => $id]
                );
            }
            self::saveTranslations($id, $translations);
            return $id;
        });
    }
}
