<?php
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller ;
use Ecoride\Ecoride\Services\UserService;


class PartnerController extends Controller {
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService= new UserService();
    }
    
    public function become_partner(): void{
        // rediriger si utilisateur non authentifié;
         $this->service->require_auth();

             //   je recupére les ionformations de l'utilisateur stocker en session via son id
         $sessionUser= $this->service->get_connected_user();
         $user= $this->userModel->find_by_id($sessionUser['id']);


        //  rediriger vers profil si utilisateur deja chauffeur;
        if($this->userModel->is_driver($user->user_id)){
            $this->session->set_flash('info',"vous etes deja partenaire/Chauffeur");
            $this->redirect('profile',[
                'pseudo' => $user->pseudo
            ]);
        }

        $this->renderView('profile/become-partner', [
            'title' => "Devenir Partenaire |" . APP_NAME,
            'user' => $user,
            'errors' =>$this->session->get_session_data('formErrors') ?? [] ,
            'oldData' => $this->session->get_session_data('oldFormData') ?? []
                  
        ]);
    } 

    /**
     * @throws \Exception
     */

    //  cette fonction va faire le traitement du formulaire devenire partenaire
     public function handle_become_partner(): void{
        $this->service->require_auth();

        $sessionUser = $this->service->get_connected_user();
        $user= $this->userModel->find_by_id($sessionUser['id']);

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->redirect('profile', ['pseudo' =>$user->pseudo]);
            return; 
        }

        // info sur le postulant 

        $profileData= [
            'nom' => sanitize($_POST['name'] ?? ''),
            'prenom' => sanitize($_POST['firstname'] ?? ''),
            'prenom' => sanitize($_POST['firstname' ?? '']),
            'telephone' => sanitize($_POST['phone'] ?? ''),
            'adresse' => sanitize($_POST['address'] ?? ''),
            'date_naissance' => sanitize($_POST['birthdate'] ?? ''),
        ];

        // info sur le vehicule du postulant
        $brand = sanitize($_POST['brand'] ?? '');
        $brandId= null;

        if(!empty($brand)){
            $brandId= $this->vehicleModel->get_or_create_brand($brand);
        }

        $vehicleData=[
            'marque_id' => $brandId,
            'marque' => $brand,
            'modele' => sanitize($_POST['model'] ?? ''),
            'immatriculation' => sanitize($_POST['license_plate']?? ''),
            'date_premiere_immatriculation' => sanitize($_POST['license_plate_date'] ?? ''),
            'couleur' => sanitize($_POST['color'] ?? ''),
            'nb_places' => sanitize($_POST['seats'] ?? ''),
            'energie' => isset($_POST['energie']) ? 1 : 0,
        ];

        // preferences
        $preferencesData = [
            'animaux' => isset($_post['animal']) ? 'oui' : 'non',
            'fumeurs' => isset($_POST['smoker'])? 'oui' : 'non' ,
        ];

        if($this->userService->complete_partner_profile($user->user_id, $profileData, $vehicleData, $preferencesData)){
            $this->userService->update_user_session($user);
            $this->session->set_flash('sucess', "Félicitation , vous etes desormais partenaire chauffeur.");
            $this->redirect('profile', ['pseudo'=> $user->pseudo]);
        }else{
            $this->session->set_flash('error', "Oups, Une erreur est survenue.");
            // Sauvegarder les donnees du formulaires et les erreurs
            $this->session->set_session_data('formErrors', $this->userService->get_errors());
            $this->session->set_session_data('oldFormData', array_merge(
                $profileData, $vehicleData, $preferencesData
            ));
            $this->redirect('become-partner');
        }
     }
    
}