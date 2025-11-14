<?php
require_once  '../vendor/autoload.php';
require_once '../Config/config.php';
require_once  '../src/lib/helpers.php';

use Ecoride\Ecoride\Core\Router;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;
use Whoops\run;
use Whoops\Handler\PrettyPageHandler;

$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());

$environment = $_ENV['APP_ENV'] ?? 'developement';
if ($environment === 'developement') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    $whoops->register();
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialisation de la base de donnees
$db = Database::getInstance();
$mongoDB = MongoManager::getInstance();


try{
    // Initialisation du Router
$router = new Router();
    // Creation des Routes
$router->get('/', 'HomeController@index');
$router->get('/register', 'AuthController@register');
$router->post('/register/handle', 'AuthController@handle_Register');
$router->get('/login', 'AuthController@login');
$router->post('/login/handle', 'AuthController@handle_login');
$router->get('/profile', 'UserController@profile');
$router->get('/logout', 'AuthController@logout');


// $router->get('/trajets', 'RideController@index');
// $router->get('/trajets/recherche', 'RideController@search');
// $router->post('/trajets/creer', 'RideController@create');
$router->dispatch();
}catch(Throwable $e) {
    if ($environment === 'developement') {
        throw $e;
    } else {
        error_log("Erreur: {$e->getMessage()} dans {$e->getFile()} : {$e->getLine()}");
        http_response_code(500);
        echo "Une erreur s'est preoduites. Notre equipe y travaille";
    }

}
