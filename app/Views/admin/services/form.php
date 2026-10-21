<?php
/**
 * @var array|null $service
 * @var array      $values
 * @var array      $tr
 * @var array      $errors
 * @var array      $fields
 * @var array      $icons
 */
$action = $service ? '/admin/services/' . (int) $service['id'] : '/admin/services';
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<form method="post" action="<?= e($action) ?>" class="stack">
    <?= csrf_field() ?>
    <fieldset class="box">
        <legend>Paramètres</legend>
        <div class="field">
            <label for="slug">Identifiant</label>
            <input id="slug" name="slug" type="text" maxlength="120" value="<?= e($values['slug'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="icon">Icône</label>
            <select id="icon" name="icon">
                <?php foreach ($icons as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= ($values['icon'] ?? 'code') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="sort_order">Ordre d’affichage</label>
            <input id="sort_order" name="sort_order" type="number" min="-9999" max="9999" value="<?= (int) ($values['sort_order'] ?? 0) ?>">
        </div>
        <div class="field field-check">
            <input id="is_active" name="is_active" type="checkbox" value="1"<?= !empty($values['is_active']) ? ' checked' : '' ?>>
            <label for="is_active">Actif (visible sur le site)</label>
        </div>
    </fieldset>
    <?php require BASE_PATH . '/app/Views/admin/partials/translations.php'; ?>
    <p><button class="btn" type="submit">Enregistrer</button> <a class="btn btn-secondary" href="/admin/services">Annuler</a></p>
</form>
