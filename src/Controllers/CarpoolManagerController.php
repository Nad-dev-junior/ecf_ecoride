<?php 

namespace Ecoride\Ecoride\Controllers ;

use Ecoride\Ecoride\Core\Controller ;

use Ecoride\Ecoride\Models\CarpoolModel;
use Ecoride\Ecoride\Services\CarpoolManagerService;

class CarpoolManagerController extends Controller
{

    private CarpoolModel $carpoolModel;
    private CarpoolManagerService $carpoolManagerService;

    public function __construct()
    {
        parent::__construct();
        $this->carpoolModel = new CarpoolModel();
        $this->carpoolManagerService = new CarpoolManagerService();
    }

    public function my_carpools(): void
    {
        $this->auth->require_auth();
        $userId = $this->auth->get_connected_user_id();
        $user = $this->auth->get_connected_user();
        $driverCarpools = $this->carpoolModel->get_driver_carpools($userId);
        $passengerCarpools = $this->carpoolModel->get_passenger_carpools($userId);

        $data = [
            'driver_carpools' => $driverCarpools,
            'passenger_carpools' => $passengerCarpools,
            'title' => "Covoiturage de {$user['pseudo']}"
        ];

        $this->renderView('carpool/my-carpools', $data);
    }

    public function start_carpools(): void{
        $this->auth->require_driver();
        if(!isset($_GET['covoiturage_id'])) {
            $this->session->set_flash('error', "Aucun covoiturage selectionné.");
            $this->redirect('/carpool/my-carpools');
        }

        $carpoolId = (int)$_GET['covoiturage'];
        $driverId= $this->auth->get_connected_user_id();
        $can_user_start = 
        $this->carpoolManagerService->start_carpool($driverId, $carpoolId);

        if ($can_user_start['success']) {
            $flashMessage = <<<html
                {$can_user_start['message']} <br>
                {$can_user_start['notified_passengers']} passager(s) notifié(s)
            html;
            $this->session->set_flash('success', $flashMessage);
        } else {
            $this->session->set_flash('error', $can_user_start['errors']);
        }

        $this->redirect('/carpool/my-carpools');

    }
}