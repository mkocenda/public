<?php

namespace App\Model;

use Nette\Utils\DateTime;

class LogModel extends DBModel
{

    /**
     * @param string $action
     * @param string $message
     * @param $state
     * @return void
     */
    public function log(string $action, string $message, $user_id = null, $level = 'info', $data = null)
    {
        $now = new DateTime();
        $data = array('action' => $action, 'message' => $message, 'level' => $level, 'user_id' => $user_id, 'data' => $data, 'created_at' => $now);
        $this->db->table('log')->insert($data);
    }
}