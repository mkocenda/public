<?php

namespace App\App\Service;

use App\Model\ActionModel;
use App\Model\ParticipantsModel;
use Nette\Utils\ArrayHash;


class ActionsService
{

    public $actionModel;
    public $participantsModel;

    public function __construct(ActionModel $actionModel, ParticipantsModel $participantsModel)
    {
        $this->actionModel = $actionModel;
        $this->participantsModel = $participantsModel;
    }

    /**
     * @param $data
     * @return void
     */
    public function saveAction(ArrayHash $data)
    {
        unset($data->cancel);
        if ($data->id) {
            $this->actionModel->editAction($data);
        } else {
            $this->actionModel->addAction($data);
        }
    }

    public function removeActionStuff(ArrayHash $data)
    {
        $this->actionModel->removeActionStuf($data);
    }

    public function addActionStuff(ArrayHash $data)
    {
        $this->actionModel->addActionStuff($data);
    }

    public function addParticipantToAction(int $participant_id, int $action_id)
    {
        $data = array('participant_id' => $participant_id, 'action_id' => $action_id);
        $this->participantsModel->addParticipantsToAction($data);
    }

    public function removeParticipantFromAction(int $participant_id, int $action_id)
    {
        $data = new ArrayHash();
        $data->participant_id = $participant_id;
        $data->action_id = $action_id;
        $this->participantsModel->removeParticipantsFromAction($data);
    }

    public function addWaitingParticipant(int $participant_id, int $action_id)
    {
        $data = array('participant_id' => $participant_id, 'action_id' => $action_id);
        $this->participantsModel->addWaitingParticipant($data);
    }

    public function removeWaitingParticipant(int $participant_id, int $action_id)
    {
        $data = new ArrayHash();
        $data->participant_id = $participant_id;
        $data->action_id = $action_id;
        $this->participantsModel->removeWaitingParticipant($data);
    }

}