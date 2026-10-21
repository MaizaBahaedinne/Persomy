<?php
/**
 * @var array $page
 * @var int   $isActive
 * @var array $tr
 * @var array $errors
 * @var array $fields
 */
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<details class="help-box">
    <summary>Aide de mise en forme du contenu</summary>
    <p>Le contenu est du texte simple (aucun HTML) :</p>
    <ul>
        <li>Une ligne vide sépare les paragraphes.</li>
        <li><code>## Titre</code> crée un titre de section, <code>### Titre</code> un sous-titre.</li>
        <li><code>- élément</code> crée une liste à puces (une ligne par élément).</li>
    </ul>
</details>
<form method="post" action="/admin/pages/<?= (int) $page['id'] ?>" class="stack">
    <?= csrf_field() ?>
    <fieldset class="box">
        <legend>Paramètres</legend>
        <div class="field field-check">
            <input id="is_active" name="is_active" type="checkbox" value="1"<?= $isActive ? ' checked' : '' ?>>
            <label for="is_active">Page active (accessible sur le site)</label>
        </div>
    </fieldset>
    <?php require BASE_PATH . '/app/Views/admin/partials/translations.php'; ?>
    <p><button class="btn" type="submit">Enregistrer</button> <a class="btn btn-secondary" href="/admin/pages">Annuler</a></p>
</form>
