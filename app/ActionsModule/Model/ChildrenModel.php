<?php

namespace App\ActionsModule\Model;

use App\ActionsModule\BasePresenter;
use Nette;

class ChildrenModel extends BasePresenter
{
    public $database;

    public function __construct(Nette\Database\Context  $database)
    {
        $this->database = $database;
    }
    
    public function loadChildren($parent_id)
    {
        return $this->database->table('children')->where('parents_id = ?', $parent_id);
    }

    public function loadChild($child_id, $parent_id)
    {
        return $this->database->table('children')->where('parents_id = ?', $parent_id)->where('id = ?', $child_id)->fetch();
    }

    public function saveChild($parent_id, $child_id, $name, $surname, $birthday, $insurance_id, $note)
    {
        $data = ['parents_id'=>(int)$parent_id, 'name'=>$name, 'surname'=>$surname, 'birthday'=>$birthday,'insurance_id'=>$insurance_id, 'note'=>$note];
        if (!$child_id)
        {
            $this->database->table('children')->insert($data);
        }
        if ($child_id)
        {
            $this->database->table('children')->where('id=?',$child_id)->update($data);
        }
    }

}