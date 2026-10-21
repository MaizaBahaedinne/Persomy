<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Models\BlogCategory;
use App\Models\BlogPost;
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

        return $this->view('public/home', [
            'meta' => $this->meta($page, '', 'home', true),
            'page' => $page,
            'services' => Service::localized($locale),
            'projects' => $projects,
            'posts' => BlogPost::published($locale, null, 3),
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

        return $this->view('public/blog', [
            'meta' => $this->meta($page, 'blog', 'blog'),
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
            '@context' => 'https://schema.org',
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
        foreach (BlogPost::sitemapEntries() as $post) {
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
        return [
            'title' => $page['meta_title'] !== '' ? $page['meta_title'] : $page['title'],
            'full_title' => $fullTitle && $page['meta_title'] !== '',
            'description' => $description,
            'path' => $path,
            'nav' => $nav,
        ];
    }
}
