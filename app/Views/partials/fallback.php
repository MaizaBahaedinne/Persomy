<?php
/** @var array $row  ligne traduite (page/projet) */
if (!empty($row['_fallback'])): ?>
    <p class="notice" role="note"><?= e(t('common.fallback')) ?></p>
<?php endif; ?>
