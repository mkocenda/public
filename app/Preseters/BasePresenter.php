<?php

namespace App;

use App\ActionsModule\Model\MessagesModel;
use App\Model\UserModel;
use App\ActionsModule\Service\Authenticator;
use Nette;
use Nette\Utils\DateTime;
use App\Model\MenuModel;


abstract class BasePresenter extends Nette\Application\UI\Presenter {

	/** @var Nette\DI\Container @inject */
	public $container;

	/** @var Nette\Application\Application @inject */
	public $application;

    /** @var Authenticator @inject */
    public $authenticator;

    /** @var UserModel @inject */
    public $userModel;

    /** @var MenuModel @inject */
    public $menuModel;

    /** @var MessagesModel @inject */
    public $messagesModel;

    public $user;

    public $config;

    public function startup() {
		parent::startup();
        $this->config = $this->container->parameters;

        $today = new DateTime();
        $hour = (int)$today->format('H');

        if (($hour > 5)  && ($hour <=9))  { $this->template->greatings = 'Dobré ráno'; }
        if (($hour > 9)  && ($hour <=12)) { $this->template->greatings = 'Dobré dopoledne'; }
        if (($hour > 12) && ($hour <=16)) { $this->template->greatings = 'Dobré odpoledne'; }
        if (($hour > 16) && ($hour <=20)) { $this->template->greatings = 'Dobrý podvečer'; }
        if (($hour > 20) && ($hour <=22)) { $this->template->greatings = 'Dobrý večer'; }
        if (($hour > 22) || ($hour <=5))  { $this->template->greatings = 'Dobrý pozdní večer';}

        $userData = $this->userModel->loadUserData(1);

        $this->template->fullname = $userData->name.' '.$userData->surname;

        $this->user = new \StdClass();
        $this->user->role = [1=>'registered', 2=>'admin'];

        $this->template->menu =$this->menuModel->loadAppMenu(1);

        $this->template->user = $this->user;

        $this->getMessageCount();

        bdump('App>Startup:App:Presenter');
    }


	protected function beforeRender() {
        $this->template->frontPath = $this->config["frontPath"];
        $this->template->appName = $this->config["appName"];
        $this->template->frontImages = $this->config["frontImages"];
        $this->template->front = $this->config["front"];
        $this->template->dataPath = $this->config["dataPath"];

        bdump('Before:App:Presenter');
    }

    public function getMessageCount()
    {
        $this->template->messagesCount = $this->messagesModel->countUnreadMessages(1);
        $this->template->messages = $this->messagesModel->getMessages(1);
    }


}
