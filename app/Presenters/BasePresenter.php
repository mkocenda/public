<?php

namespace App\Presenter;

use App\Model\Authenticator;
use App\Service\LogService;
use App\Service\MessageService;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use App\Model\Types;
use App\Model\Translator;
use App\App\Service\BorrowingService;
use App\Service\FileService;
use App\Model\FileModel;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var Nette\DI\Container @inject */
    public $container;

    /** @var Nette\Application\Application @inject */
    public $application;

    /** @var Authenticator @inject */
    public $authenticator;

    /** @var LogService @inject */
    public $logService;

    /** @var Translator @inject */
    public $translator;

    public $config;

    public $confirm_data;

    /** @var BorrowingService @inject */
    public $borrowingService;

    /** @var FileModel @inject */
    public $fileModel;

    /** @var FileService @inject */
    public $fileService;

	/** @var MessageService */
	public $messageService;
	
    public function startup()
    {
        parent::startup();
        $this->config = $this->container->parameters;
        $this->confirm_data = new ArrayHash();
        bdump('App>Startup:App:Presenter');
    }

    protected function beforeRender()
    {
        $this->template->frontPath = $this->config["frontPath"];
        $this->template->appName = $this->config["appName"];
        $this->template->frontImages = $this->config["frontImages"];
        $this->template->front = $this->config["front"];
        $this->template->dataPath = $this->config["dataPath"];
        if ($this->user->getIdentity()) {
            $this->template->customer = $this->user->getIdentity()->getData();
        }

        $this->template->setTranslator($this->translator->translator());

        $this->template->addFilter('service_date', function ($start_date, $month) {
            $start_date = new DateTime($start_date);
            return $start_date->modify('+' . $month . ' month');
        });

        bdump('Before:App:Presenter');
    }

    /**
     * Default confirm form
     * @return Form
     */
    protected function createComponentConfirmForm()
    {
        $form = new Form();
        $form->addHidden('id');
        $form->addHidden('confirm_action');
        $form->addSubmit("confirm", $this->translator->translate('btn_confirm'))
            ->setHtmlAttribute('class', 'btn btn-success btn-confirm pull-right');
        $form->addButton("cancel", $this->translator->translate('btn_cancel'))
            ->setHtmlAttribute('class', 'btn btn-warning btn-confirm pull-right');
        if (isset($this->confirm_data->action))
        {
            $form->setDefaults(array('id'=>$this->confirm_data->id, 'confirm_action'=>$this->confirm_data->action));
        }
        $form->onSuccess[] = [$this, "confirmForm"];
        return $form;
    }

    public function confirmForm(Form $form, ArrayHash $data)
    {
        $this->confirm_data->confirm = (isset($data->confirm_action))? true : false;
        $organisation_id = $this->user->identity->getData()['organisation_id'];
        if ($this->confirm_data->confirm) {
            switch ($data->confirm_action) {
                case 'borrowing':
                    try {
                        $this->borrowingService->reservations2Borrowing((int) $data->id, $organisation_id);
                        $this->flashMessage($this->translator->translate('borrowing_was_successful'), Types::SUCCESS);
                    } catch (\Exception $e)
                    {
                        $this->flashMessage($this->translator->translate('borrowing_not_successful'), Types::DANGER);
                    }
                    break;
                case 'delete_document':
                    try {
                        $file = $this->fileModel->getFileName($data->id);
                        $this->fileService->deleteFile($file->path, $file->hashfilename);
                        $this->fileModel->deleteFile($data->id);
                        $this->flashMessage($this->translator->translate('deleted_was_successful'), Types::SUCCESS);
                    } catch (\Exception $e)
                    {
                        $this->flashMessage($this->translator->translate('deleted_not_successful'), Types::DANGER);
                    }
                    break;
	            case 'set_readed':
		            try {
			            $this->messageService->setReaded($data->id);
			            $this->flashMessage($this->translator->translate('messages_set_readed'), Types::SUCCESS);
		            } catch (\Exception $e)
		            {
			            $this->flashMessage($this->translator->translate('messages_not_set_readed'), Types::DANGER);
		            }
		            break;

            }
        }
    }

    public function showConfirmForm($action, $id = 0, $backUrl = '')
    {
        $this->confirm_data->action = $action;
        $this->confirm_data->id = $id;
        $this->template->confirm_text = $this->confirm_data->confirm_text;
        $this->template->modalTemplate = __DIR__.'\Templates\confirm.latte';
        $this->template->backUrl = $backUrl;
    }

    /**
     * @param $name
     * @return Form
     */
    public function createComponentLoginForm($name)
    {
        $form = new Form();

        $form->addText('username', $this->translator->translate(''))->setAttribute('placeholder', 'Uživatel');
        $form->addPassword('password', 'Heslo')->setAttribute('placeholder', 'Heslo');
        $form->addSubmit('submit', $this->translator->translate('btn_login'));

        $form->onSuccess[] = [$this, "login"];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $data
     * @return void
     * @throws Nette\Application\AbortException
     */
    public function login(Form $form, ArrayHash $data)
    {
        try {
            $this->authenticator->login($data->username, $data->password);
            $this->user->setExpiration("24 hours");
            $this->flashMessage($this->translator->translate('login_successful'), TYPES::SUCCESS);
            $this->log('user/login', $this->translator->translate('login_successful'), Types::LOG_INFO);
        } catch (\Exception $e) {
            $this->flashMessage($this->translator->translate('login_unsuccessful'), TYPES::DANGER);
            $this->log('user/login', $this->translator->translate('login_unsuccessful'), Types::LOG_DANGER, $e->getMessage());
            return;
        }
        $this->redirect(":App:Dashboard:");
    }

    /**
     * @param string $action
     * @param string $message
     * @param $level
     * @return void
     */
    public function log(string $action, string $message, $level = 'info', $data = null)
    {
        $user_id = $this->user->getIdentity() ? $this->user->getIdentity()->getId() : null;
        $this->logService->log($action, $message, $user_id, $level, $data);
    }
}
