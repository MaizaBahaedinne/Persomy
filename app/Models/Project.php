<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use App\Core\Translations;

final class Project extends TranslatedModel
{
    protected const TABLE = 'projects';
    protected const TR_TABLE = 'project_translations';
    protected const FK = 'project_id';
    protected const FIELDS = ['title', 'summary', 'description', 'problem', 'solution', 'results', 'image_alt'];
    protected const MAIN = 'title';

    /**
     * Projets publiés, localisés (repli explicite), avec nom de catégorie.
     */
    public static function published(string $locale, ?string $categorySlug = null, ?int $limit = null, bool $featuredOnly = false): array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug FROM projects p
                LEFT JOIN project_categories c ON c.id = p.category_id
                WHERE p.status = \'published\'';
        $params = [];
        if ($categorySlug !== null) {
            $sql .= ' AND c.slug = :cat';
            $params['cat'] = $categorySlug;
        }
        if ($featuredOnly) {
            $sql .= ' AND p.featured = 1';
        }
        $sql .= ' ORDER BY p.sort_order ASC, p.published_at DESC, p.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit);
        }
        return self::withCategoryNames(self::localize(Db::all($sql, $params), $locale), $locale);
    }

    public static function publishedBySlug(string $slug, string $locale): ?array
    {
        $row = Db::one(
            'SELECT p.*, c.slug AS category_slug FROM projects p
             LEFT JOIN project_categories c ON c.id = p.category_id
             WHERE p.status = \'published\' AND p.slug = :slug',
            ['slug' => $slug]
        );
        if ($row === null) {
            return null;
        }
        $list = self::withCategoryNames(self::localize([$row], $locale), $locale);
        return $list[0] ?? null;
    }

    private static function withCategoryNames(array $projects, string $locale): array
    {
        if (!$projects) {
            return [];
        }
        $names = [];
        foreach (Category::localized($locale) as $category) {
            $names[(int) $category['id']] = $category['t']['name'];
        }
        foreach ($projects as &$project) {
            $project['category_name'] = $names[(int) ($project['category_id'] ?? 0)] ?? null;
        }
        return $projects;
    }

    /** Liste d'administration (titre dans la langue donnée, avec repli). */
    public static function adminList(string $locale, ?string $status = null): array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug FROM projects p LEFT JOIN project_categories c ON c.id = p.category_id';
        $params = [];
        if ($status === 'draft' || $status === 'published') {
            $sql .= ' WHERE p.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY p.sort_order ASC, p.id DESC';
        $rows = Db::all($sql, $params);
        $translations = self::translationsFor(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $t = Translations::pick($translations[(int) $row['id']] ?? [], $locale, 'title');
            $row['title'] = $t['title'] ?? '(sans titre)';
        }
        return $rows;
    }

    public static function save(?int $id, array $data, array $translations): int
    {
        return (int) Db::transaction(static function () use ($id, $data, $translations): int {
            $params = [
                'slug' => $data['slug'],
                'category' => $data['category_id'],
                'status' => $data['status'],
                'demo' => $data['is_demo'] ? 1 : 0,
                'featured' => $data['featured'] ? 1 : 0,
                'image' => $data['main_image'],
                'sort' => $data['sort_order'],
            ];
            if ($id === null) {
                $id = Db::insert(
                    'INSERT INTO projects (slug, category_id, status, is_demo, featured, main_image, sort_order, published_at)
                     VALUES (:slug, :category, :status, :demo, :featured, :image, :sort, ' . ($data['status'] === 'published' ? 'NOW()' : 'NULL') . ')',
                    $params
                );
            } else {
                Db::run(
                    'UPDATE projects SET slug = :slug, category_id = :category, status = :status, is_demo = :demo,
                     featured = :featured, main_image = :image, sort_order = :sort'
                    . ($data['status'] === 'published' ? ', published_at = COALESCE(published_at, NOW())' : '')
                    . ' WHERE id = :id',
                    $params + ['id' => $id]
                );
            }
            self::saveTranslations($id, $translations);
            return $id;
        });
    }

    public static function setStatus(int $id, string $status): void
    {
        Db::run(
            'UPDATE projects SET status = :s, published_at = IF(:s2 = \'published\', COALESCE(published_at, NOW()), published_at) WHERE id = :id',
            ['s' => $status, 's2' => $status, 'id' => $id]
        );
    }

    public static function counts(): array
    {
        $rows = Db::all('SELECT status, COUNT(*) AS n FROM projects GROUP BY status');
        $out = ['draft' => 0, 'published' => 0];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['n'];
        }
        return $out;
    }
}
