<?php

namespace App\Model;

use Nette;

class CertificatesTypesModel
{

    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function loadCertificatesType(){
        return $this->database->table('certificates_type')->fetchpairs('id','name');
    }

    public function loadCertificatesTypeById($id){
        return $this->database->table('certificates_type')->where('id = ?', $id)->fetch();
    }


    public function loadAllCertificatesType(){
        return $this->database->table('certificates_type')->fetchAll();
    }

    public function saveCertificatesType($name, $backgroundcolor, $status, $id = null){
        $data = array('name'=>$name, 'backgroundcolor'=>$backgroundcolor, 'status'=>$status);
        if($id) {
            $this->database->table('certificates_type')->where('id = ?', $id)->update($data);
        } else{
            $this->database->table('certificates_type')->insert($data);
        }
    }

}