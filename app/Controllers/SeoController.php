<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Services\Seo;

/** Fichiers destinés aux robots : robots.txt et llms.txt / llms-full.txt. */
final class SeoController extends Controller
{
    public function robots(Request $request, array $params): Response
    {
        $mode = (string) setting('ai_crawlers', 'allow');
        $body = Seo::robotsTxt($mode, abs_url('/sitemap.xml'), abs_url('/llms.txt'));
        return $this->file($body);
    }

    public function llms(Request $request, array $params): Response
    {
        return $this->file(Seo::llmsTxt($this->llmsData(false), false), true);
    }

    public function llmsFull(Request $request, array $params): Response
    {
        return $this->file(Seo::llmsTxt($this->llmsData(true), true), true);
    }

    private function file(string $body, bool $noindex = false): Response
    {
        $headers = ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600'];
        if ($noindex) {
            $headers['X-Robots-Tag'] = 'noindex';
        }
        return new Response($body, 200, $headers);
    }

    /** Contenu dans la langue par défaut ; les contenus incomplets (« à compléter ») et les projets fictifs sont exclus. */
    private function llmsData(bool $full): array
    {
        $default = Lang::defaultLocale();
        Lang::set($default);
        $siteName = (string) setting('site_name', 'Persomy');

        $home = Page::get('home', $default);
        $summary = '';
        foreach ([$home['meta_description'] ?? '', $home['intro'] ?? ''] as $candidate) {
            if (!is_placeholder($candidate)) {
                $summary = (string) $candidate;
                break;
            }
        }
        if ($summary === '') {
            $summary = t('footer.tagline');
        }

        $pageItem = function (string $slug, string $path) use ($default, $full): ?array {
            $page = Page::get($slug, $default);
            if ($page === null || has_placeholder($page['title'])) {
                return null;
            }
            $desc = '';
            foreach ([$page['meta_description'], $page['intro']] as $candidate) {
                if (!is_placeholder($candidate)) {
                    $desc = (string) $candidate;
                    break;
                }
            }
            $body = $full && !has_placeholder($page['content']) ? (string) $page['content'] : '';
            return ['title' => $page['title'], 'url' => abs_url(lurl($path, $default)), 'desc' => $desc, 'body' => $body];
        };

        $services = [];
        foreach (Service::localized($default) as $service) {
            $services[] = [
                'title' => $service['t']['title'],
                'url' => abs_url(lurl('services', $default)),
                'desc' => (string) $service['t']['summary'],
            ];
        }

        $projects = [];
        foreach (Project::published($default) as $project) {
            if ((int) $project['is_demo'] === 1) {
                continue;
            }
            $t = $project['t'];
            $body = '';
            foreach (['description' => 'portfolio.context', 'problem' => 'portfolio.problem', 'solution' => 'portfolio.solution', 'results' => 'portfolio.results'] as $field => $label) {
                if (trim((string) $t[$field]) !== '') {
                    $body .= '## ' . t($label) . "\n\n" . trim((string) $t[$field]) . "\n\n";
                }
            }
            $projects[] = ['title' => $t['title'], 'url' => abs_url(lurl('portfolio/' . $project['slug'], $default)), 'desc' => (string) $t['summary'], 'body' => $full ? $body : ''];
        }

        $posts = [];
        try {
            foreach (BlogPost::published($default, null, 200) as $post) {
                $t = $post['t'];
                $posts[] = ['title' => $t['title'], 'url' => abs_url(lurl('blog/' . $post['slug'], $default)), 'desc' => (string) $t['excerpt'], 'body' => $full ? (string) $t['content'] : ''];
            }
        } catch (\Throwable $e) {
            Logger::error('llms: blog indisponible', ['error' => get_class($e)]);
        }

        $languages = [];
        foreach (Lang::supported() as $code) {
            if ($code !== $default) {
                $languages[] = ['title' => (string) \App\Core\Config::get('locales.' . $code . '.name', $code), 'url' => abs_url(lurl('', $code))];
            }
        }

        $notes = [t('footer.offices') . ' : Paris · Tunis'];
        $email = setting('contact_email');
        if (!is_placeholder($email)) {
            $notes[] = 'Contact : ' . $email;
        }
        $linkedin = setting('linkedin_url');
        if (!is_placeholder($linkedin) && str_starts_with((string) $linkedin, 'https://')) {
            $notes[] = 'LinkedIn : ' . $linkedin;
        }

        return [
            'name' => $siteName,
            'summary' => $summary,
            'notes' => $notes,
            'sections' => [
                'Pages' => array_values(array_filter([$pageItem('about', 'about'), $pageItem('contact', 'contact')])),
                'Services' => $services,
                'Portfolio' => $projects,
                'Blog' => $posts,
                'Languages' => $languages,
                'Optional' => array_values(array_filter([$pageItem('legal', 'legal'), $pageItem('privacy', 'privacy')])),
            ],
        ];
    }
}
