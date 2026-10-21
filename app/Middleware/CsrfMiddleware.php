<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\ErrorPage;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware
{
    public function handle(Request $request): ?Response
    {
        if ($request->isPost() && !Csrf::verify($request)) {
            return ErrorPage::response(419);
        }
        return null;
    }
}
