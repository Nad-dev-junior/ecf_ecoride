<?php

namespace Ecoride\Ecoride\Core;

use Ecoride\Ecoride\Models\UserModel;

class Service
{
    protected Session $session;
    protected UserModel $userModel;
    protected array $errors = [];

    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new UserModel();
    }

    public function get_connected_user()
    {
        return $this->session->get_session('user'); // $_SESSION['user']
    }

    public function get_connected_user_id()
    {
        return $this->session->get_session('user')['id'] ?? null; // $_SESSION['user']['id']
    }

    public function is_passenger(): bool
    {
        $user  = $this->get_connected_user();
        return $user && in_array('passager', $user['roles']);
    }

    public function is_driver(): bool
    {
        $user  = $this->get_connected_user();
        return $user && in_array('chauffeur', $user['roles']);
    }

    public function is_logged_in(): bool
    {
        return $this->session->has_session('user');
    }

}