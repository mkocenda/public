<?php

namespace App\Service;

use App\Model\LogModel;
use Exception;
use Nette\Utils\ArrayHash;
use TelegramBot\Api\Client;
use App\Model\SettingsModel;
use DateTime;
use App\Model\Translator;

class TelegramService
{

	private $api_key;
	private $chat_id;

	/** @var Client */
	private $client;
    private $settingsModel;
	public $logModel;
	public $translator;
	public function __construct(SettingsModel $settingsModel, LogModel $logModel, Translator $translator)
	{
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
        $this->api_key = $this->settingsModel->getSettingsByModuleProperty('telegram','API_KEY', $organisation_id)->value;
        $this->chat_id = $this->settingsModel->getSettingsByModuleProperty('telegram','CHAT_ID', $organisation_id)->value;
        $this->client = new Client($this->api_key);
    }

	public function sendMessage(string $color, string $message, int $organisation_id, $channel = '')
	{
		$this->setCommunication($organisation_id);
		$now = new DateTime();
		try {
			$this->client->sendMessage($this->chat_id, 'Nové upozornění ze systému - '.$message .' - '.$now->format('d.m.Y H:i'));
			$this->logModel->log('telegram_send', $this->translator->translate('message_send_successfully'));
		}
		catch (Exception $e)
		{
			$this->logModel->log('telegram_send', $this->translator->translate('message_send_unsuccessfully'),NULL,'DANGER',$e->getMessage());
		}
	}

	public function sendTestMessage()
	{
		$now = new DateTime();
		try {
			$this->client->sendMessage($this->chat_id, 'Testovací zpráva - '.$now->format('d.m.Y H:i'));
			$this->logModel->log('telegram_send', $this->translator->translate('message_send_successfully'));
		}
		catch (TelegramBot\Api\Exception $e)
		{
			$this->logModel->log('telegram_send', $this->translator->translate('message_send_unsuccessfully'),NULL,'DANGER',$e->getMessage());
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
}