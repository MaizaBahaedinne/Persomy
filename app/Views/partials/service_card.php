<?php
/** @var array $service  service localisé (clé 't') */
$t = $service['t'];
?>
<article class="card service-card">
    <div class="card-icon"><?= icon((string) $service['icon']) ?></div>
    <h3 class="card-title"<?= lang_attrs($t) ?>><?= e($t['title']) ?></h3>
    <?php if ($t['summary'] !== ''): ?><p<?= lang_attrs($t) ?>><?= e($t['summary']) ?></p><?php endif; ?>
</article>
