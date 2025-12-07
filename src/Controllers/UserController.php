<?php 
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Services\UserService;

class UserController extends Controller{
    protected UserService $userService;
    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();

    }


    public function profile(): void{
        $this->service->require_auth();
        $this->renderView('profile/profile', ['title' => "profile de nadia | ". APP_NAME]);
    }

    public function handle_add_preference(): void
    {
        $this->service->require_auth();

        $sessionUser = $this->service->get_connected_user();
        $user = $this->userModel->find_by_id($sessionUser['id']);

        $propertyData = [
            sanitize($_POST['property'] ?? '') => isset($_POST['value']) ? 'oui' : 'non'
        ];


        if ($this->userService->add_preference($sessionUser['id'], $propertyData)) {
            $this->userService->update_user_session($user);
            $this->session->set_flash('success', "Preference ajoutee avec success");
        } else {
            $this->session->set_session_data('formErrors', $this->userService->get_errors());
            $this->session->set_session_data('oldFormData', $propertyData);
        }
        $this->redirect('profile', ['pseudo' => $user->pseudo]);
    }
}