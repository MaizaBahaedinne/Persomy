<?php
/**
 * @var string $content
 * @var array  $meta
 */

use App\Core\Config;
use App\Core\Lang;

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

$navItems = [
    'home' => '',
    'about' => 'about',
    'services' => 'services',
    'portfolio' => 'portfolio',
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
        <link rel="canonical" href="<?= e(abs_url(lurl($path))) ?>">
        <?php foreach ($locales as $code => $info): ?>
            <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(abs_url(lurl($path, $code))) ?>">
        <?php endforeach; ?>
        <link rel="alternate" hreflang="x-default" href="<?= e(abs_url(lurl($path, Lang::defaultLocale()))) ?>">
        <meta property="og:title" content="<?= e($title) ?>">
        <meta property="og:description" content="<?= e($description) ?>">
        <meta property="og:type" content="<?= e($meta['type'] ?? 'website') ?>">
        <meta property="og:url" content="<?= e(abs_url(lurl($path))) ?>">
        <meta property="og:site_name" content="<?= e($siteName) ?>">
        <meta property="og:locale" content="<?= e($locales[$loc]['og'] ?? 'fr_FR') ?>">
        <?php if ($image): ?><meta property="og:image" content="<?= e($image) ?>"><?php endif; ?>
    <?php endif; ?>
    <meta name="theme-color" content="#050b1f">
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body>
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
            <ul class="lang-switch" aria-label="<?= e(t('common.language')) ?>">
                <?php foreach ($locales as $code => $info): ?>
                    <li>
                        <a href="<?= e(lurl((string) $path, $code)) ?>" lang="<?= e($code) ?>" hreflang="<?= e($code) ?>"
                           <?= $code === $loc ? 'aria-current="true"' : '' ?>><?= e($info['name']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main" tabindex="-1">
    <?= $content ?>
</main>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
</body>
</html>
