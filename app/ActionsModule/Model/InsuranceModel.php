<?php

namespace App\ActionsModule\Model;

use Nette;

class InsuranceModel
{
    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function loadInsuranceList(){
        return $this->database->table('insurance')->fetchAll();
    }

    public function loadInsurance($insurance_id){
        return $this->database->table('insurance')->where('id',$insurance_id)->fetch();
    }

}