<?php

namespace Ecoride\Ecoride\Models;

use Ecoride\Ecoride\Core\Database;
use Ecoride\Ecoride\Core\Model;
use Ecoride\Ecoride\Core\MongoManager;
use Ecoride\Ecoride\Core\RoleManager;
use InvalidArgumentException;
use MongoDB\BSON\UTCDateTime;

// cette classe gère les opérations liées aux utilisateurs dans la base de données.
class UserModel extends Model
{
    protected string $table = 'user';
   protected \PDO  $connection;

    public function __construct()
    {
        parent::__construct();
        $this->connection = Database::getInstance()->getConnection();
    }
    public function update_profile(int $userId, $profileData): bool
    {
        $allowedFields = [
            'nom', 'prenom', 'email', 'password', 'telephone', 'adresse',
            'pseudo', 'credits', 'role_admin', 'date_naissance', 'photo'
        ];
        $updates = [];
        $params = [];
        
        $profileData = [
            'nom' => sanitize($_POST['name'] ?? ''),
            'prenom' => sanitize($_POST['firstname'] ?? ''),
            'telephone' => sanitize($_POST['phone'] ?? ''),
            'adresses' => sanitize($_POST['address'] ?? ''),
            'date_naissance' => sanitize($_POST['birthdate'] ?? ''),
        ];

        foreach ($profileData as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = :$key"; //['nom' = ':nom', 'prenom' = ':prenom']
                $params[$key] = $value; // ['nom' => 'Tuchel', 'prenom' => 'Doe']
            }
        }

        if (empty($updates)) return false;

        $sql = "UPDATE user SET " . implode(', ', $updates). " WHERE user_id = :user_id";
        $params['user_id'] = $userId;

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function add_driver_role(int $userId): bool
    {
        $stmt = $this->connection->prepare("
            INSERT INTO role_user (user_id, role_id) SELECT ?, role_id FROM role WHERE libelle = 'chauffeur'
        ");

        return $stmt->execute([$userId]);
    }

    public function add_passenger_role(int $userId): bool
    {
        $stmt = $this->connection->prepare("
            INSERT INTO role_user (user_id, role_id) SELECT ?, role_id FROM role WHERE libelle = 'passager'
        ");

        return $stmt->execute([$userId]);
    }

    public function save_preferences_with_mysql(int $userId, array $preferences): true
    {
        $stmt = $this->connection->prepare(" INSERT INTO preference (propriete, valeur, conducteur_id) VALUES (?,?,?)");
        foreach ($preferences as $key => $value) {
            $stmt->execute([$key, $value, $userId]);
        }

        return true;
    }

    public function save_prefrences_with_mongoDB(int $userId, array $preferences): void
    {
        $mongoConnexion = MongoManager::getInstance();
        $collection = $mongoConnexion->getCollection('preferences');

        $document = [
            'user_id' => $userId,
            'preferences' => $preferences,
            'updates_at' => new  UTCDateTime()
        ];

        // Ajout des preferences
        $collection->updateOne(
            ['user_id' => $userId],
            ['$set' => $document],
            ['upsert' => true]
        );
    }

    public function update_preferences_with_mongoDB(int $userId, array $preferences):void{

        $mongoConnexion = MongoManager::getInstance();
        $collection= $mongoConnexion->getCollection('preferences');


        $document= [
            'user_id' => $userId,
            'preferences' => $preferences,
            'update_at' => new UTCDateTime()
        ];

        // Ajout des preferences
        $collection->findOneAndUpdate(
            ['user_id' => $userId],
            ['$set' => $document],
            ['upsert' => true]
        );
    }

    public function get_preferences(int $userId):array{

        $mongoConnexion = MongoManager::getInstance();
        $collection=
        $mongoConnexion->getCollection('preferences');

        $preferences = $collection->findOne(['user_id' =>$userId]);

        return $preferences ? (array) $preferences['preferences'] : [] ;
    }


    public function email_exist(string $email, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) from user WHERE email = ?";
        $params = [$email];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    public function pseudo_exist(string $pseudo, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) from user WHERE pseudo = ?";
        $params = [$pseudo];

        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    // cette fonction permet de recuperer un utilisateur en bd via son adresse email ou son pseudo.
    public function find_by_username_or_email(string $identifier): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE pseudo = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }
    public function find_by_username(string $username): mixed
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM user WHERE pseudo = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function find_by_id($userId): mixed
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM user WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function find_by_email(string $email): mixed
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM user WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    public function find_by_rember_token(string $token): mixed
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE remember_me = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function get_user_roles($userId): array
    {
        $stmt = $this->connection->prepare("
            SELECT r.role_id, r.libelle FROM role r -- 1: Passager, 2: Chauffeur
            JOIN role_user ru ON ru.role_id = r.role_id
            WHERE ru.user_id = ?
        ");
        $stmt->execute([$userId]);

        $roles = [];
        while ($row = $stmt->fetch()) {
            $roles[] = $row->libelle;
            // $row = {'1'=> 'Passager'}
            // $row = {'2'=> 'Chauffeur'}
            // $roles = ['passager', 'Chauffeur']
        }

        return $roles;
    }

    public function is_driver($userId): bool
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM role_user ru 
            JOIN role r on r.role_id = ru.role_id
            WHERE r.libelle = 'chauffeur' AND ru.user_id = ?
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function is_passenger($userId): bool
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM role_user ru 
            JOIN role r on r.role_id = ru.role_id
            WHERE r.libelle = 'passager' AND ru.user_id = ?
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }
    public function set_role_mask(int $userId, int $mask): bool
    {
        if (!RoleManager::is_valid_mask($mask)) {
            throw new \InvalidArgumentException("Masque de role invalide: $mask");
        }

        $stmt = $this->connection->prepare("UPDATE user SET role_admin = ? WHERE  user_id = ?");
        return $stmt->execute([$mask, $userId]);
    }

    public function has_role(int $userId, string $role): bool
    {
        $userMask = $this->get_role_mask($userId);

        return RoleManager::has_role($userMask, $role);
    }

    public function get_role_mask(int $userId): int
    {
        $stmt = $this->connection->prepare("SELECT role_admin FROM user WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result ? (int)$result->role_admin : RoleManager::VISITEUR;
    }

    public function get_role_info($userId): array
    {
        $mask = $this->get_role_mask($userId);
        return [
            'mask' => $mask,
            'name' => RoleManager::get_admin_role_name($mask),
            'roles' => RoleManager::get_admin_roles($mask),
        ];
    }
}
