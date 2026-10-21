<?php
/**
 * @var array       $rows
 * @var int         $total
 * @var int         $page
 * @var int         $pages
 * @var string|null $status
 * @var int         $retention
 */

use App\Models\Submission;

$statusLabels = Submission::STATUSES;
$mailLabels = ['sent' => 'E-mail envoyé', 'failed' => 'Échec e-mail', 'skipped' => 'E-mail non envoyé'];
$qs = static fn(array $extra): string => '?' . http_build_query(array_filter($extra, static fn($v) => $v !== null && $v !== ''));
?>
<p class="toolbar">
    <span class="filters">
        <a href="/admin/messages"<?= $status === null ? ' aria-current="true"' : '' ?>>Toutes</a>
        <?php foreach ($statusLabels as $key => $label): ?>
            <a href="/admin/messages<?= e($qs(['status' => $key])) ?>"<?= $status === $key ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </span>
</p>

<?php if (!$rows): ?>
    <p>Aucune demande.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th scope="col">Date</th><th scope="col">Nom</th><th scope="col">E-mail</th><th scope="col">Sujet</th><th scope="col">Statut</th><th scope="col">Notification</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e(format_date($r['created_at'])) ?></td>
            <td><a href="/admin/messages/<?= (int) $r['id'] ?>"><?= e($r['name']) ?></a><?= $r['company'] !== '' ? ' <small>(' . e($r['company']) . ')</small>' : '' ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['subject'] !== '' ? $r['subject'] : '—') ?></td>
            <td><span class="badge <?= $r['status'] === 'new' ? 'badge-warn' : ($r['status'] === 'done' ? 'badge-ok' : '') ?>"><?= e($statusLabels[$r['status']] ?? $r['status']) ?></span></td>
            <td><span class="badge <?= $r['mail_status'] === 'failed' ? 'badge-err' : '' ?>"><?= e($mailLabels[$r['mail_status']] ?? $r['mail_status']) ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="/admin/messages<?= e($qs(['status' => $status, 'page' => $i > 1 ? (string) $i : null])) ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
<?php endif; ?>

<section class="panel">
    <h2>Conservation des données</h2>
    <p><?= (int) $total ?> demande(s) au total. Durée de conservation configurée : <?= (int) $retention ?> jours.</p>
    <form method="post" action="/admin/messages/purge" data-confirm="Supprimer définitivement toutes les demandes de plus de <?= (int) $retention ?> jours ?">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-danger" type="submit">Purger les demandes expirées</button>
    </form>
</section>
