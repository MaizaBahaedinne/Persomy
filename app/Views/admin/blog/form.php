<?php
/**
 * @var array|null $post
 * @var array      $values
 * @var array      $tr
 * @var array      $errors
 * @var array      $categories
 * @var array      $fields
 */
$action = $post ? '/admin/blog/' . (int) $post['id'] : '/admin/blog';
$image = $values['cover_image'] ?? null;
$publishedAt = !empty($values['published_at']) ? date('Y-m-d\TH:i', (int) strtotime((string) $values['published_at'])) : '';
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
            <label for="author">Auteur</label>
            <input id="author" name="author" type="text" maxlength="120" value="<?= e($values['author'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="status">Statut</label>
            <select id="status" name="status">
                <option value="draft"<?= ($values['status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                <option value="published"<?= ($values['status'] ?? '') === 'published' ? ' selected' : '' ?>>Publié</option>
            </select>
        </div>
        <div class="field">
            <label for="published_at">Date de publication</label>
            <input id="published_at" name="published_at" type="datetime-local" value="<?= e($publishedAt) ?>" aria-describedby="date-help">
            <p class="help" id="date-help">Laissez vide pour publier immédiatement. Une date future programme la publication (statut « Publié » requis).</p>
        </div>
        <div class="field field-check">
            <input id="featured" name="featured" type="checkbox" value="1"<?= !empty($values['featured']) ? ' checked' : '' ?>>
            <label for="featured">Article mis en avant</label>
        </div>
    </fieldset>

    <fieldset class="box">
        <legend>Image de couverture</legend>
        <?php if ($image): ?>
            <p><img class="thumb" src="<?= e('/uploads/blog/' . $image) ?>" alt="Image actuelle"></p>
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

    <p class="help">Mise en forme du contenu : <code>## Titre</code>, <code>### Sous-titre</code>, <code>- liste</code>, <code>**gras**</code>, <code>[texte](https://…)</code> ou <code>[texte](/fr/contact)</code>. Une ligne vide sépare les paragraphes.</p>

    <?php require BASE_PATH . '/app/Views/admin/partials/translations.php'; ?>

    <p><button class="btn" type="submit">Enregistrer</button> <a class="btn btn-secondary" href="/admin/blog">Annuler</a></p>
</form>
