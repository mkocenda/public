<?php

namespace App\ActionsModule\Service;

use App\ActionsModule\Model\StuffModel;

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
     * @param $alias
     * @param $stuff_id
     */
    public function editStuff($id, $name, $surame, $alias){
        $this->stuffModel->saveStuff($name, $surame, $alias, $id );
    }

    /**
     * @param $name
     * @param $surame
     * @param $alias
     */
    public function addStuff($name, $surame, $alias){
        $this->stuffModel->saveStuff($name, $surame, $alias);
    }
}