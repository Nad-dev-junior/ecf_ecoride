<?php

namespace Ecoride\Ecoride\Models;

use Ecoride\Ecoride\Core\Model;
use MongoDB\BSON\UTCDateTime;

class CarpoolModel extends Model
{

    private UserModel $userModel;
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function get_carpools(array $searchParams): false|array
    {
        $query = "SELECT
         c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, c.date_arrivee, 
               c.heure_arrivee, c.lieu_arrivee, c.statut as statut_covoiturage, c.nb_places as capacite_covoiturage, c.prix_personne, 
               c.conducteur_id, c.voiture_id, c.date_creation, v.voiture_id, v.modele, v.immatriculation,
               v.energie, v.couleur, v.nb_places as capacite_vehicule, v.date_premiere_immatriculation,
               m.libelle as marque, u.nom, u.prenom, u.email, u.telephone, u.adresse, u.pseudo, u.credits,
               u.role_admin, u.date_naissance, u.photo, u.date_creation,
               (
                   SELECT SUM(r.nb_place_reservee)
                   FROM reservation r
                   WHERE c.covoiturage_id = r.covoiturage_id AND r.statut = 'confirme'
               ) as reservations_covoiturage, (
                   SELECT (capacite_covoiturage - IFNULL(reservations_covoiturage, 0))
               ) as places_restantes, (
                   SELECT AVG(note) FROM avis a WHERE a.conducteur_id = c.conducteur_id AND a.statut = 'publie'
               ) as note_chauffeur,

               --  FILTRE SUR LA DUREE MAXIMALE
                    TIMESTAMPDIFF(
                        MINUTE, 
                        CONCAT(c.date_depart, ' ', c.heure_depart), 
                        CONCAT(c.date_arrivee, ' ', c.heure_arrivee)
                    ) as duree_minutes
            
        FROM covoiturage c
        JOIN voiture v ON c.voiture_id = v.voiture_id
        JOIN marque m ON v.marque_id = m.marque_id
        JOIN user u ON c.conducteur_id = u.user_id
        LEFT JOIN (
            SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees
            FROM reservation
            WHERE statut = 'confirme'
            GROUP BY covoiturage_id
        ) r ON c.covoiturage_id = r.covoiturage_id
        WHERE c.statut = 'prevu'
        AND (c.nb_places - IFNULL(places_reservees, 0)) > 0";

        $conditions = [];
        $params = [];

        // 1. Si le lieu de depart est fourni
        if (!empty($searchParams['lieu_depart'])) {
            $conditions[] = " c.lieu_depart LIKE :lieu_depart";
            $params['lieu_depart'] = "%{$searchParams['lieu_depart']}%";
        }

        // 2. Si le lieu d'arrivee est fournie
        if (!empty($searchParams['lieu_arrivee'])) {
            $conditions[] = " c.lieu_arrivee LIKE :lieu_arrivee";
            $params['lieu_arrivee'] = "%{$searchParams['lieu_arrivee']}%";
        }

        // 3. Si la date de depart est fournie
        if (!empty($searchParams['date_depart'])) {
            $conditions[] = " c.date_depart = :date_depart";
            $params['date_depart'] = "{$searchParams['date_depart']}";
        }

        /*
         * === FILTRES AVANCES US4
         * */
        // Filtre ecologique (Pour des vehicules electrique uniquement)
        if (isset($searchParams['is_ecologic']) && $searchParams['is_ecologic'] === 'on') {
            $conditions[] = "v.energie = '1'";
        }

        // Filtrer le prix maximum
        if (!empty($searchParams['prix_max'])) {
            $conditions[] = "c.prix_personne <= :prix_max";
            $params['prix_max'] = (int)$searchParams['prix_max'];
        }

        // Filtrer sur la duree max
        if (!empty($searchParams['duree_max'])) {
            $dureeMaxMinutes = (int)$searchParams['duree_max'] * 60; // Convertir le temps renseigne dans le
            // formulaire de filtre. Si l'utilisateur renseigne une duree de 2h, on fera 2*60 = 120 minutes
            $conditions[] = "TIMESTAMPDIFF(MINUTE, CONCAT(c.date_depart, ' ', c.heure_depart), CONCAT(c.date_arrivee, ' ', c.heure_arrivee)) <= :duree_max";
            $params['duree_max'] = $dureeMaxMinutes;
        }

        // Filtrer sur la nete minimale
        if (!empty($searchParams['note_min'])) {
            $conditions[] = "(SELECT AVG(note) FROM avis a WHERE a.conducteur_id = u.user_id AND a.statut = 'publie') >= :note_min";
            $params['note_min'] = (float)$searchParams['note_min'];
        }

        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
            //$conditions = [
            //    " c.lieu_arrivee LIKE :lieu_arrivee",
            //    " c.lieu_depart LIKE :lieu_depart",
            //    " c.date_depart >= :date_depart"
            //]; => // "AND c.lieu_arrivee LIKE :lieu_arrivee AND c.lieu_depart LIKE :lieu_depart AND c.date_depart >=
            // :date_depart"
        }

        $query .= " ORDER BY c.date_depart, c.heure_depart";
        //        $query .= " LIMIT 10"


        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        // dump($results, $query, $params);
        try {
            return array_map(function ($result) {
                $dureeHeures = floor($result->duree_minutes / 60);
                $dureeMinutes = $result->duree_minutes % 60;
                return [
                    'id' => $result->covoiturage_id,
                    'date_depart' => $result->date_depart,
                    'heure_depart' => $result->heure_depart,
                    'lieu_depart' => $result->lieu_depart,
                    'date_arrivee' => $result->date_arrivee,
                    'heure_arrivee' => $result->heure_arrivee,
                    'lieu_arrivee' => $result->lieu_arrivee,
                    'capacite_covoiturage' => $result->capacite_covoiturage,
                    'reservations_covoiturage' => $result->reservations_covoiturage,
                    'places_restantes' => $result->places_restantes,
                    'prix_personne' => $result->prix_personne,
                    'statut_covoiturage' => $result->statut_covoiturage,
                    'conducteur' => [
                        'id' => $result->conducteur_id,
                        'pseudo' => $result->pseudo,
                        'nom' => $result->nom,
                        'prenom' => $result->prenom,
                        'email' => $result->email,
                        'adresse' => $result->adresse,
                        'telephone' => $result->telephone,
                        'photo' => $result->photo,
                        'note' => round($result->note_chauffeur ?? 0, 1),
                    ],
                    'vehicule' => [
                        'id' => $result->voiture_id,
                        'modele' => $result->modele,
                        'marque' => $result->marque,
                        'couleur' => $result->couleur,
                        'immatriculation' => $result->immatriculation,
                        'capacite_vehicule' => $result->capacite_vehicule,
                        'energie' => (int)$result->energie === 1 ? 'Electrique' : '',
                    ],
                    'duree' => [
                        'minutes' => $result->duree_minutes,
                        'affichage' => $dureeHeures > 0 ?
                            "{$dureeHeures} h" . ($dureeMinutes > 0 ? "{$dureeMinutes} min" : '') : "{$dureeMinutes} min"
                    ],

                    'is_ecologic' => (int)$result->energie === 1,
                ];
            }, $results);
        } catch (\Exception $e) {
            error_log("Erreur de formatage: {$e->getMessage()}");
            return [];
        }
    }

    public function get_next_available_date(string $start, string $end, $date)
    {
        $query = "SELECT 
             MIN(c.date_depart) as prochaine_date
            FROM covoiturage c
            LEFT JOIN (
                SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees
                FROM reservation
                WHERE statut = 'confirme'
                GROUP BY covoiturage_id
            ) r ON c.covoiturage_id = r.covoiturage_id
            WHERE c.statut = 'prevu'
            AND (c.nb_places - IFNULL(places_reservees, 0)) > 0
            AND c.lieu_depart LIKE ?
            AND c.lieu_arrivee LIKE ?
            AND c.date_depart >= ?
            -- ORDER BY c.date_depart
            LIMIT 1
        ";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                "%$start%",
                "%$end%",
                $date
            ]);

            $result = $stmt->fetch();
            return $result ? $result->prochaine_date : null;
        } catch (\PDOException $e) {
            error_log("Erreur rechercher procahine date: {$e->getMessage()}");
            return null;
        }
    }

    public function get_carpool_details(int $carpoolId)
    {
        try {
            $query = " SELECT 
             -- concernant le covoiturage
            c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, c.date_arrivee
            , c.heure_arrivee, c.lieu_arrivee, c.statut, c.nb_places as capacite_covoiturage, c.prix_personne,
            c.conducteur_id, c.voiture_id, 
            TIMESTAMPDIFF(MINUTE,CONCAT(c.date_depart, ' ', c.heure_depart),CONCAT(c.date_arrivee, ' ', c.heure_arrivee)) as duree_minutes,

           -- concernant le chauffeur 
            u.user_id ,u.nom, u.prenom, u.email, u.password, u.telephone, u.adresse, u.pseudo, u.photo, u.date_creation as date_inscription,
            -- concernant le vehicule
            v.voiture_id, v.modele, v.immatriculation, v.energie, v.couleur, v.nb_places as capacite_vehicule, v.date_premiere_immatriculation, v.user_id, m.libelle as marque,
            (
                SELECT AVG(note) FROM avis a WHERE a.conducteur_id = c.conducteur_id AND a.statut = 'publie'
            ) as note_chauffeur,
            (
                SELECT COUNT(*) FROM avis a WHERE a.conducteur_id = c.conducteur_id AND a.statut = 'publie'
            ) as nb_avis,
            (c.nb_places - COALESCE(
                (
                SELECT SUM(nb_place_reservee)
                FROM reservation 
                WHERE covoiturage_id = c.covoiturage_id AND reservation.statut = 'confirme'), 0)
                ) as places_restantes
            FROM covoiturage c
            JOIN user u ON c.conducteur_id = u.user_id
            JOIN voiture v ON c.voiture_id = v.voiture_id
            JOIN marque m ON v.marque_id = m.marque_id WHERE c.covoiturage_id = ? AND c.statut = 'prevu'
            ";

            $stmt = $this->connection->prepare($query);
            $stmt->execute([$carpoolId]);
            $carpool = $stmt->fetch();

            if (!$carpool) return null;
            $dureeHeures = floor($carpool->duree_minutes / 60);
            $dureeMinutes = $carpool->duree_minutes % 60;
            $noteMoyenne = round($carpool->note_chauffeur ?? 0, 1);
            $driverId = $carpool->conducteur_id;

            return [
                'id' => $carpool->covoiturage_id,

                'depart' => [
                    'date' => $carpool->date_depart,
                    'heure' => $carpool->heure_depart,
                    'lieu' => $carpool->lieu_depart,
                    'date_formatee' => date('d/m/y', strtotime($carpool->date_depart)),
                    'heure_formatee' => date('H:i', strtotime($carpool->heure_depart)),
                ],

                'arrivee' => [
                    'date' => $carpool->date_arrivee,
                    'heure' => $carpool->heure_arrivee,
                    'lieu' => $carpool->lieu_arrivee,
                    'date_formatee' => date('d/m/y', strtotime($carpool->date_arrivee)),
                    'heure_formatee' => date('H:i', strtotime($carpool->heure_arrivee)),
                ],
                'general' => [
                    'tarif' => $carpool->prix_personne,
                    'places_totals' => $carpool->capacite_covoiturage,
                    'places_restantes' => $carpool->places_restantes,
                    'statut' => $carpool->statut,
                    'ecologique' => (int)$carpool->energie === 1,
                    'duree' => [
                        'minutes' => $carpool->duree_minutes,
                        'affichage' => $dureeHeures > 0 ? "{$dureeHeures} h" . ($dureeMinutes > 0 ? "
                        {$dureeMinutes} min" : '')
                            : "{$dureeMinutes} min"
                    ]
                ],
                'conducteur' => [
                    'id' => $driverId,
                    'nom' => $carpool->nom,
                    'prenom' => $carpool->prenom,
                    'email' => $carpool->email,
                    'telephone' => $carpool->telephone,
                    'photo' => $carpool->photo,
                    'date_inscription' => $carpool->date_inscription,
                    'membre_depuis' => date('D d M Y', strtotime($carpool->date_inscription)),
                    'note' => $noteMoyenne,
                    'nb_avis' => $carpool->nb_avis ?? 0,
                    'preferences' => $this->userModel->get_preferences($driverId) ??
                        $this->userModel->get_preferences_with_mysql($driverId),
                ],

                'vehicule' => [
                    'id' => $carpool->voiture_id,
                    'marque' => $carpool->marque,
                    'modele' => $carpool->modele,
                    'immatriculation' => $carpool->immatriculation,
                    'couleur' => $carpool->couleur,
                    'energie' => $carpool->energie == '1' ? 'Electrique' : '',
                    'nb_places' => $carpool->capacite_vehicule,
                    'date_premiere_immatriculation' => $carpool->date_premiere_immatriculation,
                    'annee_circulation' => date('Y', strtotime($carpool->date_premiere_immatriculation)),
                ],

                'avis' => array_map(function ($avis) {
                    return [
                        'id' => $avis->avis_id,
                        'note' => $avis->note,
                        'commentaire' => $avis->commentaire,
                        'date' => $avis->date_creation,
                        'date_formatee' => date('d/m/y', strtotime($avis->date_creation)),
                        'passager' => [
                            'pseudo' => $avis->pseudo,
                            'nom' => $avis->nom,
                            'prenom' => $avis->prenom,
                            'photo' => $avis->photo,
                        ]
                    ];
                }, $this->get_driver_reviews($driverId)),


                'passagers' => array_map(function ($passager) {
                    return [
                        'id' => $passager->user_id,
                        'pseudo' => $passager->pseudo,
                        'nom' => $passager->nom,
                        'prenom' => $passager->prenom,
                        'photo' => $passager->photo,
                        'nb_places' => $passager->nb_place_reservee,
                        'date_inscription' => $passager->date_creation,
                        'date_formatee' => date('d/m/y', strtotime($passager->date_creation)),
                    ];
                }, $this->get_carpool_passengers($carpoolId)),

                'autres_vehicules' => array_map(function ($vehicule) {
                    return [
                        'marque' => $vehicule->marque,
                        'modele' => $vehicule->modele,
                        'immatriculation' => $vehicule->immatriculation,
                        'couleur' => $vehicule->couleur,
                        'nb_places' => $vehicule->nb_places,
                        'energie' => $vehicule->energie == '1' ? 'Electrique' : '',
                        'ecologique' => $vehicule->energie == '1'
                    ];
                }, $this->get_other_vehicles($driverId, $carpool->voiture_id))

            ];
        } catch (\PDOException $e) {
            error_log("Erreur recuperation details covoiturage {$e->getMessage()}");
            return null;
        }
    }
    // cette fonction me permet de recuperer les credits d' un utilisateur.
    public function get_user_credits(int $passengerId)
    {
        $query = "SELECT credits From user
         WHERE user_id = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$passengerId]);

        $result = $stmt->fetch();

        return $result ? $result->credits : 0;
    }

    // cette fonction me pemet de verifier si un utilisateur est deja enregistré sur un covoiturage.
    public function is_user_already_registered(int $carpoolId, int $passengerId): bool
    {
        $query = "SELECT COUNT(*) as count
        FROM reservation r 
        WHERE r.covoiturage_id = ?
        AND r.passager_id = ? 
        AND r.statut = 'confirme'";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$carpoolId, $passengerId]);
        $result = $stmt->fetch();

        return $result && $result->count > 0;
    }

    public function deduct_user_credits(int $passengerId, float $totalCoast): void
    {
        $query = "UPDATE user SET credits = credits - ? WHERE user_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$totalCoast, $passengerId]);
    }
    // creer une reservation
    public function create_reservation(int $passengerId, int $carpoolId, int $seats): int
    {
        $query = " INSERT INTO reservation (passager_id, covoiturage_id, statut, nb_place_reservee)
            VALUES (?, ?, 'confirme', ?)
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$passengerId, $carpoolId, $seats]);

        // on renvois l 'id de la reservation qui viens d' etre créer.
        return (int)$this->connection->lastInsertId();
    }


    // dans mon mongodb je vais creer une transaction 
    public function log_transaction(int $userId, int $carpoolId, int $reservationId, float $totalCoast, float $commission, float $driverEarnings): void
    {
        try {
            $collection = $this->mongo->getCollection('transaction');

            $transaction = [
                'user_id' => $userId,
                'carpool_id' => $carpoolId,
                'reservation_id' => $reservationId,
                'total_coast' => $totalCoast,
                'commissions' => $commission,
                'driver_earning' => $driverEarnings,
                'platform_earning' => $commission,
                'transaction_date' => new UTCDateTime(),
                'statut' => 'completed'
            ];

            $collection->insertOne($transaction);
        } catch (\Exception $e) {
            error_log("Erreur enregistrement de la transction dans mongoDB");
        }
    }

    public function get_reservation_details(int $reservationId): ?array
    {
        $query = "SELECT
                r.reservation_id, r.statut, r.nb_place_reservee, r.date_creation,
                u.nom, u.prenom, u.email, u.pseudo, c.covoiturage_id, c.lieu_depart, 
                c.lieu_arrivee, c.date_depart, c.heure_depart, c.prix_personne, 
                driver.pseudo as pseudo_conducteur, driver.nom as nom_conducteur,
                driver.prenom as prenom_conducteur
            FROM reservation r
            JOIN covoiturage c on r.covoiturage_id = c.covoiturage_id
            JOIN user u on r.passager_id = u.user_id
            JOIN user driver on c.conducteur_id = driver.user_id
            WHERE r.reservation_id = ?
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$reservationId]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return [
            'id' => $result->reservation_id,
            'nb_places' => $result->nb_place_reservee,
            'date_creation' => $result->date_creation,
            'statut' => $result->statut,
            'passager' => [
                'nom' => $result->nom,
                'prenom' => $result->prenom,
                'email' => $result->email,
                'pseudo' => $result->pseudo,
            ],
            'carpool' => [
                'id' => $result->covoiturage_id,
                'lieu_depart' => $result->lieu_depart,
                'lieu_arrivee' => $result->lieu_arrivee,
                'date_depart' => $result->date_depart,
                'heure_depart' => $result->heure_depart,
                'tarif' => (float)$result->prix_personne
            ],
            'conducteur' => [
                'nom' => $result->nom_conducteur,
                'prenom' => $result->prenom_conducteur,
                'pseudo' => $result->pseudo_conducteur,
            ]
        ];
    }
    //  cette fonction retourne les avis d'un conducteur.
    private function get_driver_reviews(int $driverId): false|array
    {
        $query = " SELECT 
                a.avis_id, a.commentaire, a.note, a.date_creation,
                u.nom, u.prenom, u.pseudo, u.photo
            FROM avis a 
            JOIN user u on a.passager_id = u.user_id
            WHERE a.conducteur_id = ? AND a.statut = 'publie'
            ORDER BY a.date_creation DESC
            LIMIT 10
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$driverId]);

        return $stmt->fetchAll();
    }

    //  cette fonction retourne les passagers d'un covoiturage
    private function get_carpool_passengers(int $carpoolId): false|array
    {
        $query = "SELECT 
                 r.passager_id, r.nb_place_reservee, r.date_creation, 
                 u.user_id, nom, prenom, pseudo, credits, photo
             FROM reservation r 
             JOIN user u on r.passager_id = u.user_id
             WHERE r.covoiturage_id = ?
             AND r.statut = 'confirme'
             ORDER BY r.date_creation
         ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$carpoolId]);

        return $stmt->fetchAll();
    }
    // cette fonction retourne les autres vehicules d'un conducteur
    private function get_other_vehicles(int $driverId, int $currentVehicleId): false|array
    {
        $query = "SELECT v.voiture_id, modele, immatriculation, energie, couleur, m.libelle as marque
             FROM voiture v 
             JOIN marque m on v.marque_id = m.marque_id
             WHERE v.user_id = ? AND v.voiture_id != ?
             ORDER BY v.date_creation DESC
             LIMIT 3
         ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$driverId, $currentVehicleId]);
        return $stmt->fetchAll();
    }
}
