<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\Project;
use App\Services\Uploader;

final class ProjectController extends AdminController
{
    private const LIMITS = ['title' => 200, 'summary' => 500, 'description' => 8000, 'problem' => 8000, 'solution' => 8000, 'results' => 8000, 'image_alt' => 200];

    public function index(Request $request, array $params): Response
    {
        $status = $request->query('status');
        return $this->adminView('admin/projects/index', [
            'title' => 'Projets',
            'active' => 'projects',
            'projects' => Project::adminList(Lang::defaultLocale(), $status !== '' ? $status : null),
            'status' => $status,
        ]);
    }

    public function create(Request $request, array $params): Response
    {
        return $this->form(null, [
            'slug' => '', 'category_id' => '', 'status' => 'draft', 'is_demo' => 0, 'featured' => 0, 'sort_order' => 0, 'main_image' => null,
        ], [], [], 200);
    }

    public function edit(Request $request, array $params): Response
    {
        $project = Project::find((int) $params['id']) ?? $this->abort(404);
        return $this->form($project, $project, $project['tr'], [], 200);
    }

    public function store(Request $request, array $params): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        Project::find($id) ?? $this->abort(404);
        return $this->save($request, $id);
    }

    public function toggle(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        $project = Project::find($id) ?? $this->abort(404);
        $new = $project['status'] === 'published' ? 'draft' : 'published';
        Project::setStatus($id, $new);
        $this->success($new === 'published' ? 'Projet publié.' : 'Projet repassé en brouillon.');
        return $this->redirect('/admin/projects');
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        $project = Project::find($id) ?? $this->abort(404);
        Project::delete($id);
        Uploader::delete($project['main_image']);
        $this->success('Projet supprimé.');
        return $this->redirect('/admin/projects');
    }

    private function save(Request $request, ?int $id): Response
    {
        $existing = $id !== null ? (Project::find($id) ?? $this->abort(404)) : null;
        $errors = [];

        $slug = $request->input('slug');
        [$translations, $trError] = Project::cleanTranslations($request->arrayInput('tr'), self::LIMITS);
        if ($trError !== null) {
            $errors[] = $trError;
        }
        if ($slug === '') {
            foreach (Lang::supported() as $locale) {
                if (($translations[$locale]['title'] ?? '') !== '') {
                    $slug = slugify($translations[$locale]['title']);
                    break;
                }
            }
        }
        if (!is_valid_slug($slug)) {
            $errors[] = 'Identifiant d’URL invalide (lettres minuscules, chiffres et tirets).';
        } elseif (Project::slugExists($slug, $id)) {
            $errors[] = 'Cet identifiant d’URL est déjà utilisé.';
        }

        $categoryId = $request->input('category_id');
        $category = null;
        if ($categoryId !== '') {
            $category = ctype_digit($categoryId) && Category::find((int) $categoryId) ? (int) $categoryId : null;
            if ($category === null) {
                $errors[] = 'Catégorie invalide.';
            }
        }
        $status = $request->input('status') === 'published' ? 'published' : 'draft';
        $sort = (int) $request->input('sort_order', '0');

        $data = [
            'slug' => $slug,
            'category_id' => $category,
            'status' => $status,
            'is_demo' => $this->checkbox($request, 'is_demo'),
            'featured' => $this->checkbox($request, 'featured'),
            'sort_order' => max(-9999, min(9999, $sort)),
            'main_image' => $existing['main_image'] ?? null,
        ];

        // Image : validation stricte avant toute écriture en base.
        $newImage = null;
        $upload = $request->file('image');
        if ($upload !== null && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE && !$errors) {
            try {
                $newImage = Uploader::saveImage($upload);
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        } elseif ($upload !== null && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Corrigez les erreurs puis sélectionnez de nouveau l’image.';
        }
        $oldImage = $existing['main_image'] ?? null;
        if ($newImage !== null) {
            $data['main_image'] = $newImage;
        } elseif ($this->checkbox($request, 'remove_image')) {
            $data['main_image'] = null;
        }

        if ($errors) {
            return $this->form($existing, $data + ['id' => $id], $translations, $errors, 422);
        }

        try {
            Project::save($id, $data, $translations);
        } catch (\Throwable $e) {
            Logger::error('Project save failed', ['error' => get_class($e)]);
            if ($newImage !== null) {
                Uploader::delete($newImage);
            }
            return $this->form($existing, $data + ['id' => $id], $translations, ['Erreur technique : le projet n’a pas pu être enregistré.'], 500);
        }

        if ($oldImage !== null && $data['main_image'] !== $oldImage) {
            Uploader::delete($oldImage);
        }
        $this->success('Projet enregistré.');
        return $this->redirect('/admin/projects');
    }

    private function form(?array $project, array $values, array $tr, array $errors, int $status): Response
    {
        return $this->adminView('admin/projects/form', [
            'title' => $project ? 'Modifier le projet' : 'Nouveau projet',
            'active' => 'projects',
            'project' => $project,
            'values' => $values,
            'tr' => $tr,
            'errors' => $errors,
            'categories' => Category::adminList(Lang::defaultLocale()),
            'fields' => [
                ['title', 'Titre', 'text', 1],
                ['summary', 'Résumé', 'textarea', 3],
                ['description', 'Contexte', 'textarea', 6],
                ['problem', 'Problématique', 'textarea', 5],
                ['solution', 'Solution', 'textarea', 5],
                ['results', 'Résultats publiables', 'textarea', 5],
                ['image_alt', 'Texte alternatif de l’image', 'text', 1],
            ],
        ], $status);
    }
}
