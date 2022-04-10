<?php

namespace App\Model;

use Nette;

class UserModel
{
    public $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $user_id
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     */
    public function loadUserData($user_id){
        return $this->database->query('SELECT u.id, u.email, s.name, s.surname, s.alias, s.photo, s.birthday
                                    FROM users u
                                    JOIN stuff s ON s.id = u.stuff_id
                                    WHERE u.id = ?', (int)$user_id)->fetch();
    }

    /**
     * @param $id
     * @return false|Nette\Database\Table\ActiveRow
     */
    public function loadUser($id){
        return $this->database->table('users')->select('id, username, email, stuff_id, enabled')->where('id = ?', $id)->fetch();
    }

    public function loadUserFromStuff($id){
        return $this->database->table('users')->select('id, username, email, stuff_id')->where('stuff_id = ?', $id)->fetch();
    }

    public function loadUsers(){
        return $this->database->table('users')->select('id, username, email, stuff_id, enabled')->fetchAll();
    }

    public function saveUser($username, $password, $email, $stuff_id, $enabled, $userid = null){
        $data = array('username'=>$username, 'password'=>$password, 'email'=>$email, 'stuff_id'=>$stuff_id, 'enabled'=>$enabled);
        if ($userid){
            $this->database->table('users')->where('id = ?',$userid)->update($data);
        } else {
            $this->database->table('users')->insert($data);
        }

    }

}