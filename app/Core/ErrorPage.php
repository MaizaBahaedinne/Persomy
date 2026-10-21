<?php

declare(strict_types=1);

namespace App\Core;

final class ErrorPage
{
    private const ALLOWED = [403, 404, 405, 419, 429, 500];

    public static function response(int $status, ?\Throwable $debug = null): Response
    {
        if (!in_array($status, self::ALLOWED, true)) {
            $status = 500;
        }
        try {
            $body = View::render('errors/generic', [
                'status' => $status,
                'debug' => (Config::get('debug') && $debug) ? $debug->getMessage() . "\n" . $debug->getFile() . ':' . $debug->getLine() : null,
                'meta' => ['title' => (string) $status, 'path' => null],
            ], 'layouts/error');
        } catch (\Throwable) {
            $body = '<!DOCTYPE html><meta charset="utf-8"><title>Error ' . $status . '</title><p>Error ' . $status . '</p>';
        }
        $response = new Response($body, $status, ['X-Robots-Tag' => 'noindex']);
        return $response->noCache();
    }
}
