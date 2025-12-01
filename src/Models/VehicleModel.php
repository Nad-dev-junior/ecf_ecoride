<?php

namespace Ecoride\Ecoride\Models ;

use Ecoride\Ecoride\Core\Model;

class VehicleModel extends Model{

    protected string $table = 'voiture' ;
    public function get_or_create_brand(string $brandName){
        
        //je tcheke si la marque existe déja dans la bdd;
        $stmt = $this->connection->prepare("SELECT marque_id FROM marque WHERE libelle= ?");
        if($brandName === '') return false ;

        $stmt->execute([$brandName]);
        $result = $stmt->fetch();

        if($result){
            return $result->marque_id;
        }

        // si $result ne renvoie rien, alors la marque n'existe pas et doit etre créee.
        $stmt= $this->connection->prepare("INSERT INTO marque(libelle) VALUES(?)");
        $stmt->execute([$brandName]);

        return $this->connection->lastInsertId();
    }

    public function add_vehicle(int $userId, array $vehicleData): bool
    {
        $vehicleData['user_id'] = $userId;
        return $this->create($vehicleData);
    }
}