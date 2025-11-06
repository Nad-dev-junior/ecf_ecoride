<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Core\Session;
use Ecoride\Ecoride\Services\AuthService;

class AuthController extends Controller{ 
    protected Session $session;
    private AuthService $auth;
    public function __construct()
    {
        $this->session = new Session();
        $this->auth = new AuthService();
    }

    public function register(): void
    {
        $this->renderView('auth/register', [
            'title' => "Inscription | EcoRide"
        ]);
    }
    public function handleRegister(): void
    {
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
    {
        $this->renderView('auth/login', ['title' => "Connexion | Ecoride"]);
    }
}
