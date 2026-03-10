<?php
declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $routes = [];

    private Closure|string|array|null $notFoundHandler = null;

    public function get(string $pattern, Closure|string|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, Closure|string|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, Closure|string|array $handler): void
    {
        $this->routes[strtoupper($method)][] = [
            'pattern' => $this->normalizePattern($pattern),
            'handler' => $handler,
        ];
    }

    public function setNotFoundHandler(Closure|string|array $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        $candidates = $this->routes[$method] ?? [];

        foreach ($candidates as $route) {
            $match = $this->match($route['pattern'], $path);
            if ($match === null) {
                continue;
            }

            $this->invoke($route['handler'], $request, $match);
            return;
        }

        if ($this->notFoundHandler !== null) {
            $this->invoke($this->notFoundHandler, $request, []);
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function normalizePattern(string $pattern): string
    {
        $normalized = '/' . ltrim($pattern, '/');
        return rtrim($normalized, '/') === '' ? '/' : rtrim($normalized, '/');
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        if ($regex === null) {
            return null;
        }

        $regex = '#^' . $regex . '$#';
        $result = preg_match($regex, $path, $matches);
        if ($result !== 1) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            $params[$key] = $value;
        }

        return $params;
    }

    private function invoke(Closure|string|array $handler, Request $request, array $params): void
    {
        if ($handler instanceof Closure) {
            $handler($request, $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = new $class();
            $instance->$method($request, $params);
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = new $class();
            $instance->$method($request, $params);
            return;
        }

        throw new \RuntimeException('Invalid route handler.');
    }
}

