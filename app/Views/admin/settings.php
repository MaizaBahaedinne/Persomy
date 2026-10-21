<?php
/**
 * @var array $values
 * @var array $errors
 * @var array $fields
 */

use App\Core\Config;
use App\Core\Lang;
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<p class="help">Les identifiants techniques (base de données, SMTP) ne se règlent pas ici : ils restent dans le fichier <code>.env</code> du serveur.</p>
<form method="post" action="/admin/settings" class="stack">
    <?= csrf_field() ?>
    <fieldset class="box">
        <legend>Réglages</legend>
        <?php foreach ($fields as $key => [$label, $type, $help]): ?>
            <?php $value = (string) ($values[$key] ?? ''); ?>
            <div class="field">
                <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
                <?php if ($type === 'textarea'): ?>
                    <textarea id="s-<?= e($key) ?>" name="<?= e($key) ?>" rows="3" maxlength="500" aria-describedby="h-<?= e($key) ?>"><?= e($value) ?></textarea>
                <?php elseif ($type === 'locale'): ?>
                    <select id="s-<?= e($key) ?>" name="<?= e($key) ?>" aria-describedby="h-<?= e($key) ?>">
                        <?php foreach (Config::get('locales', []) as $code => $info): ?>
                            <option value="<?= e($code) ?>"<?= $value === $code ? ' selected' : '' ?>><?= e($info['name']) ?> (<?= e($code) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input id="s-<?= e($key) ?>" name="<?= e($key) ?>" type="<?= $type === 'number' ? 'number' : 'text' ?>" maxlength="500" value="<?= e($value) ?>" aria-describedby="h-<?= e($key) ?>">
                <?php endif; ?>
                <p class="help" id="h-<?= e($key) ?>"><?= e($help) ?></p>
            </div>
        <?php endforeach; ?>
    </fieldset>
    <p><button class="btn" type="submit">Enregistrer</button></p>
</form>
