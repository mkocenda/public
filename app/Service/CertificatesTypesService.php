<?php

namespace App\Service;

use App\Model\CertificatesTypesModel;

class CertificatesTypesService
{
    public $certificatesTypesModel;

    public function __construct(CertificatesTypesModel $certificatesTypesModel){
        $this->certificatesTypesModel = $certificatesTypesModel;
    }

    /**
     * @param $name
     * @param $color
     * @param $status
     * @return void
     */
    public function addCertificateType($name, $color, $status){
        $this->certificatesTypesModel->saveCertificatesType($name, $color, $status);
    }

    /**
     * @param $name
     * @param $color
     * @param $status
     * @param $id
     * @return void
     */
    public function editCertificateType($name, $color, $status, $id){
        $this->certificatesTypesModel->saveCertificatesType($name, $color, $status, $id);
    }


}