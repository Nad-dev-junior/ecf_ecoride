<?php 

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Services\UserService;

class VehicleController extends Controller{
   private UserService $userService;

   public function __construct(){

    parent::__construct();
    $this->userService=new UserService();
   }
   
    public function add_car():void{
        // rediriger si utilisateur non authentifie
        $this->service->require_auth();
        $this->service->require_driver();

        $sessionUser = $this->service->get_connected_user();
        $user=$this->userModel->find_by_id($sessionUser['id']);

        $this->renderView('vehicle/add-car', [
            'title' => "Ajouter Un Vehicle |" . APP_NAME,
            'errors' => $this->session->get_session_data('formErrors') ?? [] ,
          $this->session->get_session_data('oldFormData') ?? []
        ]);

    }
   
    public function handle_add_car():void{
        $this->service->require_auth();
        $this->service->require_driver();
        $sessionUser= $this->service->get_connected_user();
        $user= $this->userModel->find_by_id($sessionUser['id']);
        $userId = $user->user_id;

        // info sur le vehicule du postulant,
        $brand= sanitize($_POST['brand'] ?? '');
        $brandId= null;
        if(!empty($brand)){
            $brandId= $this->vehicleModel->get_or_create_brand($brand);
        }

        $vehicleData=[
            'marque_id' => $brandId,
            'marque' => $brand,
            'modele'=> sanitize($_POST['model'] ?? ''),
            'immatriculation' => sanitize($_POST['license_plate'] ?? ''),
            'date_premiere_immatriculation' => sanitize($_POST['license_plate_date'] ?? ''),
            'couleur' => sanitize($_POST['color'] ?? ''),
            'nb_places' => sanitize($_POST['seats'] ?? ''),
            'energie' => isset($_POST['energie']) ? : 0,
        ];

        if ($this->userService->add_user_vehicle($userId, $vehicleData)){
            $this->userService->update_user_session($user);
            $this->session->set_flash('success', "Votre nouveau vehicule a ete pris en compte.");
            $this->redirect('profile', ['pseudo' => $user->pseudo]);
        }else{
            $this->session->set_flash('error',"Oups, Erreur lors de l'ajout du vehicule.");
            // Sauvegarder les donnes du formulaires et les erreurs

            $this->session->set_session_data('formErrors', $this->userService->get_errors());
            $this->session->set_session_data('oldFormData', $vehicleData);
            $this->redirect('add-car');
        }
    }
}