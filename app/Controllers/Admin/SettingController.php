<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;

final class SettingController extends AdminController
{
    /** Réglages éditables : clé => [libellé, type, aide]. Aucun secret ici (SMTP, base : voir .env). */
    public const FIELDS = [
        'site_name' => ['Nom du site', 'text', 'Affiché dans le titre, l’en-tête et le pied de page.'],
        'default_locale' => ['Langue par défaut', 'locale', 'Utilisée pour la racine du site (/) et comme langue de repli.'],
        'contact_email' => ['E-mail public de contact', 'email', 'Affiché sur le site. Laisser vide pour ne pas l’afficher.'],
        'contact_phone' => ['Téléphone public', 'text', 'Facultatif.'],
        'contact_recipient' => ['Destinataire des demandes du formulaire', 'email', 'Adresse qui reçoit les notifications. À défaut : MAIL_TO du fichier .env.'],
        'address_paris' => ['Adresse du bureau de Paris', 'textarea', 'Facultatif. Ne publier que des informations confirmées.'],
        'address_tunis' => ['Adresse du bureau de Tunis', 'textarea', 'Facultatif. Ne publier que des informations confirmées.'],
        'linkedin_url' => ['Lien LinkedIn', 'url', 'URL complète en https://.'],
        'contact_retention_days' => ['Conservation des demandes (jours)', 'number', 'Les demandes plus anciennes peuvent être purgées depuis « Messages ».'],
    ];

    public function edit(Request $request, array $params): Response
    {
        return $this->form(Setting::all(), [], 200);
    }

    public function update(Request $request, array $params): Response
    {
        $values = [];
        $errors = [];
        foreach (self::FIELDS as $key => [$label, $type]) {
            $value = $request->input($key);
            if (mb_strlen($value) > 500) {
                $errors[] = $label . ' : valeur trop longue.';
                continue;
            }
            if ($value !== '' && !has_placeholder($value)) {
                switch ($type) {
                    case 'email':
                        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                            $errors[] = $label . ' : adresse e-mail invalide.';
                        }
                        break;
                    case 'url':
                        if (!preg_match('#^https://[^\s]+$#i', $value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
                            $errors[] = $label . ' : URL https:// invalide.';
                        }
                        break;
                    case 'number':
                        if (!ctype_digit($value) || (int) $value < 1 || (int) $value > 3650) {
                            $errors[] = $label . ' : entier entre 1 et 3650.';
                        }
                        break;
                    case 'locale':
                        if (!Lang::isSupported($value)) {
                            $errors[] = $label . ' : langue non prise en charge.';
                        }
                        break;
                }
            }
            if ($key === 'site_name' && $value === '') {
                $errors[] = 'Le nom du site est obligatoire.';
            }
            if ($key === 'default_locale' && $value === '') {
                $errors[] = 'La langue par défaut est obligatoire.';
            }
            $values[$key] = $value;
        }

        if ($errors) {
            return $this->form($values, $errors, 422);
        }
        try {
            foreach ($values as $key => $value) {
                Setting::set($key, $value);
            }
        } catch (\Throwable $e) {
            Logger::error('Settings save failed', ['error' => get_class($e)]);
            return $this->form($values, ['Erreur technique : enregistrement impossible.'], 500);
        }
        $this->success('Réglages enregistrés.');
        return $this->redirect('/admin/settings');
    }

    private function form(array $values, array $errors, int $status): Response
    {
        return $this->adminView('admin/settings', [
            'title' => 'Réglages du site',
            'active' => 'settings',
            'values' => $values,
            'errors' => $errors,
            'fields' => self::FIELDS,
        ], $status);
    }
}
