<?php
/**
 * @var int   $newMessages
 * @var array $projects
 * @var array $todo
 * @var array $warnings
 */
?>
<?php if ($warnings): ?>
    <section class="panel panel-warn" aria-labelledby="warn-title">
        <h2 id="warn-title">Points d’attention</h2>
        <ul><?php foreach ($warnings as $w): ?><li><?= e($w) ?></li><?php endforeach; ?></ul>
    </section>
<?php endif; ?>

<div class="stats">
    <a class="stat" href="/admin/messages?status=new"><strong><?= (int) $newMessages ?></strong><span>Nouvelle(s) demande(s)</span></a>
    <a class="stat" href="/admin/projects?status=published"><strong><?= (int) $projects['published'] ?></strong><span>Projet(s) publié(s)</span></a>
    <a class="stat" href="/admin/projects?status=draft"><strong><?= (int) $projects['draft'] ?></strong><span>Brouillon(s)</span></a>
</div>

<section class="panel" aria-labelledby="todo-title">
    <h2 id="todo-title">Informations à compléter</h2>
    <?php if (!$todo): ?>
        <p>Aucun réglage marqué « à compléter ». Vérifiez tout de même les pages (marqueurs dans les contenus) dans « Pages ».</p>
    <?php else: ?>
        <p>Ces réglages contiennent encore un marqueur <code>[À COMPLÉTER …]</code> :</p>
        <ul><?php foreach ($todo as $key): ?><li><code><?= e($key) ?></code></li><?php endforeach; ?></ul>
        <p><a class="btn btn-sm" href="/admin/settings">Ouvrir les réglages</a></p>
    <?php endif; ?>
</section>
