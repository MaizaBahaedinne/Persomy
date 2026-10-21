<?php
/**
 * Emplacement TEMPORAIRE du logo.
 * Pour installer le logo officiel : déposer public/assets/img/logo.svg (ou logo.png) ; il sera utilisé automatiquement.
 */
$logoFile = null;
foreach (['logo.svg', 'logo.png'] as $candidate) {
    if (is_file(BASE_PATH . '/public/assets/img/' . $candidate)) {
        $logoFile = $candidate;
        break;
    }
}
$brandName = (string) setting('site_name', 'Persomy');
?>
<a class="brand" href="<?= e(lurl('')) ?>">
    <?php if ($logoFile): ?>
        <img src="<?= e(asset('img/' . $logoFile)) ?>" alt="<?= e($brandName) ?>" height="32">
    <?php else: ?>
        <span class="brand-text"><?= e(mb_strtoupper($brandName)) ?></span>
    <?php endif; ?>
</a>
