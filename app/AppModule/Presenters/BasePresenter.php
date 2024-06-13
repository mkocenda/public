<?php

namespace App\App\Presenter;

use App\Model\MenuModel;
use App\Service\MessageService;
use Nette\Application\Responses\JsonResponse;

class BasePresenter extends \App\Presenter\BasePresenter
{
	
	/** @var MessageService @inject */
	public $messageService;
	public function startup()
	{
		parent::startup();
		if (!$this->user->getIdentity()) {
			$this->redirect(':User:login');
		}
	}
	public function beforeRender()
	{
		parent::beforeRender();
		$menuModel = new MenuModel('customer', $this->user->getRoles());
		$this->template->menu = $menuModel->getItems();
		$appMenuModel = new MenuModel('app');
		$this->template->app_menu = $appMenuModel->getItems();
	}
	
		public function actionGetUserMessages()
	{
		$user_id = $this->user->identity->getId();
		$this->sendResponse(new JsonResponse($this->messageService->getUserMessages($user_id)));
	}
}