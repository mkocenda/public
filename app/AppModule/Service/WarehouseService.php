<?php

namespace App\App\Service;

use App\Model\WarehouseModel;
use Nette\Utils\ArrayHash;

class WarehouseService
{

    public $warehouseModel;
    public function __construct(WarehouseModel $warehouseModel)
    {
        $this->warehouseModel = $warehouseModel;
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function saveWarehouse(ArrayHash $data){
        if ($data->id){
            $this->warehouseModel->setWarehouse($data);
        } else {
            $this->warehouseModel->addWarehouse($data);
        }
    }
}