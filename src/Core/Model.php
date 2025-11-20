<?php
namespace Ecoride\Ecoride\Core;

abstract class Model
{
   protected \PDO $connection;
    protected $mongo;
    protected $table;

    public function __construct(){
        // Connexion MariaDB
        $this->connection = Database::getInstance()->getConnection();
         // Connexion MongoDB
        $this->mongo = MongoManager::getInstance();
    }

    // Methodes communes pour MariaDB
     // Récupère une seule ligne de la table SQL en fonction de son ID
    public function find_by_id(int $id): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
   // Récupère toutes les lignes de la table SQL;
    public function find_all(): false|array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }
// Insère une nouvelle ligne dans la table SQL à partir d’un tableau associatif
    public function create(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' .implode(', :', array_keys($data));

        $stmt = $this->connection->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        return $stmt->execute($data);
    }


    // Methodes Pour MongoDB
    //Insère un document dans une collection MongoDB
    protected function mongoInsert($collection, $data): \MongoDB\InsertOneResult
    {
        $collection = $this->mongo->getCollection($collection);
        return $collection->insertOne($data);
    }
 /**
     * Recherche des documents dans une collection MongoDB
     *
     * @param string $collection Nom de la collection
     * @param array $filter      Critères de recherche
     * @return iterable          Curseur parcourable (équivalent à CursorInterface & Iterator)
     */
     // Si aucun filtre n’est fourni, retourne tous les documents
    protected function mongoFind($collection, $filter = []): iterable
    {
        $collection = $this->mongo->getCollection($collection);
        return $collection->find($filter);
    }
// recuperer les avis
    public function get_notices() {
        $stmt = $this->connection->query("
            SELECT a.*, u.photo, u.nom, u.prenom
            FROM avis a
            JOIN user u ON a.passager_id = u.user_id
            WHERE statut = 'publie' AND note >= '3.0'
            ORDER BY date_creation DESC
            LIMIT 12
        ");

        return $stmt->fetchAll();
    }
}
