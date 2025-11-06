<?php
namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Session;
use Ecoride\Ecoride\Models\UserModel;

class AuthService
{
    private $session;
    private $userModel;

    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new UserModel();
    }

    public function register(array $userData): bool
    {
        // Hasher le mot de passe
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        if (!$this->validate_register($userData)) {
            return false;
        }

        return $this->userModel->create($userData);
    }

    private function validate_register(array $userData): bool
    {
        // Validation des donnees
        if ($this->userModel->email_exist($userData['email'])) {
            $this->session->set_flash('error', "Cet email est deja utilise.");
            return false;
        }

        // Validation des donnees
        if ($this->userModel->pseudo_exist($userData['pseudo'])) {
            $this->session->set_flash('error', "Ce pseudo est deja utilise.");
            return false;
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flash('error', "Email invalide.");
            return false;
        }

        return true;
    }
}