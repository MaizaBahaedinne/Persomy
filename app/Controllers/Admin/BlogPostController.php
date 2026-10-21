<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Uploader;

final class BlogPostController extends AdminController
{
    private const LIMITS = ['title' => 200, 'excerpt' => 500, 'content' => 60000, 'meta_title' => 200, 'meta_description' => 320, 'image_alt' => 200];

    public function index(Request $request, array $params): Response
    {
        $status = $request->query('status');
        return $this->adminView('admin/blog/index', [
            'title' => 'Blog',
            'active' => 'blog',
            'posts' => BlogPost::adminList(Lang::defaultLocale(), $status !== '' ? $status : null),
            'status' => $status,
        ]);
    }

    public function create(Request $request, array $params): Response
    {
        return $this->form(null, [
            'slug' => '', 'category_id' => '', 'status' => 'draft', 'featured' => 0, 'cover_image' => null, 'author' => '', 'published_at' => null,
        ], [], [], 200);
    }

    public function edit(Request $request, array $params): Response
    {
        $post = BlogPost::find((int) $params['id']) ?? $this->abort(404);
        return $this->form($post, $post, $post['tr'], [], 200);
    }

    public function store(Request $request, array $params): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        BlogPost::find($id) ?? $this->abort(404);
        return $this->save($request, $id);
    }

    public function toggle(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        $post = BlogPost::find($id) ?? $this->abort(404);
        $new = $post['status'] === 'published' ? 'draft' : 'published';
        BlogPost::setStatus($id, $new);
        $this->success($new === 'published' ? 'Article publié.' : 'Article repassé en brouillon.');
        return $this->redirect('/admin/blog');
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        $post = BlogPost::find($id) ?? $this->abort(404);
        BlogPost::delete($id);
        Uploader::delete($post['cover_image'], 'blog');
        $this->success('Article supprimé.');
        return $this->redirect('/admin/blog');
    }

    private function save(Request $request, ?int $id): Response
    {
        $existing = $id !== null ? (BlogPost::find($id) ?? $this->abort(404)) : null;
        $errors = [];

        $slug = $request->input('slug');
        [$translations, $trError] = BlogPost::cleanTranslations($request->arrayInput('tr'), self::LIMITS);
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
        } elseif (BlogPost::slugExists($slug, $id)) {
            $errors[] = 'Cet identifiant d’URL est déjà utilisé.';
        }

        $categoryId = $request->input('category_id');
        $category = null;
        if ($categoryId !== '') {
            $category = ctype_digit($categoryId) && BlogCategory::find((int) $categoryId) ? (int) $categoryId : null;
            if ($category === null) {
                $errors[] = 'Catégorie invalide.';
            }
        }

        $publishedAt = null;
        $rawDate = $request->input('published_at');
        if ($rawDate !== '') {
            $date = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $rawDate) ? \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $rawDate) : false;
            if ($date === false) {
                $errors[] = 'Date de publication invalide.';
            } else {
                $publishedAt = $date->format('Y-m-d H:i:00');
            }
        }

        $author = trim($request->input('author'));
        if (mb_strlen($author) > 120) {
            $errors[] = 'Le nom de l’auteur dépasse 120 caractères.';
        }

        $data = [
            'slug' => $slug,
            'category_id' => $category,
            'status' => $request->input('status') === 'published' ? 'published' : 'draft',
            'featured' => $this->checkbox($request, 'featured'),
            'cover_image' => $existing['cover_image'] ?? null,
            'author' => mb_substr($author, 0, 120),
            'published_at' => $publishedAt,
        ];

        // Image : validation stricte avant toute écriture en base.
        $newImage = null;
        $upload = $request->file('image');
        if ($upload !== null && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE && !$errors) {
            try {
                $newImage = Uploader::saveImage($upload, 'blog');
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        } elseif ($upload !== null && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Corrigez les erreurs puis sélectionnez de nouveau l’image.';
        }
        $oldImage = $existing['cover_image'] ?? null;
        if ($newImage !== null) {
            $data['cover_image'] = $newImage;
        } elseif ($this->checkbox($request, 'remove_image')) {
            $data['cover_image'] = null;
        }

        if ($errors) {
            return $this->form($existing, $data + ['id' => $id], $translations, $errors, 422);
        }

        try {
            BlogPost::save($id, $data, $translations);
        } catch (\Throwable $e) {
            Logger::error('Blog post save failed', ['error' => get_class($e)]);
            if ($newImage !== null) {
                Uploader::delete($newImage, 'blog');
            }
            return $this->form($existing, $data + ['id' => $id], $translations, ['Erreur technique : l’article n’a pas pu être enregistré.'], 500);
        }

        if ($oldImage !== null && $data['cover_image'] !== $oldImage) {
            Uploader::delete($oldImage, 'blog');
        }
        $this->success('Article enregistré.');
        return $this->redirect('/admin/blog');
    }

    private function form(?array $post, array $values, array $tr, array $errors, int $status): Response
    {
        return $this->adminView('admin/blog/form', [
            'title' => $post ? 'Modifier l’article' : 'Nouvel article',
            'active' => 'blog',
            'post' => $post,
            'values' => $values,
            'tr' => $tr,
            'errors' => $errors,
            'categories' => BlogCategory::adminList(Lang::defaultLocale()),
            'fields' => [
                ['title', 'Titre', 'text', 1],
                ['excerpt', 'Chapeau (résumé affiché dans les listes)', 'textarea', 3],
                ['content', 'Contenu de l’article', 'textarea', 18],
                ['image_alt', 'Texte alternatif de l’image de couverture', 'text', 1],
                ['meta_title', 'Titre SEO (facultatif)', 'text', 1],
                ['meta_description', 'Description SEO (facultatif)', 'textarea', 2],
            ],
        ], $status);
    }
}
