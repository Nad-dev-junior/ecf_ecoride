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
    private array $stats = [];

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
        // creation des donnees de test.

        $this->clearExistingData();
        $this->seedMarques();
        $this->seedUsers();
        $this->seedVoitures();
        $this->seedUserRoles();
        // $this->seedCovoiturages();
        // $this->seedReservations();
        // $this->seedAvis();
        // $this->seedMongoData();

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
        foreach ($this->userIds as $userId) {
            if ($this->faker->boolean(60)) {
                // choisir une marque au hasard dans celles disponibles en base
                $marque = $this->faker->randomElement($marques);
                // je génère un nom de modèle aléatoire pour éviter les erreurs.
                $modeles = $modelesParMarque[$marque->libelle] ?? [$this->faker->word . ' ' . $this->faker->numberBetween(1, 9)];
                //  choisir un modèle aléatoire dans la liste de cette marque.
                $modele = $this->faker->randomElement($modeles);

                // Je prépare les informations de la voiture sous forme de tableau associatif
                // Ces données seront insérées dans la table `voiture`
                $infoVoiture = [
                    'modele' => $modele,
                    'immatriculation' => $this->generationImmatriculation(),
                    'energie' => $this->faker->randomElement(['0', '1']),
                    'couleur' => $this->faker->colorName,
                    'nb_places' => $this->faker->numberBetween(3, 7),
                    'date_premiere_immatriculation' => $this->faker->dateTimeBetween('-8 years, -1 years')->format('y-m-d'),
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

    private function seedUserRoles(): void{
        echo "Attribution des roles aux Utilisateurs...\n";

          // $stats est le tableau qui nous indiquera le nombre d'utilisateur avec le role passager,
        // le nombre d'utilisateur avec le role chauffeur
        // et le nombre d'utilisateur avec les deux roles.
        $stats=[
            'role_passager' =>0,
            'role_conducteur' =>0,
            'role_passager_chauffeur' =>0
        ];
foreach($this->userIds as $userId){
    $hasCar = $this->userHasCar($userId);

     if($hasCar){
            $this->assignRole($userId,2);

            // 70% des chauffeurs sont aussi passagers 
            if($this->faker->boolean(70)){
                $this->assignRole($userId,1);
                $stats['role_passager_chauffeur']++;
            }else{
                     $stats['role_conducteur']++;
            }
        }else{
            // utlisateur sans vehicule = passager
            $this->assignRole($userId,1);
            $stats['role_passager']++;
          }
}
    
    // Affichage de la repartition 
    //Chauffeur|passager|chauffeur-passager
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
    private function assignRole($userId, $roleId):void{
        try{
            $stmt= $this->db->prepare("INSERT INTO role_user(user_id, role_id)VALUES(?,?)");
            $stmt->execute([$userId,$roleId]);
        }catch(\PDOException $e){
            // Ignorer les doublons
            if($e->getCode() === '23000'){
                // Violation de contrainte d'unicite
                echo "role deja assigne: Utilisateur $userId -Role $roleId \n";
                return;
            }else{
                throw $e;
            }
        }
    }
// cette fonction permet d'afficher le nombre de chauffeur, passager et chauffeur-passager
    private function displayStatiscs(array $stats):void{
        echo "repartition des roles \n";
        echo "passagers:{$stats['role_passager']} \n";
        echo "chauffeurs:{$stats['role_conducteur']} \n";
        echo "chauffeurs_passagers:{$stats['role_passager_chauffeur']} \n";
    }
}