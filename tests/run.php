<?php

declare(strict_types=1);

/**
 * Tests essentiels (logique pure, sans base de données ni serveur web) :
 *
 *   php tests/run.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';
restore_exception_handler();
ini_set('display_errors', '1');

use App\Core\Env;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Models\Page;
use App\Models\Project;
use App\Services\Mailer;
use App\Services\Seo;
use App\Services\SqlSplitter;
use App\Services\Uploader;

$passed = 0;
$failed = [];
$check = static function (string $name, bool $condition) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
    } else {
        $failed[] = $name;
    }
};

// --- Routeur ---
$slugRoute = Router::compile('/{lang:fr|en|ar}/portfolio/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}');
$check('router: route localisée', preg_match($slugRoute, '/fr/portfolio/demo-projet', $m) === 1 && $m['lang'] === 'fr' && $m['slug'] === 'demo-projet');
$check('router: langue inconnue refusée', preg_match($slugRoute, '/de/portfolio/x') !== 1);
$check('router: slug invalide refusé', preg_match($slugRoute, '/en/portfolio/Bad_Slug') !== 1);
$check('router: id numérique', preg_match(Router::compile('/admin/projects/{id:\d+}/edit'), '/admin/projects/12/edit', $m2) === 1 && $m2['id'] === '12');
$check('router: id non numérique refusé', preg_match(Router::compile('/admin/projects/{id:\d+}/edit'), '/admin/projects/abc/edit') !== 1);
$check('router: racine', preg_match(Router::compile('/'), '/') === 1);
$check('router: point littéral échappé', preg_match(Router::compile('/sitemap.xml'), '/sitemapXxml') !== 1);

// --- Requête ---
$check('request: normalisation', Request::normalizePath('/fr//about/?x=1') === '/fr/about');
$check('request: racine', Request::normalizePath('/') === '/');
$check('request: octet nul rejeté', Request::normalizePath('/fr/%00') === '/__invalid__');
$check('request: UTF-8 invalide rejeté', Request::normalizePath('/fr/%FF') === '/__invalid__');

// --- Réponse : pas de redirection ouverte ---
$check('redirect: chemin interne', Response::redirect('/fr/')->headers['Location'] === '/fr/');
$check('redirect: URL externe neutralisée', Response::redirect('https://evil.example')->headers['Location'] === '/');
$check('redirect: // neutralisé', Response::redirect('//evil.example')->headers['Location'] === '/');
$check('redirect: injection d’en-tête neutralisée', Response::redirect("/a\r\nSet-Cookie: x=1")->headers['Location'] === '/');

// --- Contenu : échappement ---
$html = format_content("## Titre <script>alert(1)</script>\n\nTexte & \"quotes\"\n- un\n- <b>deux</b>");
$check('contenu: script échappé', !str_contains($html, '<script>') && str_contains($html, '&lt;script&gt;'));
$check('contenu: titre h2', str_contains($html, '<h2>'));
$check('contenu: liste', str_contains($html, '<ul><li>un</li><li>&lt;b&gt;deux&lt;/b&gt;</li></ul>'));
$check('e(): guillemets', e('"a" & \'b\'') === '&quot;a&quot; &amp; &#039;b&#039;');

// --- Helpers ---
$check('slugify: accents', slugify('Plateforme de Gestion – Été') === 'plateforme-de-gestion-ete');
$check('slug valide', is_valid_slug('demo-1') && !is_valid_slug('Demo') && !is_valid_slug('a--b') && !is_valid_slug('-a'));
$check('placeholder FR', is_placeholder('[À COMPLÉTER : e-mail]') && is_placeholder('') && !is_placeholder('contact@exemple.com'));
$check('placeholder EN/AR', has_placeholder('[TO BE COMPLETED: x]') && has_placeholder('[يُستكمل لاحقًا: x]'));
$check('truncate', mb_strlen(truncate(str_repeat('é', 400), 160)) === 160);

// --- Env ---
$tmp = tempnam(sys_get_temp_dir(), 'env');
file_put_contents($tmp, "# commentaire\nTEST_A=valeur\nTEST_B=\"avec # dièse\"\nTEST_C=1 # commentaire\nTEST_D=\n");
Env::load($tmp);
unlink($tmp);
$check('env: simple', Env::get('TEST_A') === 'valeur');
$check('env: guillemets', Env::get('TEST_B') === 'avec # dièse');
$check('env: commentaire en ligne', Env::get('TEST_C') === '1' && Env::bool('TEST_C'));
$check('env: valeur vide', Env::get('TEST_D') === '' && Env::bool('TEST_D', true) === true);

// --- Langues : parité des clés FR / EN / AR ---
$fr = Lang::flatten(require BASE_PATH . '/app/Languages/fr.php');
$en = Lang::flatten(require BASE_PATH . '/app/Languages/en.php');
$ar = Lang::flatten(require BASE_PATH . '/app/Languages/ar.php');
$check('langues: EN complet', array_diff_key($fr, $en) === [] && array_diff_key($en, $fr) === []);
$check('langues: AR complet', array_diff_key($fr, $ar) === [] && array_diff_key($ar, $fr) === []);
$check('langues: aucune valeur vide', !in_array('', $fr, true) && !in_array('', $en, true) && !in_array('', $ar, true));
$check('langues: arabe en RTL', Lang::dir('ar') === 'rtl' && Lang::dir('fr') === 'ltr');
$check('langues: clé manquante => clé', Lang::t('inexistante.cle') === 'inexistante.cle');
Lang::set('en');
$check('langues: traduction EN', Lang::t('nav.home') === 'Home');

// --- Traductions administrables ---
[$clean, $error] = Project::cleanTranslations(['fr' => ['title' => 'Titre'], 'en' => ['summary' => 'sans titre']]);
$check('traductions: champ principal requis', $error !== null);
[$clean, $error] = Project::cleanTranslations(['fr' => ['title' => ' Titre ', 'summary' => 'R'], 'ar' => []]);
$check('traductions: valide + trim', $error === null && $clean['fr']['title'] === 'Titre');
[, $error] = Page::cleanTranslations([]);
$check('traductions: au moins une langue', $error !== null);
[, $error] = Project::cleanTranslations(['fr' => ['title' => str_repeat('a', 300)]], ['title' => 200]);
$check('traductions: longueur maximale', $error !== null);

// --- SQL ---
$statements = SqlSplitter::split("-- c\nCREATE TABLE a (x INT);\nINSERT INTO a VALUES ('a;b'), ('it''s; ok');\n# note\nSELECT 1;");
$check('sql: découpage', count($statements) === 3 && str_contains($statements[1], "'a;b'") && str_contains($statements[1], "'it''s; ok'"));
$schema = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/migrations/001_create_schema.sql'));
$check('sql: schéma (13 tables)', count($schema) === 13);
$seed = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/seeders/001_base_content.sql'));
$check('sql: seeders découpés', count($seed) >= 10);
$demo = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/demo/001_demo_projects.sql'));
$check('sql: démo découpée', count($demo) === 2);

// --- E-mail : pas d'injection d'en-têtes ---
$message = Mailer::buildMessage('from@example.com', 'Persomy', 'to@example.com', "Sujet\r\nBcc: evil@example.com", 'Corps', 'a@b.com', "Nom\r\nBcc: x@y.z", 'example.com');
$headerPart = explode("\r\n\r\n", $message, 2)[0];
$check('mail: aucun en-tête Bcc injecté', !preg_match('/^Bcc:/mi', $headerPart));
$check('mail: Reply-To présent', str_contains($headerPart, 'Reply-To:'));
$check('mail: corps base64', base64_decode(str_replace("\r\n", '', explode("\r\n\r\n", $message, 2)[1])) === 'Corps');
$check('mail: clean()', Mailer::clean("a\r\nb\0c") === 'a b c');

// --- Téléversements ---
$check('upload: nom valide', Uploader::isValidName(str_repeat('a', 32) . '.jpg'));
$check('upload: traversée refusée', !Uploader::isValidName('../../.env') && !Uploader::isValidName(str_repeat('a', 32) . '.php'));

// --- Blog ---
$inline = format_content("Un **gras** et [lien](https://exemple.com/a?b=1) et [page](/fr/contact)\n\n[mal](javascript:alert(1)) [x](//evil.example) <b>ok</b>", true);
$check('blog: gras', str_contains($inline, '<strong>gras</strong>'));
$check('blog: lien externe sécurisé', str_contains($inline, '<a href="https://exemple.com/a?b=1" rel="noopener noreferrer" target="_blank">lien</a>'));
$check('blog: lien interne', str_contains($inline, '<a href="/fr/contact">page</a>'));
$check('blog: javascript: et // refusés', !str_contains($inline, 'href="javascript') && !str_contains($inline, 'href="//') && !str_contains($inline, '<b>'));
$check('blog: format simple inchangé', !str_contains(format_content('**x** [a](https://b.c)'), '<strong>'));
$check('blog: temps de lecture', reading_minutes('') === 1 && reading_minutes(str_repeat('mot ', 401)) === 3);
$check('blog: date vide', format_day(null) === '' && format_day('2026-03-05 10:00:00', 'en') !== '');
$check('blog: dossier d’upload inconnu refusé', (static function (): bool {
    try {
        Uploader::delete(str_repeat('a', 32) . '.jpg', '../etc');
        return false;
    } catch (\InvalidArgumentException) {
        return true;
    }
})());
$blogRoute = Router::compile('/{lang:fr|en|ar}/blog/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}');
$check('blog: route article (slug valide uniquement)', preg_match($blogRoute, '/fr/blog/mon-article') === 1 && preg_match($blogRoute, '/fr/blog/rss.xml') !== 1);
$blogSql = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/migrations/002_create_blog.sql'));
$check('blog: migration (4 tables)', count($blogSql) === 4);
$logoSql = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/migrations/003_project_client_logo.sql'));
$check('logos clients: migration (1 instruction, 2 colonnes)', count($logoSql) === 1 && str_contains($logoSql[0], 'client_name') && str_contains($logoSql[0], 'client_logo'));
$check('logos clients: dossier d’upload autorisé', (static function (): bool {
    try {
        Uploader::delete(str_repeat('a', 32) . '.png', 'clients');
        return true;
    } catch (\InvalidArgumentException) {
        return false;
    }
})());

$split = split_sections("Intro\n\n## A\n\nTexte A\n\n## B\n\n- x\n- y");
$check('à propos: découpage en blocs', $split['intro'] === 'Intro' && count($split['sections']) === 2 && $split['sections'][0] === ['title' => 'A', 'body' => 'Texte A'] && str_contains($split['sections'][1]['body'], '- y'));
$check('à propos: sans titre => aucune section', split_sections('Seulement un texte')['sections'] === []);
$pb = parse_blocks("Intro\nsuite\n\n- **Titre** — Description **gras**\n- Simple\n\n*Une note*\n\n**Ligne forte**");
$check('à propos: puces titre/description', $pb['items'] === [['Titre', 'Description **gras**'], ['', 'Simple']]);
$check('à propos: paragraphes, notes et ligne en gras', $pb['paragraphs'] === ["Intro\nsuite", '**Ligne forte**'] && $pb['notes'] === ['Une note']);
$aboutSql = SqlSplitter::split((string) file_get_contents(BASE_PATH . '/database/seeders/003_about_page.sql'));
$check('à propos: seeder (3 langues, sans marqueur à compléter)', count($aboutSql) === 3 && !has_placeholder(implode(' ', $aboutSql)));
foreach ($aboutSql as $i => $statement) {
    $about = split_sections((string) preg_replace("/^.*?content = '(.*)'\nWHERE.*$/su", '$1', $statement));
    $check('à propos: seeder #' . ($i + 1) . ' (5 sections, 2 listes de 5)', count($about['sections']) === 5
        && count(parse_blocks($about['sections'][1]['body'])['items']) === 5 && count(parse_blocks($about['sections'][2]['body'])['items']) === 5);
}

// --- SEO / GEO ---
$robotsAllow = Seo::robotsTxt('allow', 'https://x.test/sitemap.xml', 'https://x.test/llms.txt');
$robotsSearch = Seo::robotsTxt('search_only', 'https://x.test/sitemap.xml', 'https://x.test/llms.txt');
$robotsBlock = Seo::robotsTxt('block', 'https://x.test/sitemap.xml', 'https://x.test/llms.txt');
$check('robots: sitemap + /admin interdit', str_contains($robotsAllow, "Sitemap: https://x.test/sitemap.xml\n") && str_contains($robotsAllow, "User-agent: *\nDisallow: /admin\n"));
$check('robots: mode allow autorise GPTBot et OAI-SearchBot', str_contains($robotsAllow, "User-agent: OAI-SearchBot\n") && str_contains($robotsAllow, "User-agent: Bytespider\nAllow: /\n"));
$check('robots: search_only bloque l’entraînement', str_contains($robotsSearch, "User-agent: Bytespider\nDisallow: /\n") && str_contains($robotsSearch, "User-agent: Claude-User\nUser-agent: PerplexityBot\nUser-agent: Perplexity-User\nAllow: /\n"));
$check('robots: block interdit aussi les assistants', str_contains($robotsBlock, "User-agent: Perplexity-User\nDisallow: /\n"));
$check('robots: mode inconnu => allow', Seo::robotsTxt('??', 'a', 'b') === Seo::robotsTxt('allow', 'a', 'b'));
$check('robots: /admin protégé dans les groupes IA', substr_count($robotsAllow, 'Disallow: /admin') === 3);

$llms = Seo::llmsTxt([
    'name' => 'Persomy', 'summary' => 'Résumé [x]  sur   deux', 'notes' => ['Bureaux : Paris'],
    'sections' => ['Pages' => [['title' => 'À [propos]', 'url' => 'https://x.test/fr/about', 'desc' => 'Desc', 'body' => "## Titre\n\nTexte"]], 'Vide' => []],
]);
$check('llms: structure H1 + citation + liens', str_starts_with($llms, "# Persomy\n\n> Résumé x sur deux\n\n") && str_contains($llms, '- [À propos](https://x.test/fr/about): Desc') && !str_contains($llms, '## Vide'));
$llmsFull = Seo::llmsTxt([
    'name' => 'Persomy', 'summary' => '', 'notes' => [],
    'sections' => ['Pages' => [['title' => 'A', 'url' => 'https://x.test/a', 'desc' => '', 'body' => "## Titre\n\n### Sous\n\nTexte"]]],
], true);
$check('llms-full: contenu avec titres décalés', str_contains($llmsFull, "### A\n\nSource: https://x.test/a\n\n#### Titre\n\n##### Sous\n\nTexte"));
$crumbs = Seo::breadcrumbs([['Accueil', 'https://x.test/fr/'], ['Blog', 'https://x.test/fr/blog']]);
$check('jsonld: fil d’Ariane (positions)', $crumbs['itemListElement'][1]['position'] === 2 && Seo::graph([$crumbs])['@context'] === 'https://schema.org');
$nodes = Seo::siteNodes(['name' => 'Persomy', 'url' => 'https://x.test/', 'logo' => 'https://x.test/l.png', 'description' => '', 'locales' => ['fr'], 'email' => null, 'phone' => null, 'linkedin' => null, 'services' => [], 'offices' => [['Paris', 'FR', null]]]);
$check('jsonld: organisation sans champ vide', !isset($nodes[0]['email'], $nodes[0]['sameAs'], $nodes[0]['description']) && $nodes[0]['location'][0]['address']['addressCountry'] === 'FR' && $nodes[1]['publisher']['@id'] === 'https://x.test/#organization');
$encoded = json_encode(Seo::graph([['name' => '</script><script>alert(1)</script>']]), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$check('jsonld: balise script impossible à fermer', !str_contains((string) $encoded, '</script>') && !str_contains((string) $encoded, '<'));
$manifest = json_decode((string) file_get_contents(BASE_PATH . '/public/site.webmanifest'), true);
$check('manifest: valide + icônes existantes', is_array($manifest) && is_file(BASE_PATH . '/public' . $manifest['icons'][1]['src']));

// --- Gabarits : aucun style/script en ligne (CSP stricte) ---
$offenders = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/app/Views'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = (string) file_get_contents($file->getPathname());
        if (preg_match('/<script(?![^>]*\bsrc=)(?![^>]*\btype="application\/ld\+json")[^>]*>|\sstyle="|\sonclick=|\sonsubmit=|<style\b/i', $content)) {
            $offenders[] = str_replace(BASE_PATH . '/', '', $file->getPathname());
        }
    }
}
$check('csp: pas de script/style en ligne (' . implode(', ', $offenders) . ')', $offenders === []);

// --- Fichiers sensibles ---
$gitignore = (string) file_get_contents(BASE_PATH . '/.gitignore');
$check('git: .env ignoré', str_contains($gitignore, "\n.env\n") || str_starts_with($gitignore, ".env\n") || str_contains($gitignore, "\n.env\n"));
$exampleEnv = (string) file_get_contents(BASE_PATH . '/.env.example');
$check('env.example: secrets vides', (bool) preg_match('/^SMTP_PASSWORD=$/m', $exampleEnv) && (bool) preg_match('/^DB_PASSWORD=$/m', $exampleEnv) && (bool) preg_match('/^APP_KEY=$/m', $exampleEnv));

echo "\n$passed test(s) réussi(s)";
if ($failed) {
    echo ', ' . count($failed) . " échec(s) :\n";
    foreach ($failed as $name) {
        echo "  - $name\n";
    }
    exit(1);
}
echo ".\n";
