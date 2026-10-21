<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;

final class ServiceController extends AdminController
{
    public function index(Request $request, array $params): Response
    {
        return $this->adminView('admin/services/index', [
            'title' => 'Services',
            'active' => 'services',
            'services' => Service::adminList(Lang::defaultLocale()),
        ]);
    }

    public function create(Request $request, array $params): Response
    {
        return $this->form(null, ['slug' => '', 'icon' => 'code', 'sort_order' => 0, 'is_active' => 1], [], [], 200);
    }

    public function edit(Request $request, array $params): Response
    {
        $service = Service::find((int) $params['id']) ?? $this->abort(404);
        return $this->form($service, $service, $service['tr'], [], 200);
    }

    public function store(Request $request, array $params): Response
    {
        return $this->save($request, null);
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        Service::find($id) ?? $this->abort(404);
        return $this->save($request, $id);
    }

    public function destroy(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        Service::find($id) ?? $this->abort(404);
        Service::delete($id);
        $this->success('Service supprimé.');
        return $this->redirect('/admin/services');
    }

    private function save(Request $request, ?int $id): Response
    {
        $existing = $id !== null ? (Service::find($id) ?? $this->abort(404)) : null;
        $errors = [];
        [$translations, $trError] = Service::cleanTranslations($request->arrayInput('tr'), ['title' => 200, 'summary' => 600]);
        if ($trError !== null) {
            $errors[] = $trError;
        }
        $slug = $request->input('slug');
        if ($slug === '') {
            foreach (Lang::supported() as $locale) {
                if (($translations[$locale]['title'] ?? '') !== '') {
                    $slug = slugify($translations[$locale]['title']);
                    break;
                }
            }
        }
        if (!is_valid_slug($slug) || $slug === 'other') {
            $errors[] = 'Identifiant invalide (lettres minuscules, chiffres et tirets ; « other » est réservé).';
        } elseif (Service::slugExists($slug, $id)) {
            $errors[] = 'Cet identifiant est déjà utilisé.';
        }
        $icon = $request->input('icon');
        if (!isset(Service::ICONS[$icon])) {
            $errors[] = 'Icône invalide.';
            $icon = 'code';
        }
        $values = [
            'slug' => $slug,
            'icon' => $icon,
            'sort_order' => max(-9999, min(9999, (int) $request->input('sort_order', '0'))),
            'is_active' => $this->checkbox($request, 'is_active') ? 1 : 0,
        ];

        if ($errors) {
            return $this->form($existing, $values, $translations, $errors, 422);
        }
        try {
            Service::save($id, $values, $translations);
        } catch (\Throwable $e) {
            Logger::error('Service save failed', ['error' => get_class($e)]);
            return $this->form($existing, $values, $translations, ['Erreur technique : enregistrement impossible.'], 500);
        }
        $this->success('Service enregistré.');
        return $this->redirect('/admin/services');
    }

    private function form(?array $service, array $values, array $tr, array $errors, int $status): Response
    {
        return $this->adminView('admin/services/form', [
            'title' => $service ? 'Modifier le service' : 'Nouveau service',
            'active' => 'services',
            'service' => $service,
            'values' => $values,
            'tr' => $tr,
            'errors' => $errors,
            'icons' => Service::ICONS,
            'fields' => [['title', 'Titre', 'text', 1], ['summary', 'Description courte', 'textarea', 3]],
        ], $status);
    }
}
