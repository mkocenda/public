<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class BorrowingModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getBorrowingByOrganisationId(int $organisation_id)
    {
        return $this->db->table('borrowing')
            ->where('organisation_id', $organisation_id)
            ->fetchAll();
    }

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getBorrowingByActionId(int $action_id, int $organisation_id)
    {
        return $this->db->table('borrowing')
            ->where('action_id', $action_id)
            ->where('organisation_id', $organisation_id)
            ->fetchAll();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addBorrowing(ArrayHash $data)
    {
        $this->db->table('borrowing')->insert($data);
    }

    /**
     * @param int $part_id
     * @param int $action_id
     * @return void
     */
    public function deleteBorrowing(int $part_id, int $action_id)
    {
        $this->db->table('borrowing')->where('part_id', $part_id)->where('action_id', $action_id)->delete();
    }
}