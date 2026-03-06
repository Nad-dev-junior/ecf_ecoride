<?php 
namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;
use Ecoride\Ecoride\Core\Service;


class CarpoolManagerService extends Service {

    private \PDO $connection;
    private ?MongoManager $mongoConnection;
    private EmailService $emailService;


    public function __construct()
    {
        parent::__construct();
        $this->connection = Database::getInstance()->getConnection() ;
        $this->mongoConnection=MongoManager::getInstance();
        $this->emailService = new EmailService();
    }

    public function start_carpool(int $driverId, int $carpoolId): array
    {
        //verifier les permissions
        $check = $this->can_user_manage_carpool($driverId, $carpoolId);
        if(!$check['can_manage'] || !$check['can_start']){
            return[
                'success' => false,
                'errors'=> $check['errors'] ?? "Impossible de demarrer ce covoiturage."
            ];
        }

        try{
            $this->connection->beginTransaction();

            // Mettre a jour le statut du covoiturage
            $carpoolUpdateStatus = $this->carpoolModel->update_carpool_status($carpoolId, $driverId,  'en_cours');
            if(!$carpoolUpdateStatus){
                throw new \Exeption("Echec lors de la mise a jour du statut de covoiturage.");
            }

              // Recuperer la liste
              $passengers = $this->carpoolModel->get_carpool_passengers($carpoolId);


            // Envoyer des emails de notification aux passagers 
            foreach ($passengers as $passenger){
                $this->emailService->send_carpool_started_notification(
                    $passenger->email,
                    $passenger->nom,
                    $check['carpool']['lieu_depart'],
                    $check['carpool']['lieu_arrivee'],
                    $check['carpool']['date_depart'],
                    $check['carpool']['heure_depart'],
                );
            }

            // Enregistrer l'element (dans mongoDb)
            $this->carpoolModel->log_carpool_event($carpoolId, 'started' , [
                'driver_id' => $driverId,
                'passenger_count' => count($passengers),
                'started_at' => new \DateTime()
            ]);
            $this->connection->commit();

            return[
                'success' => true,
                'message' => 'Covoiturage demarré avec succès.',
                'notified_passengers' => count($passengers),
            ];
        }
        catch (\Exception $e) {
            $this->connection->rollBack();
            error_log("Erreur demarrage de covoiturage {$e->getMessage()}");
            return [
                'success' => false,
                'errors' => "Erreur lors du demarrage du covoiturage.",
            ];
        }
    }

    public function can_user_manage_carpool(int $driverId, int $carpoolId): array{
        try{
            $carpool = $this->carpoolModel->get_carpool_details($carpoolId);
            if(!$carpool){
                return[
                    'can_manage' => false,
                    'errors' => "Covoiturage  introuvable."
                ];
            }


              // S'assurer que seul le conducteur puisse demarrer son covoiturage
              if ($carpool['conducteur']['id'] !== $driverId) {
                return [
                    'can_manage' => false,
                    'error' => "Vous n'etes pas le chauffeur de ce covoiturage"
                ];
            }

            //Verifier le statut du covoiturage
            $validStatus = ['prevu','en cours'];
            if(!in_array($carpool['general']['statut'], $validStatus)){
                return[
                    'can_manage' => false,
                    'errors' => "Ce covoiturage ne peut plus etre géré."
                ];
            }

            // Verifier la date 
            $startTime = new \DateTime($carpool['depart']['date'] . ' ' . $carpool['depart']['heure']);
            $now= new \DateTime();
            $canStart = $now >= $startTime && $carpool['general']['statut'] === 'prevu';
            $canEnd = $carpool['general']['statut'] === 'en_cours';

            return[
                'can_manage' => true,
                'can_start' => $canStart,
                'can_end' =>$canEnd,
                'carpool' =>[
                    'id'=> $carpool['id'],
                    'satut' => $carpool['general']['statut'],
                    'lieu_depart' => $carpool['depart']['lieu'],
                    'date_depart' => $carpool['depart']['date'],
                    'heure_depart' => $carpool['depart']['heure'],
                    'lieu_arrivee' => $carpool['arrivee']['lieu'],
                    'nb_passagers' => $carpool['general']['places_totales'] - $carpool['general']['places_restantes'],
                ]
                ];

        }catch(\Exception $e){
            error_log("Erreur de verification gestion covoiturage {$e->getMessage()} {$e->getFile()} : {$e->getLine()}");
            return [
                'can_manage' => false,
                'error' => "Erreur de verification."
            ];
        }
    }
}