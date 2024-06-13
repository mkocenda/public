<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class WarehouseModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getWarehousesByOrganisationId(int $organisation_id)
    {
        return $this->db->table('warehouses')->where('organisation_id', $organisation_id)->fetchAll();
    }

    /**
     * @param int $id
     * @param int $organisation_id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getWarehouseById(int $id, int $organisation_id)
    {
        return $this->db->table('warehouses')
            ->where('id', $id)
            ->where('organisation_id', $organisation_id)
            ->fetch();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addWarehouse(ArrayHash $data)
    {
		$this->db->table('warehouses')->insert($data);
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function setWarehouse(ArrayHash $data)
    {
		$this->db->table('warehouses')
                ->where('id', $data->id)
				->where('organisation_id', $data->organisation_id)
				->update($data);
    }

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getReservationParts(int $action_id, int $organisation_id)
    {
		return $this->db->query('SELECT *
									 FROM parts p
									 JOIN warehouses w ON w.id = p.warehouse_id
									 JOIN reservations r ON r.part_id = p.id AND r.organisation_id = w.organisation_id
									 WHERE r.action_id = ?
									 AND r.organisation_id = ?', $action_id, $organisation_id)
			->fetchAll();
    }
	
	/**
	 * @param int $organisation_id
	 * @param $part_id
	 * @return array|\Nette\Database\IRow[]
	 */
    public function getAvailableParts(int $organisation_id, $part_id = null)
    {
	    $where = $part_id ? "AND ap.id = ".$part_id : null;
		return $this->db->query('SELECT ap.*, (ap.qty - IFNULL(ap.used,0)) as available, w.name, w.location
        							 FROM available_parts ap
        							 JOIN warehouses w ON w.id = ap.warehouse_id AND w.organisation_id = ap.organisation_id
        							 WHERE w.organisation_id = ? '. $where, $organisation_id)
			->fetchAll();
    }
}