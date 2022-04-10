<?php

namespace App\ActionsModule;

use Nette\Security\Identity;
use Nette\Security\User;

class DashboardPresenter extends BasePresenter {

    /** @var $user  */
    public $user;

    public $identity;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function actionDefault() {
        bdump('Default');
    }

}
