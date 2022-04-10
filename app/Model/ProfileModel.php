<?php

namespace App\Model;

use Nette;

class ProfileModel
{

    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function loadUserProfileImage($userid){
        $profileImage = $this->database->table('profile')->where('userid=?',$userid)->fetch();
        $filename = $profileImage ? $profileImage->photo : '0.png';
        $file = fopen(__DIR__.'/../../data/profile/'.$filename,'r');
        return base64_encode(fread($file,filesize(__DIR__.'/../../data/profile/'.$filename)));
    }

    public function loadProfileData($userid){
        $profileData = $this->database->query('SELECT u.email, s.name, s.surname, s.birthday, s.alias 
                                                   FROM users u 
                                                   JOIN stuff s ON s.id = u.stuff_id
                                                   WHERE u.id = ?',$userid)->fetch();
        return $profileData;
    }

    public function saveProfileData($userid, $name, $surname, $alias, $email, $birthday, $photo){

        if ($photo->size > 0) {
            $data['photo'] = $photo->name;
            $this->database->table('profile')->where('userid = ?',$userid)->update($data);
        }

        $data = array('email'=>$email);
        $this->database->table('users')->where('id = ?', $userid)->update($data);

        $stuff = $this->database->table('users')->where('id = ?', $userid)->fetch();

        $data = array('name'=>$name, 'surname'=>$surname, 'alias'=>$alias, 'birthday'=>$birthday);
        $this->database->table('stuff')->where('id = ?', $stuff->stuff_id)->update($data);
    }

}