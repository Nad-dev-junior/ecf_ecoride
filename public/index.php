<?php
//  les chemins absolus
define('ROOT_DIR', dirname(__DIR__));                 
define('SRC_DIR',  ROOT_DIR . '/Src');
define('VIEW_DIR', SRC_DIR . '/Views');
define('CTRL_DIR', SRC_DIR . '/Controllers');
define('CORE_DIR', SRC_DIR . '/Core');
define('Vendor_DIR', SRC_DIR . '/Vendor');
define('Config_DIR', SRC_DIR . '/Config');

require_once Vendor_DIR. '/autoload.php';
require_once Config_DIR. '/config.php';

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
$router->get('/profil', 'UserController@profile');

$router->dispatch();


