<?php

namespace App\Service;

use App\Model\LogModel;

class LogService
{

    public $logModel;

    public function __construct(LogModel $logModel)
    {
        $this->logModel = $logModel;
    }

    /**
     * @param string $action
     * @param string $message
     * @param $level
     * @return void
     */
    public function log(string $action, string $message, $user_id = null, $level = 'info', $data = null)
    {
        $this->logModel->log($action, $message, $user_id, $level, $data);
    }
}