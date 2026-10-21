<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Submission;
use App\Services\Mailer;

final class DashboardController extends AdminController
{
    public function index(Request $request, array $params): Response
    {
        $todo = [];
        foreach (Setting::all() as $key => $value) {
            if (has_placeholder($value)) {
                $todo[] = $key;
            }
        }

        $warnings = [];
        if ((string) Config::get('key', '') === '') {
            $warnings[] = 'APP_KEY est vide dans le fichier .env : renseignez une valeur aléatoire (voir README).';
        }
        if (!Mailer::configured()) {
            $warnings[] = 'SMTP non configuré : les demandes seront enregistrées mais aucun e-mail ne sera envoyé.';
        }
        if (Config::get('env') !== 'production') {
            $warnings[] = 'APP_ENV n’est pas « production ».';
        }
        if (!Request::isHttps()) {
            $warnings[] = 'Cette connexion n’est pas en HTTPS.';
        }

        return $this->adminView('admin/dashboard', [
            'title' => 'Tableau de bord',
            'active' => 'dashboard',
            'newMessages' => Submission::countNew(),
            'projects' => Project::counts(),
            'todo' => $todo,
            'warnings' => $warnings,
        ]);
    }
}
