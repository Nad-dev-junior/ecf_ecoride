<?php

namespace
Ecoride\Ecoride\Core;

class Router
{
    /**
     * @param string $path Chemin de la route (ex: '/home')
     * @param mixed $callback Callback ou contrôleur@méthode à exécuter
     * @return static
     */

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

       // URL demandée
       $url = $_GET['url'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ;
        // Parcourt les routes enregistrées pour cette méthode
        foreach ($this->routes[$method] as $path => $callback) {
            // Si l'URL correspond à une route enregistrée
            if ($this->matchRoute($path, $url)) {
                // Exécute le callback associé à cette route
                return $this->executeCallback($callback);
            }
        }
        return
            /** 
             * @param string envoi une page 404 si la route n'est pas trouvée
             */
            $this->handleNotFound();
    }
    private function matchRoute($route, $url): bool
    {
        $route = '/' . trim($route, '/');
        $url = '/' . trim($url, '/');

        return $route === $url;
    }

    private function executeCallback($callback)
    {
        // Si le callback est une chaîne de type "Controller@method"
        if (is_string($callback)) {
            list($controllerName, $method) = explode('@', $callback);
            // Construit le namespace complet du contrôleur
            $controller = "Ecoride\\Ecoride\\Controllers\\$controllerName";
           
            // Vérifie si le contrôleur existe
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                // Vérifie si la méthode existe dans ce contrôleur
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
        require_once "../src/Views/{$view}.php";
        return true;
    }
}
//
