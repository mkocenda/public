<?php

namespace App\Model;

class InsuranceModel extends DBModel
{

    /**
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listAllInsurance(){
        return $this->db->table('insurance')->fetchAll();
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getInsuranceById(int $id){
        return $this->db->table('insurance')->where('id', $id)->fetch();
    }

    /**
     * @return array
     */
    public function selectAllInsurance(){
        return $this->db->table('insurance')->fetchPairs('id','name');
    }
}