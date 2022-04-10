<?php

namespace App\Service;

use App\Model\StuffModel;

class StuffService
{

    public $stuffService;

    public function __construct(StuffModel $stuffModel) {
        $this->stuffModel = $stuffModel;
    }

    /**
     * @param $id
     * @param $name
     * @param $surame
     * @param $stuff_id
     */
    public function editStuff($id, $name, $surame, $alias, $stuff_id){
        $this->stuffModel->saveStuff($id, $name, $surame, $alias, $stuff_id);
    }

}