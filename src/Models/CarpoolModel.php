<?php

namespace Ecoride\Ecoride\Models;
use Ecoride\Ecoride\Core\Model;

class CarpoolModel extends Model{
   

    public function __construct(){
        parent::__construct();
        
    }

    public function get_carpools(array $searchParams): false|array{

        $query="
        SELECT c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, c.date_arrivee, c.heure_arrivee, c.lieu_arrivee, c.statut as statut_covoiturage, c.nb_places as capacite_covoiturage, c.prix_personne, c.conducteur_id, c.voiture_id, c.date_creation, v.voiture_id, v.modele, v.immatriculation, m.libelle as marque, u.nom, u.prenom, u.email, u.telephone, u.adresse, u.pseudo, u.credits, u.role_admin, u.date_naissance, u.photo, u.date_creation, 
        (
            SELECT SUM(r.bn_place_reservee) FROM reservation r WHERE c.covoiturage_id = r.covoiturage_id AND r.statut = 'confirme'
        ) as reservations_covoiturage,
        (
            SELECT(capacite_covoiturage -IFNULL(reservations_covoiturage, 0))
        )as places_restantes, (
            SELECT AVG(note) FROM avis a WHERE a.conducteur_id = c.conducteur_id AND a.statut = 'publie'
        ) as note_chauffeur
        FROM covoiturage c JOIN voiture v ON c.voiture_id= v.voiture_id JOIN  marque m ON v.marque_id= m.marque_id JOIN user u ON c.conducteur_id= u.user_id
        LEFT JOIN(
            SELECT covoiturage_id, SUM(nb_place_reservee) as places_reservees FROM reservation WHERE statut= 'confirme' GROUP BY covoiturage_id
        )r ON c.covoiturage_id= r.covoiturage_id
        WHERE c.statut= 'prevu'
        AND(c.nb_places - IFNULL(places_reservees, 0)) > 0
        ";

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

        try {
            return array_map(function ($result) {
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
                    'is_ecologic' => (int)$result->energie === 1,
                ];
            }, $results);
        } catch (\Exception $e) {
            error_log("Erreur de formatage: {$e->getMessage()}");
            return [];
        }


    }

    public function get_next_available_date(string $start, string $end, $date) {
        $query = "
            SELECT  MIN(c.date_depart) as prochaine_date
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
}