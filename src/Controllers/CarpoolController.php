<?php
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\CarpoolModel;
use Ecoride\Ecoride\Services\AddressAutocompleteService;
use Ecoride\Ecoride\Services\CarpoolService;

class CarpoolController extends Controller{
    private CarpoolModel $carpoolModel;
    private AddressAutocompleteService $addressService;
    private CarpoolService $carpoolService ;
    public function __construct()
    {
        parent::__construct();
        $this->carpoolModel = new CarpoolModel();
        $this->addressService = new AddressAutocompleteService();
        $this->carpoolService = new CarpoolService();

    }

    public function index(){
        $data=[
            'title' => 'Trouver un trajet |' . APP_NAME,
            'carpools' => [],
            'searchParams' => []
        ];
        $this->renderView('Carpool/index', $data);
    }

    public function search(){
        $searchParams=[
            // fitre
            'lieu_depart' =>sanitize($_GET['lieu_depart'] ?? '') ,
            'lieu_arrivee' =>sanitize($_GET['lieu_arrivee'] ?? '') ,
            'date_depart' =>sanitize($_GET['date_depart'] ?? '') ,
            'nb_passagers' =>sanitize($_GET['nb_passagers'] ?? ''),

            // filtre avance
            'is_ecologic' => $_GET['is_ecologic'] ?? '' ,
            'prix_max' => $_GET['prix_max'] ?? '' ,
            'duree_max' => $_GET['duree_max'] ?? '',
            'note_min' => $_GET['note_min'] ?? '',

        ]; 
        $carpools = [];
        $nextAvailableDate = null;

        $hasCriteria = !empty($searchParams['lieu_depart']) || !empty($searchParams['lieu_arrivee']) ||
            !empty($searchParams['date_depart']);
        if ($hasCriteria) {
            $carpools = $this->carpoolModel->get_carpools($searchParams);
        
            // Si aucun resultat, trouver la procahine date
            if (empty($carpools) && !empty($searchParams['lieu_depart']) && !empty($searchParams['lieu_arrivee']) && !empty
                ($searchParams['date_depart'])) {
                // Mettre la logique de la prochaine date dispo

                $nextAvailableDate = $this->carpoolModel->get_next_available_date($searchParams['lieu_depart'],
                    $searchParams['lieu_arrivee'], $searchParams['date_depart']);
            }
        }
        $data = [
            'carpools' => $carpools,
            'title' => "Resultats pour {$searchParams['lieu_depart']} - {$searchParams['lieu_arrivee']} | " . APP_NAME,
            'searchParams' => $searchParams,
            'nextAvailableDate' => $nextAvailableDate,
            'activeFilters' => $this->get_active_filters($searchParams)
        ];

        $this->renderView('carpool/index', $data);

    }
// cette fonction nous permet d'afficher la vue détaillée d'un covoiturage.
    public function carpool_details(){
        $this->auth->require_auth();
        if(!isset($_GET['covoiturage'])){
            $this->redirect('404');
            
        }

        $carpoolId= (int)$_GET['covoiturage'];
            $seats = (int)$_GET['nb_passagers'] ?? 1;  
            $carpoolDetails = $this->carpoolModel->get_carpool_details($carpoolId);

            if(!$carpoolDetails){
                $this->session->set_flash('error',"Covoiturage non trouvé");
                return;
            }

            $userId= $this->auth->get_connected_user_id() ?? null;
            $canUserParticipate = $this->carpoolService->can_user_participate($carpoolId,$userId,$seats);
// dd($canUserParticipate);
            if(!$canUserParticipate['can_participate']){
                $this->session->set_flash('error', implode('<br>', $canUserParticipate['errors']));
            }

            $data = [
                'title' => "Trajet" . $carpoolDetails['depart']['lieu'] . "-" . $carpoolDetails['arrivee']['lieu'],
                'carpool' => $carpoolDetails,
                'user' => $this->userModel->find_by_id($userId),
                'canParticipate' => $canUserParticipate['can_participate'],
                'canUserParticipate' => $canUserParticipate
            ];
            $this->renderView('/carpool/details', $data);
   
        
    }
//   cette fonction va nous permetre de valider une reservation
public function handle_apply(): void
{
    $this->auth->require_auth();
    if (!isset($_POST['covoiturage'])) {
        error_log("Erreur parametre covoiturage dans le GET");
        $this->redirect('/carpool');
    }

    $carpoolId = (int)$_POST['covoiturage'];
    $seats = (int)$_POST['nb_passagers'] ?? 1;
    $userId = $this->auth->get_connected_user_id();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->session->set_flash('error', "Methode non autorisée.");
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }
//  on verifie si la case confirm est cochée
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'on') {
        $this->session->set_flash('error', "Vous devez confirmer votre participation");
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }

    // Participer au covoiturage
    $participation = $this->carpoolService->participate($carpoolId, $userId, $seats);

    if (!$participation['success']) {
        $this->session->set_flash('error', implode('<br>', $participation['errors']));
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }

    $user = $this->userModel->find_by_id($userId);
    $this->auth->update_user_session($user);
    $this->session->set_flash('success', "Votre reservation a bien ete prise en compte");

    $this->redirect("/carpool/apply-success", [
        'covoiturage' => $carpoolId,
        'reservation' => $participation['reservation_id'],
        'nb_passagers' => $seats
    ]);


}

public function apply_success(): void
{
    $this->auth->require_auth();

    $reservationId = (int)$_GET['reservation'];
    $carpoolId = (int)$_GET['covoiturage'];
    $seats = (int)$_GET['nb_passagers'];

    if ($reservationId === 0) {
        $this->session->set_flash('error', "Reservation Introuvable");
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }

    $reservation = $this->carpoolModel->get_reservation_details($reservationId);
    if (!$reservation) {
        $this->session->set_flash('error', "Reservation Introuvable");
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }

    // Verifier que la reservation appartient bien a l'utiliateur connecte
    $user = $this->auth->get_connected_user();
    $userEmail = $user['email'];
    if ($reservation['passager']['email'] !== $userEmail) {
        $this->session->set_flash('error', "Acces non autorisé");
        $this->redirect('/carpool/details', [
            'covoiturage' => $carpoolId,
            'nb_passagers' => $seats
        ]);
    }

    $data = [
        'reservation' => $reservation,
        'carpoolId' => $carpoolId,
        'title' => "Reservation No ". $reservation['id']. " | " . APP_NAME
    ];


    $this->renderView('carpool/apply-success', $data);
}

    public function autocomplete(): void
    {
        $query = $_GET['query'] ?? '';

        // Verifier qqu'omn a a faire a une requete AJAX
        if (empty($query) || mb_strlen($query) < 3) {                       
            $this->json([]);
            return;
        }

        $addresses = $this->addressService->search_address($query);

        $this->json($addresses);
    }

    private function get_active_filters(array $searchParams): array {
        $activeFilters = [];

        if (!empty($searchParams['is_ecologic']) && $searchParams['is_ecologic'] === 'on') {
            $activeFilters[] = ['name' => 'is_ecologic', 'label' => 'Ecologique: Oui'];
        }

        if (!empty($searchParams['prix_max'])) {
            $activeFilters[] = ['name' => 'prix_max', 'label' => "Prix max: {$searchParams['prix_max']} Credits"];
        }

        if (!empty($searchParams['duree_max'])) {
            $activeFilters[] = ['name' => 'duree_max', 'label' => "Duree max: {$searchParams['duree_max']} minutes"];
        }

        if (!empty($searchParams['note_min'])) {
            $activeFilters[] = ['name' => 'note_min', 'label' => "Note min: {$searchParams['note_min']}/5"];
        }

        return $activeFilters;
    }

}