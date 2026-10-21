<?php
/**
 * @var array  $projects
 * @var string $status
 */
?>
<p class="toolbar">
    <a class="btn" href="/admin/projects/create">Nouveau projet</a>
    <span class="filters">
        <a href="/admin/projects"<?= $status === '' ? ' aria-current="true"' : '' ?>>Tous</a>
        <a href="/admin/projects?status=published"<?= $status === 'published' ? ' aria-current="true"' : '' ?>>Publiés</a>
        <a href="/admin/projects?status=draft"<?= $status === 'draft' ? ' aria-current="true"' : '' ?>>Brouillons</a>
    </span>
</p>
<?php if (!$projects): ?>
    <p>Aucun projet.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Titre</th><th scope="col">Statut</th><th scope="col">Catégorie</th><th scope="col">Ordre</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($projects as $p): ?>
        <tr>
            <td>
                <a href="/admin/projects/<?= (int) $p['id'] ?>/edit"><?= e($p['title']) ?></a>
                <?php if ((int) $p['is_demo'] === 1): ?><span class="badge badge-warn">démo</span><?php endif; ?>
                <?php if ((int) $p['featured'] === 1): ?><span class="badge">à la une</span><?php endif; ?>
            </td>
            <td><?= $p['status'] === 'published' ? '<span class="badge badge-ok">Publié</span>' : '<span class="badge">Brouillon</span>' ?></td>
            <td><?= e($p['category_slug'] ?? '—') ?></td>
            <td><?= (int) $p['sort_order'] ?></td>
            <td class="actions">
                <a class="btn btn-sm btn-secondary" href="/admin/projects/<?= (int) $p['id'] ?>/edit">Modifier</a>
                <form method="post" action="/admin/projects/<?= (int) $p['id'] ?>/toggle">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-secondary" type="submit"><?= $p['status'] === 'published' ? 'Dépublier' : 'Publier' ?></button>
                </form>
                <form method="post" action="/admin/projects/<?= (int) $p['id'] ?>/delete" data-confirm="Supprimer définitivement ce projet ?">
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
