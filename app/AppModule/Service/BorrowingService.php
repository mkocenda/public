<?php

namespace App\App\Service;

use App\Model\BorrowingModel;
use App\Model\ReservationsModel;
use App\Model\ActionModel;

class BorrowingService
{

    public $borrowingModel;
    public $reservationsModel;
    public $actionModel;
    public function  __construct(BorrowingModel $borrowingModel, ReservationsModel $reservationsModel, ActionModel $actionModel)
    {
        $this->borrowingModel = $borrowingModel;
        $this->reservationsModel = $reservationsModel;
        $this->actionModel = $actionModel;
    }

    /**
     * Copy from reservations to borrowing
     * @param int $action_id
     * @param int $organisation_id
     * @return void
     */
    public function reservations2Borrowing(int $action_id, int $organisation_id)
    {
        $reservations = $this->reservationsModel->getReservationsForBorrowing($action_id, $organisation_id);
        $action = $this->actionModel->getAction($action_id);
        foreach ($reservations as $reservation)
        {
            unset($reservation->reserved_date);
            $reservation->date_from = $action->starttime;
            $reservation->date_to = $action->stoptime;
            $this->borrowingModel->addBorrowing($reservation);
        }
    }

    public function deleteBorrowing(int $part_id, int $action_id)
    {
        $this->borrowingModel->deleteBorrowing($part_id, $action_id);
    }
}