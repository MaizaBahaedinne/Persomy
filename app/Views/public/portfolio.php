<?php
/**
 * @var array       $page
 * @var array       $projects
 * @var array       $categories
 * @var string|null $selected
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

        <?php if ($categories): ?>
            <nav class="filters" aria-label="<?= e(t('portfolio.filter')) ?>">
                <a class="chip" href="<?= e(lurl('portfolio')) ?>"<?= $selected === null ? ' aria-current="true"' : '' ?>><?= e(t('portfolio.all')) ?></a>
                <?php foreach ($categories as $category): ?>
                    <a class="chip" href="<?= e(lurl('portfolio') . '?category=' . rawurlencode($category['slug'])) ?>"<?= $selected === $category['slug'] ? ' aria-current="true"' : '' ?>><?= e($category['t']['name']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (!$projects): ?>
            <div class="empty-state">
                <div class="card-icon"><?= icon('portfolio') ?></div>
                <p><?= e(t('portfolio.empty')) ?></p>
                <a class="btn btn-outline btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
            </div>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($projects as $project): ?>
                    <?php require BASE_PATH . '/app/Views/partials/project_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
