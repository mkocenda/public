<?php

namespace App\Service;

use Exception;
use Nette\Utils\ArrayHash;
use App\Model\StuffModel;
use App\Model\ParentsModel;
use PHPMailer;
use App\Model\Translator;
use App\Model\LogModel;

class SmtpService
{

	public $message;
	public $phpMailer;
	public $stuffModel;
	public $parentsModel;
	public $translator;
	public $logModel;
	
	public function __construct(ParentsModel $parentsModel, StuffModel $stuffModel, LogModel $logModel, Translator $translator)
	{
		$this->parentsModel = $parentsModel;
		$this->stuffModel = $stuffModel;
		$this->logModel = $logModel;
		$this->translator = $translator;
	}
	
	/**
	 * @param int $user
	 * @param string $type
	 * @return false|\Nette\Database\Table\ActiveRow
	 */
	public function getRecipient(int $user, string $type)
	{
		switch ($type) {
			case 'parents':
				$recipient = $this->parentsModel->getParentByUserID($user);
				break;
			case 'stuff':
				$recipient = $this->stuffModel->getStuffByUserID($user);
				break;
		}
		return $recipient;
	}
	
	/**
	 * @return void
	 * @throws \phpmailerException
	 */
	public function init()
	{
		$this->phpMailer = new PHPMailer();
		$this->phpMailer->isSMTP();
		$this->phpMailer->Host = 'localhost';
		$this->phpMailer->Port = 25;
//		$this->phpMailer->SMTPSecure = "";
//		$this->phpMailer->SMTPAuth = false;
//		$this->phpMailer->Username = '';
//		$this->phpMailer->Password = '';
		$this->phpMailer->CharSet = 'UTF-8';
		$this->phpMailer->IsHTML(true);
		$this->phpMailer->setFrom('automat@adam.devel');
	}
	
	/**
	 * @param ArrayHash $data
	 * @return bool
	 */
	public function send(ArrayHash $data)
	{
		$this->init();
		$recipientData = $this->getRecipient($data->user_id, $data->type);
		$this->phpMailer->addAddress($recipientData->email);
		$this->phpMailer->Subject = $data->caption;
		$this->phpMailer->Body = $data->message;
		try {
			$this->phpMailer->send();
			$this->logModel->log('smtp_send', $this->translator->translate('message_send_successfully'));
			return true;
		} catch (Exception $e)
		{
			$this->logModel->log('smtp_send', $this->translator->translate('message_send_unsuccessfully'),NULL,'DANGER',$e->getMessage());
			return false;
		}
	}
}