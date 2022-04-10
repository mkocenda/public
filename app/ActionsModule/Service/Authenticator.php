<?php

namespace App\ActionsModule\Service;

use Nette;

class Authenticator
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function login($username, $password)
    {
        $row = $this->database->table('users')
            ->where('username', 'marian')
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

/*
         if (!$this->passwords->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }
*/
        return new Nette\Security\Identity(
            1,
            array('registered', 'admin')
        );
    }
}