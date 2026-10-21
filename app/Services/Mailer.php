<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Client SMTP minimal (STARTTLS / SSL / sans chiffrement), sans dépendance externe.
 * Lève MailException en cas d'échec : l'appelant ne doit jamais annoncer un envoi réussi sans retour normal.
 */
final class Mailer
{
    public static function configured(): bool
    {
        $cfg = Config::get('mail');
        return !empty($cfg['host']) && filter_var($cfg['from'] ?? '', FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function send(string $to, string $subject, string $text, ?string $replyTo = null, ?string $replyName = null): void
    {
        if (!self::configured()) {
            throw new MailException('SMTP is not configured');
        }
        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            throw new MailException('Invalid recipient');
        }

        $cfg = Config::get('mail');
        $host = (string) $cfg['host'];
        $port = (int) $cfg['port'];
        $encryption = (string) $cfg['encryption'];
        $from = (string) $cfg['from'];
        $domain = self::domainOf($from);

        $message = self::buildMessage($from, (string) $cfg['from_name'], $to, $subject, $text, $replyTo, $replyName, $domain);

        $context = stream_context_create(['ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $host,
        ]]);
        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            throw new MailException('SMTP connection failed (' . $errno . ')');
        }
        stream_set_timeout($socket, 15);

        try {
            self::expect($socket, [220]);
            self::command($socket, 'EHLO ' . $domain, [250]);
            if ($encryption === 'tls') {
                self::command($socket, 'STARTTLS', [220]);
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new MailException('STARTTLS negotiation failed');
                }
                self::command($socket, 'EHLO ' . $domain, [250]);
            }
            $user = (string) ($cfg['username'] ?? '');
            if ($user !== '') {
                self::command($socket, 'AUTH LOGIN', [334]);
                self::command($socket, base64_encode($user), [334], true);
                self::command($socket, base64_encode((string) ($cfg['password'] ?? '')), [235], true);
            }
            self::command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::command($socket, 'DATA', [354]);
            self::write($socket, $message . "\r\n.\r\n");
            self::expect($socket, [250]);
            @self::write($socket, "QUIT\r\n");
        } finally {
            @fclose($socket);
        }
    }

    /** Construit un message RFC 5322 (texte UTF-8, base64). Public pour les tests. */
    public static function buildMessage(
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $text,
        ?string $replyTo,
        ?string $replyName,
        string $domain
    ): string {
        $headers = [
            'Date: ' . date('r'),
            'From: ' . self::address($fromName, $from),
            'To: <' . self::clean($to) . '>',
            'Subject: ' . self::encodeHeader($subject),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . self::clean($domain) . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL) !== false) {
            $headers[] = 'Reply-To: ' . self::address((string) $replyName, $replyTo);
        }
        $body = chunk_split(base64_encode($text), 76, "\r\n");
        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    /** Supprime tout caractère de contrôle (anti injection d'en-têtes). */
    public static function clean(string $value): string
    {
        return trim((string) preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value));
    }

    private static function encodeHeader(string $value): string
    {
        $value = self::clean($value);
        return $value === '' ? '' : (string) mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
    }

    private static function address(string $name, string $email): string
    {
        $email = self::clean($email);
        $name = self::clean($name);
        return $name === '' ? '<' . $email . '>' : self::encodeHeader($name) . ' <' . $email . '>';
    }

    private static function domainOf(string $email): string
    {
        $at = strrpos($email, '@');
        $domain = $at === false ? 'localhost' : substr($email, $at + 1);
        return preg_replace('/[^a-zA-Z0-9.\-]/', '', $domain) ?: 'localhost';
    }

    /** @param resource $socket */
    private static function write($socket, string $data): void
    {
        if (@fwrite($socket, $data) === false) {
            throw new MailException('SMTP write failed');
        }
    }

    /** @param resource $socket */
    private static function command($socket, string $command, array $expected, bool $secret = false): void
    {
        self::write($socket, $command . "\r\n");
        self::expect($socket, $expected, $secret);
    }

    /** @param resource $socket */
    private static function expect($socket, array $codes, bool $secret = false): void
    {
        $line = '';
        $reply = '';
        while (($line = fgets($socket, 1024)) !== false) {
            $reply .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        if ($reply === '') {
            throw new MailException('SMTP connection closed or timed out');
        }
        $code = (int) substr($reply, 0, 3);
        if (!in_array($code, $codes, true)) {
            $detail = $secret ? '' : ': ' . self::clean(substr($reply, 0, 200));
            throw new MailException('Unexpected SMTP reply ' . $code . $detail);
        }
    }
}
