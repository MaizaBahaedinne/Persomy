<?php

declare(strict_types=1);

/**
 * Applique les migrations SQL (database/migrations) puis, sur demande, les données initiales.
 *
 *   php bin/migrate.php            # schéma uniquement
 *   php bin/migrate.php --seed     # + contenu initial FR/EN/AR (idempotent)
 *   php bin/migrate.php --demo     # + projets de démonstration FICTIFS (optionnel)
 */

require __DIR__ . '/_cli.php';

use App\Core\Config;
use App\Services\SqlSplitter;

$options = array_slice($argv, 1);
$cfg = Config::get('db');

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['name']),
        (string) $cfg['user'],
        (string) $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Connexion à la base impossible : " . $e->getMessage() . "\nVérifiez DB_HOST, DB_NAME, DB_USER et DB_PASSWORD dans .env.\n");
    exit(1);
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
       id INT UNSIGNED NOT NULL AUTO_INCREMENT,
       name VARCHAR(190) NOT NULL,
       applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       UNIQUE KEY uq_migrations_name (name)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
$applied = $pdo->query('SELECT name FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

$groups = ['migrations' => 'database/migrations/*.sql'];
if (in_array('--seed', $options, true)) {
    $groups['seeders'] = 'database/seeders/*.sql';
}
if (in_array('--demo', $options, true)) {
    $groups['demo'] = 'database/demo/*.sql';
}

$count = 0;
foreach ($groups as $prefix => $pattern) {
    $files = glob(BASE_PATH . '/' . $pattern) ?: [];
    sort($files);
    foreach ($files as $file) {
        $name = $prefix . '/' . basename($file);
        if (in_array($name, $applied, true)) {
            echo "  déjà appliqué : $name\n";
            continue;
        }
        echo "  application   : $name\n";
        foreach (SqlSplitter::split((string) file_get_contents($file)) as $statement) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                fwrite(STDERR, "Erreur dans $name :\n" . $e->getMessage() . "\n---\n" . substr($statement, 0, 300) . "\n");
                exit(1);
            }
        }
        $pdo->prepare('INSERT INTO migrations (name) VALUES (:name)')->execute(['name' => $name]);
        $count++;
    }
}

echo $count === 0 ? "Rien à appliquer.\n" : "$count fichier(s) appliqué(s).\n";
