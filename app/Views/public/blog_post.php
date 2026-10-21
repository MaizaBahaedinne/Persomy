<?php
/**
 * @var array $post     article localisé
 * @var array $related  articles liés
 */
$t = $post['t'];
$author = trim((string) $post['author']);
?>
<section class="page-hero">
    <div class="container">
        <p><a class="link-back" href="<?= e(lurl('blog')) ?>"><?= e(t('blog.back')) ?></a></p>
        <p class="meta">
            <?php if (!empty($post['category_name'])): ?>
                <a class="tag" href="<?= e(lurl('blog') . '?category=' . rawurlencode((string) $post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
            <?php endif; ?>
        </p>
        <h1<?= lang_attrs($t) ?>><?= e($t['title']) ?></h1>
        <?php if (trim((string) $t['excerpt']) !== ''): ?><p class="lead"<?= lang_attrs($t) ?>><?= e($t['excerpt']) ?></p><?php endif; ?>
        <p class="post-meta">
            <time datetime="<?= e(date('Y-m-d', (int) strtotime((string) $post['published_at']))) ?>"><?= e(format_day((string) $post['published_at'])) ?></time>
            <?php if ($author !== ''): ?><span aria-hidden="true">·</span><span><?= e(t('blog.by', ['name' => $author])) ?></span><?php endif; ?>
            <span aria-hidden="true">·</span>
            <span><?= e(t('blog.min_read', ['n' => reading_minutes((string) $t['content'])])) ?></span>
        </p>
    </div>
</section>
<section class="section">
    <div class="container article">
        <?php $row = $t; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>

        <?php if ($post['cover_image']): ?>
            <figure class="project-figure">
                <img src="<?= e('/uploads/blog/' . $post['cover_image']) ?>" alt="<?= e($t['image_alt'] !== '' ? $t['image_alt'] : $t['title']) ?>" width="1200" height="750" decoding="async">
            </figure>
        <?php endif; ?>

        <div class="prose"<?= lang_attrs($t) ?>>
            <?= format_content((string) $t['content'], true) ?>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section section-soft">
    <div class="container">
        <header class="section-head">
            <h2><?= e(t('blog.related')) ?></h2>
        </header>
        <div class="grid grid-3">
            <?php foreach ($related as $post): ?>
                <?php require BASE_PATH . '/app/Views/partials/post_card.php'; ?>
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
