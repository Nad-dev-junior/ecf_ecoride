<?php
namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\Service;

class UserService extends Service{

    private ValidationService $validationService;


    private array $profileErrors= [];
    private array $vehicleErrors= [];
    private array $preferenceErrors = [];

    private \PDO $connexion;

    public function __construct()
    {
        parent::__construct();
        $this->validationService = new ValidationService();
        $this->connexion = Database::getInstance()->getConnection();
    }

    /**
     * @throws \Exception
     */

    //  cette fonction va valider toute les informations qui seront entrées par l'utilisateur.
     public function validate_profile_data(array $profileData): array{
        $this->validationService->clear_errors();

        // Validation explicite et directe
        $this->validationService->validate_required('nom', $profileData['nom'] ?? '');
        $this->validationService->validate_max('nom',$profileData['nom'] ?? '' , 64);
        $this->validationService->validate_min('nom', $profileData['nom'] ?? '' , 3);


        $this->validationService->validate_required('prenom', $profileData['prenom' ?? '']);
        $this->validationService->validate_min('prenom', $profileData['prenom'] ?? '' , 3);

        $this->validationService->validate_required('telephone', $profileData['telephone'] ?? '');
        $this->validationService->validate_required('telephone', $profileData['telephone'] ?? '' , 20);
        $this->validationService->validate_phone('telephone', $profileData['telephone'] ?? '');

        
        $this->validationService->validate_required('adresse', $profileData['adresse'] ?? '');
        $this->validationService->validate_max('adresse', $profileData['adresse'] ?? '', 255);

        $this->validationService->validate_required('date_naissance', $profileData['date_naissance'] ?? '');
        $this->validationService->validate_date('date_naissance', $profileData['date_naissance'] ?? '');
        $this->validationService->validate_adult('date_naissance', $profileData['date_naissance'] ?? '');

        // ici je stocke les erreurs recupérés dans validateService
        $this->profileErrors = $this->validationService->get_errors();
        $isValid = !$this->validationService->has_errors();

        return[
            'errors' => $this->profileErrors,
            'isValid' => $isValid
        ];
     }


     public function validate_vehicle_data(array $vehicleData): array{

        $this->validationService->clear_errors();

        $this->validationService->validate_required('immatriculation', $vehicleData['immatriculation'] ?? '');
        $this->validationService->validate_max('immatriculation' , $vehicleData['immatriculation'] ?? '', 20);
        $this->validationService->validate_license_plate('immatriculation', $vehicleData['immatriculation'] ??'');
        $this->validationService->validate_unique('immatriculation',
        $vehicleData['immatriculation'] ?? '',
        'voiture',
        'immatriculation');


        $this->validationService->validate_date('date_premiere_immatriculation', $vehicleData['date_premiere_immatriculation'] ?? '');

        $this->validationService->validate_required('marque', $vehicleData['marque'] ?? '');
        $this->validationService->validate_max('marque', $vehicleData['marque'] ?? '', 64);

        $this->validationService->validate_required('modele', $vehicleData['modele'] ?? '');
        $this->validationService->validate_max('modele', $vehicleData['modele'] ?? '', 64);

        $this->validationService->validate_required('couleur', $vehicleData['couleur'] ?? '');
        $this->validationService->validate_max('couleur', $vehicleData['couleur'] ?? '', 64);

        $this->validationService->validate_required('nb_places', $vehicleData['nb_places'] ?? '');
        $this->validationService->validate_numeric('nb_places', $vehicleData['nb_places'] ?? '');

        $this->vehicleErrors = $this->validationService->get_errors();
        $isValid = !$this->validationService->has_errors();

        return [
            'errors' => $this->vehicleErrors,
            'isValid' => $isValid
        ];
     }

      /**
     * @throws \Exception
     */

     public function complete_partner_profile(int $userId, array $profileData, array $vehicleData, array $preferencesData): bool{
        $validateProfile = $this->validate_profile_data($profileData);
        $validateVehicle = $this->validate_vehicle_data($vehicleData); //[], 'isValid'
        // Validation en cascade
        if (!$validateProfile['isValid'] || !$validateVehicle['isValid']) {
            return false;
        }

        unset($vehicleData['marque']);
        $this->connexion->beginTransaction();
        try {
            // Completer le profil utilisateur
            $this->userModel->update_profile($userId, $profileData);

            // Ajouter le role chauffeur
            $this->userModel->add_driver_role($userId);

            // Creer le vehicule
            $this->vehicleModel->add_vehicle($userId, $vehicleData);

            // Sauvegarder preference MySQL
            $this->userModel->save_preferences_with_mysql($userId, $preferencesData);

            //  Sauvegarder preference MongoDB
            $preferences = [
                'animaux' => $preferencesData['animaux'] ?? false,
                'fumeurs' => $preferencesData['fumeurs'] ?? false,
            ];
            $this->userModel->save_prefrences_with_mongoDB($userId, $preferences);
            $this->connexion->commit();

            return true;
        } catch (\Exception $e) {
            error_log("Erreur completion profil partenaire: {$e->getMessage()}");
            $this->connexion->rollBack();
            return false;
        }
        
     }


     public function add_user_vehicle(int $userId, array $vehicleData): bool
     {
         if (!$this->validate_vehicle_data($vehicleData)['isValid']) {
             return false;
         }
 
         unset($vehicleData['marque']);
         try {
             $vehicleData['user_id'] = $userId;
             $this->vehicleModel->add_vehicle($userId, $vehicleData);
 
             return true;
         } catch (\PDOException $e) {
             error_log("Erreur ajout vehicule: {$e->getMessage()}");
             return false;
         }
     }
     public function add_preference(int $userId, array $preferencesArray): bool
     {
         $this->validationService->clear_errors();
         $this->validationService->validate_required('preference', array_keys($preferencesArray)[0]);
         $this->validationService->validate_max('preference', array_keys($preferencesArray)[0], 64);
 
         if ($this->validationService->has_errors()) {
             $this->preferenceErrors = $this->validationService->get_errors();
             return false;
         }
 
         $this->userModel->save_preferences_with_mysql($userId, $preferencesArray);
 
         $preference = [array_keys($preferencesArray)[0] => array_values($preferencesArray)[0] ?? false];
         $this->userModel->save_prefrences_with_mongoDB($userId, $preference);
 
         return true;
     }
 
     public function get_errors(): array
     {
         return array_merge($this->profileErrors, $this->vehicleErrors);
     }
}