<?php

namespace App\Model;

use Nette;

class CertificatesModel
{

    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    public function loadUserCartificates($userid)
    {
        return $this->database->query('SELECT c.userid, c.id, c.validfrom, c.validto, c.certfile, ct.name, ct.backgroundcolor 
                                           FROM certificates c 
                                           JOIN certificates_type ct ON c.certtype = ct.id
                                           WHERE c.userid = ?
                                           ORDER BY c.validfrom',$userid)
                              ->fetchAll();
    }

    public function loadCertificate($userid, $certid){
        $certificate = $this->database->table('certificates')->where('userid = ?',$userid)->where('id = ?',$certid)->fetch();
        $file = fopen(__DIR__.'/../../data/certificates/'.$userid.'/'.$certificate->certfile,'r');
        return base64_encode(fread($file,filesize(__DIR__.'/../../data/certificates/'.$userid.'/'.$certificate->certfile)));
    }

    public function getCertificateFilename($userid, $certid){
        $certificate = $this->database->table('certificates')->where('userid = ?',$userid)->where('id = ?',$certid)->fetch();
        return __DIR__.'/../../data/certificates/'.$userid.'/'.$certificate->certfile;
    }

    public function saveCertificate($userid, $certtype, $validfrom, $validto, $certfile)
    {
        $data = array ('userid'=>$userid, 'certtype'=>$certtype, 'validfrom'=>$validfrom, 'validto'=>$validto, 'certfile'=>$certfile->name);

        if ($certfile->size > 0){
            /** Todo UPLOAD FILE */
        }
        $this->database->table('certificates')->insert($data);

    }

}