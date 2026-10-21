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
                <span class="pill">Paris</span>
                <span class="pill">Tunis</span>
            </p>
            <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
        </div>
        <?php require BASE_PATH . '/app/Views/partials/hero_visual.php'; ?>
    </div>
</section>

<?php if (!empty($clients)):
    // Chaque groupe doit être plus large que l’écran pour que la boucle soit continue.
    $repeat = max(1, (int) ceil(12 / count($clients)));
    $long = count($clients) * $repeat > 20;
    ?>
<section class="logos" aria-labelledby="clients-title">
    <h2 id="clients-title" class="logos-title"><?= e(t('home.clients_title')) ?></h2>
    <div class="logo-marquee<?= $long ? ' is-long' : '' ?>">
        <?php for ($g = 0; $g < 2; $g++): ?>
            <ul class="logo-group"<?= $g === 1 ? ' aria-hidden="true"' : '' ?>>
                <?php for ($r = 0; $r < $repeat; $r++): ?>
                    <?php foreach ($clients as $client): ?>
                        <?php $dup = $g === 1 || $r > 0; ?>
                        <li class="logo-item<?= $dup ? ' logo-dup' : '' ?>"<?= $dup && $g === 0 ? ' aria-hidden="true"' : '' ?>>
                            <img src="<?= e('/uploads/clients/' . $client['logo']) ?>" alt="<?= $dup ? '' : e($client['name']) ?>" height="48" decoding="async">
                        </li>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </ul>
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>

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

<?php if (!empty($posts)): ?>
<section class="section">
    <div class="container">
        <header class="section-head">
            <h2><?= e(t('blog.latest_title')) ?></h2>
            <p><?= e(t('blog.latest_intro')) ?></p>
        </header>
        <div class="grid grid-3">
            <?php foreach ($posts as $post): ?>
                <?php require BASE_PATH . '/app/Views/partials/post_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <p class="section-more"><a class="btn btn-outline" href="<?= e(lurl('blog')) ?>"><?= e(t('blog.all_posts')) ?></a></p>
    </div>
</section>
<?php endif; ?>

<section class="section">
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
        <a class="btn btn-primary btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
