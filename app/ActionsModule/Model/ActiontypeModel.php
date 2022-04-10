<?php

namespace App\ActionsModule\Model;

use Nette;

class ActiontypeModel
{

    public $database;

    public function __construct(Nette\Database\Context $database){
        $this->database = $database;
    }

    public function loadTypes(){
        return $this->database->table('actions2type')->select('id, name')->fetchAll();
    }

    public function loadType($id){
        return $this->database->table('actions2type')->select('id, name')->where('id = ?', $id)->fetch();
    }


}

