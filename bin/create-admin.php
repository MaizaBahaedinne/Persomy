<?php

declare(strict_types=1);

/**
 * Crée un compte administrateur.
 *
 *   php bin/create-admin.php admin@exemple.com "Nom Prénom"
 *
 * Le mot de passe est demandé de façon interactive (non affiché). Ne le passez jamais en argument.
 * Pour un usage non interactif : variable d'environnement PERSOMY_ADMIN_PASSWORD.
 */

require __DIR__ . '/_cli.php';

use App\Models\Admin;

$email = $argv[1] ?? '';
$name = $argv[2] ?? 'Administrateur';

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Usage : php bin/create-admin.php email@exemple.com \"Nom\"\n");
    exit(1);
}

$password = getenv('PERSOMY_ADMIN_PASSWORD') ?: '';
if ($password === '') {
    $interactive = function_exists('posix_isatty') ? posix_isatty(STDIN) : true;
    if ($interactive) {
        echo 'Mot de passe (12 caractères minimum) : ';
        shell_exec('stty -echo 2>/dev/null');
        $password = rtrim((string) fgets(STDIN), "\r\n");
        shell_exec('stty echo 2>/dev/null');
        echo "\nConfirmation : ";
        shell_exec('stty -echo 2>/dev/null');
        $confirm = rtrim((string) fgets(STDIN), "\r\n");
        shell_exec('stty echo 2>/dev/null');
        echo "\n";
        if ($password !== $confirm) {
            fwrite(STDERR, "Les mots de passe ne correspondent pas.\n");
            exit(1);
        }
    }
}
if (mb_strlen($password) < 12) {
    fwrite(STDERR, "Le mot de passe doit contenir au moins 12 caractères.\n");
    exit(1);
}

try {
    if (Admin::findByEmail($email) !== null) {
        fwrite(STDERR, "Un compte existe déjà avec cette adresse.\n");
        exit(1);
    }
    $id = Admin::create($email, $name, $password);
    echo "Administrateur créé (id $id). Connexion : /admin/login\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Erreur : ' . $e->getMessage() . "\nAvez-vous exécuté php bin/migrate.php ?\n");
    exit(1);
}
