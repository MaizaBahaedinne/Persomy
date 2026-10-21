<?php
/** @var array $project  projet localisé (clé 't') */
$t = $project['t'];
$demo = (int) $project['is_demo'] === 1;
?>
<article class="card project-card">
    <a class="project-media" href="<?= e(lurl('portfolio/' . $project['slug'])) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($project['main_image']): ?>
            <img src="<?= e('/uploads/projects/' . $project['main_image']) ?>" alt="" loading="lazy" decoding="async" width="640" height="400">
        <?php else: ?>
            <span class="project-placeholder"><?= e(mb_strtoupper(mb_substr($t['title'], 0, 1))) ?></span>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <p class="meta">
            <?php if (!empty($project['category_name'])): ?><span class="tag"><?= e($project['category_name']) ?></span><?php endif; ?>
            <?php if ($demo): ?><span class="tag tag-demo"><?= e(t('common.demo')) ?></span><?php endif; ?>
            <?php if (!empty($t['_fallback'])): ?><span class="tag tag-lang" lang="<?= e($t['_locale']) ?>"><?= e(strtoupper($t['_locale'])) ?></span><?php endif; ?>
        </p>
        <h3 class="card-title"<?= lang_attrs($t) ?>>
            <a href="<?= e(lurl('portfolio/' . $project['slug'])) ?>"><?= e($t['title']) ?></a>
        </h3>
        <?php if ($t['summary'] !== ''): ?><p<?= lang_attrs($t) ?>><?= e($t['summary']) ?></p><?php endif; ?>
        <a class="link-arrow" href="<?= e(lurl('portfolio/' . $project['slug'])) ?>">
            <?= e(t('common.view_project')) ?><span class="sr-only"> : <?= e($t['title']) ?></span>
        </a>
    </div>
</article>
