<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    public static function run(): void
    {
        $request = Request::capture();
        Lang::init($request);

        $router = new Router();
        require BASE_PATH . '/routes/web.php';

        try {
            $response = $router->dispatch($request) ?? ErrorPage::response(404);
        } catch (HttpException $e) {
            $response = ErrorPage::response($e->status);
        }
        $response->send($request->method === 'HEAD');
    }
}
