<?php

namespace App\Presenter;

use App\Model\Types;
use Nette;
use App\Model\MessagesModel;
use App\Service\TelegramService;
use App\Service\SlackService;
use App\Service\SmtpService;
use App\Service\CronService;
use App\Model\Translator;
use App\Model\LogModel;
use App\Model\PillsModel;
use App\Model\SettingsModel;
class CronPresenter extends Nette\Application\UI\Presenter
{
	
	public $messageModel;
	public $telegramService;
	public $slackService;
	public $smtpService;
	public $cronService;
	public $logModel;
	public $pillsModel;
	public $settingsModel;
	public $translator;
	public function __construct(MessagesModel $messageModel,
	                            SlackService $slackService,
	                            TelegramService $telegramService,
	                            SmtpService $smtpService,
								CronService $cronService,
								LogModel $logModel,
								PillsModel $pillsModel,
								SettingsModel $settingsModel,
								Translator $translator){
		$this->messageModel = $messageModel;
		$this->slackService = $slackService;
		$this->telegramService = $telegramService;
		$this->smtpService = $smtpService;
		$this->cronService = $cronService;
		$this->logModel = $logModel;
		$this->pillsModel = $pillsModel;
		$this->settingsModel = $settingsModel;
		$this->translator = $translator;
	}
	
	public function actionRun()
	{
		$this->cronService->executeJobs();
		$this->presenter->terminate();
	}
	
	/**
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function actionSend()
	{
		$messages = $this->messageModel->getMailQueue();
		foreach ($messages as $message)
		{
			try {
				$className = $message->mo_name.'Service';
				$class = $this->$className;
				$class->send($message);
				/* Označí zprávu jako odeslanou */
			    $this->messageModel->setMailSend($message->id);
				$this->logModel->log('cron_send',$this->translator->translate('task_run_successfully'));
			} catch (\Exception $e)
			{
				$this->logModel->log('cron_send',$this->translator->translate('task_run_unsuccessfully'),NULL, 'DANGER');
			}
		}
		$this->presenter->terminate();
	}

	public function actionAlert()
	{
		$messages = $this->messageModel->getMailQueue();
		foreach ($messages as $message)
		{
			try {
				$className = $message->mo_name.'Service';
				$class = $this->$className;
				$class->send($message);
				/* Označí zprávu jako odeslanou */
			    $this->messageModel->setMailSend($message->id);
				$this->logModel->log('cron_send',$this->translator->translate('task_run_successfully'));
			} catch (\Exception $e)
			{
				$this->logModel->log('cron_send',$this->translator->translate('task_run_unsuccessfully'),NULL, 'DANGER');
			}
		}
		$this->presenter->terminate();
	}
	
	public function actionMedicaments()
	{
		$pills = $this->pillsModel->getAlertMedicaments();
		foreach ($pills as $pill) {
			$dosage = explode('-',Types::DOSAGE[$pill->dosage]);
			$now = new \DateTime();
			$hour = $now->format('H');
			$beginHour = $this->settingsModel->getSettingsByModuleProperty('actions', 'BeginHour', $pill->organisation_id)->value;
			$endHour = $this->settingsModel->getSettingsByModuleProperty('actions', 'EndHour', $pill->organisation_id)->value;
			$halfTime = round(((int) $endHour - (int) $beginHour) / 2) + (int) $beginHour;
			$send = 0;
			if (($hour == $beginHour) && ($dosage[0] == 1)) { $send = 1; }
			if (($hour == $halfTime) && ($dosage[1] == 1)) { $send = 1; }
			if (($hour == $endHour) && ($dosage[2] == 1)) { $send = 1; }
			if ($send) {
				$message = 'Podat lék '.$pill->pill_name.' účastníkovi '.$pill->participant_name.' '.$pill->participant_surname;
				$data = array('fromuser_id'=>1, 'touser_id'=>$pill->recipient_id, 'caption'=>'Upozornění na léky', 'message'=>$message, 'organisation_id'=>$pill->organisation_id);
				$this->messageModel->insertMessage($data);
			}
		}
		$this->presenter->terminate();
	}
	
}