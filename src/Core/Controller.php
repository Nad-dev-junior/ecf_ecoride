<?php
namespace Ecoride\Ecoride\Core;

class Controller
{
    protected function renderView($view, $data = []): void
    {
        extract($data);
        require __DIR__ . "/../Views/{$view}.php";
    }

    protected function json($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function redirect($url): void
    {
        header("Location: $url");
        exit();
    }

}