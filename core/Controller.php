<?php
/**
 * Base Controller + helper rendering view & JSON
 */

namespace Core;

class Controller
{
    // Data yang dibagikan ke SEMUA view (settings, user, dll)
    protected array $sharedData = [];

    public function __construct()
    {
        $this->sharedData = [
            'settings' => App::settings(),
            'currentUser' => Auth::user(),
            'baseUrl' => App::baseUrl(),
        ];
    }

    protected function view(string $view, array $data = [], string $layout = 'admin.php'): void
    {
        $data = array_merge($this->sharedData, $data);

        // Ekstrak data sebagai variabel di scope view
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View tidak ditemukan: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/' . $layout;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . App::url($path));
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        return $_POST[$key] ?? $body[$key] ?? $default;
    }

    protected function inputAll(): array
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        return array_merge($_POST, $body);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/auth/login');
        }
    }
}