<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Db;

/**
 * Limitation de fréquence stockée en base (aucune dépendance cron, ni cache externe).
 * Fenêtre fixe démarrant au premier évènement.
 */
final class RateLimiter
{
    public static function bucket(string $name, string $identifier): string
    {
        return hash('sha256', $name . '|' . $identifier);
    }

    /** Enregistre un évènement et retourne le nombre d'évènements dans la fenêtre. */
    public static function hit(string $bucket, int $windowSeconds): int
    {
        $now = time();
        $cut = $now - $windowSeconds;
        Db::run(
            'INSERT INTO rate_limits (bucket, hits, window_start) VALUES (:b, 1, :now1)
             ON DUPLICATE KEY UPDATE
               hits = IF(window_start < :cut1, 1, hits + 1),
               window_start = IF(window_start < :cut2, :now2, window_start)',
            ['b' => $bucket, 'now1' => $now, 'cut1' => $cut, 'cut2' => $cut, 'now2' => $now]
        );
        if (random_int(1, 100) === 1) {
            Db::run('DELETE FROM rate_limits WHERE window_start < :old', ['old' => $now - 86400]);
        }
        return (int) Db::val('SELECT hits FROM rate_limits WHERE bucket = :b', ['b' => $bucket]);
    }

    /** Nombre d'évènements dans la fenêtre, sans en enregistrer un nouveau. */
    public static function count(string $bucket, int $windowSeconds): int
    {
        return (int) Db::val(
            'SELECT hits FROM rate_limits WHERE bucket = :b AND window_start >= :cut',
            ['b' => $bucket, 'cut' => time() - $windowSeconds]
        );
    }

    public static function clear(string $bucket): void
    {
        Db::run('DELETE FROM rate_limits WHERE bucket = :b', ['b' => $bucket]);
    }
}
