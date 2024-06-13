<?php

namespace App\Service;

use Exception;
use Maknz\Slack\Client;
use App\Model\SettingsModel;
use DateTime;
use Nette\Utils\ArrayHash;
use App\Model\LogModel;
use App\Model\Translator;

class SlackService
{

	public $client;
	public $settingsModel;
	public $logModel;
	public $translator;
	
	public function __construct(SettingsModel $settingsModel, LogModel $logModel, Translator $translator){
        $this->settingsModel = $settingsModel;
		$this->logModel = $logModel;
		$this->translator = $translator;
	}

    /**
     * Nastavuje komunikační parametry
     * @param int $organisation_id
     * @return void
     */
    public function setCommunication(int $organisation_id)
    {
        $this->client = new Client($this->settingsModel->getSettingsByModuleProperty('slack','SLACK_URL', $organisation_id)->value);
    }

	/**
	 * @param string $color
	 * @param string $message
	 * @return void
	 */
	public function sendMessage(string $color, string $message, int $organisation_id, $channel = '')
	{
		$this->setCommunication($organisation_id);
		$now = new DateTime();
		$channel = ($channel <> '') ? $channel : $this->settingsModel->getSettingsByModuleProperty('slack','SLACK_CHANNEL', $organisation_id)->value;
		try {
			$this->client->to('#'.$channel)->attach([
				'text'      => $message .' - '. $now->format('d.m.Y H:i'),
				'color'     => $color,
				'mrkdwn_in' => ['pretext', 'text']
			])->send('Nové upozornění ze systému');
			$this->logModel->log('slack_send', $this->translator->translate('message_send_successfully'));
		} catch (Exception $e)
		{
			$this->logModel->log('slack_send', $this->translator->translate('message_send_unsuccessfully'),NULL,'DANGER',$e->getMessage());
		}
	}
	
	/**
	 * @return void
	 */
	public function sendTest()
	{
		$now = new DateTime();
		try {
			$this->client->to('#test')->attach([
				'text'      => 'Test - '. $now->format('d.m.Y H:i'),
				'color'     => '00FFFF',
				'mrkdwn_in' => ['pretext', 'text']
			])->send('Testovací upozornění ze systému');
			$this->logModel->log('slack_send', $this->translator->translate('message_send_successfully'));
		} catch (Exception $e)
		{
			$this->logModel->log('slack_send', $this->translator->translate('message_send_unsuccessfully'),NULL,'DANGER',$e->getMessage());
		}
	}
	
	/**
	 * @param ArrayHash $data
	 * @return true
	 */
	public function send(ArrayHash $data)
	{
		$this->sendMessage('#FF0000', $data->message, $data->organisation_id, $data->username);
		return true;
	}
	
	/**
	 * @param ArrayHash $data
	 * @return true
	 */
	public function alert(ArrayHash $data)
	{
		$this->sendMessage('#FF0000', $data->message, $data->organisation_id, $data->username);
		return true;
	}
	
}