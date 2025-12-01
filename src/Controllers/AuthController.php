<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;

class AuthController extends Controller{ 
    public function __construct()
    {
        parent::__construct();
    }


    public function register(): void
    {
        $this->service->require_guest();
        $this->renderView('auth/register', [
            'title' => "Inscription | " . APP_NAME 
        ]);
    }
    public function handle_Register(): void
    {
        $this->service->require_guest();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $userData = [
            'pseudo' => htmlspecialchars($_POST['pseudo']) ?? '',
            'email' => htmlspecialchars($_POST['email']) ?? '',
            'password' => htmlspecialchars($_POST['password']) ?? '',
            'password_confirm' => htmlspecialchars($_POST['password_confirm']) ?? '',
            'credits' => 20
        ];

        // Validation du mot de passe
        if ($userData['password'] !== $userData['password_confirm']) {
            $this->session->set_flash('error', "Les deux mots de passes ne correspondent pas.");
            $this->redirect('/register');
        }

        if (mb_strlen($userData['password']) < 8) {
            $this->session->set_flash('error', "Le mot de passe doit contenir au moins 8 caracteres.");
            $this->redirect('/register');
        }

        // Supprimer la confirmation du mot de passe
        unset($userData['password_confirm']);

        if ($this->auth->register($userData)) {
            $this->session->set_flash('success', "Inscription reussie!");
            $this->redirect('/login');
        } else {
            $this->redirect('/register');
        }
    }
    public function login(): void
    { $this->service->require_guest();

        $this->renderView('auth/login', ['title' => "Connexion |" . APP_NAME]);
    }

    public function handle_login(): void{
        // si l'utilisateur est connecté le rediriger vers sa page profile.
        $this->service->require_guest();

        // si la page n'est pas accedee en POST ,on redirige,il y a violation de protocol
        if($_SERVER['REQUEST_METHOD']!=='POST'){
            $this->redirect('/login');
        }

        $identifier = sanitize($_POST['pseudo'] ?? '');
        $password= sanitize($_POST['password']?? '');

        if(empty($identifier)|| empty($password)){
            $this->session->set_flash('error',"veuillez remplir tous les champs");
            $this->redirect('/login');
        }

        // je tchecke si le remember est cocher.
        $remember= isset($_POST['remember_me']);

        if($this->auth->attempt_to_connect($identifier, $password, $remember)){
            // si cette fonction renvoie un test positif ,je connecte l'utilisateur et je le redirige vers sa page profil.

            $this->session->set_flash('succes', 'connexion reussie');
            $this->redirect('/profile', ['pseudo' => $identifier]);
        }else{
            // sinon l'utilisateur sera rediriger vers le formulaire de connexion.
            $this->session->set_flash('error','pseudo ou mot de passe incorrect.');
            $this->redirect('/login');
        }
    }
    public function logout(): void
    {
        $this->service->logout();
    }

}
