<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Submission
{
    public const STATUSES = [
        'new' => 'Nouvelle',
        'in_progress' => 'En cours',
        'done' => 'Traitée',
        'spam' => 'Indésirable',
    ];

    public static function create(array $d): int
    {
        return Db::insert(
            'INSERT INTO contact_submissions (name, email, company, subject, message, locale, consent, ip_hash)
             VALUES (:name, :email, :company, :subject, :message, :locale, 1, :ip)',
            [
                'name' => $d['name'], 'email' => $d['email'], 'company' => $d['company'], 'subject' => $d['subject'],
                'message' => $d['message'], 'locale' => $d['locale'], 'ip' => $d['ip_hash'],
            ]
        );
    }

    public static function setMailStatus(int $id, string $status): void
    {
        Db::run('UPDATE contact_submissions SET mail_status = :s WHERE id = :id', ['s' => $status, 'id' => $id]);
    }

    public static function find(int $id): ?array
    {
        return Db::one('SELECT * FROM contact_submissions WHERE id = :id', ['id' => $id]);
    }

    /** @return array{rows:list<array>,total:int} */
    public static function paginate(?string $status, int $page, int $perPage = 20): array
    {
        $where = '';
        $params = [];
        if ($status !== null && isset(self::STATUSES[$status])) {
            $where = ' WHERE status = :status';
            $params['status'] = $status;
        }
        $total = (int) Db::val('SELECT COUNT(*) FROM contact_submissions' . $where, $params);
        $offset = max(0, ($page - 1) * $perPage);
        $rows = Db::all(
            'SELECT id, name, email, company, subject, status, mail_status, created_at FROM contact_submissions' . $where
            . ' ORDER BY created_at DESC, id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );
        return ['rows' => $rows, 'total' => $total];
    }

    public static function setStatus(int $id, string $status): void
    {
        if (isset(self::STATUSES[$status])) {
            Db::run('UPDATE contact_submissions SET status = :s WHERE id = :id', ['s' => $status, 'id' => $id]);
        }
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM contact_submissions WHERE id = :id', ['id' => $id]);
    }

    public static function purgeOlderThan(int $days): int
    {
        return Db::run(
            'DELETE FROM contact_submissions WHERE created_at < (NOW() - INTERVAL ' . max(1, $days) . ' DAY)'
        )->rowCount();
    }

    public static function countNew(): int
    {
        return (int) Db::val('SELECT COUNT(*) FROM contact_submissions WHERE status = \'new\'');
    }
}
