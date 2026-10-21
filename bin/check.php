<?php

declare(strict_types=1);

/**
 * Diagnostic de l'installation (à lancer sur le serveur, en SSH).
 *
 *   php bin/check.php
 *   php bin/check.php --smtp-test=destinataire@exemple.com   # envoie un e-mail de test
 */

require __DIR__ . '/_cli.php';

use App\Core\Config;
use App\Core\Db;
use App\Services\Mailer;

$failures = 0;
$report = static function (string $status, string $label, string $detail = '') use (&$failures): void {
    if ($status === 'FAIL') {
        $failures++;
    }
    printf("[%-4s] %s%s\n", $status, $label, $detail !== '' ? ' — ' . $detail : '');
};

echo "Persomy — diagnostic\n\n";

$report(PHP_VERSION_ID >= 80200 ? 'OK' : 'FAIL', 'PHP ' . PHP_VERSION, 'PHP 8.2 ou plus requis');
foreach (['pdo_mysql' => true, 'mbstring' => true, 'fileinfo' => true, 'openssl' => true, 'intl' => false, 'gd' => false] as $ext => $required) {
    $report(extension_loaded($ext) ? 'OK' : ($required ? 'FAIL' : 'WARN'), 'extension ' . $ext, $required ? 'requise' : 'recommandée');
}

$report(is_file(BASE_PATH . '/.env') ? 'OK' : 'FAIL', 'fichier .env', 'copier .env.example vers .env');
$report(Config::get('env') === 'production' ? 'OK' : 'WARN', 'APP_ENV = ' . Config::get('env'), 'doit valoir production en ligne');
$report(Config::get('key') !== '' ? 'OK' : 'FAIL', 'APP_KEY', 'valeur aléatoire requise');
$report(str_starts_with((string) Config::get('url'), 'https://') ? 'OK' : 'WARN', 'APP_URL = ' . (Config::get('url') ?: '(vide)'), 'https attendu en production');

foreach (['storage/logs', 'storage/sessions', 'public/uploads/projects'] as $dir) {
    $path = BASE_PATH . '/' . $dir;
    if (!is_dir($path)) {
        @mkdir($path, 0750, true);
    }
    $report(is_dir($path) && is_writable($path) ? 'OK' : 'FAIL', 'dossier inscriptible ' . $dir, 'droits pour l’utilisateur du serveur web');
}

try {
    $tables = (int) Db::val('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()');
    $report('OK', 'connexion base de données', $tables . ' table(s)');
    $report($tables >= 12 ? 'OK' : 'FAIL', 'schéma installé', 'exécuter php bin/migrate.php --seed');
    $admins = (int) Db::val('SELECT COUNT(*) FROM admins');
    $report($admins > 0 ? 'OK' : 'WARN', 'compte administrateur', $admins . ' compte(s) ; php bin/create-admin.php');
} catch (Throwable $e) {
    $report('FAIL', 'connexion base de données', $e->getMessage());
}

$report(Mailer::configured() ? 'OK' : 'WARN', 'SMTP configuré', 'SMTP_HOST et MAIL_FROM_ADDRESS requis pour les e-mails');

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--smtp-test=')) {
        $to = substr($arg, strlen('--smtp-test='));
        try {
            Mailer::send($to, '[Persomy] Test SMTP', "Ceci est un e-mail de test envoyé par bin/check.php.\n");
            $report('OK', 'envoi SMTP de test', 'envoyé à ' . $to);
        } catch (Throwable $e) {
            $report('FAIL', 'envoi SMTP de test', $e->getMessage());
        }
    }
}

echo $failures === 0 ? "\nAucun blocage détecté.\n" : "\n$failures point(s) bloquant(s).\n";
exit($failures === 0 ? 0 : 1);
