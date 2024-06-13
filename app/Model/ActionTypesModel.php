<?php

namespace App\Model;

class ActionTypesModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listAllActions($organisation_id = 0)
    {
        $list = $this->db->table('actions2type');
        if ($organisation_id > 0) {
            $list->where('organisation_id', $organisation_id);
        }
        return $list->fetchAll();
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getActionTypeById(int $id)
    {
        return $this->db->table('actions2type')->where('id', $id)->fetch();
    }

    /**
     * @param string $name
     * @param $color
     * @param $organisation_id
     * @return void
     */
    public function addActionType(string $name, $color = '', $organisation_id = 0)
    {
        $data = array('name' => $name, 'color' => $color);
        if ($organisation_id > 0) {
            $data['organisation_id'] = $organisation_id;
        }
        $this->db->table('actions2type')->insert($data);
    }

    /**
     * @param string $name
     * @param $color
     * @param $organisation_id
     * @param int $id
     * @return void
     */
    public function editActionType(string $name, $color = '', $organisation_id = 0, int $id)
    {
        $data = array('name' => $name, 'color' => $color);
        if ($organisation_id == 0) {
            $data['organisation_id'] = null;
        } else {
            $data['organisation_id'] = $organisation_id;
        }
        $this->db->table('actions2type')->where('id', $id)->update($data);
    }
}