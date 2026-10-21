<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogCategory;

final class BlogCategoryController extends AdminController
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminView('admin/blog_categories/index', [
            'title' => 'Catégories du blog',
            'active' => 'blog_categories',
            'categories' => BlogCategory::adminList(Lang::defaultLocale()),
        ]);
    }

    public function create(Request $request, array $params): Response
    {
        return $this->form(null, ['slug' => '', 'sort_order' => 0], [], [], 200);
    }

    public function edit(Request $request, array $params): Response
    {
        $category = BlogCategory::find((int) $params['id']) ?? $this->abort(404);
        return $this->form($category, $category, $category['tr'], [], 200);
    }

    public function store(Request $request, array $params): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        BlogCategory::find($id) ?? $this->abort(404);
        return $this->save($request, $id);
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        BlogCategory::find($id) ?? $this->abort(404);
        BlogCategory::delete($id);
        $this->success('Catégorie supprimée (les articles associés n’ont plus de catégorie).');
        return $this->redirect('/admin/blog-categories');
    }

    private function save(Request $request, ?int $id): Response
    {
        $existing = $id !== null ? (BlogCategory::find($id) ?? $this->abort(404)) : null;
        $errors = [];
        [$translations, $trError] = BlogCategory::cleanTranslations($request->arrayInput('tr'), ['name' => 150]);
        if ($trError !== null) {
            $errors[] = $trError;
        }
        $slug = slugify($request->input('slug'));
        if ($slug === '') {
            foreach (Lang::supported() as $locale) {
                if (($translations[$locale]['name'] ?? '') !== '') {
                    $slug = slugify($translations[$locale]['name']);
                    break;
                }
            }
        }
        if (!is_valid_slug($slug)) {
            $errors[] = 'Identifiant invalide (lettres minuscules, chiffres et tirets).';
        } elseif (BlogCategory::slugExists($slug, $id)) {
            $errors[] = 'Cet identifiant est déjà utilisé.';
        }
        $sort = max(-9999, min(9999, (int) $request->input('sort_order', '0')));
        $values = ['slug' => $slug, 'sort_order' => $sort];

        if ($errors) {
            return $this->form($existing, $values, $translations, $errors, 422);
        }
        try {
            BlogCategory::save($id, $slug, $sort, $translations);
        } catch (\Throwable $e) {
            Logger::error('Blog category save failed', ['error' => get_class($e)]);
            return $this->form($existing, $values, $translations, ['Erreur technique : enregistrement impossible.'], 500);
        }
        $this->success('Catégorie enregistrée.');
        return $this->redirect('/admin/blog-categories');
    }

    private function form(?array $category, array $values, array $tr, array $errors, int $status): Response
    {
        return $this->adminView('admin/blog_categories/form', [
            'title' => $category ? 'Modifier la catégorie' : 'Nouvelle catégorie',
            'active' => 'blog_categories',
            'category' => $category,
            'values' => $values,
            'tr' => $tr,
            'errors' => $errors,
            'fields' => [['name', 'Nom', 'text', 1]],
        ], $status);
    }
}
