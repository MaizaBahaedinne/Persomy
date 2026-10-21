<?php
/**
 * @var int         $status
 * @var string|null $debug
 */
$key = in_array($status, [403, 404, 405, 419, 429, 500], true) ? 'errors.e' . $status : 'errors.e500';
?>
<section class="page-hero error-page">
    <div class="container">
        <p class="eyebrow"><?= e((string) $status) ?></p>
        <h1><?= e(t($key . '.title')) ?></h1>
        <p class="lead"><?= e(t($key . '.text')) ?></p>
        <p><a class="btn btn-primary" href="<?= e(lurl('')) ?>"><?= e(t('errors.home')) ?></a></p>
        <?php if (!empty($debug)): ?><pre class="debug"><?= e($debug) ?></pre><?php endif; ?>
    </div>
</section>
