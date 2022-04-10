<?php

namespace App\Service;

use App\Model\TypesModel;
use Nette\PhpGenerator\Type;

class TypesService
{

    public $typesModel;

    public function __construct(TypesModel $typesModel){
        $this->typesModel = $typesModel;
    }

    public function addType($name, $color){
        $this->typesModel->saveType($name, $color);
    }


    public function saveType($name, $color, $id){
        $this->typesModel->saveType($name, $color, $id);
    }
}