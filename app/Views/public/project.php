<?php
/** @var array $project */
$t = $project['t'];
$demo = (int) $project['is_demo'] === 1;
$sections = [
    'description' => 'portfolio.context',
    'problem' => 'portfolio.problem',
    'solution' => 'portfolio.solution',
    'results' => 'portfolio.results',
];
?>
<section class="page-hero">
    <div class="container">
        <p><a class="link-back" href="<?= e(lurl('portfolio')) ?>"><?= e(t('portfolio.back')) ?></a></p>
        <p class="meta">
            <?php if (!empty($project['category_name'])): ?><span class="tag"><?= e($project['category_name']) ?></span><?php endif; ?>
            <?php if ($demo): ?><span class="tag tag-demo"><?= e(t('common.demo')) ?></span><?php endif; ?>
        </p>
        <h1<?= lang_attrs($t) ?>><?= e($t['title']) ?></h1>
        <?php if ($t['summary'] !== ''): ?><p class="lead"<?= lang_attrs($t) ?>><?= e($t['summary']) ?></p><?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container project-detail">
        <?php $row = $t; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
        <?php if ($demo): ?><p class="notice notice-demo" role="note"><?= e(t('portfolio.demo_notice')) ?></p><?php endif; ?>

        <?php if ($project['main_image']): ?>
            <figure class="project-figure">
                <img src="<?= e('/uploads/projects/' . $project['main_image']) ?>" alt="<?= e($t['image_alt'] !== '' ? $t['image_alt'] : $t['title']) ?>" width="1200" height="750" decoding="async">
            </figure>
        <?php endif; ?>

        <div class="prose"<?= lang_attrs($t) ?>>
            <?php foreach ($sections as $field => $labelKey): ?>
                <?php if (trim((string) $t[$field]) !== ''): ?>
                    <h2><?= e(t($labelKey)) ?></h2>
                    <?= format_content((string) $t[$field]) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2><?= e(t('cta.discuss')) ?></h2>
            <p><?= e(t('cta.discuss_text')) ?></p>
        </div>
        <a class="btn btn-light" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
    </div>
</section>
