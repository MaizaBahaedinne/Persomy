<?php
/**
 * @var string      $email
 * @var string|null $error
 */
?>
<div class="login-card">
    <p class="admin-brand">PERSOMY <span>admin</span></p>
    <h1>Connexion</h1>
    <?php if ($error !== null): ?><div class="flash flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/admin/login" class="stack">
        <?= csrf_field() ?>
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" autocomplete="username" required autofocus value="<?= e($email) ?>">
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn" type="submit">Se connecter</button>
    </form>
</div>
