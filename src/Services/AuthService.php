<?php
namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Service;
use Ecoride\Ecoride\Core\Database;

class AuthService extends Service
{
   
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db=Database::getInstance()->getConnection();
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

    // cette fonction permet de se connecter en utilisant le pseudo, le mot de passe;
    public function attempt_to_connect($identifier, $password, $remember=false):bool
    {
        $user= $this->userModel->find_by_username_or_email($identifier);
        
        // si aucun n'utilisateur n' a été trouver retourne false
        if(!$user){
            return false;
        }
        
        if(!password_verify($password, $user->password)){
            return false;
        }

        if($remember){
            $token= bin2hex(random_bytes(32));
            $stmt= $this->db->prepare("UPDATE user SET remember_me = ? WHERE user_id= ?");
            $result= $stmt->execute([$token, $user->user_id]);
            if(!$result){
                return false;
            }

            // je stocke egalement le token dans le navigateur;
            setcookie('remember_me',$token, time()+60+60*24*30, '/',false, true);
            
        }

       
        $this->update_user_session($user);
        return true;
       
    }
    public function login_with_remeber_toker(): bool
    {
        if (isset($_COOKIE['remember_me'])) {
            $user = $this->userModel->find_by_rember_token($_COOKIE['remember_me']);
            if ($user) {
                $this->update_user_session($user);
                return true;
            }
        }

        return false;
    }

   
}