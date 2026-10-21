<?php

declare(strict_types=1);

/**
 * Supprime les demandes de contact plus anciennes que la durée de conservation (réglage du site).
 * À planifier si le cron est disponible, sinon utiliser le bouton « Purger » dans l'administration :
 *
 *   0 3 * * *  php /chemin/vers/persomy/bin/purge-submissions.php
 */

require __DIR__ . '/_cli.php';

use App\Models\Setting;
use App\Models\Submission;

$days = (int) (Setting::get('contact_retention_days', '365') ?: 365);
$count = Submission::purgeOlderThan($days);
echo "$count demande(s) de plus de $days jours supprimée(s).\n";
