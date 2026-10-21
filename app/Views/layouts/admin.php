<?php
/**
 * @var string $content
 * @var string $title
 * @var string $active
 */

use App\Core\Auth;
use App\Core\Session;

$user = Auth::user();
$success = Session::pullFlash('success');
$error = Session::pullFlash('error');
$items = [
    'dashboard' => ['/admin', 'Tableau de bord'],
    'projects' => ['/admin/projects', 'Projets'],
    'categories' => ['/admin/categories', 'Catégories'],
    'services' => ['/admin/services', 'Services'],
    'pages' => ['/admin/pages', 'Pages'],
    'messages' => ['/admin/messages', 'Messages'],
    'settings' => ['/admin/settings', 'Réglages'],
    'account' => ['/admin/account', 'Mon compte'],
];
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — Administration Persomy</title>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin">
<a class="skip-link" href="#main">Aller au contenu</a>
<div class="admin-shell">
    <aside class="admin-side">
        <p class="admin-brand">PERSOMY <span>admin</span></p>
        <nav aria-label="Administration">
            <ul>
                <?php foreach ($items as $key => [$href, $label]): ?>
                    <li><a href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <p class="admin-user"><?= e($user['name'] ?? '') ?></p>
        <form method="post" action="/admin/logout">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-secondary" type="submit">Se déconnecter</button>
        </form>
        <p class="admin-view-site"><a href="/" target="_blank" rel="noopener">Voir le site</a></p>
    </aside>
    <main id="main" class="admin-main" tabindex="-1">
        <h1><?= e($title) ?></h1>
        <?php if (is_string($success) && $success !== ''): ?><div class="flash flash-ok" role="status"><?= e($success) ?></div><?php endif; ?>
        <?php if (is_string($error) && $error !== ''): ?><div class="flash flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
