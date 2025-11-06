<?php

namespace Ecoride\Ecoride\Models;

use Ecoride\Ecoride\Core\Database;

// cette classe gère les opérations liées aux utilisateurs dans la base de données.
class UserModel
{

    private $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
    }

    public function create(array $data): bool
    {
        $columns = implode(', ', array_keys($data)); // pseudo, email, password, credits
        $placeholders = ':' . implode(', :', array_keys($data)); // :pseudo, :email, :password, :credits
        $stmt = $this->connection->prepare("INSERT INTO user ($columns) VALUES ($placeholders)");
        return $stmt->execute($data);
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

}