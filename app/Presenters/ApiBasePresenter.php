<?php

namespace App\Presenter;

use Nette;

abstract class ApiBasePresenter extends Nette\Application\UI\Presenter
{
    public function startup()
    {
        \Tracy\Debugger::$showBar = false;
        parent::startup();
    }
}
