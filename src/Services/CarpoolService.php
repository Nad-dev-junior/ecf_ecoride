<?php
namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database ;
use Ecoride\Ecoride\Core\Service;
use Ecoride\Ecoride\Models\CarpoolModel ;

class CarpoolService extends Service{
    private \PDO $connection;
    private CarpoolModel $carpoolModel;

    public function __construct(){
        parent::__construct();
        $this->connection = Database::getInstance()->getConnection();
        $this->carpoolModel = new CarpoolModel();
    }

    public function can_user_participate(int $carpoolId, int $passengerId, int $seats = 1){
        $errors = [];
        $carpoolDetails = null;
        try{
            $carpoolDetails = $this->carpoolModel->get_carpool_details($carpoolId);

            if(!$carpoolDetails){
                return[
                    'can_participate' => false,
                    'errors' => ["Covoiturage non disponible."],
                    'carpool' => null
                ];
            }

                // 1. Si l'utililisateur a deja fait une reservation, on lui indique qu'il est deja inscrit a ce covoiturage.
                if($this->carpoolModel->is_user_already_registered($carpoolId, $passengerId)){
                    $errors[] = "Vous etes deja inscrit a ce covoiturage.";
                }

                // 2. s'il n'y a plus de place disponible.
                $leftSeats = $carpoolDetails['general']['places_restantes'];
                if($leftSeats < $seats){
                    $errors[] = "Plus que $leftSeats places restantes.";
                }
            // 3.Si pas assez de credit informer passager
            $userCredits = $this->carpoolModel->get_user_credits($passengerId);
            $coast = $carpoolDetails['general']['tarif'];
            $totalCoast = $coast * $seats;
            if($userCredits < $totalCoast){
                $missedCredits = $totalCoast - $userCredits;
                $errors[] = "Credits insuffisants. Il vous en faut $missedCredits.";
            }

            // 4. Verifier que l'utilisateur n'est pas chauffeur du covoiturage demandé.
            if(!$passengerId == $carpoolDetails['conducteur']['id']){
                $errors[] = "Vous ne pouvez pas participer a votre covoiturage.";
            }

            return[
                'can_participate' =>empty($errors),
                'errors' => $errors,
                'carpool' => $carpoolDetails,
                'user_credits'=>$userCredits,
                'coast' => $coast,
                'totalCoast' => $totalCoast,
                'seats' => $seats,
                'credits_after' => $userCredits - $totalCoast
            ];
        } catch (\Exception $e){
            error_log("Erreur procedure de participation: {$e->getMessage()}");
            return[
                'can_participate' => false,
                'errors' => ["Une erreur est survenue lors de l'inscription au covoiturage."],
                'carpool' => $carpoolDetails
            ];
        }
    }

    public function participate(int $carpoolId, int $passengerId, int $seats = 1): array
    {
        // Verifier si l'utilisateur peut participer au covoiturage
        $check = $this->can_user_participate($carpoolId, $passengerId, $seats);

        if (!$check['can_participate']) {
            return [
                'success' => false,
                'error' => $check['errors'] ?? [$check['error']]
            ];
        }

        try {
            $this->connection->beginTransaction();
            // Deduire les credits de l'utilisateur
            $totalCoast = $check['totalCoast'];
            $this->carpoolModel->deduct_user_credits($passengerId, $totalCoast);

            // Preleve les commissions de la plateforme
            // Implementer une methode platform_commisions_fees(float $amount) dans une table specifique sur mongoDB
            $commissions = 2 * $seats;

            // Creer la reservation
            $reservationId = $this->carpoolModel->create_reservation($passengerId, $carpoolId, $seats);


            // Gains du chauffeur
            $driverEarnings = ($check['carpool']['general']['tarif'] - 2) * $seats;

            // Journaliser la transaction
            $this->carpoolModel->log_transaction($passengerId, $carpoolId, $reservationId, $totalCoast, $commissions, $driverEarnings);

            $this->connection->commit();

            return [
                'success' => true,
                'reservation_id' => $reservationId,
                'coast' => $check['carpool']['general']['tarif'],
                'total_coast' => $totalCoast,
                'commissions' => $commissions,
                'driver_earning' => $driverEarnings,
                'remaining_credits' => $check['credits_after'],
            ];

        }catch (\Exception $e) {
            error_log("Erreur confirmation de reservation du covoiturage: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => ["Erreur confirmation de reservation du covoiturage."],
            ];

        }

    }
}