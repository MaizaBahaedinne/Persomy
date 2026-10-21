<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Service extends TranslatedModel
{
    protected const TABLE = 'services';
    protected const TR_TABLE = 'service_translations';
    protected const FK = 'service_id';
    protected const FIELDS = ['title', 'summary'];
    protected const MAIN = 'title';

    public const ICONS = [
        'code' => 'Code',
        'cloud' => 'Cloud',
        'ai' => 'Intelligence artificielle',
        'automation' => 'Automatisation',
        'consulting' => 'Conseil',
    ];

    /** Services actifs localisés pour le site public. */
    public static function localized(string $locale): array
    {
        $rows = Db::all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id');
        return self::localize($rows, $locale);
    }

    /** Liste d'administration avec le titre dans la langue par défaut. */
    public static function adminList(string $locale): array
    {
        $rows = Db::all('SELECT * FROM services ORDER BY sort_order, id');
        $translations = self::translationsFor(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $t = \App\Core\Translations::pick($translations[(int) $row['id']] ?? [], $locale, 'title');
            $row['title'] = $t['title'] ?? '(sans titre)';
        }
        return $rows;
    }

    public static function save(?int $id, array $data, array $translations): int
    {
        return (int) Db::transaction(static function () use ($id, $data, $translations): int {
            $params = [
                'slug' => $data['slug'],
                'icon' => $data['icon'],
                'sort' => $data['sort_order'],
                'active' => $data['is_active'] ? 1 : 0,
            ];
            if ($id === null) {
                $id = Db::insert(
                    'INSERT INTO services (slug, icon, sort_order, is_active) VALUES (:slug, :icon, :sort, :active)',
                    $params
                );
            } else {
                Db::run(
                    'UPDATE services SET slug = :slug, icon = :icon, sort_order = :sort, is_active = :active WHERE id = :id',
                    $params + ['id' => $id]
                );
            }
            self::saveTranslations($id, $translations);
            return $id;
        });
    }
}
