<?php

namespace App\App\Service;

use App\Model\CertificatesModel;

class CertificatesTypesService
{
	
	public $certificateModel;
	public function __construct(CertificatesModel $certificatesModel)
	{
		$this->certificateModel = $certificatesModel;
	}
	
	public function saveType($data)
	{
		unset($data->cancel);
		if (isset($this->id)){
			$this->certificateModel->addCertificateType($data);
		} else{
			$this->certificateModel->updateCertificateType($data);
		}
	}
	
}