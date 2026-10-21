<?php
use App\Core\Response;

$siteName = (string) setting('site_name', 'Persomy');
$email = setting('contact_email');
$phone = setting('contact_phone');
$paris = setting('address_paris');
$tunis = setting('address_tunis');
$linkedin = setting('linkedin_url');
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <p class="footer-brand"><?php require BASE_PATH . '/app/Views/partials/brand.php'; ?></p>
            <p class="footer-text"><?= e(t('footer.tagline')) ?></p>
        </div>
        <div>
            <h2 class="footer-title"><?= e(t('footer.navigate')) ?></h2>
            <ul class="footer-list">
                <li><a href="<?= e(lurl('')) ?>"><?= e(t('nav.home')) ?></a></li>
                <li><a href="<?= e(lurl('about')) ?>"><?= e(t('nav.about')) ?></a></li>
                <li><a href="<?= e(lurl('services')) ?>"><?= e(t('nav.services')) ?></a></li>
                <li><a href="<?= e(lurl('portfolio')) ?>"><?= e(t('nav.portfolio')) ?></a></li>
                <li><a href="<?= e(lurl('contact')) ?>"><?= e(t('nav.contact')) ?></a></li>
            </ul>
        </div>
        <div>
            <h2 class="footer-title"><?= e(t('footer.contact')) ?></h2>
            <ul class="footer-list">
                <?php if (!is_placeholder($email)): ?>
                    <li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
                <?php endif; ?>
                <?php if (!is_placeholder($phone)): ?>
                    <li><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) $phone)) ?>" dir="ltr"><?= e($phone) ?></a></li>
                <?php endif; ?>
                <li><?= e(t('footer.offices')) ?> : Paris · Tunis</li>
                <?php if (!is_placeholder($linkedin) && str_starts_with((string) $linkedin, 'https://')): ?>
                    <li><a href="<?= e($linkedin) ?>" rel="noopener noreferrer" target="_blank">LinkedIn</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <p>© <?= e(date('Y')) ?> <?= e($siteName) ?>. <?= e(t('footer.rights')) ?></p>
        <ul class="footer-legal">
            <li><a href="<?= e(lurl('legal')) ?>"><?= e(t('footer.legal')) ?></a></li>
            <li><a href="<?= e(lurl('privacy')) ?>"><?= e(t('footer.privacy')) ?></a></li>
            <?php if (Response::$allowAnalytics): ?>
                <li><button class="link-button" type="button" data-consent-open hidden><?= e(t('consent.manage')) ?></button></li>
            <?php endif; ?>
        </ul>
    </div>
</footer>
