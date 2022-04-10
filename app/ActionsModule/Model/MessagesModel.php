<?php

namespace App\ActionsModule\Model;

use Nette;

class MessagesModel
{

    public $database;

    public function __construct(Nette\Database\Context $database){
        $this->database = $database;
    }

    public function getMessages($user_id){
        return $this->database->query('SELECT m.*, CONCAT(s1.surname," ",s1.name) AS from_fullname, CONCAT(s2.surname," ",s2.name) AS to_fullname 
                                           FROM messages m
                                           JOIN users u1 ON u1.id = m.fromuser_id
                                           JOIN users u2 ON u2.id = m.touser_id
                                           JOIN stuff s1 ON s1.id = u1.stuff_id
                                           JOIN stuff s2 ON s2.id = u2.stuff_id
                                           WHERE m.touser_id = ?
                                           ORDER BY created_at', $user_id)->fetchAll();
    }

    public function countUnreadMessages($user_id){
        return $this->database->table('messages')
                              ->where('touser_id = ?',$user_id)
                              ->where('unread = ?',1)
                              ->count('*');
    }

}