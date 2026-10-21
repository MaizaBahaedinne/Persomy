<?php
/**
 * @var array $page
 * @var array $services
 */
?>
<section class="page-hero">
    <div class="container">
        <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
        <?php if (trim((string) $page['intro']) !== ''): ?><p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p><?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
        <div class="grid grid-3">
            <?php foreach ($services as $service): ?>
                <?php require BASE_PATH . '/app/Views/partials/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (trim((string) $page['content']) !== ''): ?>
            <div class="prose prose-after"<?= lang_attrs($page) ?>><?= format_content((string) $page['content']) ?></div>
        <?php endif; ?>
    </div>
</section>
<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2><?= e(t('services.cta_title')) ?></h2>
            <p><?= e(t('cta.discuss_text')) ?></p>
        </div>
        <a class="btn btn-light" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
