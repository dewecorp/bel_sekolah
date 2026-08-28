<?php
/**
 * Router sederhana: URL -> Controller@Method
 */

namespace Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct()
    {
        $this->basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Dukungan method spoofing via field _method (untuk form DELETE/PUT)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        if ($uri === '' ) $uri = '/';

        $pathParts = explode('?', $uri);
        $uri = $pathParts[0];

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '/?$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->callHandler($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }

    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);

        $namespace = 'App\\Controllers\\';
        $controllerClass = $namespace . $controllerName;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller tidak ditemukan: {$controllerClass}";
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Method tidak ditemukan: {$controllerClass}@{$method}";
            return;
        }

        $controller->{$method}(...$params);
    }
}