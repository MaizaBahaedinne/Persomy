<?php
/** @var array $errors */
if (!empty($errors)): ?>
    <div class="flash flash-err" role="alert" tabindex="-1" data-autofocus>
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
