<?php

namespace Ecoride\Ecoride\Database;

use Faker\Factory;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;

class Seeder
{

    private \Faker\Generator $faker;
    private \PDO $db;
    private ?MongoManager $mongo;
    private array $userIds = [];
    private array $voitureIds = [];
    private array $marqueIds = [];
    private array $covoiturageIds = [];
    // initialiser la connexion
    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
        $this->db = Database::getInstance()->getConnection();
        $this->mongo = MongoManager::getInstance();
    }
    //   cette fonction permet de lancer les differentes methodes de génération des données de test
    public function run(): void
    {
        echo "creation des donnees de test. \n";

        $this->clearExistingData();
        $this->seedMarques();
        $this->seedUsers();
        $this->seedVoitures();
        $this->seedUserRoles();
        $this->seedCovoiturages();
        $this->seedReservations();
        $this->seedAvis();
        $this->seedPreferences();

        echo "generation des donnees termiee avec succes ! \n";
    }
    // cette fonction permet de nettoyer les donnees exixtantes
    public function clearExistingData(): void
    {
        // desactiver les contraintes des clés etrangéres
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = [
            'preference',
            'avis',
            'role_admin',
            'reservation',
            'covoiturage',
            'voiture',
            'role_user',
            'user',
            'marque',
        ];
        foreach ($tables as $table) {
            $this->db->exec("TRUNCATE TABLE $table");
        }
        // reactiver les containtes 
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Nettoyer mongodb
        $this->mongo->getCollection('trajets_geolocalisation')->deleteMany([]);

        echo "donnees nettoyees";
    }

    // cette function permetra de creer des marques de façon aléatoir
    private function seedMarques(): void
    {
        echo "creation de marque de voiture \n";
        $marques = [
            'Renault',
            'Peugeot',
            'Citroën',
            'Volkswagen',
            'Ford',
            'BMW',
            'Mercedes',
            'Audi',
            'Toyota',
            'Nissan',
            'Hyundai',
            'Kia',
            'Fiat',
            'Opel',
            'Volvo',
            'Seat',
            'Skoda',
            'Mazda',
            'Honda',
            'Suzuki'
        ];

        foreach ($marques as $marque) {
            $stmt = $this->db->prepare("INSERT INTO marque (libelle) VALUES (?)");
            $stmt->execute([$marque]);
            $this->marqueIds[] = $this->db->lastInsertId();
        }

        echo count($marques) . "marques crees ";
    }
    // cette fonction permet de créer les users de façon aléatoir
    private function seedUsers(): void
    {
        echo "création des utilisateurs...\n";

        for ($i = 0; $i < 50; $i++) {
            $name = $this->faker->lastName;
            $firstname = $this->faker->firstName;

            $userData = [
                'nom' => $name,
                'prenom' => $firstname,
                'email' => $this->faker->unique()->email,
                'role_admin' => $this->faker->randomElement([1, 3, 7, 15]),
                'password' => password_hash('1234567890', PASSWORD_DEFAULT),
                'telephone' => $this->faker->phoneNumber, //générer un  numero de telephone 
                'adresse' => $this->faker->address,
                'pseudo' => $this->generateUniquePseudo($firstname, $name), // générer un pseudo unique
                'date_naissance' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'photo' => $this->faker->optional(0.3)->imageUrl(200, 200, 'people', true, $firstname),
                'date_creation' => $this->faker->dateTimeBetween('-1 years')->format('Y-m-d')
            ];
            $stmt = $this->db->prepare(
                "INSERT INTO user (nom, prenom, email, role_admin, password, telephone, adresse, pseudo, date_naissance, photo, date_creation)
                VALUES (:nom, :prenom, :email, :role_admin, :password, :telephone, :adresse, :pseudo, :date_naissance, :photo, :date_creation)"
            );
            $stmt->execute($userData);
            $this->userIds[] = $this->db->lastInsertId(); // Récupérer l'ID de l'utilisateur inséré
        }

        echo  count($this->userIds) . " utilisateurs crees ...\n";
    }
    // créer des pseudo unique de façon aléatoir
    private function generateUniquePseudo(string $firstname, string $name): string
    {
        $basePseudo = strtolower($firstname . '.' . $name);
        $pseudo = $basePseudo;
        $counter = 1;

        // Checker que le pseudo choisi ne se trouve pas deja en base de donnees
        while (true) {
            // preparer une requete pour compter combien d utilisateurs ont ce pseudo
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM user WHERE  pseudo = ?");
            // Je passe le pseudo courant en paramètre pour éviter les injections SQL.
            $stmt->execute([$pseudo]);
            $result = $stmt->fetch();
            // si le pseudo n'existe pas, on le retourne
            if ($result->count == 0) {
                break;
            }

            $pseudo = $basePseudo . $counter;
            $counter++;
        }

        return $pseudo;
    }

    private function seedVoitures(): void
    {
        echo "création des voitures de façon aléatoire. \n ";

        // Je prépare ici une liste de modèles associés à chaque marque.
        // Cela servira à générer des données réalistes et cohérentes.
        $modelesParMarque = [
            'Renault' => ['Clio', 'Mégane', 'Scénic', 'Captur', 'Kadjar', 'Twingo', 'Zoe'],
            'Peugeot' => ['208', '308', '3008', '5008', '2008', '508', 'Partner'],
            'Citroën' => ['C3', 'C4', 'C5', 'Berlingo', 'Cactus', 'DS3', 'Jumpy'],
            'Volkswagen' => ['Golf', 'Polo', 'Passat', 'T-Roc', 'Tiguan', 'Touran', 'Caddy'],
            'Ford' => ['Fiesta', 'Focus', 'Kuga', 'Puma', 'S-Max', 'Mondeo', 'Tourneo'],
            'BMW' => ['Série 1', 'Série 3', 'Série 5', 'X1', 'X3', 'X5', 'Série 7'],
            'Mercedes' => ['Classe A', 'Classe C', 'Classe E', 'GLA', 'GLC', 'GLE', 'Classe S'],
        ];
        // Récuperer toutes les marques de la base de données pour faire correspondre les modeles.
        $stmt = $this->db->query("SELECT marque_id, libelle From marque");
        // stocker le resultat dans $marques,
        $marques = $stmt->fetchAll();
        // Ce compteur servira à afficher à la fin combien de voitures ont été créées.
        $countVoiture = 0;

        // Je boucle sur tous les utilisateurs stockés dans $this->userIds
        // pour correspondre  une voiture à un utilisateur.
        foreach ($this->userIds as $userId) { // choisir une marque au hasard dans celles disponibles en base
            $marque = $this->faker->randomElement($marques);
            // je génère un nom de modèle aléatoire pour éviter les erreurs.
            $modeles = $modelesParMarque[$marque->libelle] ?? [$this->faker->word . ' ' . $this->faker->numberBetween(1, 9)];
            //  choisir un modèle aléatoire dans la liste de cette marque.
            $modele = $this->faker->randomElement($modeles);
            if ($this->faker->boolean(60)) {
                // Je prépare les informations de la voiture sous forme de tableau associatif
                // Ces données seront insérées dans la table `voiture`
                $infoVoiture = [
                    'modele' => $modele,
                    'immatriculation' => $this->generationImmatriculation(),
                    'energie' => $this->faker->randomElement(['0', '1']),
                    'couleur' => $this->faker->colorName,
                    'nb_places' => $this->faker->numberBetween(3, 20),
                    'date_premiere_immatriculation' => $this->faker->dateTimeBetween('-7 years, -1 weeks')->format('y-m-d'),
                    'user_id' => $userId,
                    'marque_id' => $marque->marque_id
                ];
                // Je prépare la requête SQL pour insérer une voiture dans la base.
                $stmt = $this->db->prepare("INSERT INTO voiture (modele,immatriculation,energie,couleur,nb_places,date_premiere_immatriculation, user_id,marque_id)VALUES (:modele,:immatriculation,:energie,:couleur,:nb_places,:date_premiere_immatriculation, :user_id,:marque_id)");

                $stmt->execute($infoVoiture);
                // Je recupére l'id de la voiture insérée
                $this->voitureIds[] = $this->db->lastInsertId();
                $countVoiture++;
            }
        }
        echo $countVoiture . "voitures crees \n";
    }
    /**
     * Genere un numero d'immatriculation 
     * @return string
     */
    private function generationImmatriculation(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '1234567890';

        return
            substr(str_shuffle($letters), 0, 2) . '-' .
            substr(str_shuffle($numbers), 0, 3) . '-' .
            substr(str_shuffle($letters), 0, 2);
    }

    private function seedUserRoles(): void
    {
        echo "Attribution des roles aux Utilisateurs...\n";

        // $stats est le tableau qui nous indiquera le nombre d'utilisateur avec le role passager,
        // le nombre d'utilisateur avec le role chauffeur
        // et le nombre d'utilisateur avec les deux roles.
        $stats = [
            'role_passager' => 0,
            'role_chauffeur' => 0,
            'role_passager_chauffeur' => 0
        ];
        foreach ($this->userIds as $userId) {
            $hasCar = $this->userHasCar($userId);

            if ($hasCar) {
                $this->assignRole($userId, 2);
                if ($this->faker->boolean(70)) {
                    // 70% des chauffeurs sont aussi passagers 

                    $this->assignRole($userId, 1);
                    $stats['role_passager_chauffeur']++;
                } else {
                    $stats['role_chauffeur']++;
                }
            } else {
                // utlisateur sans vehicule = passager
                $this->assignRole($userId, 1);
                $stats['role_passager']++;
            }
        }
        // Affichage de la repartition Chauffeur | Passager | Chauffeur-Passager
        $this->displayStatiscs($stats);
    }

    /**
     * Verifie si un utilisateur possede un vehicule en BDD
     * @param $userId l'identifiant de l'utilisateur dont on souhaite verifier la possession d'un vehicule
     * @return bool
     */
    private function userHasCar(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM voiture WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $userId]);

            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            echo "Erreur verification voiture pour utilisateur $userId: {$e->getMessage()}";
            return false;
        }
    }
    //cette fonction permet d'assigner un role a un utilisateur dans la table role_user
    private function assignRole($userId, $roleId): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO role_user(user_id, role_id)VALUES(?,?)");
            $stmt->execute([$userId, $roleId]);
        } catch (\PDOException $e) {
            // Ignorer les doublons
            if ($e->getCode() === '23000') {
                // Violation de contrainte d'unicite
                echo "role deja assigne: Utilisateur $userId -Role $roleId \n";
                return;
            } else {
                throw $e;
            }
        }
    }
    // cette fonction permet d'afficher le nombre de chauffeur, passager et chauffeur-passager
    private function displayStatiscs(array  $stats): void
    {
        echo "repartition des roles \n";
        echo "passagers:{$stats['role_passager']} \n";
        echo "chauffeurs:{$stats['role_chauffeur']} \n";
        echo "chauffeurs_passagers:{$stats['role_passager_chauffeur']} \n";
    }

    private function seedCovoiturages(): void
    {
        echo "création des covoiturages. \n";

        $villes = [
            'Paris',
            'Lyon',
            'Marseille',
            'Toulouse',
            'Nice',
            'Nantes',
            'Strasbourg',
            'Montpellier',
            'Bordeaux',
            'Lille',
            'Rennes',
            'Reims',
            'Le Havre',
            'Saint-Étienne',
            'Toulon',
            'Grenoble',
            'Dijon',
            'Angers',
            'Villeurbanne',
            'Le Mans'
        ];

        $countCovoiturage = 0;
        foreach ($this->voitureIds as $voitureId) {
            $stmt = $this->db->prepare("SELECT user_id FROM voiture WHERE voiture_id = :voiture_id");
            $stmt->execute(['voiture_id' => $voitureId]);
            $voiture = $stmt->fetch();
            $conducteurId = $voiture->user_id;

            //generer des covoiturages entre 0 et 10.
            $nbCovoiturages = $this->faker->numberBetween(0, 10);
            // Boucle pour créer autant de covoiturages que le nombre généré précédemment
            for ($i = 0; $i < $nbCovoiturages; $i++) {
                // Génère une date de départ aléatoire entre demain et dans un mois
                $dateDepart = $this->faker
                    ->dateTimeBetween('+1 days', '+1 months');
                $heureDepart = $this->faker->time('H:i:s');

                // Génère une durée de trajet aléatoire en minutes (entre 1h et 4h)
                $dureeTrajet = $this->faker->numberBetween(60, 240);
                // Sélectionne une ville de départ aléatoire parmi la liste $villes
                $lieuDepart = $this->faker->randomElement($villes);
                $lieuArrivee = $this->faker->randomElement(array_diff($villes, [$lieuDepart]));
                // Prépare les informations du covoiturage sous forme de tableau associatif
                $infoCovoiturage = [
                    'date_depart' => $dateDepart->format('Y-m-d'),
                    'heure_depart' => $heureDepart,
                    'lieu_depart' => $lieuDepart,
                    'date_arrivee' => $dateDepart->format('Y-m-d'),
                    // Heure d’arrivée calculée à partir de la date de départ + durée du trajet
                    'heure_arrivee' => $this->generateArrivalTime($dateDepart, $dureeTrajet),
                    'lieu_arrivee' => $lieuArrivee,
                    // Statut aléatoire du covoiturage parmi une liste donnée
                    'statut' => $this->faker->randomElement(['prevu', 'annule', 'en cours', 'termine', 'prevu', 'prevu']),
                    'nb_places' => $this->faker->numberBetween(1, 20),
                    'prix_personne' => $this->faker->numberBetween(5, 50),
                    'conducteur_id' => $conducteurId,
                    'voiture_id' => $voitureId,
                    // Date de création du covoiturage aléatoire entre il y a 1 mois et 1 semaine
                    'date_creation' => $this->faker->dateTimeBetween('-1 months', '-1 weeks')->format('Y-m-d')
                ];
                // Prépare la requête SQL d'insertion dans la table "covoiturage"
                // On utilise des paramètres nommés pour éviter les injections SQL
                $stmt = $this->db
                    ->prepare(
                        "INSERT INTO covoiturage (
                     date_depart, heure_depart, lieu_depart, date_arrivee, heure_arrivee, 
                     lieu_arrivee, statut, nb_places, prix_personne, conducteur_id, 
                     voiture_id, date_creation) VALUES ( 
                     :date_depart, :heure_depart, :lieu_depart, :date_arrivee, :heure_arrivee, 
                     :lieu_arrivee, :statut, :nb_places, :prix_personne, :conducteur_id, 
                     :voiture_id, :date_creation)"
                    );

                // Exécute la requête en liant automatiquement les valeurs de $infoCovoiturage
                $stmt->execute($infoCovoiturage);

                // Récupère l'identifiant du dernier covoiturage inséré et le stocke dans un tableau
                $this->covoiturageIds[] = $this->db->lastInsertId();
                $countCovoiturage++;
            }
        }
        //  faire le compte du nombre de covoiturage crées;
        echo " $countCovoiturage covoiturages crees \n";
    }

    private function generateArrivalTime($dateDepart, $dureeTrajet)
    {
        // Clone l'objet DateTime de départ pour ne pas modifier l’original
        $arrival = clone $dateDepart;
        // Ajoute la durée du trajet en minutes à la date/heure de départ  
        $arrival->modify("+{$dureeTrajet} minutes");

        return $arrival->format('H:i:s');
    }

    private function seedReservations(): void{
        echo "creation des reservations \n";
        $countReservation = 0;

        foreach($this->covoiturageIds as $covoiturageId)
        {
            $stmt= $this->db->prepare("SELECT nb_places, conducteur_id FROM covoiturage WHERE covoiturage_id = :covoiturage_id");

            $stmt->execute(['covoiturage_id'=>$covoiturageId]);
            $covoiturage = $stmt->fetch();
            $nbPlacesDispo= $covoiturage->nb_places;
            $conducteurId= $covoiturage->conducteur_id;

            // generer aléatoirement  des reservations en fonction des places disponibles
            $nbReservations= $this->faker->numberBetween(1, $nbPlacesDispo);

            $passagersAyantReserve = [];

            for($i=0; $i< $nbReservations; $i++){
                // Une reservation est faite uniquement aux utilisateurs autres que le chauffeur,
                  // Ou au utilisateurs n'ayant pas encore de reservation.
                  $passagersDispo = array_diff($this->userIds, [$conducteurId], $passagersAyantReserve);
                  if (empty($passagersDispo)) break;

                  $passagerId = $this->faker->randomElement($passagersDispo);
                $passagersAyantReserve[] = $passagerId;

                $infoResa = [
                    'passager_id' => $passagerId,
                    'covoiturage_id' => $covoiturageId,
                    'statut' => $this->faker->randomElement(['en attente', 'confirme', 'annule', 'confirme', 'confirme']),
                    'nb_place_reservee' => $this->faker->numberBetween(1, mt_rand(1, $nbPlacesDispo)),
                    'date_creation' => $this->faker->dateTimeBetween('-4 days')->format('Y-m-d')
                ];

                try {
                    $stmt = $this->db->prepare(
                        "INSERT INTO reservation (
                     passager_id, covoiturage_id, statut, nb_place_reservee, date_creation)  
                    VALUES (:passager_id, :covoiturage_id, :statut, :nb_place_reservee, :date_creation)");

                    $stmt->execute($infoResa);
                    $countReservation++;
                } catch (\PDOException $e) {
                    // Ignorer les doublons
                    continue;
                }
            }
           
        }
        echo "$countReservation reservations crees. \n";
    } 

    private function seedAvis(){
        echo "Création des avis \n";
        // un avis peut etre donne que lorsqu'un covoiturage est terminé,
        // on a besoin d'une reservation confirme et d'un covoiturage terminer.

        $stmt = $this->db->query("
        SELECT  r.passager_id, r.covoiturage_id, c.conducteur_id
        FROM reservation r
        JOIN covoiturage c ON r.covoiturage_id = c.covoiturage_id
        WHERE r.statut = 'confirme' AND c.statut = 'termine'
    ");

    //On récupère tous les résultats sous forme de tableau d’objets ou de tableaux associatifs
    $reservations = $stmt->fetchAll();
 // Compteur pour suivre combien d’avis seront créés au total
    $countAvis = 0;

    //  On suppose que seulement 60% des utilisateurs laissent un avis
    foreach ($reservations as $reservation) {
        // On suppose que seul 60% de nos utilisateurs vont donner des avis
        if ($this->faker->boolean(60)) {

            $infoAvis = [
                'commentaire' => $this->faker->optional(0.8)->realText($this->faker->numberBetween(50, 1000)),
                'note' => $this->faker->numberBetween(1, 5),
                'statut' => $this->faker->randomElement(['publie', 'modere', 'modere', 'modere']),
                'passager_id' => $reservation->passager_id,
                'conducteur_id' => $reservation->conducteur_id,
                'covoiturage_id'=> $reservation->covoiturage_id,
                'date_creation' => $this->faker->dateTimeBetween('-12 months')->format('Y-m-d')
            ];
           // Préparation d'une requête SQL d'insertion sécurisée dans la table avis;
            $stmt = $this->db->prepare(
                "INSERT INTO avis (commentaire, note, statut, passager_id, conducteur_id, covoiturage_id, date_creation)
                VALUES (:commentaire, :note, :statut, :passager_id, :conducteur_id, :covoiturage_id, :date_creation)");
//Exécution de la requête en liant les valeurs contenues dans $infoAvis.
            $stmt->execute($infoAvis);
            $countAvis++;
        }
    }

    echo  $countAvis . " avis crees.\n";
    }

    private function seedPreferences(): void
    {
        echo "Creations des preferences utilisateurs...\n";

        $countPreference = 0;
       // On parcourt tous les identifiants d’utilisateurs enregistrés
        foreach ($this->userIds as $userId) {
              // Vérifie si cet utilisateur possède le rôle "chauffeur" (role_id = 2)
        // On prépare une requête pour rechercher une correspondance dans la table role_user
            $stmt = $this->db->prepare("SELECT role_id FROM role_user WHERE user_id = ? AND role_id = 2");
            $stmt->execute([$userId]);

            $result = $stmt->fetch();
            //Si un résultat existe, cela veut dire que l'utilisateur est un chauffeur
            if ($result) {
                $preferences = [
                    ['propriete' => 'Musique autorisee', 'valeur' => 'non'],
                    ['propriete' => 'Animaux autorises', 'valeur' => 'non'],
                ];

                foreach ($preferences as $preference) {
                    $stmt = $this->db->prepare(
                        "INSERT INTO preference (propriete, valeur, conducteur_id) VALUES (?, ?, ?)"
                    );
                    //Exécute la requête en liant les valeurs de la préférence actuelle et l’ID du chauffeur
                    $stmt->execute([$preference['propriete'], $preference['valeur'], $userId]);

                    //Incrémente le compteur total de préférences créées
                    $countPreference++;
                }
            }
        }
        echo " $countPreference preferences creees.\n";
    }
}
