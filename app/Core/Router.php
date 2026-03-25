<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, string $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function delete(string $path, string $action): void
    {
        $this->routes['DELETE'][$path] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') $uri = '/';

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $action) {
            $regex = $this->patternToRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $this->call($action, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        View::render('errors/404', [], 'auth');
    }

    private function patternToRegex(string $pattern): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function call(string $action, array $params): void
    {
        [$class, $method] = explode('@', $action);
        $controller = 'App\\Controllers\\' . $class;
        $instance = new $controller();
        $instance->$method(...$params);
    }
}
