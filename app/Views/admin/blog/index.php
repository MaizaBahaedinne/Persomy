<?php
/**
 * @var array  $posts
 * @var string $status
 */
?>
<p class="toolbar">
    <a class="btn" href="/admin/blog/create">Nouvel article</a>
    <a class="btn btn-secondary" href="/admin/blog-categories">Catégories</a>
    <span class="filters">
        <a href="/admin/blog"<?= $status === '' ? ' aria-current="true"' : '' ?>>Tous</a>
        <a href="/admin/blog?status=published"<?= $status === 'published' ? ' aria-current="true"' : '' ?>>Publiés</a>
        <a href="/admin/blog?status=draft"<?= $status === 'draft' ? ' aria-current="true"' : '' ?>>Brouillons</a>
    </span>
</p>
<?php if (!$posts): ?>
    <p>Aucun article.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Titre</th><th scope="col">Statut</th><th scope="col">Catégorie</th><th scope="col">Date</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $p): ?>
        <tr>
            <td>
                <a href="/admin/blog/<?= (int) $p['id'] ?>/edit"><?= e($p['title']) ?></a>
                <?php if ((int) $p['featured'] === 1): ?><span class="badge">à la une</span><?php endif; ?>
            </td>
            <td>
                <?php if ($p['status'] !== 'published'): ?>
                    <span class="badge">Brouillon</span>
                <?php elseif (!empty($p['scheduled'])): ?>
                    <span class="badge badge-warn">Programmé</span>
                <?php else: ?>
                    <span class="badge badge-ok">Publié</span>
                <?php endif; ?>
            </td>
            <td><?= e($p['category_slug'] ?? '—') ?></td>
            <td><?= $p['published_at'] ? e(date('d/m/Y H:i', (int) strtotime((string) $p['published_at']))) : '—' ?></td>
            <td class="actions">
                <a class="btn btn-sm btn-secondary" href="/admin/blog/<?= (int) $p['id'] ?>/edit">Modifier</a>
                <form method="post" action="/admin/blog/<?= (int) $p['id'] ?>/toggle">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-secondary" type="submit"><?= $p['status'] === 'published' ? 'Dépublier' : 'Publier' ?></button>
                </form>
                <form method="post" action="/admin/blog/<?= (int) $p['id'] ?>/delete" data-confirm="Supprimer définitivement cet article ?">
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
