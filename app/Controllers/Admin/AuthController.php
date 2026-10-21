<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Services\RateLimiter;

final class AuthController extends Controller
{
    private const WINDOW = 900; // 15 minutes
    private const MAX_PER_IP = 10;
    private const MAX_PER_ACCOUNT = 5;

    public function showLogin(Request $request, array $params): Response
    {
        if (Auth::check()) {
            return $this->redirect('/admin');
        }
        return $this->loginView('', null, 200);
    }

    public function login(Request $request, array $params): Response
    {
        $email = mb_strtolower($request->input('email'));
        $password = isset($request->post['password']) && is_string($request->post['password']) ? $request->post['password'] : '';

        $ipBucket = RateLimiter::bucket('login-ip', $request->ipHash());
        $accountBucket = RateLimiter::bucket('login-account', $email);

        try {
            if (RateLimiter::count($ipBucket, self::WINDOW) >= self::MAX_PER_IP
                || RateLimiter::count($accountBucket, self::WINDOW) >= self::MAX_PER_ACCOUNT) {
                return $this->loginView($email, 'Trop de tentatives. Réessayez dans 15 minutes.', 429);
            }

            $admin = ($email !== '' && $password !== '') ? Auth::attempt($email, $password) : null;
            if ($admin === null) {
                RateLimiter::hit($ipBucket, self::WINDOW);
                RateLimiter::hit($accountBucket, self::WINDOW);
                return $this->loginView($email, 'Identifiants incorrects.', 422);
            }
        } catch (\Throwable $e) {
            Logger::error('Admin login failed unexpectedly', ['error' => get_class($e)]);
            return $this->loginView($email, 'Service momentanément indisponible.', 500);
        }

        RateLimiter::clear($accountBucket);
        Auth::login($admin);
        return $this->redirect('/admin')->noCache();
    }

    public function logout(Request $request, array $params): Response
    {
        Auth::logout();
        return $this->redirect('/admin/login')->noCache();
    }

    private function loginView(string $email, ?string $error, int $status): Response
    {
        return $this->view('admin/login', ['email' => $email, 'error' => $error, 'title' => 'Connexion'], 'layouts/admin_guest', $status)->noCache();
    }
}
