<?php
/** @var array $message */

use App\Models\Submission;

$mailLabels = ['sent' => 'E-mail envoyé', 'failed' => 'Échec de l’envoi de l’e-mail', 'skipped' => 'E-mail non envoyé (SMTP ou destinataire non configuré)'];
?>
<p><a href="/admin/messages">← Retour aux messages</a></p>
<dl class="details">
    <dt>Reçue le</dt><dd><?= e(format_date($message['created_at'])) ?></dd>
    <dt>Nom</dt><dd><?= e($message['name']) ?></dd>
    <dt>E-mail</dt><dd><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></dd>
    <dt>Entreprise</dt><dd><?= e($message['company'] !== '' ? $message['company'] : '—') ?></dd>
    <dt>Sujet</dt><dd><?= e($message['subject'] !== '' ? $message['subject'] : '—') ?></dd>
    <dt>Langue du site</dt><dd><?= e(strtoupper($message['locale'])) ?></dd>
    <dt>Notification</dt><dd><?= e($mailLabels[$message['mail_status']] ?? $message['mail_status']) ?></dd>
    <dt>Message</dt><dd class="message-body"><?= nl2br(e($message['message'])) ?></dd>
</dl>

<form method="post" action="/admin/messages/<?= (int) $message['id'] ?>/status" class="inline-form">
    <?= csrf_field() ?>
    <label for="status">Statut</label>
    <select id="status" name="status">
        <?php foreach (Submission::STATUSES as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $message['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-sm" type="submit">Mettre à jour</button>
</form>

<form method="post" action="/admin/messages/<?= (int) $message['id'] ?>/delete" data-confirm="Supprimer définitivement cette demande ?">
    <?= csrf_field() ?>
    <button class="btn btn-sm btn-danger" type="submit">Supprimer cette demande</button>
</form>
