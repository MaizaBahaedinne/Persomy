<?php
/**
 * Logo du site (en-tête et pied de page clairs : logo violet #402371 sur fond transparent).
 * Fichier : public/assets/img/logo.webp (ou logo.svg / logo.png). Sans fichier, une marque temporaire est affichée.
 */
$logoFile = null;
foreach (['logo.webp', 'logo.svg', 'logo.png'] as $candidate) {
    if (is_file(BASE_PATH . '/public/assets/img/' . $candidate)) {
        $logoFile = $candidate;
        break;
    }
}
$brandName = (string) setting('site_name', 'Persomy');
?>
<a class="brand" href="<?= e(lurl('')) ?>">
    <?php if ($logoFile): ?>
        <img src="<?= e(asset('img/' . $logoFile)) ?>" alt="<?= e($brandName) ?>" width="164" height="46" decoding="async">
    <?php else: ?>
        <svg class="brand-mark" viewBox="0 0 32 32" width="32" height="32" aria-hidden="true" focusable="false">
            <defs><linearGradient id="bm-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6a45a8"/><stop offset="1" stop-color="#2b1850"/></linearGradient></defs>
            <rect width="32" height="32" rx="9" fill="url(#bm-g)"/>
            <path d="M11.5 23V9h6a4.5 4.5 0 0 1 0 9h-6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="brand-text"><?= e(mb_strtoupper($brandName)) ?></span>
    <?php endif; ?>
</a>
