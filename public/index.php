<?php
require_once  '../vendor/autoload.php';
// require_once '../Config/config.php';
require_once  '../src/lib/helpers.php';


use Ecoride\Ecoride\Core\Router;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;

// Initialisation de la base de donnees
$db = Database::getInstance();
$mongoDB = MongoManager::getInstance();

// Initialisation du Router
$router = new Router();

// Creation des Routes
$router->get('/', 'HomeController@index');
$router->get('/trajets', 'RideController@index');
$router->get('/trajets/recherche', 'RideController@search');
$router->post('/trajets/creer', 'RideController@create');
// $router->get('/profil', 'AuthController@profile');
$router->get('/register', 'AuthController@register');
$router->post('/register/handle', 'AuthController@handleRegister');
$router->get('/login', 'AuthController@login');

$router->dispatch();


