<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class OrganisationModel extends DBModel
{

    public function getAllOrganisations()
    {
        return $this->db->table('organisations')->fetchAll();
    }

    public function selectOrganisations()
    {
        return $this->db->table('organisations')->fetchPairs('id', 'name');
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getOrganisationById($id = 0)
    {
        return $this->db->table('organisations')->where('id', $id)->fetch();
    }

    /**
     * @param ArrayHash $data
     * @return string
     */
    public function addOrganisation(ArrayHash $data)
    {
        $this->db->table('organisations')->insert($data);
        return $this->db->getInsertId();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function editOrganisation(ArrayHash $data)
    {
        unset ($data->cancel);
        if ($data->logo->name) {
            $data->logo = $data->logo->name;
        } else {
            unset($data->logo);
        }
        $this->db->table('organisations')->where('id', $data->id)->update($data);
    }
}