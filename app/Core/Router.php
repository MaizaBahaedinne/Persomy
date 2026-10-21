<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{method:string,regex:string,handler:array,middleware:array}> */
    private array $routes = [];

    public function get(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    private function add(string $method, string $pattern, array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'regex' => self::compile($pattern),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /** Transforme /{lang:fr|en}/page/{slug} en expression régulière. */
    public static function compile(string $pattern): string
    {
        $regex = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}|[^{]+#',
            static function (array $m): string {
                if (isset($m[1]) && $m[1] !== '') {
                    return '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')';
                }
                return preg_quote($m[0], '#');
            },
            $pattern
        );
        return '#^' . $regex . '$#u';
    }

    /** Retourne la réponse, ou null si aucune route ne correspond. */
    public function dispatch(Request $request): ?Response
    {
        $method = $request->method === 'HEAD' ? 'GET' : $request->method;
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path, $m)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $middlewareClass) {
                $result = (new $middlewareClass())->handle($request);
                if ($result instanceof Response) {
                    return $result;
                }
            }
            [$class, $action] = $route['handler'];
            return (new $class())->$action($request, $params);
        }

        if ($pathMatched) {
            throw new HttpException(405);
        }
        return null;
    }
}
