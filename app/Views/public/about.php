<?php
/**
 * @var array $page
 * @var array $services
 */
$blocks = split_sections((string) $page['content']);
$icons = ['target', 'heart', 'pin', 'shield', 'users', 'star'];
?>
<section class="page-hero">
    <div class="container">
        <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
        <?php if (trim((string) $page['intro']) !== ''): ?><p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p><?php endif; ?>
        <p class="hero-meta">
            <span class="hero-meta-label"><?= e(t('footer.offices')) ?></span>
            <span class="pill">Paris</span>
            <span class="pill">Tunis</span>
        </p>
        <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
    </div>
</section>

<?php if ($blocks['intro'] !== ''): ?>
<section class="section about-intro-section">
    <div class="container">
        <div class="about-intro"<?= lang_attrs($page) ?>>
            <div class="card-icon"><?= icon('ai') ?></div>
            <div class="prose"><?= format_content($blocks['intro'], true) ?></div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($services): ?>
<section class="section section-soft">
    <div class="container">
        <header class="section-head">
            <h2><?= e(t('home.expertise_title')) ?></h2>
            <p><?= e(t('home.expertise_intro')) ?></p>
        </header>
        <div class="grid-fill">
            <?php foreach ($services as $service): ?>
                <?php require BASE_PATH . '/app/Views/partials/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($blocks['sections']): ?>
<section class="section">
    <div class="container">
        <div class="grid grid-3"<?= lang_attrs($page) ?>>
            <?php foreach ($blocks['sections'] as $i => $block): ?>
                <article class="card info-card">
                    <div class="card-icon"><?= icon($icons[$i % count($icons)]) ?></div>
                    <h2 class="card-title"><?= e($block['title']) ?></h2>
                    <div class="info-body"><?= format_content($block['body'], true) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2><?= e(t('cta.discuss')) ?></h2>
            <p><?= e(t('cta.discuss_text')) ?></p>
        </div>
        <a class="btn btn-primary btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
