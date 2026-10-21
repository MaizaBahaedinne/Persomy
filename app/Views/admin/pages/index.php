<?php
/** @var array $pages */
?>
<p class="help">Les marqueurs <code>[À COMPLÉTER …]</code> signalent les textes officiels encore attendus.</p>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Page</th><th scope="col">Langues renseignées</th><th scope="col">Actif</th><th scope="col">À compléter</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
        <tr>
            <td><a href="/admin/pages/<?= (int) $p['id'] ?>/edit"><?= e($p['label']) ?></a></td>
            <td><?= e(strtoupper(implode(', ', $p['locales']))) ?: '—' ?></td>
            <td><?= (int) $p['is_active'] === 1 ? '<span class="badge badge-ok">Oui</span>' : '<span class="badge">Non</span>' ?></td>
            <td><?= $p['has_placeholder'] ? '<span class="badge badge-warn">Oui</span>' : '—' ?></td>
            <td class="actions"><a class="btn btn-sm btn-secondary" href="/admin/pages/<?= (int) $p['id'] ?>/edit">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
