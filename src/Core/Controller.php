<?php
namespace Ecoride\Ecoride\Core;

use Ecoride\Ecoride\Models\UserModel;
use Ecoride\Ecoride\Services\AuthService;
use http\Client\Curl\User;


 
class Controller
{
  protected Session $session;
  protected AuthService $auth;
  protected Service $service;
  protected UserModel $userModel;

  public function __construct()
  {
    $this->session= new Session();
    $this->auth= new AuthService();
    $this->service = new Service();
    $this->userModel = new UserModel();
  }
 protected function redirect(string $path, $params=[]): void
    {
        // Détection du protocole (http ou https)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
        // On retire la partie public/index.php ou index.php selon le cas
        $basePath = str_replace(['public/index.php', 'index.php'], '', $scriptName);
        $basePath = rtrim($basePath, '/');
    
        // Nettoyage du chemin cible
        $path = '/' . ltrim($path, '/');
    
        // Construction de l’URL finale complète
        $url = "$protocol://$host$basePath$path";

    // je check si le tableau des parametres n'est pas vide. S'il n'est pas vide, dans
        // ce cas, je vais faire un traitement de ce qu'il contient
        if (!empty($params)) {
            // Filtrer les parametre null ou vide, On conserve uniquement les parametres non null
            // Tous les parametres null sont ignore
            $filteredParams = array_filter($params, function($value) {
                return $value !== null && trim((string)$value) !== '';
            });

            if (!empty($filteredParams)) {
                $url .= '?' . http_build_query($filteredParams);
            }
         
        }   
        // Redirection HTTP
        header("Location: $url");
        exit();
    }
    protected function renderView($view, $data = []): void
    {

        // Verifier le remember token au chargement des pages
        if (!$this->auth->is_logged_in()) {
            $this->auth->login_with_remeber_toker();
        }
        $sessionUser = $this->service->get_connected_user() ?? null;
        $globalData = [
            'currentUser' => $sessionUser,
            'user' => $this->userModel->find_by_id($sessionUser['id']  ?? null),
            'roles' => $this->userModel->get_user_roles($sessionUser['id'] ?? null),
            'isPassenger' => $this->userModel->is_passenger($sessionUser['id'] ?? null),
            'isDriver' => $this->userModel->is_driver($sessionUser['id'] ?? null),
            'isLoggedIn' => $this->service->is_logged_in(),
        ];

        $viewData = array_merge($globalData, $data);

        extract($viewData);
        require __DIR__ . "/../Views/partials/header.php";
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . "/../Views/partials/footer.php";
        
    }

    protected function json($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

  
}