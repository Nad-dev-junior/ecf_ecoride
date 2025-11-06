<?php
namespace Ecoride\Ecoride\Controllers;
use Ecoride\Ecoride\Core\Controller;

class HomeController extends Controller{
    public function index(){
        $this->renderView('home/index', ['title'=>'Ecoride - Accueil']);
        
    }
}