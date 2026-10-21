<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Admin
{
    public static function find(int $id): ?array
    {
        return Db::one('SELECT * FROM admins WHERE id = :id', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Db::one('SELECT * FROM admins WHERE email = :email', ['email' => mb_strtolower(trim($email))]);
    }

    public static function create(string $email, string $name, string $password): int
    {
        return Db::insert(
            'INSERT INTO admins (email, name, password_hash) VALUES (:email, :name, :hash)',
            ['email' => mb_strtolower(trim($email)), 'name' => $name, 'hash' => password_hash($password, PASSWORD_DEFAULT)]
        );
    }

    public static function updatePassword(int $id, string $password): void
    {
        Db::run('UPDATE admins SET password_hash = :hash WHERE id = :id', [
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    public static function touchLogin(int $id): void
    {
        Db::run('UPDATE admins SET last_login_at = NOW() WHERE id = :id', ['id' => $id]);
    }
}
