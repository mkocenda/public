<?php

namespace App\Model;

use Nette\Utils\ArrayHash;

class PillsModel extends DBModel
{
	
	/**
	 * @return array|\Nette\Database\Table\IRow[]
	 */
	public function getPillsList()
	{
		return $this->db->table('medicaments')->fetchAll();
	}

    /**
     * @param int $participant_id
     * @param int $action_id
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listActionParticipantPills(int $participant_id, int $action_id)
    {
        return $this->db->query('SELECT id, action_id, participant_id, pill_name, pill_id, dosage, period,
									 (SELECT MAX(date_apply)
									  FROM participants_pills_apply ppa
									  WHERE ppa.participant_id = pp.participant_id
										AND ppa.action_id = pp.action_id
										AND ppa.pill_id = pp.id) AS last_apply
									  FROM participants_pills pp
									  WHERE pp.participant_id = ? AND pp.action_id = ?', $participant_id, $action_id)
			->fetchAll();
    }

    /**
     * @param int $participant_id
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getActionParticipantPill(int $participant_id, int $id)
    {
        return $this->db->table('participants_pills')
			->where('participant_id', $participant_id)
			->where('id', $id)
			->fetch();
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addActionParticipantPill(ArrayHash $data)
    {
        $this->db->table('participants_pills')->insert($data);
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function saveActionParticipantPill(ArrayHash $data)
    {
        $this->db->table('participants_pills')
			->where('id', $data->id)
			->where('participant_id', $data->participant_id)
			->update($data);
    }
	
	/**
	 * Get data for medicaments alert
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getAlertMedicaments(){
		return $this->db->query('SELECT a.id, pp.pill_name, pp.dosage, p.name AS participant_name,
       										p.surname AS participant_surname,
												 u.id AS recipient_id,
												 a.organisation_id
							  FROM participants_pills pp
							  JOIN participants p ON p.id = pp.participant_id
							  JOIN actions a ON pp.action_id = a.id AND a.organisation_id = p.organisation_id
							  JOIN action2stuff a2s ON a2s.action_id = a.id
							  JOIN stuff s ON s.id = a2s.stuff_id AND s.organisation_id = a.organisation_id
							  JOIN certificates_type ct ON ct.organisation_id = a.organisation_id AND ct.id = a2s.stuff_type AND ct.name = "Zdravotník zotavovacích akcí"
							  JOIN users u ON u.id = s.user_id
							  WHERE NOW() BETWEEN a.starttime AND a.stoptime
 							  GROUP BY u.username, pp.pill_name, pp.dosage, u.id, a.organisation_id')->fetchAll();
		
	}
	
	/**
	 * @param $data
	 * @return void
	 */
	public function pillApply($data)
	{
		$this->db->table('participants_pills_apply')->insert($data);
	}
	
	/**
	 * @param int $participant_id
	 * @param int $action_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getPillsApply(int $participant_id, int $action_id)
	{
		return $this->db->query('SELECT pp.pill_name, ppa.date_apply, p.name, p.surname
									 FROM participants_pills_apply ppa
									 JOIN users u ON u.id = ppa.user_id
									 JOIN v_persons p ON p.user_id = u.id
									 JOIN participants_pills pp ON pp.id = ppa.pill_id
									 WHERE ppa.participant_id = ?
									 AND ppa.action_id = ?', $participant_id, $action_id)->fetchAll();
	}
}