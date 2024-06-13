<?php

namespace App\Model;

use Nette\ArrayHash;

class StuffModel extends DBModel
{

    /**
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listAllStuffs()
    {
        return $this->db->table('stuff')->fetchAll();
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getStuffByID(int $id)
    {
        return $this->db->table('stuff')->where('id', $id)->fetch();
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getStuffByUserID(int $id)
    {
        return $this->db->table('stuff')->where('user_id', $id)->fetch();
    }

	/**
	 * @param int $id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
    public function getStuffsByOrganisationID(int $id)
    {
        return $this->db->table('stuff')->where('organisation_id', $id)->fetchAll();
    }

	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
    public function getGroupedStuffsByCertificateType(int $organisation_id)
    {
        return $this->db->query('SELECT ct.name, ct.icon, ct.backgroundcolor, COUNT(*) AS count
                                    FROM stuff s
                                    JOIN users u ON u.id = s.user_id
                                    JOIN certificates c ON c.stuff_id = s.id
                                    JOIN certificates_type ct ON ct.id = c.certtype
                                    WHERE s.organisation_id = ?
                                    GROUP BY ct.backgroundcolor, ct.icon, ct.name', $organisation_id)->fetchAll();
    }

	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
    public function getEndingStuffsCertificates(int $organisation_id){
        return $this->db->query("SELECT s.name, s.surname, c.validto, ct.name AS cert_name, ct.icon, ct.backgroundcolor
                            		 FROM stuff s
                            		 JOIN certificates c ON c.stuff_id = s.id
                            		 JOIN certificates_type ct ON ct.id = c.certtype AND ct.organisation_id = s.organisation_id
                            		 WHERE DATEDIFF(c.validto,NOW()) <= 31
                            		 AND s.organisation_id = ?
                            		 ORDER BY c.validto", $organisation_id)->fetchAll();
    }

	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getStuffsBirthday(int $organisation_id){
		return $this->db->query("SELECT name, 
       										surname, 
       										birthday, 
       										DATE_FORMAT(birthday,'%m-%d'),
       										CONCAT(DATE_FORMAT(birthday,'%d.%m'),'.',DATE_FORMAT(CURDATE(),'%Y')) calendar_date, 
       										DATE_FORMAT(CURDATE(),'%Y') -  DATE_FORMAT(birthday,'%Y') age
									 FROM stuff
									 WHERE work_to IS NULL
									 AND DATEDIFF(CONCAT(DATE_FORMAT(CURDATE() ,'%Y'),'-', DATE_FORMAT(birthday,'%m-%d')),CURDATE()) <= 31
									 AND DATE_FORMAT(birthday,'%m') >= DATE_FORMAT(NOW(), '%m')
									 AND organisation_id = ?", $organisation_id)
			->fetchAll();
	}

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getAvailableStuffs(int $action_id, int $organisation_id){
        return $this->db->query('SELECT s.id, ct.id AS cert_type, s.name, s.surname, s.alias, ct.name AS cert_name, '.$action_id.' AS action_id
                                     FROM stuff s 
                                     JOIN users u ON u.id = s.user_id
                                     JOIN certificates c ON c.stuff_id = s.id
                                     JOIN certificates_type ct ON ct.id = c.certtype AND s.organisation_id = ct.organisation_id
                                     WHERE s.id NOT IN (
	                                     SELECT a2s.stuff_id 
	                                     FROM actions a
	                                     JOIN action2stuff a2s ON a2s.action_id = a.id
	                                     WHERE a.id = ?
                                     )
                                     AND s.organisation_id = ?',$action_id, $organisation_id )->fetchAll();
    }

    /**
     * @param ArrayHash $data
     * @return string
     */
    public function addStuff(ArrayHash $data)
    {
        unset ($data->cancel);
        if ($data->photo->name) {
            $data->photo = $data->photo->name;
        } else {
            unset($data->photo);
        }
        $data->user_id = ($data->user_id == 0) ? null : $data->user_id;
        $this->db->table('stuff')->insert($data);
        return $this->db->getInsertId();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function editStuff(ArrayHash $data)
    {
        unset ($data->cancel);
        if (isset($data->photo) && ($data->photo->name)) {
            $data->photo = $data->photo->name;
        } else {
            unset($data->photo);
        }

        $stuff = $this->getStuffByID($data->id);
        if (isset($data->active) && $data->active && $stuff->work_to) {
            $data->work_to = null;
        }
        if (isset($data->work_to) && $data->work_to && $stuff->active) {
            $data->active = 0;
        }
        if (isset($data->user_id)) {
            $data->user_id = ($data->user_id == 0) ? null : $data->user_id;
        }
        $this->db->table('stuff')->where('id', $data->id)->update($data);
    }
	
	/**
	 * @param int $stuff_id
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getStuffCertificates(int $stuff_id, int $organisation_id)
	{
		return $this->db->query('SELECT c.id AS certificate_id, ct.name, c.validfrom, c.validto, c.certfile, ct.icon, '.$stuff_id.' AS user_id
							  FROM certificates c
							  JOIN certificates_type ct ON ct.id = c.certtype
							  WHERE c.stuff_id = ? AND ct.organisation_id = ?', $stuff_id, $organisation_id)->fetchAll();
	}
	
	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getStuffsTypes(int $organisation_id)
	{
		return $this->db->table('stuff2type')
			->whereOr(array('organisation_id IS NULL ', 'organisation_id' => $organisation_id))
			->order('order')
			->fetchAll();
	}
	
	/**
	 * @param int $id
	 * @param int $organisation_id
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getStuffType(int $id, int $organisation_id)
	{
		return $this->db->table('stuff2type')
			->where('id', $id)
			->where('organisation_id', $organisation_id)
			->fetch();
	}
	
	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveStuffType(ArrayHash $data)
	{
		if ($data->id > 0){
			$this->db->table('stuff2type')->where('id', $data->id)->update($data);
		} else
		{
			$this->db->table('stuff2type')->insert($data);
		}
		
	}
}