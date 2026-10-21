<?php
/**
 * @var array $page
 * @var array $services
 * @var array $projects
 */
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-inner">
            <p class="eyebrow">Persomy · Digital Solutions</p>
            <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
            <?php if (trim((string) $page['intro']) !== ''): ?>
                <p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p>
            <?php endif; ?>
            <div class="hero-actions">
                <a class="btn btn-primary btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
                <a class="btn btn-ghost" href="<?= e(lurl('portfolio')) ?>"><?= e(t('cta.projects')) ?></a>
            </div>
            <p class="hero-meta">
                <span class="hero-meta-label"><?= e(t('footer.offices')) ?></span>
                <span class="pill-dark">Paris</span>
                <span class="pill-dark">Tunis</span>
            </p>
            <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
        </div>
        <?php require BASE_PATH . '/app/Views/partials/hero_visual.php'; ?>
    </div>
</section>

<?php if ($services): ?>
<section class="section">
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

<?php if ($projects): ?>
<section class="section section-soft">
    <div class="container">
        <header class="section-head">
            <h2><?= e(t('home.featured_title')) ?></h2>
            <p><?= e(t('home.featured_intro')) ?></p>
        </header>
        <div class="grid grid-3">
            <?php foreach ($projects as $project): ?>
                <?php require BASE_PATH . '/app/Views/partials/project_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <p class="section-more"><a class="btn btn-outline" href="<?= e(lurl('portfolio')) ?>"><?= e(t('cta.all_projects')) ?></a></p>
    </div>
</section>
<?php endif; ?>

<section class="section section-dark">
    <div class="container">
        <header class="section-head">
            <h2><?= e(t('home.method_title')) ?></h2>
            <p><?= e(t('home.method_intro')) ?></p>
        </header>
        <ol class="steps">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <li class="step">
                    <h3><?= e(t('home.step' . $i . '.title')) ?></h3>
                    <p><?= e(t('home.step' . $i . '.text')) ?></p>
                </li>
            <?php endfor; ?>
        </ol>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2><?= e(t('cta.discuss')) ?></h2>
            <p><?= e(t('cta.discuss_text')) ?></p>
        </div>
        <a class="btn btn-light btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
