<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class ParentsModel extends DBModel
{

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getParentByID(int $id)
    {
        return $this->db->table('parents')->where('id', $id)->fetch();
    }
	
	/**
	 * @param int $id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getParentByUserID(int $id)
	{
		return $this->db->table('parents')->where('user_id', $id)->fetch();
	}
	
	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
    public function listParentsByOrganisationId(int $organisation_id){
        return  $this->db->table('parents')->where('organisation_id', $organisation_id)->fetchAll();
    }

    /**
     * @param int $organisation_id
     * @return array
     */
    public function selectParentsByOrganisationId(int $organisation_id){
        $parents =  $this->db->table('parents')->where('organisation_id', $organisation_id)->fetchAll();
        $data = array();
        foreach ($parents as $parent){
            $id = ($parent->id == null) ? 0 : $parent->id;
            $data[$id] = $parent->surname.' '.$parent->name.' / '.$parent->email;
        }
        return $data;
    }

    /**
     * @param ArrayHash $data
     * @return string
     */
    public function addParent(ArrayHash $data)
    {
        $this->db->table('parents')->insert($data);
        return $this->db->getInsertId();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function saveParent(ArrayHash $data)
    {
        $this->db->table('parents')->where('id', $data->id)->update($data);
    }
}