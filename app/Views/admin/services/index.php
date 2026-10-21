<?php
/** @var array $services */
?>
<p class="toolbar"><a class="btn" href="/admin/services/create">Nouveau service</a></p>
<p class="help">Seuls les services réellement proposés doivent être actifs : ils sont affichés sur l’accueil, la page Services et dans le formulaire de contact.</p>
<?php if (!$services): ?>
    <p>Aucun service.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Titre</th><th scope="col">Identifiant</th><th scope="col">Actif</th><th scope="col">Ordre</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($services as $s): ?>
        <tr>
            <td><a href="/admin/services/<?= (int) $s['id'] ?>/edit"><?= e($s['title']) ?></a></td>
            <td><code><?= e($s['slug']) ?></code></td>
            <td><?= (int) $s['is_active'] === 1 ? '<span class="badge badge-ok">Oui</span>' : '<span class="badge">Non</span>' ?></td>
            <td><?= (int) $s['sort_order'] ?></td>
            <td class="actions">
                <a class="btn btn-sm btn-secondary" href="/admin/services/<?= (int) $s['id'] ?>/edit">Modifier</a>
                <form method="post" action="/admin/services/<?= (int) $s['id'] ?>/delete" data-confirm="Supprimer définitivement ce service ?">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
