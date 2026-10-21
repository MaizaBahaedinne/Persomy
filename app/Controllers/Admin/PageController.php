<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Translations;
use App\Models\Page;

final class PageController extends AdminController
{
    private const LABELS = [
        'home' => 'Accueil',
        'about' => 'À propos',
        'services' => 'Services (introduction)',
        'portfolio' => 'Portfolio (introduction)',
        'contact' => 'Contact (introduction)',
        'legal' => 'Mentions légales',
        'privacy' => 'Politique de confidentialité',
    ];

    public function index(Request $request, array $params): Response
    {
        $pages = Page::all();
        $translations = Page::translationsFor(array_column($pages, 'id'));
        foreach ($pages as &$page) {
            $page['label'] = self::LABELS[$page['slug']] ?? $page['slug'];
            $page['locales'] = array_keys(array_filter(
                $translations[(int) $page['id']] ?? [],
                static fn(array $t): bool => trim((string) $t['title']) !== ''
            ));
            $page['has_placeholder'] = false;
            foreach ($translations[(int) $page['id']] ?? [] as $t) {
                if (has_placeholder((string) $t['content']) || has_placeholder((string) $t['intro'])) {
                    $page['has_placeholder'] = true;
                }
            }
        }
        unset($page);

        return $this->adminView('admin/pages/index', ['title' => 'Pages', 'active' => 'pages', 'pages' => $pages]);
    }

    public function edit(Request $request, array $params): Response
    {
        $page = Page::find((int) $params['id']) ?? $this->abort(404);
        return $this->form($page, (int) $page['is_active'], $page['tr'], [], 200);
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        $page = Page::find($id) ?? $this->abort(404);
        $active = $this->checkbox($request, 'is_active');
        [$translations, $error] = Page::cleanTranslations($request->arrayInput('tr'), [
            'title' => 200, 'meta_title' => 200, 'meta_description' => 320, 'intro' => 1500, 'content' => 30000,
        ]);
        if ($error !== null) {
            return $this->form($page, $active ? 1 : 0, $translations, [$error], 422);
        }
        try {
            Page::save($id, $active, $translations);
        } catch (\Throwable $e) {
            Logger::error('Page save failed', ['error' => get_class($e)]);
            return $this->form($page, $active ? 1 : 0, $translations, ['Erreur technique : enregistrement impossible.'], 500);
        }
        $this->success('Page enregistrée.');
        return $this->redirect('/admin/pages');
    }

    private function form(array $page, int $active, array $tr, array $errors, int $status): Response
    {
        $isHome = $page['slug'] === 'home';
        return $this->adminView('admin/pages/form', [
            'title' => 'Modifier : ' . (self::LABELS[$page['slug']] ?? $page['slug']),
            'active' => 'pages',
            'page' => $page,
            'isActive' => $active,
            'tr' => $tr,
            'errors' => $errors,
            'fields' => [
                ['title', $isHome ? 'Titre principal (bandeau d’accueil)' : 'Titre', 'text', 1],
                ['meta_title', 'Titre SEO (balise title, facultatif)', 'text', 1],
                ['meta_description', 'Description SEO (160 caractères conseillés)', 'textarea', 2],
                ['intro', 'Introduction', 'textarea', 3],
                ['content', 'Contenu (voir l’aide de mise en forme)', 'textarea', 14],
            ],
        ], $status);
    }
}
