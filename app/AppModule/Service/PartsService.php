<?php

namespace App\App\Service;

use App\Model\PartsModel;
use Nette\Utils\ArrayHash;
use App\Model\FileModel;
class PartsService
{
    public $partModel;
    public $fileMmodel;

    public function __construct(PartsModel $partsModel, FileModel $fileModel)
    {
        $this->partModel = $partsModel;
        $this->fileModel = $fileModel;
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function savePart(ArrayHash $data)
    {
        $image = $data->image_id;
        unset($data->cancel);
        if ($image->getError() <> 0) {
            unset($data->image_id);
        } else{
            $data->image_id = $this->fileModel->saveFile($image->getName, 'material',$data->getTmpFile(), null, $data->organisation_id);
        }
        if ($data->id) {
            $this->partModel->setPart($data);
        } else {
            $this->partModel->addPart($data);
        }
    }
	
	public function deletePart(int $id, int $organisation_id)
	{
		$this->partModel->deletePart($id, $organisation_id);
	}
}