<?php

namespace App\Model;
	
use Nette\Utils\ArrayHash;

class ReservationsModel extends DBModel
{
	
	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getReservationsByOrganisationId(int $organisation_id)
	{
		return $this->db->query('SELECT r.*
									 FROM reservations r
									 JOIN actions a ON a.id = r.action_id AND a.organisation_id = r.organisation_id
									 WHERE a.stoptime > NOW()
									 AND r.organisation_id = ?', $organisation_id)
			->fetchAll();
	}
	
	/**
	 * @param int $action_id
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getReservationsByActionId(int $action_id, int $organisation_id)
	{
		return $this->db->query("SELECT r.id AS rid, a.id AS aid, r.amount, r.stuff_id, p.part_no, p.description, p.image_id, w.name
									 FROM reservations r
									 JOIN actions a ON a.id = r.action_id AND a.organisation_id = r.organisation_id
									 JOIN parts p ON p.id = r.part_id AND p.organisation_id = r.organisation_id
									 JOIN warehouses w ON w.id = p.warehouse_id AND w.organisation_id = r.organisation_id
									 WHERE (a.stoptime > NOW() OR a.stoptime = '0000-00-00 00:00:00')
									 AND a.id = ?
									 AND r.organisation_id = ?", $action_id, $organisation_id)
			->fetchAll();
	}

    /**
     * @param int $rid
     * @param int $action_id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getReservationByID(int $rid, int $action_id)
    {
        return $this->db->table('reservations')->where('id', $rid)->where('action_id', $action_id)->fetch();
    }

    /**
     * @param int $rid
     * @param int $organisation_id
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getReservationPart(int $rid, int $organisation_id)
    {
        return $this->db
            ->query('SELECT r.*, p.part_no, p.description FROM reservations r
                         JOIN parts p ON p.id = r.part_id
                         WHERE r.id = ? AND r.organisation_id = ?', $rid, $organisation_id)
                       ->fetch();
    }

    /**
     * Get new reserved items for borrowing
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getReservationsForBorrowing(int $action_id, int $organisation_id)
    {
        return $this->db->query('SELECT r.*
									 FROM reservations r
									 WHERE r.part_id not in (SELECT b.part_id 
									                         FROM borrowing b 
									                         WHERE b.action_id = ?
									                         AND b.organisation_id = ?)  
									 AND r.action_id = ?
									 AND r.organisation_id = ?', $action_id, $organisation_id, $action_id, $organisation_id)
            ->fetchAll();
    }

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return bool
     */
    public function isReservedOnAction(int $action_id, int $organisation_id)
    {
        return (count($this->getReservationsByActionId($action_id, $organisation_id))>0) ? true : false;
    }

	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function addReservation(ArrayHash $data)
	{
		$this->db->table('reservations')->insert($data);
	}
	
	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function setReservation(ArrayHash $data)
	{
		$this->db->table('reservations')->where('id', $data->id)->update($data);
	}
	
	/**
	 * @param int $id
	 * @param int $organisation_id
	 * @return void
	 */
	public function deleteReservation(int $id, int $organisation_id)
	{
		$this->db->table('reservations')
			->where('id', $id)
			->where('organisation_id', $organisation_id)
			->delete();
	}
}