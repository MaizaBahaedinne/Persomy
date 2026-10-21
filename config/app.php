<?php

declare(strict_types=1);

use App\Core\Env;

$env = Env::get('APP_ENV', 'production') ?? 'production';

return [
    'env' => $env,
    // Le mode debug n'est jamais actif en production.
    'debug' => $env !== 'production' && Env::bool('APP_DEBUG', false),
    'url' => rtrim(Env::get('APP_URL', '') ?? '', '/'),
    'key' => Env::get('APP_KEY', '') ?? '',
    'trust_proxy' => Env::bool('TRUST_PROXY_HEADERS', false),
    'default_locale' => Env::get('APP_DEFAULT_LOCALE', 'fr') ?? 'fr',

    'locales' => [
        'fr' => ['name' => 'Français', 'dir' => 'ltr', 'og' => 'fr_FR'],
        'en' => ['name' => 'English', 'dir' => 'ltr', 'og' => 'en_US'],
        'ar' => ['name' => 'العربية', 'dir' => 'rtl', 'og' => 'ar_AR'],
    ],

    'db' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::int('DB_PORT', 3306),
        'name' => Env::get('DB_NAME', ''),
        'user' => Env::get('DB_USER', ''),
        'password' => Env::get('DB_PASSWORD', ''),
    ],

    'mail' => [
        'host' => Env::get('SMTP_HOST', ''),
        'port' => Env::int('SMTP_PORT', 587),
        'encryption' => strtolower(Env::get('SMTP_ENCRYPTION', 'tls') ?? 'tls'), // tls | ssl | none
        'username' => Env::get('SMTP_USERNAME', ''),
        'password' => Env::get('SMTP_PASSWORD', ''),
        'from' => Env::get('MAIL_FROM_ADDRESS', ''),
        'from_name' => Env::get('MAIL_FROM_NAME', 'Persomy'),
        'to' => Env::get('MAIL_TO', ''),
    ],

    'upload' => [
        'max_bytes' => Env::int('UPLOAD_MAX_MB', 5) * 1048576,
        'mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
        'max_dimension' => 6000,
    ],

    'session_lifetime' => 7200,
];
