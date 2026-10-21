<?php
/**
 * @var string $content
 * @var array  $meta
 */

use App\Core\Config;
use App\Core\Lang;
use App\Core\Response;

$loc = Lang::locale();
$dir = Lang::dir();
$siteName = (string) setting('site_name', 'Persomy');
$path = $meta['path'] ?? null;
$title = (string) ($meta['title'] ?? $siteName);
$fullTitle = !empty($meta['full_title']) ? $title : $title . ' — ' . $siteName;
$description = (string) ($meta['description'] ?? '');
$nav = (string) ($meta['nav'] ?? '');
$locales = Config::get('locales', []);
$image = !empty($meta['image']) ? abs_url($meta['image']) : null;
$query = !empty($meta['query']) ? '?' . $meta['query'] : '';
$gaId = (string) setting('analytics_id', '');
$gaId = preg_match('/^G-[A-Z0-9]{6,14}$/', $gaId) ? $gaId : '';
Response::$allowAnalytics = $gaId !== '';
$consentMode = (string) setting('consent_mode', 'basic') === 'advanced' ? 'advanced' : 'basic';

$navItems = [
    'home' => '',
    'about' => 'about',
    'services' => 'services',
    'portfolio' => 'portfolio',
    'blog' => 'blog',
    'contact' => 'contact',
];
?>
<!DOCTYPE html>
<html lang="<?= e($loc) ?>" dir="<?= e($dir) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <?php if ($description !== ''): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>

    <?php if ($path === null): ?><meta name="robots" content="noindex"><?php endif; ?>

    <?php if ($path !== null): ?>
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="<?= e(abs_url(lurl($path)) . $query) ?>">
        <?php foreach ($locales as $code => $info): ?>
            <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(abs_url(lurl($path, $code)) . $query) ?>">
        <?php endforeach; ?>
        <link rel="alternate" hreflang="x-default" href="<?= e(abs_url(lurl($path, Lang::defaultLocale())) . $query) ?>">
        <meta property="og:title" content="<?= e($title) ?>">
        <meta property="og:description" content="<?= e($description) ?>">
        <meta property="og:type" content="<?= e($meta['type'] ?? 'website') ?>">
        <meta property="og:url" content="<?= e(abs_url(lurl($path)) . $query) ?>">
        <meta property="og:site_name" content="<?= e($siteName) ?>">
        <meta property="og:locale" content="<?= e($locales[$loc]['og'] ?? 'fr_FR') ?>">
        <?php foreach ($locales as $code => $info): ?>
            <?php if ($code !== $loc): ?><meta property="og:locale:alternate" content="<?= e($info['og'] ?? '') ?>"><?php endif; ?>
        <?php endforeach; ?>
        <meta property="og:image" content="<?= e($image ?? abs_url('/assets/img/icon-512.png')) ?>">
        <meta name="twitter:card" content="<?= $image ? 'summary_large_image' : 'summary' ?>">
        <meta name="twitter:title" content="<?= e($title) ?>">
        <meta name="twitter:description" content="<?= e($description) ?>">
        <meta name="twitter:image" content="<?= e($image ?? abs_url('/assets/img/icon-512.png')) ?>">
        <?php if (!empty($meta['published_time'])): ?><meta property="article:published_time" content="<?= e(date('c', (int) strtotime((string) $meta['published_time']))) ?>"><?php endif; ?>
    <?php endif; ?>
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="alternate" type="application/rss+xml" title="<?= e($siteName . ' — ' . t('nav.blog')) ?>" href="<?= e(lurl('blog/rss.xml')) ?>">
    <?php if (!empty($meta['jsonld']) && is_array($meta['jsonld'])): ?>
        <script type="application/ld+json"><?= json_encode($meta['jsonld'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?= e(asset('img/icon-192.png')) ?>" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <?php if ($gaId !== ''): ?><script src="<?= e(asset('js/consent.js')) ?>" defer></script><?php endif; ?>
</head>
<body<?= $gaId !== '' ? ' data-ga-id="' . e($gaId) . '" data-consent-mode="' . e($consentMode) . '"' : '' ?>>
<a class="skip-link" href="#main"><?= e(t('common.skip')) ?></a>

<header class="site-header" data-header>
    <div class="container header-inner">
        <?php require BASE_PATH . '/app/Views/partials/brand.php'; ?>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="nav-toggle-bars" aria-hidden="true"></span>
            <span class="sr-only"><?= e(t('common.menu')) ?></span>
        </button>

        <nav id="site-nav" class="site-nav" aria-label="<?= e(t('common.main_nav')) ?>">
            <ul>
                <?php foreach ($navItems as $key => $itemPath): ?>
                    <li>
                        <a href="<?= e(lurl($itemPath)) ?>"<?= $nav === $key ? ' aria-current="page"' : '' ?>><?= e(t('nav.' . $key)) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <details class="lang-menu" data-lang-menu>
                <summary aria-label="<?= e(t('common.language') . ' : ' . ($locales[$loc]['name'] ?? $loc)) ?>">
                    <span class="lang-code" aria-hidden="true"><?= e(strtoupper($loc)) ?></span>
                </summary>
                <ul aria-label="<?= e(t('common.language')) ?>">
                    <?php foreach ($locales as $code => $info): ?>
                        <li>
                            <a href="<?= e(lurl((string) $path, $code)) ?>" lang="<?= e($code) ?>" hreflang="<?= e($code) ?>"
                               <?= $code === $loc ? 'aria-current="true"' : '' ?>><?= e($info['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>
        </nav>
    </div>
</header>

<main id="main" tabindex="-1">
    <?= $content ?>
</main>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
<?php if ($gaId !== ''): require BASE_PATH . '/app/Views/partials/consent.php'; endif; ?>
</body>
</html>
