<?php
/** @var array $page */
?>
<section class="page-hero">
    <div class="container">
        <h1<?= lang_attrs($page) ?>><?= e($page['title']) ?></h1>
        <?php if (trim((string) $page['intro']) !== ''): ?><p class="lead"<?= lang_attrs($page) ?>><?= e($page['intro']) ?></p><?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container prose"<?= lang_attrs($page) ?>>
        <?php $row = $page; require BASE_PATH . '/app/Views/partials/fallback.php'; ?>
        <?= format_content((string) $page['content']) ?>
    </div>
</section>
