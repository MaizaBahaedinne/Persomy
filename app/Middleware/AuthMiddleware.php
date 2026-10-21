<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware
{
    public function handle(Request $request): ?Response
    {
        if (!Auth::check()) {
            return Response::redirect('/admin/login')->noCache();
        }
        return null;
    }
}
