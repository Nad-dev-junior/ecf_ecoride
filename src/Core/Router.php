<?php

namespace
Ecoride\Ecoride\Core;

class Router
{
    private array $routes = [];

    public function get($path, $callback): static
    {
        $this->routes['GET'][$path] = $callback;
        return $this;
    }

    public function post($path, $callback): static
    {
        $this->routes['POST'][$path] = $callback;
        return $this;
    }

    public function dispatch()
    {
        $url = $_GET['url'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes[$method] as $path => $callback) {
            if ($this->matchRoute($path, $url)) {
                return $this->executeCallback($callback);
            }
        }
        return
            $this->handleNotFound();
    }
    private function matchRoute($route, $url): bool
    {
        $route = trim($route, '/');
        $url = trim($url, '/');

        return $route === $url;
    }

    private function executeCallback($callback) {
        if(is_string($callback)) {
            list($controllerName, $method) = explode('@', $callback); //RideController@search
            $controllerName = 'Ridecontroller';
            $method = 'search';
            $controller = "Ecoride\\Ecoride\\Controllers\\$controllerName";

            if(class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    return $controllerInstance->$method();
                }

                return $this->handleError('Methode introuvable');
            }

            return $this->handleError('Controleur introuvable');
        }

        // Si callback invalide, alors, echec
        return $this->handleError("Callback Invalide.");
    }

    private function handleNotFound(): bool
    {
        http_response_code(404);
        $this->renderView('error/404');
        return false;
    }

    public function handleError(string $message): bool
    {
        //Facultatif
        error_log($message);
        http_response_code(500);
        $this->renderView('error/500');
        return false;

    }

    public function renderView($view, $data = []): bool
    {
        extract($data);
        require_once "../src/Views/$view.php";
        return true;
    }

   
}
