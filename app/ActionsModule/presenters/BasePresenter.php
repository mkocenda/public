<?php

namespace App\ActionsModule;

abstract class BasePresenter extends \App\BasePresenter {

    public function startup() {

		parent::startup();

        bdump('Startup:Front:Presenter');
	}

	public function beforeRender() {

		parent::beforeRender();
        bdump('Before:Front:Presenter');
	}

    public function login($form, $data){
        $this->authenticator->login($data->username,$data->password);
        $this->redirect('Dashboard:');
    }



}
