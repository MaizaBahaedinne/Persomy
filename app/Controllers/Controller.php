<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Response;
use App\Core\View;

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'layouts/main', int $status = 200): Response
    {
        return new Response(View::render($template, $data, $layout), $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    protected function abort(int $status): never
    {
        throw new HttpException($status);
    }
}
