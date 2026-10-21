<?php
/** @var array $categories */
?>
<p class="toolbar"><a class="btn" href="/admin/categories/create">Nouvelle catégorie</a></p>
<?php if (!$categories): ?>
    <p>Aucune catégorie.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Nom</th><th scope="col">Identifiant</th><th scope="col">Projets</th><th scope="col">Ordre</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
        <tr>
            <td><a href="/admin/categories/<?= (int) $c['id'] ?>/edit"><?= e($c['name']) ?></a></td>
            <td><code><?= e($c['slug']) ?></code></td>
            <td><?= (int) $c['projects_count'] ?></td>
            <td><?= (int) $c['sort_order'] ?></td>
            <td class="actions">
                <a class="btn btn-sm btn-secondary" href="/admin/categories/<?= (int) $c['id'] ?>/edit">Modifier</a>
                <form method="post" action="/admin/categories/<?= (int) $c['id'] ?>/delete" data-confirm="Supprimer cette catégorie ? Les projets associés seront conservés sans catégorie.">
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
