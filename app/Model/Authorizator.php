<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;

/**
 * Class Authorizator
 * @package App\Model
 */
class Authorizator implements Nette\Security\IAuthorizator
{

    use Nette\SmartObject;
    private $database;
    /** @var array */
    private $permissions;
    private $module;

    /**
     * Authorizator constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param string|null $role
     * @param string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        return true;
    }
}

