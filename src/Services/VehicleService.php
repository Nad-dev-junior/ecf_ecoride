<?php

namespace Ecoride\Ecoride\Services;

use Ecoride\Ecoride\Core\Service;
use Ecoride\Ecoride\Models\VehicleModel;

class VehicleService extends Service
{
    private ValidationService $validationService;
    private VehicleModel $vehicleModel;

    public function __construct()
    {
        parent::__construct();
        $this->validationService = new ValidationService();
        $this->vehicleModel = new VehicleModel();
    }
}
