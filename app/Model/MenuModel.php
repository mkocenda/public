<?php

namespace App\Model;

use Nette;

class MenuModel
{

    public $database;

    public function __construct(Nette\Database\Context $datatase)
    {
        $this->database = $datatase;
    }


    public function loadAppMenu($userid){
        return $this->database->query('SELECT a.app, a.name
                                    FROM applications a 
                                    JOIN useraccess ua ON ua.appid = a.id
                                    WHERE ua.userid = ?
                                    ORDER BY a.name', $userid)->fetchAll();
    }

}