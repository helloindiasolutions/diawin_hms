<?php
/**
 * Router System
 */

declare(strict_types=1);

namespace System;

class Router
{
    private array $routes = [];
    private string $currentPrefix = '';
    private array $currentMiddleware = [];

    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function any(string $path, callable|array $handler, array $middleware = []): self
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $path, $handler, $middleware);
        }
        return $this;
    }

    public function group(array $attributes, callable $callback): self
    {
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->currentMiddleware;

        if (isset($attributes['prefix'])) {
            $this->currentPrefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $mw = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->currentMiddleware = array_merge($this->currentMiddleware, $mw);
        }

        $callback($this);

        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;

        return $this;
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): self
    {
        $fullPath = $this->currentPrefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');

        $pattern = preg_quote($fullPath, '#');
        $pattern = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^/]+)', $pattern);

        $this->routes[$method][] = [
            'path' => $fullPath,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middleware' => array_merge($this->currentMiddleware, $middleware)
        ];

        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $route = $this->findRoute($method, $uri);

        if (!$route) {
            $this->handleNotFound();
            return;
        }

        $params = $this->extractParams($route['pattern'], $uri);

        if (!$this->runMiddleware($route['middleware'], $params)) {
            return;
        }

        $this->executeHandler($route['handler'], $params);
    }

    private function getUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = $_ENV['BASE_PATH'] ?? '';

        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return '/' . trim($uri ?: '/', '/');
    }

    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri)) {
                return $route;
            }
        }
        return null;
    }

    private function extractParams(string $pattern, string $uri): array
    {
        preg_match($pattern, $uri, $matches);
        $params = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);

        foreach ($params as $key => $value) {
            if (is_numeric($value) && (string) (int) $value === $value) {
                $params[$key] = (int) $value;
            }
        }

        return $params;
    }

    private function runMiddleware(array $middleware, array $params): bool
    {
        foreach ($middleware as $mw) {
            $result = true;

            if (is_callable($mw)) {
                $result = $mw($params);
            } elseif (is_array($mw) && count($mw) >= 2) {
                [$class, $method] = $mw;
                $args = $mw[2] ?? [];
                $result = call_user_func_array([$class, $method], array_merge([array_values($params)], (array) $args));
            }

            if ($result === false)
                return false;
        }
        return true;
    }

    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, array_values($params));
        } elseif (is_array($handler) && count($handler) >= 2) {
            [$controller, $method] = $handler;
            if (is_string($controller)) {
                $controller = new $controller();
            }
            call_user_func_array([$controller, $method], array_values($params));
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);

        if (strpos($_SERVER['REQUEST_URI'], '/api') !== false) {
            Response::notFound('API endpoint not found');
        } else {
            echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 - Not Found</h1></body></html>';
        }
    }

    public static function redirect(string $url, int $status = 302): void
    {
        header('Location: ' . $url, true, $status);
        exit;
    }
}
