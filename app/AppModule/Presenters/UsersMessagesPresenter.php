<?php

namespace App\App\Presenter;

use App\Model\MessagesModel;
use App\Model\Translator;
use App\Model\Types;
use DateTime;
use Exception;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\DataGrid;
use App\Service\MessageService;

class UsersMessagesPresenter extends BasePresenter
{
	
	public $messageModel;
	public $messageService;
	public $message_id;
	public $translator;
	public function __construct(MessageService $messageService, MessagesModel $messageModel, Translator $translator)
	{
		$this->messageModel = $messageModel;
		$this->messageService = $messageService;
		$this->translator = $translator;
	}
	
	public function createComponentMessagesGrid($name){
		$grid = new DataGrid($this, $name);
		$userid = $this->user->identity->getId();
		$grid->setPrimaryKey('id');
		$grid->setRefreshUrl(false);

		$grid->setDataSource($this->messageModel->getUserMessagesList($userid, $this->user->identity->getData()['organisation_id']));
		$grid->addColumnText('unread',$this->translator->translate('message_status'))
			->setRenderer(function ($datasource) {
				if ($datasource->unread == 1) { return '<i class="fa-solid fa-envelope"></i>'; }
										 else { return '<i class="fa-solid fa-envelope-open"></i>'; }
			})->setAlign('center')
			->setTemplateEscaping(FALSE);
		$grid->addColumnText('fullname',$this->translator->translate('fromuser'));
		$grid->addColumnText('caption',$this->translator->translate('caption'));
		$grid->addColumnDateTime('created_at',$this->translator->translate('created_at'))
			->setFormat('d.m.Y H:i:s');
		
		$grid->addColumnCallback('fullname', function ($column, $item) {
			if ($item['unread'] == Types::unread) {
				$td = $column->getElementPrototype('td');
				$td->style[] = 'font-weight: bold';
			}
		});
		
		$grid->addColumnCallback('caption', function ($column, $item) {
			if ($item['unread'] == Types::unread) {
				$td = $column->getElementPrototype('td');
				$td->style[] = 'font-weight: bold';
			}
		});
		
		$grid->addColumnCallback('created_at', function ($column, $item) {
			if ($item['unread'] == Types::unread) {
				$td = $column->getElementPrototype('td');
				$td->style[] = 'font-weight: bold';
			}
		});
		
		$grid->addGroupAction($this->translator->translate('select_readed'))->onSelect[] = [$this, 'bulkSetReaded'];
		$grid->addGroupAction($this->translator->translate('select_deleted'))->onSelect[] = [$this, 'bulkDeleted'];
		
		$grid->addAction('read','','read!', ['id'])
			->setTitle($this->translator->translate('open'))
			->setIcon('glasses')
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function bulkSetReaded(array $IDs)
	{
		try {
			foreach ($IDs as $id)
			{
				$this->messageService->setBulkReaded($id);
			}
			$this->flashMessage($this->translator->translate('messages_set_readed'), Types::SUCCESS);
		} catch (Exception $e)
		{
			$this->flashMessage($this->translator->translate('messages_not_set_readed'), Types::DANGER);
		}
	}
	
	public function bulkDeleted(array $IDs)
	{
		try {
			foreach ($IDs as $id)
			{
				$this->messageService->setBulkDeleted($id);
			}
			$this->flashMessage($this->translator->translate('messages_deleted'), Types::SUCCESS);
		} catch (Exception $e)
		{
			$this->flashMessage($this->translator->translate('messages_not_deleted'), Types::DANGER);
		}
	}
	
	
	public function createComponentMessageFormModal()
	{
		$form = new Form();
		$user_id = $this->user->identity->getId();
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$data = $this->messageModel->getMessageById($this->message_id, $user_id, $organisation_id);
		$form->addHidden('id');
		$form->addText('fullname', $this->translator->translate('fromuser'))
			->setHtmlAttribute('readonly','readonly');
		$form->addText('caption', $this->translator->translate('caption'))
			->setHtmlAttribute('readonly','readonly');
		$form->addTextArea('message', $this->translator->translate('message'))
			->setHtmlAttribute('readonly','readonly');
		$form->addText('created_at', $this->translator->translate('created_at'))
			->setHtmlAttribute('readonly','readonly');
		$form->addButton('cancel', $this->translator->translate('btn_close'));
		$created_at = new DateTime($data->created_at);
		$form->setDefaults(array(
			'id'=>$this->message_id,
			'fullname'=>$data->fullname,
			'caption'=>$data->caption,
			'message'=>$data->message,
			'created_at'=>$created_at->format('d.m.Y H:i:s')));
		return $form;
	}
	
	public function createComponentAddMessageFormModal()
	{
		$form = new Form();
		$user_id = $this->user->identity->getId();
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$recipients = $this->messageModel->getRecipients($organisation_id, $user_id);
		$form->addHidden('id');
		$form->addMultiSelect('recipient_id', $this->translator->translate('touser'), $recipients);
		$form->addText('caption', $this->translator->translate('caption'));
		$form->addTextArea('message', $this->translator->translate('message'));
		$form->addText('created_at', $this->translator->translate('created_at'))
			->setHtmlAttribute('readonly','readonly');
		$form->addButton('cancel', $this->translator->translate('btn_close'));
		$form->addSubmit('submit', $this->translator->translate('btn_send'));
		
		$created_at = new DateTime();
		$form->setDefaults(array(
			'created_at'=>$created_at->format('d.m.Y H:i:s')));
		$form->onSuccess[] = [$this, 'sendMessage'];
		return $form;
	}
	
	public function sendMessage(Form $form, ArrayHash $data)
	{
		try {
			$data->fromuser_id = $this->user->identity->getId();
			$data->message_id = $this->user->identity->getData()['organisation_id'];
			$this->messageService->saveMessage($data);
			$this->flashMessage($this->translator->translate('messageSend'), Types::SUCCESS);
		} catch (Exception $e)
		{
			$this->flashMessage($this->translator->translate('messageNotSend'), Types::DANGER);
		}
	}
	
	public function handleRead($id)
	{
		$this->message_id = $id;
		$this->template->modalTemplate = 'message.latte';
	}
	
	public function handleAdd()
	{
		$this->template->modalTemplate = 'addMessage.latte';
	}
	
	public function handleSetReaded()
	{
		$this->confirm_data->confirm_text = $this->translator->translate('set_readed');
		$url = '/user/messages/';
		$this->showConfirmForm('set_readed',  $this->user->getId(), $url);
		$this->presenter->redrawControl('messagesGrid');
	}
}