<?php

namespace App\App\Service;

use App\Model\ReservationsModel;
use Nette\Utils\ArrayHash;

class ReservationsService
{
	
	public $reservationsModel;
	
	public function __construct(ReservationsModel $reservationsModel)
	{
		$this->reservationsModel = $reservationsModel;
	}
	
	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveReservation(ArrayHash $data)
	{
		unset($data->cancel);
		if ($data->id)
		{
			$this->reservationsModel->setReservation($data);
		} else {
			$this->reservationsModel->addReservation($data);
		}
	}
	
	/**
	 * @param int $id
	 * @param int $organisation_id
	 * @return void
	 */
	public function deleteReservation(int $id, int $organisation_id)
	{
		$this->reservationsModel->deleteReservation($id, $organisation_id);
	}
}