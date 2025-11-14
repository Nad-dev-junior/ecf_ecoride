<?php 
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;

class UserController extends Controller{
    public function profile(): void{
        $this->renderView('profile/profile', ['title' => "profile de nadia | ". APP_NAME]);
    }
}