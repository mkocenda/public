<?php

namespace App\App\Service;

use App\Model\SettingsModel;
use Nette\Utils\ArrayHash;

class SettingsService
{

	public $settingsModel;
	public function __construct(SettingsModel $settingsModel)
	{
		$this->settingsModel = $settingsModel;
	}

	/**
	 * @param ArrayHash $data
	 * @param int $organisation_id
	 * @return void
	 */
	public function saveSettings(ArrayHash $data, int $organisation_id)
	{
		$operators = $data->operators;
		unset($data->cancel);
		unset($data->operators);
		foreach ($data as $key => $value) {
			$_data = explode('__', $key);
			$record = new ArrayHash();
			$record->module = $_data[0];
			$record->property = $_data[1];
			$record->value = $value;
			$record->organisation_id = $organisation_id;
			$this->settingsModel->saveSettingsModuleProperty($record);
		}
		$this->settingsModel->resetOperatorsByOrganisationID($organisation_id);
		foreach ($operators as $key => $operator) {
			$this->settingsModel->setOperator($operator, $organisation_id);
		}
	}
}