<?php
/**
 * Champs de traduction pour chaque langue.
 *
 * @var array $fields  liste de [nom, libellé, type (text|textarea), lignes]
 * @var array $tr      [locale => [champ => valeur]]
 */

use App\Core\Config;
use App\Core\Lang;

$names = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
foreach (Lang::supported() as $code):
    $dirAttr = Lang::dir($code);
    ?>
    <fieldset class="locale-box">
        <legend><?= e($names[$code] ?? strtoupper($code)) ?> (<?= e(strtoupper($code)) ?>)</legend>
        <?php foreach ($fields as [$name, $label, $type, $rows]): ?>
            <?php $id = 'tr-' . $code . '-' . $name; $value = (string) ($tr[$code][$name] ?? ''); ?>
            <div class="field">
                <label for="<?= e($id) ?>"><?= e($label) ?></label>
                <?php if ($type === 'textarea'): ?>
                    <textarea id="<?= e($id) ?>" name="tr[<?= e($code) ?>][<?= e($name) ?>]" rows="<?= (int) $rows ?>" lang="<?= e($code) ?>" dir="<?= e($dirAttr) ?>"><?= e($value) ?></textarea>
                <?php else: ?>
                    <input id="<?= e($id) ?>" name="tr[<?= e($code) ?>][<?= e($name) ?>]" type="text" lang="<?= e($code) ?>" dir="<?= e($dirAttr) ?>" value="<?= e($value) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
