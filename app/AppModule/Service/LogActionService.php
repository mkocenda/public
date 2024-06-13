<?php

namespace App\App\Service;

use App\Model\ParticipantsModel;
use Nette\Utils\ArrayHash;
use App\Model\FileModel;

class LogActionService
{

    public $participantsModel;
    public $fileModel;

    public function __construct(ParticipantsModel $participantsModel,FileModel $fileModel){
        $this->participantsModel = $participantsModel;
        $this->fileModel = $fileModel;
    }
	
	/**
	 * @param ArrayHash $data
	 * @param int $created_by
	 * @param $organisation_id
	 * @return void
	 */
    public function addInjury(ArrayHash $data, int $created_by, $organisation_id = null){
        $file = $data->file;
        $file_id = null;
        if ($file->getName())
        {
           $file_id =  $this->fileModel->saveFile($file->getName(),'actions/logs',$file->getTemporaryFile(), $created_by, $organisation_id);
        }
        $this->participantsModel->addParticipantsRecord($data->participant_id, $data->action_id, $data->description, $created_by, $file_id);
    }
}