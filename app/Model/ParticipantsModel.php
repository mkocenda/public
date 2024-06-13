<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;
use App\Model\FileModel;

class ParticipantsModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @param $filtered
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function getAllParticipantsByOrganisation(int $organisation_id, $filtered = null)
    {
        $data = $this->db->table('participants');
        if ($organisation_id > 0) {
            $data->where('organisation_id', $organisation_id);
        }
        if ($filtered) {
            if (is_array($filtered)) {
                $data->where('id NOT IN (' . implode(',', $filtered) . ')');
            } else {
                $data->where('id NOT IN (' . $filtered . ')');
            }
        }
        return $data->fetchAll();
    }

    /**
     * @param int $id
     * @param int $organisation_id
     * @return array|false|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow[]
     */
    public function getParticipantById(int $id, int $organisation_id)
    {
        return $this->db->table('participants')
            ->where('id', $id)
            ->where('organisation_id', $organisation_id)
            ->fetch();
    }

    /**
     * @param int $participant_id
     * @param int $action_id
     * @return array|\Nette\Database\Table\IRow[
     *
     */
    public function getParticipantsRecords(int $participant_id)
    {
        return $this->db->query('SELECT pr.*, a.name, a.starttime, a.stoptime, s.name AS s_name, s.surname AS s_surname
                                     FROM participants_records pr
                                     JOIN actions a ON a.id = pr.action_id
                                     JOIN stuff s ON s.id = pr.created_by
                                     WHERE pr.participants_id = ?', $participant_id)
            ->fetchAll();
    }

    public function getParticipantsActionRecords(int $participant_id, int $action_id)
    {
        return $this->db->query('SELECT pr.*, a.name, a.starttime, a.stoptime, s.name AS s_name, s.surname AS s_surname
                                     FROM participants_records pr
                                     JOIN actions a ON a.id = pr.action_id
                                     JOIN stuff s ON s.id = pr.created_by
                                     WHERE pr.participants_id = ? AND a.id = ?', $participant_id, $action_id)
            ->fetchAll();
    }

    /**
     * @param int $participant_id
     * @param $record_id
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getParticipantRecord(int $participant_id, $record_id)
    {
        return $this->db->query('SELECT pr.*, a.name, a.starttime, a.stoptime, s.name AS s_name, s.surname AS s_surname
                                     FROM participants_records pr
                                     JOIN actions a ON a.id = pr.action_id
                                     JOIN stuff s ON s.id = pr.created_by
                                     WHERE pr.participants_id = ? AND pr.id = ?', $participant_id, $record_id)
            ->fetch();
    }

    /**
     * @param int $participant_id
     * @param int $action_id
     * @return array|\Nette\Database\IRow[]
     */
    public function listActionParticipantRecords(int $participant_id, int $action_id)
    {
        return $this->db->query('SELECT pr.*, a.name, a.starttime, a.stoptime, s.name AS s_name, s.surname AS s_surname
                                     FROM participants_records pr
                                     JOIN actions a ON a.id = pr.action_id
                                     JOIN stuff s ON s.id = pr.created_by
                                     WHERE pr.participants_id = ? AND a.id = ?', $participant_id, $action_id)
            ->fetchAll();
    }

    /**
     * @param int $participant_id
     * @param int $action_id
     * @param string $description
     * @param int $created_by
     * @param $file
     * @return void
     */
    public function addParticipantsRecord(int $participant_id, int $action_id, string $description, int $created_by, $file = null)
    {
        $today = new \DateTime();
        $data = array('participants_id' => $participant_id, 'action_id' => $action_id, 'description' => $description, 'created_by' => $created_by, 'created' => $today, 'file_id' => $file);
        $this->db->table('participants_records')->insert($data);
    }

    /**
     * @param int $participant_id
     * @param $action_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getParticipantAction(int $participant_id, $action_id = null)
    {
        $where = '';
        if ((int)$action_id > 0) {
            $where = 'AND a.id = ' . (int)$action_id;
        }
        return $this->db->query('SELECT a.* 
                                     FROM participants p
                                     JOIN participants2action pa ON pa.participant_id = p.id
                                     JOIN actions a ON a.id = pa.action_id
                                     WHERE p.id = ?
                                     ' . $where . '
                                     ORDER BY a.starttime', $participant_id)->fetchAll();
    }

    /**
     * @param string $date
     * @param int $limit_from
     * @param int $limit_to
     * @param int $organisation_id
     * @param $filtered
     * @return array|\Nette\Database\IRow[]
     */
    public function getLimitedParticipants(string $date, int $limit_from, int $limit_to, int $organisation_id, $filtered = null)
    {
        $where = '';
        if ($filtered) {
            if (is_array($filtered)) {
                $where = 'AND id NOT IN (' . implode(',', $filtered) . ')';
            } else {
                $where = 'AND id NOT IN (' . $filtered . ')';
            }
        }
        return $this->db->query('SELECT * 
									 FROM participants
									 WHERE organisation_id = ?
									 AND TIMESTAMPDIFF(year,birthday, ?) BETWEEN ? AND ?
									 ' . $where, $organisation_id, $date, $limit_from, $limit_to)
            ->fetchAll();
    }

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getWaitingParticipants(int $action_id, int $organisation_id)
    {
        return $this->db->query('SELECT p.*, p2w.id AS pw_id 
                                     FROM participants2waiting p2w
                                     JOIN participants p ON p.id = p2w.participant_id
                                     WHERE p.organisation_id = ?
                                     AND p2w.action_id = ?', $organisation_id, $action_id)
            ->fetchAll();
    }

	/**
	 * @param int $participant_id
	 * @param int $action_id
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getParticipantDocuments(int $participant_id, int $action_id, int $organisation_id){
		return $this->db->query('SELECT f.* 
									 FROM participants p
									 JOIN participants_documents pd ON pd.participant_id = p.id
									 JOIN files f ON f.id = pd.file_id AND f.organisation_id = p.organisation_id
									 WHERE p.id = ?
									 AND pd.action_id = ?
									 AND p.organisation_id = ?',$participant_id, $action_id, $organisation_id)->fetchAll();
	}

    /**
     * @param int $action_id
     * @param int $participant_id
     * @return bool
     */
    public function isOnWaitingList(int $action_id, int $participant_id)
    {
        return $this->db->table('participants2waiting')
            ->where('action_id', $action_id)
            ->where('participant_id', $participant_id)
            ->fetch() ? true : false;
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addParticipantsToAction($data)
    {
        $this->db->table('participants2action')->insert($data);
    }

    /**
     * @param $data
     * @return void
     */
    public function removeParticipantsFromAction($data)
    {
        $this->db->table('participants2action')
            ->where('action_id=?', $data->action_id)
            ->where('participant_id=?', $data->participant_id)
            ->delete();
    }

    /**
     * @param $data
     * @return void
     */
    public function addWaitingParticipant($data)
    {
        $this->db->table('participants2waiting')->insert($data);
    }

    /**
     * @param $data
     * @return void
     */
    public function removeWaitingParticipant($data)
    {
        $this->db->table('participants2waiting')
            ->where('participant_id = ?', $data->participant_id)
            ->where('action_id = ?', $data->action_id)
            ->delete();
    }

    /**
     * @param $data
     * @return void
     */
    public function addParticipant($data)
    {
        $this->db->table('participants')->insert($data);
    }

    /**
     * @param $data
     * @return void
     */
    public function saveParticipant($data)
    {
        $this->db->table('participants')->where('id', $data->id)->update($data);
    }

	/**
	 * @param $data
	 * @return void
	 */
	public function addPill($data)
	{
		$this->db->table('participants_pills')->insert($data);
	}

	/**
	 * @param int $participant_id
	 * @param int $action_id
	 * @param int $file_id
	 * @return void
	 */
	public function addParticipantsDocument(int $participant_id, int $action_id, int $file_id)
	{
		$data = array('participant_id'=>$participant_id, 'action_id'=>$action_id, 'file_id'=>$file_id);
		$this->db->table('participants_documents')->insert($data);
	}
}