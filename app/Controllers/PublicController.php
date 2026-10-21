<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;

final class PublicController extends Controller
{
    /** Pages administrables listées dans le sitemap : slug de page => chemin localisé. */
    private const SITEMAP_PAGES = [
        'home' => '',
        'about' => 'about',
        'services' => 'services',
        'portfolio' => 'portfolio',
        'contact' => 'contact',
        'legal' => 'legal',
        'privacy' => 'privacy',
    ];

    public function root(Request $request, array $params): Response
    {
        return $this->redirect('/' . Lang::defaultLocale() . '/');
    }

    public function home(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $page = $this->page('home');
        $projects = Project::published($locale, null, 3, true) ?: Project::published($locale, null, 3);

        return $this->view('public/home', [
            'meta' => $this->meta($page, '', 'home', true),
            'page' => $page,
            'services' => Service::localized($locale),
            'projects' => $projects,
        ]);
    }

    public function about(Request $request, array $params): Response
    {
        $page = $this->page('about');
        return $this->view('public/about', ['meta' => $this->meta($page, 'about', 'about'), 'page' => $page]);
    }

    public function services(Request $request, array $params): Response
    {
        $page = $this->page('services');
        return $this->view('public/services', [
            'meta' => $this->meta($page, 'services', 'services'),
            'page' => $page,
            'services' => Service::localized(Lang::locale()),
        ]);
    }

    public function portfolio(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $page = $this->page('portfolio');
        $categories = Category::localized($locale);
        $selected = $request->query('category');
        $validSlugs = array_column($categories, 'slug');
        $selected = in_array($selected, $validSlugs, true) ? $selected : null;

        $projects = Project::published($locale, $selected);
        $usedCategories = array_column(Project::published($locale), 'category_slug');
        $categories = array_values(array_filter(
            $categories,
            static fn(array $c): bool => in_array($c['slug'], $usedCategories, true)
        ));

        return $this->view('public/portfolio', [
            'meta' => $this->meta($page, 'portfolio', 'portfolio'),
            'page' => $page,
            'projects' => $projects,
            'categories' => $categories,
            'selected' => $selected,
        ]);
    }

    public function project(Request $request, array $params): Response
    {
        $project = Project::publishedBySlug((string) $params['slug'], Lang::locale());
        if ($project === null) {
            $this->abort(404);
        }
        $t = $project['t'];
        return $this->view('public/project', [
            'meta' => [
                'title' => $t['title'],
                'description' => truncate($t['summary'] !== '' ? $t['summary'] : (string) $t['description']),
                'path' => 'portfolio/' . $project['slug'],
                'nav' => 'portfolio',
                'image' => $project['main_image'] ? '/uploads/projects/' . $project['main_image'] : null,
                'type' => 'article',
            ],
            'project' => $project,
        ]);
    }

    public function legal(Request $request, array $params): Response
    {
        $page = $this->page('legal');
        return $this->view('public/page', ['meta' => $this->meta($page, 'legal', ''), 'page' => $page]);
    }

    public function privacy(Request $request, array $params): Response
    {
        $page = $this->page('privacy');
        return $this->view('public/page', ['meta' => $this->meta($page, 'privacy', ''), 'page' => $page]);
    }

    public function robots(Request $request, array $params): Response
    {
        $body = "User-agent: *\nDisallow: /admin\n\nSitemap: " . abs_url('/sitemap.xml') . "\n";
        return Response::text($body);
    }

    public function sitemap(Request $request, array $params): Response
    {
        $locales = Lang::supported();
        $default = Lang::defaultLocale();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        $entries = [];
        foreach (self::SITEMAP_PAGES as $slug => $path) {
            if (Page::get($slug, $default) !== null) {
                $entries[] = [$path, null];
            }
        }
        foreach (Project::published($default) as $project) {
            $entries[] = ['portfolio/' . $project['slug'], $project['updated_at']];
        }

        foreach ($entries as [$path, $lastmod]) {
            foreach ($locales as $locale) {
                $xml .= "  <url>\n    <loc>" . $this->xml(abs_url(lurl($path, $locale))) . "</loc>\n";
                if ($lastmod) {
                    $xml .= '    <lastmod>' . $this->xml(date('Y-m-d', (int) strtotime((string) $lastmod))) . "</lastmod>\n";
                }
                foreach ($locales as $alt) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . $this->xml(abs_url(lurl($path, $alt))) . "\"/>\n";
                }
                $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $this->xml(abs_url(lurl($path, $default))) . "\"/>\n";
                $xml .= "  </url>\n";
            }
        }
        return Response::text($xml . '</urlset>' . "\n", 'application/xml; charset=UTF-8');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function page(string $slug): array
    {
        $page = Page::get($slug, Lang::locale());
        if ($page === null) {
            $this->abort(404);
        }
        return $page;
    }

    private function meta(array $page, string $path, string $nav, bool $fullTitle = false): array
    {
        $description = $page['meta_description'] !== '' ? $page['meta_description'] : truncate((string) $page['intro']);
        return [
            'title' => $page['meta_title'] !== '' ? $page['meta_title'] : $page['title'],
            'full_title' => $fullTitle && $page['meta_title'] !== '',
            'description' => $description,
            'path' => $path,
            'nav' => $nav,
        ];
    }
}
