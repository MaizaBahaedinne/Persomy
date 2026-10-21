<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\ErrorPage;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Submission;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Services\Seo;

final class ContactController extends Controller
{
    private const MAX_PER_HOUR = 5;
    private const MIN_FILL_SECONDS = 3;

    public function show(Request $request, array $params): Response
    {
        Session::set('contact_ts', time());
        $result = Session::pullFlash('contact_result');
        return $this->render([], [], null, 200, is_string($result) ? $result : null);
    }

    public function submit(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $old = [
            'name' => $request->input('name'),
            'email' => mb_strtolower($request->input('email')),
            'company' => $request->input('company'),
            'subject' => $request->input('subject'),
            'message' => str_replace("\r\n", "\n", $request->input('message')),
        ];

        // Anti-spam : champ piège et délai minimal de remplissage.
        $startedAt = (int) Session::get('contact_ts', 0);
        if ($request->input('website') !== '' || $startedAt === 0 || time() - $startedAt < self::MIN_FILL_SECONDS) {
            return $this->render($old, [], t('contact.error_generic'), 422, null);
        }

        $bucket = RateLimiter::bucket('contact', $request->ipHash());
        try {
            if (RateLimiter::hit($bucket, 3600) > self::MAX_PER_HOUR) {
                return $this->render($old, [], t('contact.error_rate'), 429, null);
            }
        } catch (\Throwable $e) {
            Logger::error('Rate limiter unavailable', ['error' => get_class($e)]);
        }

        // Sujets autorisés : slugs des services actifs + "other".
        $services = Service::localized($locale);
        $subjectLabels = ['' => '', 'other' => t('contact.subject_other')];
        foreach ($services as $service) {
            $subjectLabels[$service['slug']] = $service['t']['title'];
        }

        $errors = [];
        if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) {
            $errors['name'] = t('contact.errors.name');
        }
        if ($old['email'] === '' || mb_strlen($old['email']) > 190 || filter_var($old['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = t('contact.errors.email');
        }
        if (mb_strlen($old['company']) > 150) {
            $errors['company'] = t('contact.errors.company');
        }
        if (!array_key_exists($old['subject'], $subjectLabels)) {
            $errors['subject'] = t('contact.errors.subject');
        }
        if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 5000) {
            $errors['message'] = t('contact.errors.message');
        }
        if ($request->input('consent') !== '1') {
            $errors['consent'] = t('contact.errors.consent');
        }
        if ($errors) {
            return $this->render($old, $errors, null, 422, null);
        }

        $data = [
            'name' => $old['name'],
            'email' => $old['email'],
            'company' => $old['company'],
            'subject' => $subjectLabels[$old['subject']],
            'message' => $old['message'],
            'locale' => $locale,
            'ip_hash' => $request->ipHash(),
        ];

        // 1. Enregistrement en base (si possible).
        $id = null;
        try {
            $id = Submission::create($data);
        } catch (\Throwable $e) {
            Logger::error('Contact submission could not be stored', ['error' => get_class($e)]);
        }

        // 2. Notification par e-mail (SMTP configuré via .env).
        $mailStatus = $this->notify($data);
        if ($id !== null) {
            try {
                Submission::setMailStatus($id, $mailStatus);
            } catch (\Throwable $e) {
                Logger::error('Mail status could not be stored', ['error' => get_class($e)]);
            }
        }

        // 3. Retour honnête : on n'annonce que ce qui a réellement réussi.
        if ($mailStatus === 'sent') {
            $result = 'sent';
        } elseif ($id === null) {
            return $this->render($old, [], t('contact.error_failed'), 500, null);
        } else {
            $result = $mailStatus === 'failed' ? 'saved_mail_failed' : 'saved';
        }

        Session::forget('contact_ts');
        Session::flash('contact_result', $result);
        return $this->redirect(lurl('contact'));
    }

    /** @return 'sent'|'failed'|'skipped' */
    private function notify(array $data): string
    {
        $recipient = trim((string) Setting::get('contact_recipient', ''));
        if (is_placeholder($recipient) || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            $recipient = (string) Config::get('mail.to', '');
        }
        if (!Mailer::configured() || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return 'skipped';
        }

        $body = "Nouvelle demande de contact depuis le site\n\n"
            . 'Nom : ' . $data['name'] . "\n"
            . 'E-mail : ' . $data['email'] . "\n"
            . 'Entreprise : ' . ($data['company'] !== '' ? $data['company'] : '-') . "\n"
            . 'Sujet : ' . ($data['subject'] !== '' ? $data['subject'] : '-') . "\n"
            . 'Langue : ' . $data['locale'] . "\n\n"
            . "Message :\n" . $data['message'] . "\n";

        try {
            Mailer::send($recipient, '[Persomy] Nouvelle demande de contact', $body, $data['email'], $data['name']);
            return 'sent';
        } catch (\Throwable $e) {
            // Aucun contenu du message ni identifiant dans le journal.
            Logger::error('Contact notification failed: ' . $e->getMessage());
            return 'failed';
        }
    }

    private function render(array $old, array $errors, ?string $formError, int $status, ?string $result): Response
    {
        $locale = Lang::locale();
        $page = Page::get('contact', $locale);
        if ($page === null) {
            return ErrorPage::response(404);
        }
        $description = $page['meta_description'] !== '' ? $page['meta_description'] : truncate((string) $page['intro']);

        $response = $this->view('public/contact', [
            'meta' => [
                'title' => $page['meta_title'] !== '' ? $page['meta_title'] : $page['title'],
                'description' => $description,
                'path' => 'contact',
                'nav' => 'contact',
                'jsonld' => Seo::graph([Seo::breadcrumbs([
                    [t('nav.home'), abs_url(lurl(''))],
                    [$page['title'], abs_url(lurl('contact'))],
                ])]),
            ],
            'page' => $page,
            'services' => Service::localized($locale),
            'old' => $old,
            'errors' => $errors,
            'formError' => $formError,
            'result' => $result,
        ], 'layouts/main', $status);

        return $response->noCache();
    }
}
