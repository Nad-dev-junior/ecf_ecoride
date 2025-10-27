<?php
namespace Ecoride\Ecoride\Database;

use Faker\Factory;
use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\MongoManager;

Class Seeder{

    private \Faker\Generator $faker;
    private \PDO $db;
    private ?MongoManager $mongo;
    private array $userIds=[];
    private array $marqueIds=[];

    // initialiser la connexion
    public function __construct(){
       $this->faker = Factory::create("fr_FR");
       $this->db = Database::getInstance()->getConnection();
       $this->mongo = MongoManager::getInstance();
    }
//   cette fonction permet de lancer les differentes methodes de génération des données de test
public function run():void{
    // creation des donnees de test.
    
    $this->clearExistingData();
    $this->seedMarques();
    $this->seedUsers();
    // $this->seedVoitures();
    // $this->seedUserRoles();
    // $this->seedCovoiturages();
    // $this->seedReservations();
    // $this->seedAvis();
    // $this->seedMongoData();

    echo "generation des donnees termiee avec succes ! \n";
}
// cette fonction permet de nettoyer les donnees exixtantes
public function clearExistingData(): void{
    // desactiver les contraintes des clés etrangéres
    $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
 $tables= [
    'preference', 'avis', 'role_admin' ,'reservation',
    'covoiturage', 'voiture', 'role_user','user','marque',
 ];
 foreach($tables as $table){
    $this->db->exec("TRUNCATE TABLE $table");
}
// reactiver les containtes 
$this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

// Nettoyer mongodb
$this->mongo->getCollection('trajets_geolocalisation')->deleteMany([]);

echo "donnees nettoyees";
}

// cette function permetra de creer des marques de façon aléatoir
private function seedMarques(): void {
    echo"creation de marque de voiture";
    $marques= [
        'Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'Ford', 'BMW',
            'Mercedes', 'Audi', 'Toyota', 'Nissan', 'Hyundai', 'Kia',
            'Fiat', 'Opel', 'Volvo', 'Seat', 'Skoda', 'Mazda', 'Honda', 'Suzuki'
    ];
    
    foreach ($marques as $marque) {
        $stmt = $this->db->prepare("INSERT INTO marque (libelle) VALUES (?)");
        $stmt->execute([$marque]);
        $this->marqueIds[] = $this->db->lastInsertId();
    }

    echo count($marques)."marques crees ";
}
// cette fonction permet de créer les users de façon aléatoir
private function seedUsers(): void{
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
    while(true) {
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

}
