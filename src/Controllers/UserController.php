<?php 
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Core\Service;
use Ecoride\Ecoride\Models\UserModel;

class UserController extends Controller{
    protected UserModel $userModel;
    protected Service $service;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->service = new Service();

    }


    public function profile(): void{
        $this->renderView('profile/profile', ['title' => "profile de nadia | ". APP_NAME]);
    }
}