<?php

namespace App\Service;

use App\Model\CertificatesModel;
use App\Model\ProfileModel;
use App\Model\UserModel;

class UserService
{

    public $certificatesModel;

    public $profileModel;

    public $userModel;

    public function __construct (CertificatesModel $certificatesModel, ProfileModel $profileModel, UserModel $userModel)
    {
        $this->certificatesModel = $certificatesModel;
        $this->profileModel = $profileModel;
        $this->userModel = $userModel;
    }

    public function addUserCertificate($userid, $certtype, $validfrom, $validto, $certfile){
        if ($validto === '') {$validto = null;}

        $this->certificatesModel->saveCertificate($userid, $certtype, $validfrom, $validto, $certfile);
    }

    public function modifyUserData($userid, $name, $surname, $alias, $email, $birthday, $photo) {
        $this->profileModel->saveProfileData($userid, $name, $surname, $alias, $email, $birthday, $photo);
    }

    public function addUser($username, $password, $email, $stuff_id, $enabled){
        $this->userModel->saveUser($username, $password, $email, $stuff_id, $enabled);
    }

    public function modifyUser($userid, $username, $password, $email, $stuff_id, $enabled){
        $this->userModel->saveUser($username, $password, $email, $stuff_id, $enabled, $userid);

    }

}