<?php

namespace App\Model;

use Cassandra\Date;
use DateTime;
use Nette\ArrayHash;
use Nette\Database\Context;

class MessagesModel extends DBModel
{
	
	private $usersModel;

	public function __construct(UsersModel $usersModel, Context $db)
	{
		$this->usersModel = $usersModel;
		parent::__construct($db);
	}
	
	/**
	 * @param int $user_id
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getUserMessagesList(int $user_id, int $organisation_id)
	{
		return $this->db->query('SELECT id, caption, message, created_at, unread, CONCAT(NAME,surname) AS fullname
									FROM(
											SELECT messages.id, caption, message, created_at, unread,
												IFNULL(CONCAT(stuff.name, " ", stuff.surname), "") AS name,
												IFNULL(CONCAT(parents.name, " ", parents.surname), "") AS surname,
												messages.deleted
											FROM messages
											LEFT JOIN stuff ON stuff.user_id = messages.fromuser_id AND stuff.organisation_id = ?
											LEFT JOIN parents ON parents.user_id = messages.fromuser_id AND parents.organisation_id = ?
											WHERE touser_id = ? OR fromuser_id = ? ) AS _messages
									WHERE deleted = 0
									ORDER BY created_at DESC', $organisation_id, $organisation_id, $user_id, $user_id)
					->fetchAll();
	}
	
	
	/**
	 * @param int $user_id
	 * @return \Nette\Database\Table\Selection
	 */
	public function getUserMessages(int $user_id)
	{
		$records =  $this->db->query('SELECT id, caption, message, created_at, unread, CONCAT(NAME,surname) AS fullname
										  FROM(
											SELECT messages.id, caption, message, created_at, unread,
												IFNULL(CONCAT(stuff.name, " ", stuff.surname), "") AS name,
												IFNULL(CONCAT(parents.name, " ", parents.surname), "") AS surname
											FROM messages
											LEFT JOIN stuff ON stuff.user_id = messages.fromuser_id
											LEFT JOIN parents ON parents.user_id = messages.fromuser_id
											WHERE touser_id = ? AND messages.deleted = 0) AS _messages
										  ORDER BY created_at DESC', $user_id)
			->fetchAll();
		$data = array();
		foreach ($records as $record)
		{
			$data[] = array('id'=>$record->id, 'fromuser'=>$record->fullname,
				'caption'=>$record->caption, 'message'=>$record->message, 'created'=>$record->created_at, 'unread'=>$record->unread);
		}
		return $data;
	}
	
	/**
	 * @param $user_id
	 * @return int
	 */
	public function countUnreadUserMessages(int $user_id)
	{
		return count($this->db->table('messages')
			->where('unread', 1)
			->where('deleted', 0)
			->whereOr(array('fromuser_id'=>$user_id, 'touser_id'=>$user_id))
			->fetchAll());
	}
	
	/**
	 * @param int $id
	 * @param int $user_id
	 * @param int $organisation_id
	 * @return bool|\Nette\Database\IRow|\Nette\Database\Row
	 */
	public function getMessageById(int $id, int $user_id, int $organisation_id){
		$this->setReaded($id);
		return $this->db->query('SELECT id, caption, message, created_at, unread, CONCAT(NAME,surname) AS fullname
									FROM(
											SELECT messages.id, caption, message, created_at, unread,
												IFNULL(CONCAT(stuff.name, " ", stuff.surname), "") AS name,
												IFNULL(CONCAT(parents.name, " ", parents.surname), "") AS surname
											FROM messages
											JOIN users u ON u.id =  messages.touser_id
											LEFT JOIN stuff ON stuff.user_id = u.id AND stuff.organisation_id = ?
											LEFT JOIN parents ON parents.user_id = u.id AND parents.organisation_id = ?
											WHERE messages.id = ? AND (touser_id = ? OR fromuser_id = ? )
											AND messages.deleted = 0
										) AS _messages', $organisation_id, $organisation_id, $id, $user_id, $user_id)
			->fetch();
	}
	
	/**
	 * @param int $organisation_id
	 * @param $exlude_user
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getRecipients(int $organisation_id, $exlude_user = 0)
	{
		return $this->db->query('SELECT CONCAT(`name`, " ", surname) AS fullname, user_id
						  		     FROM parents
						  		     WHERE organisation_id = ? AND user_id <> ?
						  		     UNION
						  		     SELECT CONCAT(`name`, " ", surname) AS fullname, user_id
						  		     FROM stuff
						  		     WHERE organisation_id = ? AND user_id <> ? ', $organisation_id, $exlude_user, $organisation_id, $exlude_user)
			->fetchPairs('user_id','fullname');
	}
	
	/**
	 * Get list messages for sending
	 * @return array|\Nette\Database\IRow[]
	 */
	public function getMailQueue()
	{
		return $this->db->query('SELECT mq.id, mo.name AS mo_name, m.caption, m.message, p.name AS p_name, p.surname AS p_surname, p.user_id, p.`type`, u.username, mo.organisation_id
			    					 FROM message_queue mq
							  		 JOIN messages m ON m.id = mq.message_id
							  		 JOIN v_persons p ON p.user_id = m.touser_id AND p.organisation_id = m.organisation_id
							  		 JOIN users u on p.user_id = u.id
							  		 JOIN message_operators mo ON mo.organisation_id = m.organisation_id AND mo.enabled = 1
							  		 WHERE mq.send_time IS NULL AND m.deleted = 0')->fetchAll();
	}
	
	/**
	 * Set send time to sended message in queue
	 * @param int $id
	 * @return void
	 */
	public function setMailSend(int $id)
	{
		$today = new DateTime();
		$this->db->table('message_queue')->where('id',$id)->update(array('send_time'=>$today));
	}
	
	/**
	 * @param array $data
	 * @return void
	 */
	public function updateMessage(array $data)
	{
		unset($data->submit);
		$date = new DateTime();
		$data->updated = $date;
		$this->db->table('messages')
				 ->where('id', $data->id)
				 ->update($data);
	}
	
	/**
	 * @param array $data
	 * @return string
	 */
	public function insertMessage(array  $data)
	{
		unset($data->submit);
		$message_id = $this->db->table('messages')
				->insert($data);
		$this->db->table('message_queue')->insert(array('message_id'=>$message_id));
		return $message_id;
	}
	
	/**
	 * @param int $id
	 * @return void
	 */
	public function setReaded(int $id)
	{
		$date = new DateTime();
		$this->db->table('messages')
				->where('id', $id)
				->update(array('unread'=>0, 'readed_at'=>$date));
	}
	
	public function deleted(int $id)
	{
		$date = new DateTime();
		$this->db->table('messages')
				->where('id', $id)
				->update(array('deleted'=>1, 'deleted_at'=>$date));
	}
}