<?php

namespace App\App\Service;

use App\Model\ParticipantsModel;
use App\Model\ParentsModel;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;
use App\Model\FileModel;

class ParticipantsService
{

	public $participantsModel;
	public $parentsModel;
	public $fileModel;

	public function __construct(ParticipantsModel $participantsModel, ParentsModel $parentsModel, FileModel $fileModel)
	{
		$this->participantsModel = $participantsModel;
		$this->parentsModel = $parentsModel;
		$this->fileModel = $fileModel;
	}

	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveParticipant(ArrayHash $data, int $organisation_id)
	{
		unset($data->cancel);
		$data->parents_id = ($data->parents_id == "") ? null : $data->parents_id;
        $data->organisation_id = $organisation_id;
		if ($data->id) {
			$this->participantsModel->saveParticipant($data);
		} else {
			$this->participantsModel->addParticipant($data);
		}
	}

	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function saveParent(ArrayHash $data)
	{
		unset($data->cancel);
		if ($data->id) {
			$this->parentsModel->saveParent($data);
		} else {
			$this->parentsModel->addParent($data);
		}
	}

	/**
	 * @param ArrayHash $data
	 * @return void
	 */
	public function addPill(ArrayHash $data)
	{
		unset($data->cancel);
		$this->participantsModel->addPill($data);
	}

	/**
	 * @param ArrayHash $data
	 * @param $organisation_id
	 * @param $user_id
	 * @return void
	 */
	public function addDocument(ArrayHash $data, $organisation_id = null, $user_id = null)
	{
		unset($data->cancel);
		/** @var FileUpload $file */
		$file = $data->file_id;
		$file_id = $this->fileModel->saveFile($file->getName(),'actions/participants/documents', $file->getTemporaryFile(), $organisation_id, $user_id);

		$this->participantsModel->addParticipantsDocument($data->participant_id, $data->action_id, $file_id);
	}
}