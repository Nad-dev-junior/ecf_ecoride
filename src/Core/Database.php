<?php


namespace Ecoride\Ecoride\Core;

use PDO;
use PDOException;

class Database
{
    private static $host = 'localhost';
    private static $dbname = 'bdd_ecoride';
    private static $username = 'root';
    private static $password = 'MysqlN04@';
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
              "mysql:host=" .self::$host . ";dbname=". self::$dbname . ";charset=utf8" ,self::$username,
              self::$password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            die("Erreur connexion BDD: {$exception->getMessage()}");
        }
    }

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}