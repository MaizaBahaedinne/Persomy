<?php
/**
 * Mise en page minimale pour les pages d'erreur : n'utilise pas la base de données.
 *
 * @var string $content
 * @var array  $meta
 */

use App\Core\Lang;

$loc = Lang::locale();
?>
<!DOCTYPE html>
<html lang="<?= e($loc) ?>" dir="<?= e(Lang::dir()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e((string) ($meta['title'] ?? 'Error')) ?></title>
    <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<main id="main">
    <?= $content ?>
</main>
</body>
</html>
