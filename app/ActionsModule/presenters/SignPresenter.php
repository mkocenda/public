<?php

namespace App\ActionsModule;

use Nette\Application\UI\Form;
use Nette;


class SignPresenter extends BasePresenter
{

    public function __construct()
    {

    }

    protected  function createComponentSignForm()
    {
        $form = new Form();
        $form->addText('username','Uživ. jméno')->setRequired(true)->setHtmlAttribute('class','form-control');
        $form->addPassword('password','Heslo')->setRequired(true)->setHtmlAttribute('class','form-control');
        $form->addSubmit("submit", "Přihlásit")->setHtmlAttribute('class', 'btn btn-success pull-right');

        $form->onSuccess[] = [$this, "login"];

        return $form;
    }

    public function renderLogin(){

    }
}