<?php
/**
 * @var array|null $project
 * @var array      $values
 * @var array      $tr
 * @var array      $errors
 * @var array      $categories
 * @var array      $fields
 */
$action = $project ? '/admin/projects/' . (int) $project['id'] : '/admin/projects';
$image = $values['main_image'] ?? null;
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="stack">
    <?= csrf_field() ?>
    <fieldset class="box">
        <legend>Paramètres</legend>
        <div class="field">
            <label for="slug">Identifiant d’URL</label>
            <input id="slug" name="slug" type="text" maxlength="120" value="<?= e($values['slug'] ?? '') ?>" aria-describedby="slug-help">
            <p class="help" id="slug-help">Minuscules, chiffres et tirets. Laissez vide pour le générer depuis le titre.</p>
        </div>
        <div class="field">
            <label for="category_id">Catégorie</label>
            <select id="category_id" name="category_id">
                <option value="">— Aucune —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"<?= (string) ($values['category_id'] ?? '') === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="status">Statut</label>
            <select id="status" name="status">
                <option value="draft"<?= ($values['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                <option value="published"<?= ($values['status'] ?? '') === 'published' ? ' selected' : '' ?>>Publié</option>
            </select>
        </div>
        <div class="field">
            <label for="sort_order">Ordre d’affichage</label>
            <input id="sort_order" name="sort_order" type="number" min="-9999" max="9999" value="<?= (int) ($values['sort_order'] ?? 0) ?>">
        </div>
        <div class="field field-check">
            <input id="featured" name="featured" type="checkbox" value="1"<?= !empty($values['featured']) ? ' checked' : '' ?>>
            <label for="featured">Mettre à la une (page d’accueil)</label>
        </div>
        <div class="field field-check">
            <input id="is_demo" name="is_demo" type="checkbox" value="1"<?= !empty($values['is_demo']) ? ' checked' : '' ?>>
            <label for="is_demo">Projet de démonstration (fictif, affiché avec un badge)</label>
        </div>
    </fieldset>

    <fieldset class="box">
        <legend>Image principale</legend>
        <?php if ($image): ?>
            <p><img class="thumb" src="<?= e('/uploads/projects/' . $image) ?>" alt="Image actuelle"></p>
            <div class="field field-check">
                <input id="remove_image" name="remove_image" type="checkbox" value="1">
                <label for="remove_image">Supprimer l’image actuelle</label>
            </div>
        <?php endif; ?>
        <div class="field">
            <label for="image">Téléverser une image (JPEG, PNG ou WebP, 5 Mo max.)</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
        </div>
    </fieldset>

    <?php require BASE_PATH . '/app/Views/admin/partials/translations.php'; ?>

    <p><button class="btn" type="submit">Enregistrer</button> <a class="btn btn-secondary" href="/admin/projects">Annuler</a></p>
</form>
