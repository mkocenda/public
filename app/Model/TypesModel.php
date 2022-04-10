<?php

namespace App\Model;

use Nette;

class TypesModel
{

    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return array|Nette\Database\Table\IRow[]
     */
    public function loadTypes()
    {
        return $this->database->table('actions2type')->fetchAll();
    }

    /**
     * @param $id
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function loadType($id)
    {
        return $this->database->table('actions2type')->where('id = ?', $id)->fetch();
    }

    /**
     * @param $name
     * @param $color
     * @param $id
     * @return void
     */
    public function saveType($name, $color,  $id = null){
        $data = array('name'=>$name, 'color'=>$color);
        if ($id)
        {
            $this->database->table('actions2type')->where('id = ?', $id)->update($data);
        } else{
            $this->database->table('actions2type')->insert($data);

        }
    }

}