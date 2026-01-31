<?php

namespace Ecoride\Ecoride\Core;

use MongoDB\Client;
use MongoDB\Collection;

class MongoManager
{
    const MONGO_DB_URI = 'mongodb://localhost:27017';
    const MONGO_DB_NAME = 'ecoride';

    // Instance unique de la classe (pattern Singleton)
    private static ?MongoManager $instance = null;
      // Objet Client utilisé pour établir la connexion à MongoDB
    private Client $client;
       // Base de données MongoDB sélectionnée
    private \MongoDB\Database $database;

  
      // Constructeur privé : empêche la création directe d’objets avec "new"
    // Il initialise la connexion à MongoDB et sélectionne la base de données
    private function __construct() { 
        $this->client = new Client(self::MONGO_DB_URI);
        $this->database = $this->client->selectDatabase(self::MONGO_DB_NAME);
    }

       // Méthode statique qui retourne une seule et unique instance de MongoManager
    // Si elle n'existe pas encore, elle est créée ici
    public static function getInstance(): ?MongoManager
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
 // Retourne une collection MongoDB à partir de son nom
    // Cela évite de devoir retaper tout le code de connexion à chaque fois
    public function getCollection($name): Collection
    {
        return $this->database->selectCollection($name);
    }

    public function getConnection(): Client
    {
        return $this->client;
    }
}