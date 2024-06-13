<?php

namespace App\Model;

use Nette\Database\Context;

class DBModel
{
    public $db;
    public function __construct(Context $db)
    {
        $this->db = $db;
    }
}