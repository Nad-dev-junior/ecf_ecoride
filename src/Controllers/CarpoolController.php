<?php
namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\CarpoolModel;
use Ecoride\Ecoride\Services\AddressAutocompleteService;

class CarpoolController extends Controller{
    private CarpoolModel $carpoolModel;
    private AddressAutocompleteService $addressService;
    public function __construct()
    {
        parent::__construct();
        $this->carpoolModel = new CarpoolModel();

    }

    public function index(){
        $data=[
            'title' => 'Trouver un trajet |' . APP_NAME,
        ];
        $this->renderView('Carpool/index', $data);
    }

    public function search(){
        $searchParams=[
            'lieu_depart' =>sanitize($_GET['lieu_depart'] ?? '') ,
            'lieu_arrivee' =>sanitize($_GET['lieu_arrivee'] ?? '') ,
            'date_depart' =>sanitize($_GET['date_depart'] ?? '') ,
            'nb_passagers' =>sanitize($_GET['nb_passagers'] ?? '') ,
 
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
            '$searchParams' => $searchParams,
            'nextAvailableDate' => $nextAvailableDate
        ];

        $this->renderView('carpool/index', $data);

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


}