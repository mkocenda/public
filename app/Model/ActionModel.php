<?php

namespace App\Model;

use DateTime;
use Nette\Utils\ArrayHash;

class ActionModel extends DBModel
{

    /**
     * @param int $organisation_id
     * @param int $status
     * @return array|\Nette\Database\Table\IRow[]
     */
    public function listActionsByStatus(int $organisation_id, int $status)
    {
        $today = new DateTime();
        $data = $this->db->table('actions');
        if ($organisation_id) {
            $data->where('organisation_id', $organisation_id);
        }
        switch ($status) {
            case Types::PLANNED:
                $data->whereOr(array('starttime'=>'0000-00-00 00:00:00',
                    'starttime >= '=>$today->format('Y-m-d H:i:s')));
                break;
            case Types::RUNNING:
                $data->where('starttime <=', $today->format('Y-m-d H:i:s'));
                $data->whereOr(array('stoptime >='=>$today->format('Y-m-d H:i:s'), 'stoptime'=>'0000-00-00 00:00:00'));
                break;
            case Types::DONE:
                $data->where('stoptime <=', $today->format('Y-m-d H:i:s'));
                $data->where('stoptime >','0000-00-00 00:00:00');
                break;
        }
        return $data->fetchAll();
    }
	
	/**
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getIncommingAction(int $organisation_id)
	{
		return $this->db->query('SELECT * FROM actions
         							 WHERE MONTH(starttime)-1 >= MONTH(NOW())
         							 	OR MONTH(stoptime) >= MONTH(NOW())
         							 ORDER BY starttime')->fetchAll();
	}
	
    /**
     * @param int $action_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getActionStuffs(int $action_id){
        return $this->db->query('SELECT a2s.id, s.name, s.surname, s.alias, s.photo, ct.name AS stuff_function, a2s.action_id
                                     FROM action2stuff a2s
                                     JOIN actions a ON a.id = a2s.action_id
                                     JOIN stuff s ON s.id = a2s.stuff_id AND s.organisation_id = a.organisation_id
                                     JOIN users u ON u.id = s.user_id
                                     JOIN certificates c ON c.stuff_id = s.id AND a2s.stuff_type = c.certtype
                                     JOIN certificates_type ct ON ct.id = c.certtype AND ct.organisation_id = a.organisation_id
                                     WHERE a.id = ?', $action_id)->fetchAll();
    }

    /**
     * @param int $stuff_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function listUserAction(int $user_id, int $organisation_id, int $type){
        $today = new DateTime();
        switch ($type){
            case Types::PLANNED; $AND = "AND a.starttime = '0000-00-00 00:00:00' or a.starttime >='".$today->format('Y-m-d H:i:s')."'"; break;
            case Types::RUNNING; $AND = "AND a.starttime <= '".$today->format('Y-m-d H:i:s')."' and a.stoptime >='".$today->format('Y-m-d H:i:s')."'"; break;
            case Types::DONE;    $AND = "AND a.starttime <='".$today->format('Y-m-d H:i:s')."'"; break;
            default: $AND = '';
        }
        return $this->db->query('SELECT a.*, a2s.stuff_type
                                     FROM action2stuff a2s
                                     JOIN actions a ON a.id = a2s.action_id
                                     JOIN stuff s ON s.id = a2s.stuff_id
                                     JOIN users u ON u.id = s.user_id
                                     JOIN certificates c ON c.certtype = a2s.stuff_type AND c.stuff_id = u.id
                                     WHERE s.organisation_id = a.organisation_id
                                     '.$AND.'
                                     AND s.user_id = ? AND s.organisation_id = ?', $user_id, $organisation_id)->fetchAll();
    }

    /**
     * @param int $organisation_id
     * @param int $type
     * @return array|\Nette\Database\IRow[]
     */
    public function listActions(int $organisation_id, int $type){
        $today = new DateTime();
        switch ($type){
            case Types::PLANNED; $AND = "AND a.starttime = '0000-00-00 00:00:00' OR a.starttime >='".$today->format('Y-m-d H:i:s')."'"; break;
            case Types::RUNNING; $AND = "AND a.starttime <= '".$today->format('Y-m-d H:i:s')."' AND  a.stoptime >='".$today->format('Y-m-d H:i:s')."'"; break;
            case Types::DONE;  $AND = "AND a.starttime <='".$today->format('Y-m-d H:i:s')."'"; break;
            default: $AND = '';
        }
        return $this->db->query('SELECT a.id, 
                                            a.name, 
                                            a.motto, 
                                            a.address, 
                                            CASE WHEN a.starttime = "0000-00-00 00:00:00" THEN NULL ELSE a.starttime END starttime, 
                                            CASE WHEN a.stoptime = "0000-00-00 00:00:00" THEN NULL ELSE a.stoptime END stoptime, 
                                            a.description, 
                                            a.limit, 
                                            a.agefrom, 
                                            a.ageto, 
                                            a.photo, 
                                            a.waiting_list
                                     FROM actions a 
                                     WHERE a.organisation_id = ?
                                     '.$AND , $organisation_id)->fetchAll();
    }

    /**
     * @param int $action_id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getActionParticipants(int $action_id, int $organisation_id){
        return $this->db->query('SELECT a.id AS action_id, a.starttime, a.stoptime, p.*
                                     FROM actions a 
                                     JOIN participants2action p2a ON p2a.action_id = a.id
                                     JOIN participants p ON p.id = p2a.participant_id AND p.organisation_id = a.organisation_id
                                     WHERE a.id = ? AND a.organisation_id = ?', $action_id, $organisation_id)->fetchAll();
    }

    /**
     * @param int $action_id
     * @param int $stuff_id
     * @return void
     */
    public function removeActionStuf(ArrayHash $data){
        $this->db->table('action2stuff')->where('id', $data->id)->delete();
    }

    public function addActionStuff(ArrayHash $data){
        $this->db->table('action2stuff')->insert($data);
    }

    /**
     * @param int $id
     * @return false|\Nette\Database\Table\ActiveRow
     */
    public function getAction(int $id){
        return $this->db->table('actions')->where('id', $id)->fetch();
    }

    /**
     * @param int $id
     * @return int
     */
    public function getActionStatus(int $id){
        $data = $this->db->table('actions')->where('id', $id)->fetch();
        $today = new DateTime();
        $start = new DateTime($data->starttime);
        $stop = new DateTime($data->stoptime);
        if (($start->format('Y-m-d') == '-0001-11-30')
            || ($start->format('Ymd') > $today->format('Ymd'))
            || ($stop->format('Y-m-d') == '-0001-11-30')) {return Types::PLANNED;}
        if (($start->format('Ymd') <= $today->format('Ymd'))
            && ($stop->format('Ymd') >= $today->format('Ymd'))) {return Types::RUNNING;}
        if ($stop->format('Ymd') < $today->format('Ymd')) {return Types::DONE;}
        return 0;
    }

    /**
     * @param int $id
     * @param int $organisation_id
     * @return array|\Nette\Database\IRow[]
     */
    public function getActionDocuments(int $id, int $organisation_id){
        return $this->db->query('SELECT f.* , ad.action_id
                                     FROM files f
                                     JOIN action_documents ad ON ad.file_id = f.id
                                     JOIN actions a ON a.id = ad.action_id
                                     WHERE a.id = ?
                                     AND a.organisation_id = ?', $id, $organisation_id)->fetchAll();
    }
	
	/**
	 * @param int $action_id
	 * @param int $organisation_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getActionStuffStructure(int $action_id, int $organisation_id)
	{
		return $this->db->query('SELECT a.id, s.name, s.surname, s2t.name AS structure_name
									 FROM actions a
									 JOIN action2stuff a2s ON a2s.action_id = a.id
									 JOIN stuff2type s2t ON s2t.id = a2s.stuff_type
									 JOIN stuff s ON s.id = a2s.stuff_id AND s.organisation_id = a.organisation_id
									 WHERE a.id = ? AND (a.organisation_id = ? OR a.organisation_id IS NULL)
									 ORDER BY s2t.`order` ', $action_id, $organisation_id)->fetchAll();
	}
	
    /**
     * @param ArrayHash $data
     * @return void
     */
    public function addAction(ArrayHash $data){
        $this->db->table('actions')->insert($data);
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function editAction(ArrayHash $data){
        $this->db->table('actions')->where('id', $data->id)->update($data);
    }
}