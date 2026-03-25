<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewPath = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        // Capture view content
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Render layout with content
        include $layoutPath;
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . APP_URL . $url);
        exit;
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
