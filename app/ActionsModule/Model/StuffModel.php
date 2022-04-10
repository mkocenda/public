<?php

namespace App\ActionsModule\Model;

use Nette;

class StuffModel
{
    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $stuff_id
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function loadStuffData($stuff_id){
        return $this->database->table('stuff')->where('id = ?', $stuff_id)->fetch();
    }

    /**
     * @return array|Nette\Database\Table\IRow[]
     */
    public function loadStuff(){
        return $this->database->table('stuff')->fetchAll();
    }

    /**
     * @param $id
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function loadUserStuffData($id){
        return $this->database->table('users')->select('id, username, email, stuff_id')->where('stuff_id = ?', $id)->fetch();
    }

    /**
     * @param $id
     * @param $name
     * @param $surname
     * @param $alias
     * @param $stuff_id
     */
     public function saveStuff($name, $surname, $alias, $id = null){
        $data = ['name'=>$name, 'surname'=>$surname, 'alias'=>$alias];
        if (!$id) {
            $this->database->table('stuff')->insert($data);
        } else {
            $this->database->table('stuff')->where('id = ?', $id)->update($data);
        }
    }
}