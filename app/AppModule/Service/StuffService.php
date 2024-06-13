<?php

namespace App\App\Service;

use App\Model\StuffModel;
use Nette\Utils\ArrayHash;

class StuffTypeService
{
	
	public $stuffModel;
	
	public function __construct(StuffModel $stuffModel)
	{
		$this->stuffModel = $stuffModel;
	}
	
	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveType(ArrayHash $data)
	{
		unset($data->cancel);
		$this->stuffModel->saveStuffType($data);
	}
	
}