<?php

namespace App\App\Service;

use App\Model\PillsModel;
use Nette\Utils\ArrayHash;

class PillsService
{

    public $pillsModel;

    public function __construct(PillsModel $pillsModel)
    {
        $this->pillsModel = $pillsModel;
    }

    /**
     * @param int $direction
     * @param $formData
     * @param $tblData
     * @return false|string|string[]|null
     */
    public function formatDosage(int $direction, $formData = null, $tblData = null){
        switch ($direction){
            case 1: return implode('-',$formData); break; /* array tbl to tbl*/
            case 2: return explode('-',$tblData); break; /* from tbl to array*/
            default: return null; break;
        }
    }

    /**
     * @param ArrayHash $data
     * @return void
     */
    public function saveActionParticipantPills(ArrayHash $data){
        /** změna formátu dávkování */
        $data->dosage = $this->formatDosage(1, $data->dosage);
        if ($data->id){
            $this->pillsModel->saveActionParticipantPill($data);
        } else {
            $this->pillsModel->addActionParticipantPill($data);
        }
    }
	
	public function pillApply(array $data)
	{
		$this->pillsModel->pillApply($data);
	}
}