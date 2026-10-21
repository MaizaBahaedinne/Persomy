<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Services\Seo;

final class PublicController extends Controller
{
    /** Pages administrables listées dans le sitemap : slug de page => chemin localisé. */
    private const SITEMAP_PAGES = [
        'home' => '',
        'about' => 'about',
        'services' => 'services',
        'portfolio' => 'portfolio',
        'blog' => 'blog',
        'contact' => 'contact',
        'legal' => 'legal',
        'privacy' => 'privacy',
    ];

    /** Nombre d'articles par page du blog. */
    private const POSTS_PER_PAGE = 9;

    public function root(Request $request, array $params): Response
    {
        return $this->redirect('/' . Lang::defaultLocale() . '/');
    }

    public function home(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $page = $this->page('home');
        $projects = Project::published($locale, null, 3, true) ?: Project::published($locale, null, 3);
        $services = Service::localized($locale);

        $meta = $this->meta($page, '', 'home', true);
        $meta['jsonld'] = Seo::graph($this->siteNodes((string) $meta['description'], $services));

        return $this->view('public/home', [
            'meta' => $meta,
            'page' => $page,
            'services' => $services,
            'projects' => $projects,
            'clients' => $this->safely(static fn(): array => Project::clientLogos()),
            'posts' => $this->safely(static fn(): array => BlogPost::published($locale, null, 3)),
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
        $services = Service::localized(Lang::locale());
        $meta = $this->meta($page, 'services', 'services');
        if ($services) {
            $meta['jsonld']['@graph'][] = Seo::serviceList(
                array_map(static fn(array $s): array => [$s['t']['title'], (string) $s['t']['summary']], $services),
                (string) setting('site_name', 'Persomy')
            );
        }
        return $this->view('public/services', [
            'meta' => $meta,
            'page' => $page,
            'services' => $services,
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
                'jsonld' => Seo::graph([Seo::breadcrumbs([
                    [t('nav.home'), abs_url(lurl(''))],
                    [t('nav.portfolio'), abs_url(lurl('portfolio'))],
                    [$t['title'], abs_url(lurl('portfolio/' . $project['slug']))],
                ])]),
            ],
            'project' => $project,
        ]);
    }

    public function blog(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $page = Page::get('blog', $locale) ?? [
            'title' => t('blog.title'),
            'intro' => t('blog.intro'),
            'meta_title' => '',
            'meta_description' => '',
            'content' => '',
        ];

        $categories = BlogCategory::withPublishedPosts($locale);
        $selected = $request->query('category');
        $selected = in_array($selected, array_column($categories, 'slug'), true) ? $selected : null;

        $total = BlogPost::countPublished($selected);
        $pages = max(1, (int) ceil($total / self::POSTS_PER_PAGE));
        $current = min($pages, max(1, (int) $request->query('page')));
        $posts = BlogPost::published($locale, $selected, self::POSTS_PER_PAGE, ($current - 1) * self::POSTS_PER_PAGE);

        // Canonical propre à chaque page filtrée/paginée ; titre distinct dès la page 2.
        $meta = $this->meta($page, 'blog', 'blog');
        $meta['query'] = http_build_query(array_filter(
            ['category' => $selected, 'page' => $current > 1 ? $current : null],
            static fn($v): bool => $v !== null
        ));
        if ($current > 1) {
            $meta['title'] .= ' — ' . t('blog.page', ['n' => $current, 'total' => $pages]);
        }

        return $this->view('public/blog', [
            'meta' => $meta,
            'page' => $page,
            'posts' => $posts,
            'categories' => $categories,
            'selected' => $selected,
            'current' => $current,
            'pages' => $pages,
        ]);
    }

    public function blogPost(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $post = BlogPost::publishedBySlug((string) $params['slug'], $locale);
        if ($post === null) {
            $this->abort(404);
        }
        $t = $post['t'];
        $path = 'blog/' . $post['slug'];
        $description = $t['meta_description'] !== ''
            ? $t['meta_description']
            : truncate($t['excerpt'] !== '' ? $t['excerpt'] : (string) preg_replace('/[#*\[\]()\-]+/u', ' ', (string) $t['content']));
        $image = $post['cover_image'] ? '/uploads/blog/' . $post['cover_image'] : null;
        $siteName = (string) setting('site_name', 'Persomy');
        $author = trim((string) $post['author']);

        $jsonld = [
            '@type' => 'BlogPosting',
            'headline' => $t['title'],
            'description' => $description,
            'inLanguage' => $t['_locale'] ?? $locale,
            'datePublished' => date('c', (int) strtotime((string) $post['published_at'])),
            'dateModified' => date('c', (int) strtotime((string) ($post['updated_at'] ?? $post['published_at']))),
            'mainEntityOfPage' => abs_url(lurl($path)),
            'author' => $author !== '' ? ['@type' => 'Person', 'name' => $author] : ['@type' => 'Organization', 'name' => $siteName],
            'publisher' => ['@type' => 'Organization', 'name' => $siteName],
        ];
        if ($image !== null) {
            $jsonld['image'] = abs_url($image);
        }
        $jsonld = Seo::graph([$jsonld, Seo::breadcrumbs([
            [t('nav.home'), abs_url(lurl(''))],
            [t('nav.blog'), abs_url(lurl('blog'))],
            [$t['title'], abs_url(lurl($path))],
        ])]);

        return $this->view('public/blog_post', [
            'meta' => [
                'title' => $t['meta_title'] !== '' ? $t['meta_title'] : $t['title'],
                'description' => $description,
                'path' => $path,
                'nav' => 'blog',
                'image' => $image,
                'type' => 'article',
                'published_time' => $post['published_at'],
                'jsonld' => $jsonld,
            ],
            'post' => $post,
            'related' => BlogPost::related($post, $locale, 3),
        ]);
    }

    public function blogFeed(Request $request, array $params): Response
    {
        $locale = Lang::locale();
        $siteName = (string) setting('site_name', 'Persomy');
        $selfUrl = abs_url(lurl('blog/rss.xml'));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n<channel>\n"
            . '  <title>' . $this->xml($siteName . ' — ' . t('blog.title')) . "</title>\n"
            . '  <link>' . $this->xml(abs_url(lurl('blog'))) . "</link>\n"
            . '  <description>' . $this->xml(t('blog.intro')) . "</description>\n"
            . '  <language>' . $this->xml($locale) . "</language>\n"
            . '  <atom:link href="' . $this->xml($selfUrl) . '" rel="self" type="application/rss+xml"/>' . "\n";

        foreach (BlogPost::published($locale, null, 20) as $post) {
            $t = $post['t'];
            $url = $this->xml(abs_url(lurl('blog/' . $post['slug'])));
            $xml .= "  <item>\n"
                . '    <title>' . $this->xml($t['title']) . "</title>\n"
                . '    <link>' . $url . "</link>\n"
                . '    <guid isPermaLink="true">' . $url . "</guid>\n"
                . '    <pubDate>' . date(DATE_RSS, (int) strtotime((string) $post['published_at'])) . "</pubDate>\n"
                . '    <description>' . $this->xml($t['excerpt'] !== '' ? $t['excerpt'] : truncate((string) $t['content'], 300)) . "</description>\n"
                . "  </item>\n";
        }

        return Response::text($xml . "</channel>\n</rss>\n", 'application/rss+xml; charset=UTF-8');
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
        foreach ($this->safely(static fn(): array => BlogPost::sitemapEntries()) as $post) {
            $entries[] = ['blog/' . $post['slug'], $post['updated_at']];
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
        $meta = [
            'title' => $page['meta_title'] !== '' ? $page['meta_title'] : $page['title'],
            'full_title' => $fullTitle && $page['meta_title'] !== '',
            'description' => $description,
            'path' => $path,
            'nav' => $nav,
        ];
        if ($path !== '') {
            $meta['jsonld'] = Seo::graph([Seo::breadcrumbs([
                [t('nav.home'), abs_url(lurl(''))],
                [$page['title'], abs_url(lurl($path))],
            ])]);
        }
        return $meta;
    }

    /** Organisation + site web (données structurées de la page d'accueil). */
    private function siteNodes(string $description, array $services): array
    {
        $clean = static fn(?string $v): ?string => is_placeholder($v) ? null : trim((string) $v);
        $linkedin = $clean(setting('linkedin_url'));
        return Seo::siteNodes([
            'name' => (string) setting('site_name', 'Persomy'),
            'url' => abs_url('/'),
            'logo' => abs_url('/assets/img/icon-512.png'),
            'description' => has_placeholder($description) ? '' : $description,
            'locales' => Lang::supported(),
            'email' => $clean(setting('contact_email')),
            'phone' => $clean(setting('contact_phone')),
            'linkedin' => $linkedin !== null && str_starts_with($linkedin, 'https://') ? $linkedin : null,
            'services' => array_map(static fn(array $s): string => $s['t']['title'], $services),
            'offices' => [
                ['Paris', 'FR', $clean(setting('address_paris'))],
                ['Tunis', 'TN', $clean(setting('address_tunis'))],
            ],
        ]);
    }

    /** Exécute une lecture non essentielle (ex. blog avant migration) sans casser la page. */
    private function safely(callable $read): array
    {
        try {
            return $read();
        } catch (\Throwable $e) {
            Logger::error('Lecture optionnelle indisponible', ['error' => get_class($e)]);
            return [];
        }
    }
}
