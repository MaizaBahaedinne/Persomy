<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use App\Core\Translations;

final class BlogPost extends TranslatedModel
{
    protected const TABLE = 'blog_posts';
    protected const TR_TABLE = 'blog_post_translations';
    protected const FK = 'post_id';
    protected const FIELDS = ['title', 'excerpt', 'content', 'meta_title', 'meta_description', 'image_alt'];
    protected const MAIN = 'title';

    /** Condition SQL d'un article visible publiquement (publié et date atteinte : publication programmable). */
    private const VISIBLE = 'p.status = \'published\' AND p.published_at IS NOT NULL AND p.published_at <= NOW()';

    /** Articles visibles, localisés (repli explicite), du plus récent au plus ancien. */
    public static function published(string $locale, ?string $categorySlug = null, ?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug FROM blog_posts p
                LEFT JOIN blog_categories c ON c.id = p.category_id
                WHERE ' . self::VISIBLE;
        $params = [];
        if ($categorySlug !== null) {
            $sql .= ' AND c.slug = :cat';
            $params['cat'] = $categorySlug;
        }
        $sql .= ' ORDER BY p.published_at DESC, p.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset);
        }
        return self::withCategoryNames(self::localize(Db::all($sql, $params), $locale), $locale);
    }

    public static function countPublished(?string $categorySlug = null): int
    {
        $sql = 'SELECT COUNT(*) FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id WHERE ' . self::VISIBLE;
        $params = [];
        if ($categorySlug !== null) {
            $sql .= ' AND c.slug = :cat';
            $params['cat'] = $categorySlug;
        }
        return (int) Db::val($sql, $params);
    }

    public static function publishedBySlug(string $slug, string $locale): ?array
    {
        $row = Db::one(
            'SELECT p.*, c.slug AS category_slug FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             WHERE ' . self::VISIBLE . ' AND p.slug = :slug',
            ['slug' => $slug]
        );
        if ($row === null) {
            return null;
        }
        $list = self::withCategoryNames(self::localize([$row], $locale), $locale);
        return $list[0] ?? null;
    }

    /** Articles liés : même catégorie d'abord, complétés par les plus récents. */
    public static function related(array $post, string $locale, int $limit = 3): array
    {
        $rows = Db::all(
            'SELECT p.*, c.slug AS category_slug FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.category_id
             WHERE ' . self::VISIBLE . ' AND p.id <> :id
             ORDER BY (p.category_id <=> :cat) DESC, p.published_at DESC, p.id DESC
             LIMIT ' . max(1, $limit),
            ['id' => (int) $post['id'], 'cat' => $post['category_id'] !== null ? (int) $post['category_id'] : null]
        );
        return self::withCategoryNames(self::localize($rows, $locale), $locale);
    }

    private static function withCategoryNames(array $posts, string $locale): array
    {
        if (!$posts) {
            return [];
        }
        $names = [];
        foreach (BlogCategory::localized($locale) as $category) {
            $names[(int) $category['id']] = $category['t']['name'];
        }
        foreach ($posts as &$post) {
            $post['category_name'] = $names[(int) ($post['category_id'] ?? 0)] ?? null;
        }
        return $posts;
    }

    /** Pour le sitemap : slug + date de dernière modification des articles visibles. */
    public static function sitemapEntries(): array
    {
        return Db::all(
            'SELECT p.slug, p.updated_at, p.published_at FROM blog_posts p WHERE ' . self::VISIBLE . ' ORDER BY p.published_at DESC'
        );
    }

    /** Liste d'administration (titre dans la langue donnée, avec repli). */
    public static function adminList(string $locale, ?string $status = null): array
    {
        $sql = 'SELECT p.*, c.slug AS category_slug FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id';
        $params = [];
        if ($status === 'draft' || $status === 'published') {
            $sql .= ' WHERE p.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC';
        $rows = Db::all($sql, $params);
        $translations = self::translationsFor(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $t = Translations::pick($translations[(int) $row['id']] ?? [], $locale, 'title');
            $row['title'] = $t['title'] ?? '(sans titre)';
            $row['scheduled'] = $row['status'] === 'published' && $row['published_at'] !== null && strtotime((string) $row['published_at']) > time();
        }
        return $rows;
    }

    /**
     * @param array{slug:string,category_id:?int,status:string,featured:bool,cover_image:?string,author:string,published_at:?string} $data
     */
    public static function save(?int $id, array $data, array $translations): int
    {
        return (int) Db::transaction(static function () use ($id, $data, $translations): int {
            $params = [
                'slug' => $data['slug'],
                'category' => $data['category_id'],
                'status' => $data['status'],
                'featured' => $data['featured'] ? 1 : 0,
                'image' => $data['cover_image'],
                'author' => $data['author'],
                'pub' => $data['published_at'],
            ];
            // Publié sans date : date de publication = maintenant (heure de la base).
            $pubExpr = $data['status'] === 'published' ? 'COALESCE(:pub, NOW())' : ':pub';
            if ($id === null) {
                $id = Db::insert(
                    'INSERT INTO blog_posts (slug, category_id, status, featured, cover_image, author, published_at)
                     VALUES (:slug, :category, :status, :featured, :image, :author, ' . $pubExpr . ')',
                    $params
                );
            } else {
                Db::run(
                    'UPDATE blog_posts SET slug = :slug, category_id = :category, status = :status, featured = :featured,
                     cover_image = :image, author = :author, published_at = ' . $pubExpr . ' WHERE id = :id',
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
            'UPDATE blog_posts SET status = :s, published_at = IF(:s2 = \'published\', COALESCE(published_at, NOW()), published_at) WHERE id = :id',
            ['s' => $status, 's2' => $status, 'id' => $id]
        );
    }

    public static function counts(): array
    {
        $rows = Db::all('SELECT status, COUNT(*) AS n FROM blog_posts GROUP BY status');
        $out = ['draft' => 0, 'published' => 0];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['n'];
        }
        return $out;
    }
}
