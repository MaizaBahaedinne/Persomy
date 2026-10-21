<?php
/**
 * @var array|null $category
 * @var array      $values
 * @var array      $tr
 * @var array      $errors
 * @var array      $fields
 */
$action = $category ? '/admin/categories/' . (int) $category['id'] : '/admin/categories';
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<form method="post" action="<?= e($action) ?>" class="stack">
    <?= csrf_field() ?>
    <fieldset class="box">
        <legend>Paramètres</legend>
        <div class="field">
            <label for="slug">Identifiant (utilisé dans l’URL du filtre)</label>
            <input id="slug" name="slug" type="text" maxlength="120" value="<?= e($values['slug'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="sort_order">Ordre d’affichage</label>
            <input id="sort_order" name="sort_order" type="number" min="-9999" max="9999" value="<?= (int) ($values['sort_order'] ?? 0) ?>">
        </div>
    </fieldset>
    <?php require BASE_PATH . '/app/Views/admin/partials/translations.php'; ?>
    <p><button class="btn" type="submit">Enregistrer</button> <a class="btn btn-secondary" href="/admin/categories">Annuler</a></p>
</form>
