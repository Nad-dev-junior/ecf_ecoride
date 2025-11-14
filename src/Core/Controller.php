<?php
namespace Ecoride\Ecoride\Core;
use Ecoride\Ecoride\Services\AuthService;

 
class Controller
{
  protected Session $session;
  protected AuthService $auth;

  public function __construct()
  {
    $this->session= new Session();
    $this->auth= new AuthService();
  }

    protected function renderView($view, $data = []): void
    {

        // Verifier le remember token au chargement des pages
        if (!$this->auth->is_logged_in()) {
            $this->auth->login_with_remeber_toker();
        }
        extract($data);
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

    // protected function redirect($url): void
    // {
    //     header("Location: $url");
    //     exit();
    // }
   protected function redirect(string $path): void
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
    
        // Redirection HTTP
        header("Location: $url");
        exit();
    }
}