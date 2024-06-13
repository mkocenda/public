<?php

namespace App\Service;

use App\Model\MessagesModel;
use DateTime;

class MessageService
{
	
	private $messageModel;
	
	public function __construct(MessagesModel $messageModel)
	{
		$this->messageModel = $messageModel;
	}
	
	public function getUserMessages(int $user_id)
	{
		$response = array();
		$response['count'] = $this->messageModel->countUnreadUserMessages($user_id);
		$response['messages'] = $this->messageModel->getUserMessages($user_id);
		return $response;
	}
	
	/**
	 * @param $data
	 * @return void
	 */
	public function saveMessage($data)
	{
		foreach ($data->recipient_id as $recipient_id )
		{
			$created_at = new DateTime($data->created_at);
			$record = array('fromuser_id'=>$data->fromuser_id,
							'touser_id'=>$recipient_id,
							'caption'=>$data->caption,
							'message'=>$data->message,
							'created_at'=>$created_at);
			$this->messageModel->insertMessage($record);
		}
	}
	
	/**
	 * Set all messages as readed
	 * @param int $user_id
	 * @return void
	 */
	public function setReaded(int $user_id)
	{
		$messages = $this->messageModel->getUserMessages($user_id);
		foreach ($messages as $message) {
			$this->messageModel->setReaded($message['id']);
		}
	}
	
	public function setBulkReaded(int $message_id)
	{
    	$this->messageModel->setReaded($message_id);
	}
	
	public function setBulkDeleted(int $message_id)
	{
		$this->messageModel->deleted($message_id);
	}
	
	
}