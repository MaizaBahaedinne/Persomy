<?php
/**
 * @var array       $page
 * @var array       $posts
 * @var array       $categories
 * @var string|null $selected
 * @var int         $current
 * @var int         $pages
 */
$base = lurl('blog');
$query = static fn(int $n): string => $base . '?' . http_build_query(array_filter(
    ['category' => $selected, 'page' => $n > 1 ? $n : null],
    static fn($v): bool => $v !== null
));
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
            <nav class="filters" aria-label="<?= e(t('blog.filter')) ?>">
                <a class="chip" href="<?= e($base) ?>"<?= $selected === null ? ' aria-current="true"' : '' ?>><?= e(t('blog.all')) ?></a>
                <?php foreach ($categories as $category): ?>
                    <a class="chip" href="<?= e($base . '?category=' . rawurlencode($category['slug'])) ?>"<?= $selected === $category['slug'] ? ' aria-current="true"' : '' ?>><?= e($category['t']['name']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (!$posts): ?>
            <div class="empty-state">
                <div class="card-icon"><?= icon('blog') ?></div>
                <p><?= e(t('blog.empty')) ?></p>
                <a class="btn btn-outline btn-arrow" href="<?= e(lurl('contact')) ?>"><?= e(t('cta.contact')) ?></a>
            </div>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($posts as $post): ?>
                    <?php require BASE_PATH . '/app/Views/partials/post_card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="pagination" aria-label="<?= e(t('blog.pagination')) ?>">
                    <?php if ($current > 1): ?>
                        <a class="btn btn-outline" rel="prev" href="<?= e($query($current - 1)) ?>"><?= e(t('blog.prev')) ?></a>
                    <?php endif; ?>
                    <span class="pagination-info"><?= e(t('blog.page', ['n' => $current, 'total' => $pages])) ?></span>
                    <?php if ($current < $pages): ?>
                        <a class="btn btn-outline" rel="next" href="<?= e($query($current + 1)) ?>"><?= e(t('blog.next')) ?></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
