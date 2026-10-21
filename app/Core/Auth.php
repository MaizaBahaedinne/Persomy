<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Admin;

final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('admin_id');
            if (is_int($id)) {
                $row = Admin::find($id);
                self::$user = ($row && (int) $row['is_active'] === 1) ? $row : null;
            }
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attempt(string $email, string $password): ?array
    {
        $admin = Admin::findByEmail($email);
        // Vérification même si le compte n'existe pas : temps de réponse homogène.
        $hash = $admin['password_hash'] ?? password_hash('persomy-dummy', PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash);
        if (!$admin || !$valid || (int) $admin['is_active'] !== 1) {
            return null;
        }
        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            Admin::updatePassword((int) $admin['id'], $password);
        }
        return $admin;
    }

    public static function login(array $admin): void
    {
        Session::start();
        Session::regenerate();
        Session::set('admin_id', (int) $admin['id']);
        Admin::touchLogin((int) $admin['id']);
        self::$user = $admin;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$loaded = true;
    }
}
