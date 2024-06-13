<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class PartsModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getAllPartsByOrganisationId(int $organisation_id)
    {
        return $this->db->table('parts')->where('organisation_id', $organisation_id)->fetchAll();
    }

    /**
     * @param int $id
     * @param int $organisation_id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getPartById(int $id, int $organisation_id)
    {
        return $this->db->table('parts')
            ->where('id', $id)
            ->where('organisation_id', $organisation_id)
            ->fetch();
    }

    /**
     * @param int $warehouse_id
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getWarehouseParts(int $warehouse_id, int $organisation_id)
    {
        return $this->db->table('parts')
            ->where('warehouse_id', $warehouse_id)
            ->where('organisation_id', $organisation_id)
            ->fetchAll();
    }

    public function getUsedPartQty(int $part_id, int $organisation_id){
        return $this->db->query('SELECT SUM(r.amount) AS used
									 FROM reservations r
									 JOIN parts p ON p.id = r.part_id AND p.organisation_id = r.organisation_id
									 JOIN actions a ON a.id = r.action_id AND a.organisation_id = r.organisation_id
									 WHERE a.stoptime > NOW()
									 AND p.id = ?
									 AND p.organisation_id = ?', $part_id, $organisation_id)->fetch();
    }
	
	/**
	 * @param int $part_id
	 * @param int $organisation_id
	 * @return int
	 */
	public function isUsedPart(int $part_id, int $organisation_id)
	{
		$used = $this->db->query('SELECT 1 AS used
									 FROM reservations r
									 JOIN parts p ON p.id = r.part_id AND p.organisation_id = r.organisation_id
									 JOIN actions a ON a.id = r.action_id AND a.organisation_id = r.organisation_id
									 WHERE p.id = ?
									 AND p.organisation_id = ?', $part_id, $organisation_id)->fetch();
		return (isset($used->used)) ? 1 : 0;
	}
	
    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addPart(ArrayHash $data)
    {
        $this->db->table('parts')->insert($data);
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function setPart(ArrayHash $data)
    {
        $this->db->table('parts')
            ->where('id', $data->id)
            ->where('warehouse_id', $data->warehouse_id)
            ->where('organisation_id', $data->organisation_id)
            ->update($data);
    }
	
	/**
	 * @param int $id
	 * @param int $organisation_id
	 * @return void
	 */
	public function deletePart(int $id, int $organisation_id)
	{
		$this->db->table('parts')
			->where('id', $id)
			->where('organisation_id', $organisation_id)
			->delete();
	}
}