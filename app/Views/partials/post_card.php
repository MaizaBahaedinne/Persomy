<?php
/** @var array $post  article localisé (clé 't', 'category_name') */
$t = $post['t'];
$url = lurl('blog/' . $post['slug']);
?>
<article class="card project-card post-card">
    <a class="project-media" href="<?= e($url) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($post['cover_image']): ?>
            <img src="<?= e('/uploads/blog/' . $post['cover_image']) ?>" alt="" loading="lazy" decoding="async" width="640" height="400">
        <?php else: ?>
            <span class="project-placeholder"><?= e(mb_strtoupper(mb_substr($t['title'], 0, 1))) ?></span>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <p class="meta">
            <?php if (!empty($post['category_name'])): ?><span class="tag"><?= e($post['category_name']) ?></span><?php endif; ?>
            <?php if (!empty($t['_fallback'])): ?><span class="tag tag-lang" lang="<?= e($t['_locale']) ?>"><?= e(strtoupper($t['_locale'])) ?></span><?php endif; ?>
        </p>
        <h3 class="card-title"<?= lang_attrs($t) ?>>
            <a href="<?= e($url) ?>"><?= e($t['title']) ?></a>
        </h3>
        <?php if (trim((string) $t['excerpt']) !== ''): ?><p<?= lang_attrs($t) ?>><?= e($t['excerpt']) ?></p><?php endif; ?>
        <p class="post-meta">
            <time datetime="<?= e(date('Y-m-d', (int) strtotime((string) $post['published_at']))) ?>"><?= e(format_day((string) $post['published_at'])) ?></time>
            <span aria-hidden="true">·</span>
            <span><?= e(t('blog.min_read', ['n' => reading_minutes((string) $t['content'])])) ?></span>
        </p>
        <a class="link-arrow" href="<?= e($url) ?>">
            <?= e(t('blog.read_more')) ?><span class="sr-only"> : <?= e($t['title']) ?></span>
        </a>
    </div>
</article>
