<?php
/** @var array $errors */
?>
<?php require BASE_PATH . '/app/Views/admin/partials/errors.php'; ?>
<form method="post" action="/admin/account" class="stack narrow">
    <?= csrf_field() ?>
    <div class="field">
        <label for="current_password">Mot de passe actuel</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
    </div>
    <div class="field">
        <label for="new_password">Nouveau mot de passe (12 caractères minimum)</label>
        <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="12" required>
    </div>
    <div class="field">
        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="12" required>
    </div>
    <p><button class="btn" type="submit">Changer le mot de passe</button></p>
</form>
