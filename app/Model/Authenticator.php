<?php

namespace App\Model;

use Nette\Security\Identity;
use Nette\Security\User;

/**
 * Class Authenticator
 * @package App\Model
 */
class Authenticator extends DBModel
{

    /** @var UsersModel */
    private $userModel;

    /** @var StuffModel */
    private $stuffModel;

    /** @var User */
    private $user;

    public function __construct(User $user, UsersModel $userModel, StuffModel $stuffModel)
    {
        $this->user = $user;
        $this->userModel = $userModel;
        $this->stuffModel = $stuffModel;
    }

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    public function login(string $username, string $password)
    {
        $user = $this->userModel->loginUser($username, $password);
        if ($user) {
            $roles = $this->userModel->getUserRole($user->id);
            $stuff = $this->stuffModel->getStuffByUserID($user->id);
            $identity = array_merge($user->toArray(), (array)$roles, $stuff->toArray());
            unset($identity["password"]);
            $this->user->login(new Identity($user->id, $roles, $identity));
        } else {
            throw new \Exception('login_failed');
        }
    }
}
