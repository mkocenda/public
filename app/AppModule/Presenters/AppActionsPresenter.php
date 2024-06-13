<?php

namespace App\App\Presenter;

use App\Model\ActionModel;
use App\Model\Types;
use Nette\Application\Responses\FileResponse;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Translator;
use App\Model\FileModel;
use App\App\Service\ActionsService;
use App\Model\StuffModel;
use App\Model\SettingsModel;
use App\Model\ParticipantsModel;
use App\Model\ReservationsModel;
use App\App\Service\ParticipantsService;
use App\App\Service\ReservationsService;
use App\Model\WarehouseModel;
use App\Model\CertificatesModel;

class AppActionsPresenter extends BasePresenter
{
	
	public $actionModel;
	public $actionsService;
	public $actionStatus;
	public $stuffModel;
	public $fileModel;
	public $settingsModel;
	public $participantsModel;
	public $reservationModel;
	public $participantsService;
    public $warehouseModel;
	public $reservationService;
	public $certificateModel;
	public $translator;
	
	public $participant_id;
	public $action_id;
	
	public $id;
	public $rid;
    public $document_id;
	
	public function __construct(ActionModel         $actionModel,
	                            StuffModel          $stuffModel,
	                            ActionsService      $actionsService,
	                            FileModel           $fileModel,
	                            SettingsModel       $settingsModel,
	                            ParticipantsModel   $participantsModel,
	                            ParticipantsService $participantsService,
	                            ReservationsService $reservationsService,
	                            ReservationsModel   $reservationsModel,
                                WarehouseModel      $warehouseModel,
								CertificatesModel   $certificatesModel,
	                            Translator          $translator)
	{
		$this->actionModel = $actionModel;
		$this->stuffModel = $stuffModel;
		$this->actionsService = $actionsService;
		$this->fileModel = $fileModel;
		$this->settingsModel = $settingsModel;
		$this->participantsModel = $participantsModel;
		$this->participantsService = $participantsService;
		$this->reservationModel = $reservationsModel;
		$this->reservationService = $reservationsService;
        $this->warehouseModel = $warehouseModel;
		$this->certificateModel = $certificatesModel;
		$this->translator = $translator;
	}
	
	public function actionPlannedList()
	{
		$this->actionStatus = Types::PLANNED;
	}
	
	public function actionRunningList()
	{
		$this->actionStatus = Types::RUNNING;
	}
	
	public function actionDoneList()
	{
		$this->actionStatus = Types::DONE;
	}
	
	public function createComponentActionsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey("id");
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$grid->setDataSource($this->actionModel->listActionsByStatus($organisation_id, $this->actionStatus));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('name'));
		$grid->addColumnText('members', $this->translator->translate('members'))->setRenderer(function ($datasource) {
			$members = $this->actionModel->getActionStuffs($datasource->id);
			$data = '<ul class="list-inline">';
			foreach ($members as $member) {
				if ($member->photo) {
					$photo = $this->fileModel->getFileBase64($member->photo, 'stuffs');
					$data .= '<li><img src="data:image/png;base64, ' . $photo . '" class="avatar" title="' . $member->name . ' ' . $member->surname . ' / ' . $member->alias . '" alt="Avatar"></li>';
				} else '<li></li>';
			}
			$data .= '</ul>';
			return $data;
		})->setTemplateEscaping(FALSE);
		$grid->addColumnNumber('agefrom', $this->translator->translate('agefrom'))
			->setRenderer(function($datasource)
			{
				if ($datasource->agefrom == 0) {return '-';} else {return $datasource->agefrom;}
			});
		$grid->addColumnNumber('ageto', $this->translator->translate('ageto'))
			->setRenderer(function($datasource)
			{
				if ($datasource->ageto == 0) {return '-';} else {return $datasource->ageto;}
			});
		$grid->addColumnNumber('limit', $this->translator->translate('limit'))->setRenderer(function ($datasource) {
			$max_limit = $datasource->limit ?: '-';
			$alloc = count($this->actionModel->getActionParticipants($datasource->id, $datasource->organisation_id));
			return $alloc . ' / ' . $max_limit;;
		});
		$grid->addColumnDateTime('starttime', $this->translator->translate('start_time'))
			->setRenderer(function ($datasource) {
				$date = new DateTime($datasource->starttime);
				if ($date->format('Y-m-d') == '-0001-11-30') {
					return '';
				} else {
					return $date->format('d.m.Y');
				}
			});
		$grid->addColumnDateTime('stoptime', $this->translator->translate('stop_time'))
			->setRenderer(function ($datasource) {
				$date = new DateTime($datasource->stoptime);
				if ($date->format('Y-m-d') == '-0001-11-30') {
					return '';
				} else {
					return $date->format('d.m.Y');
				}
			});

		switch ($this->actionStatus) {
			case  Types::PLANNED:
				$grid->addAction('edit', '', 'edit!', ['id'])
					->setTitle($this->translator->translate('edit'))
					->setClass('btn-sm no-border ajax ' . Types::MB_OK)
					->setIcon('edit')
					->setAlign('center');
				break;
			case  Types::RUNNING:
			case  Types::DONE:
				$grid->addAction('detail', '', 'detail!', ['id'])
					->setTitle($this->translator->translate('detail'))
					->setClass('btn-sm no-border ajax ' . Types::MB_OK)
					->setIcon('magnifying-glass')
					->setAlign('center');
		}
		
		/** ToDo - Vymyslet jak spravovat čekací listinu, zda rovnou z přehledu účastníků nebo samostatně */
		$waiting_list = $this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $organisation_id);
		if ($waiting_list->value == Types::ENABLED) {
			$grid->addAction('waiting_list', '', 'dummy!', ['id'])
				->setTitle($this->translator->translate('waiting_list'))
				->setClass('btn-sm no-border ajax ' . Types::MB_CANCEL)
				->setIcon('clipboard-list-check')
				->setAlign('center')
				->setRenderCondition(function ($datasource) {
					return $datasource->waiting_list;
				});
		}
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}
	
	public function createComponentStuffsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->actionModel->getActionStuffs($this->id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('firstname'));
		$grid->addColumnText('surname', $this->translator->translate('surname'));
		$grid->addColumnText('alias', $this->translator->translate('alias'));
		$grid->addColumnText('stuff_function', $this->translator->translate('stuff_function'));
		switch ($this->actionStatus) {
			case Types::PLANNED:
				$grid->addAction('remove', '', 'removeStuff!', ['id', 'action_id'])
					->setTitle($this->translator->translate('detail'))
					->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
					->setIcon('trash')
					->setAlign('center');
				 break;
		}
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}
	
	public function createComponentAvailableStuffsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->stuffModel->getAvailableStuffs($this->id, $this->user->identity->getData()['organisation_id']));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('name', $this->translator->translate('firstname'));
		$grid->addColumnText('surname', $this->translator->translate('surname'));
		$grid->addColumnText('alias', $this->translator->translate('alias'));
		$grid->addColumnText('cert_name', $this->translator->translate('cert_name'));
		
		$grid->addAction('addStuff', '', 'addActionStuff!', ['id', 'cert_type', 'action_id'])
			->setTitle($this->translator->translate('add_stuff'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('user-plus')
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}
	
	public function createComponentActionStuffStructureGrid($name)
	{
		$grid = new DataGrid($this, $name);
		
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->actionModel->getActionStuffStructure($this->id, $this->user->identity->getData()['organisation_id']));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);

		$grid->addColumnText('name', $this->translator->translate('name'));
		$grid->addColumnText('surname', $this->translator->translate('surname'));
		$grid->addColumnText('structure_name', $this->translator->translate('structure_name'));
		
		$grid->addAction('editStuffType', '', 'editStuffType!', ['id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		
		return $grid;
	}
	
	
	public function createComponentStuffTypeFormModal()
	{
		$form = new Form();
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		
		$form->addHidden('id');
		$stuffsOrganisation = $this->stuffModel->getStuffsByOrganisationID($organisation_id);
		$stuffs = array();
		
		foreach ($stuffsOrganisation as $stuff)
		{
			$stuffs[$stuff->id] = $stuff->surname.' '.$stuff->name;
		}
		$form->addSelect('stuff_id', $this->translator->translate('name'), $stuffs);
		
		$stuffTypes = $this->stuffModel->getStuffsTypes($organisation_id);
		$types = array();
		foreach ($stuffTypes as $stuffType)
		{
			$types[$stuffType->id] = $stuffType->name;
		}

		$form->addSelect('certtype', $this->translator->translate('stuffType'), $types);
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
//		$stuff = $this->stuffModel->getStuffByID($this->stu)
//		$form->setDefaults(array('id'=>$this->action_id,
//								 'stuff_id'=>1,
//								 'certtype'=>1));
		return $form;
	}
	
	public function createComponentActionFormModal()
	{
		$form = new Form();
		$form->addHidden('id');
		$readOnly = $this->actionStatus <> Types::PLANNED;
		$form->addText('name', $this->translator->translate('name'))
			->setAttribute('readonly', $readOnly);
		$form->addText('motto', $this->translator->translate('motto'))
			->setAttribute('readonly', $readOnly);
		$form->addTextArea('description', $this->translator->translate('description'), 80, 10)
			->setAttribute('id', 'wysiwyg')
			->setAttribute('readonly', $readOnly);
		$form->addText('starttime', $this->translator->translate('start_time'))->setType('date')
			->setAttribute('readonly', $readOnly);
		$form->addText('stoptime', $this->translator->translate('stop_time'))->setType('date')
			->setAttribute('readonly', $readOnly);
		$form->addText('limit', $this->translator->translate('limit'))
			->setAttribute('readonly', $readOnly);
		$form->addText('agefrom', $this->translator->translate('agefrom'))
			->setAttribute('readonly', $readOnly);
		$form->addText('ageto', $this->translator->translate('ageto'))
			->setAttribute('readonly', $readOnly);
		$form->addCheckbox('waiting_list', $this->translator->translate('waiting_list'));
		
		switch ($this->actionStatus) {
			case Types::PLANNED:
				$form->addSubmit('submit', $this->translator->translate('btn_write'));
				$form->addButton('cancel', $this->translator->translate('btn_cancel'));
				break;
			case Types::RUNNING:
			case Types::DONE:
				$form->addButton('cancel', $this->translator->translate('btn_close'));
				break;
		}

		if ($this->id) {
			$action = $this->actionModel->getAction($this->id);
			$from = new DateTime($action->starttime);
			$to = new DateTime($action->stoptime);
			$form->setDefaults(array('id' => $this->id,
				'name' => $action->name,
				'motto' => $action->motto,
				'description' => $action->description,
				'limit' => $action->limit,
				'starttime' => $from->format('Y-m-d'),
				'stoptime' => $to->format('Y-m-d'),
				'agefrom' => $action->agefrom,
				'ageto' => $action->ageto,
				'waiting_list' => $action->waiting_list,
			));
		}
		
		$form->onSuccess[] = [$this, 'saveAction'];
		return $form;
	}
	
	public function saveAction(Form $form, ArrayHash $data)
	{
		try {
			$data->organisation_id = $this->user->identity->getData()['organisation_id'];
			$this->actionsService->saveAction($data);
			$this->flashMessage($this->translator->translate('action_saved'), Types::SUCCESS);
			if ($this->isAjax()) {
				$this->redrawControl('actionsGrid');
			}
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('action_not_saved'), Types::DANGER);
		}
		$this->presenter->redrawControl('actionsGrid');
	}
	
	public function handleAdd()
	{
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$waiting_list = $this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $organisation_id);
		$this->template->waiting_list = $waiting_list->value;
		$this->template->modalTemplate = 'new.latte';
	}
	
	public function handleEdit($id)
	{
		$this->id = $id;
		$this->template->id = $id;
		$this->template->modalTemplate = 'editTab.latte';
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$waiting_list = $this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $organisation_id);
		$this->template->waiting_list = $waiting_list->value;
	}
	
	public function handleStuff($id)
	{
		$this->id = $id;
		$this->template->id = $id;
		$this->template->actionStatus = $this->actionStatus;
		$this->template->modalTemplate = 'stuff.latte';
	}
	
	public function handleDetail($id)
	{
		$this->id = $id;
		$this->template->modalTemplate = 'detail.latte';
	}
	
	public function handleRemoveStuff($id, $action_id)
	{
		try {
			$data = new ArrayHash();
			$data->id = $id;
			$this->actionsService->removeActionStuff($data);
			$this->flashMessage($this->translator->translate('stuff_removed'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('stuff_not_removed'), Types::DANGER);
		}
		$this->handleStuff($action_id);
		$this->actionStatus = Types::PLANNED;
	}
	
	public function handleAddStuff($id)
	{
		$this->id = $id;
		$this->template->id = $id;
		$this->template->modalTemplate = 'addStuff.latte';
	}
	
	public function handleAddActionStuff($id, $cert_type, $action_id)
	{
		try {
			$data = new ArrayHash();
			$data->stuff_id = $id;
			$data->stuff_type = $cert_type;
			$data->action_id = $action_id;
			$this->actionsService->addActionStuff($data);
			$this->flashMessage($this->translator->translate('stuff_added'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('stuff_not_added'), Types::DANGER);
		}
		$this->handleStuff($action_id);
	}
	
	public function handleParticipants($id)
	{
		$this->id = $id;
		$this->template->modalTemplate = 'participants.latte';
	}

    public function handleActionDocuments($id)
    {
        $this->id = $id;
        $this->template->action_id = $id;
        $this->template->action_status = $this->actionModel->getActionStatus($id);
        $this->template->modalTemplate = 'actionDocuments.latte';
    }

    public function createComponentActionDocumentsGrid($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setPrimaryKey("id");
        $organisation_id = $this->user->identity->getData()['organisation_id'];
        $grid->setDataSource($this->actionModel->getActionDocuments($this->id, $organisation_id));
        $grid->setRefreshUrl(false);
        $grid->setPagination(false);

        $grid->addColumnText('origfilename', $this->translator->translate('filename'));
        $grid->addColumnDateTime('created_at', $this->translator->translate('created_at'))->setFormat('d.m.Y');

        $grid->addAction('downloadDocument','', 'downloadActionDocument!', ['id'])
            ->setTitle($this->translator->translate('download'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('cloud-arrow-down')
            ->setAlign('center')
            ->setRenderCondition(function ($datasource) {
                /** Action in planned status */
                $action = $this->actionModel->getActionStatus($datasource->action_id);
                return ($action == Types::PLANNED) ? true : false;
            });

        $grid->addAction('editDocument','', 'editActionDocument!', ['id'])
            ->setTitle($this->translator->translate('edit'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_OK)
            ->setIcon('edit')
            ->setAlign('center')
            ->setRenderCondition(function ($datasource) {
                /** Action in planned status */
                $action = $this->actionModel->getActionStatus($datasource->action_id);
                return ($action == Types::PLANNED) ? true : false;
            });
        $grid->addAction('deleteDocument','', 'deleteActionDocument!', ['id'])
            ->setTitle($this->translator->translate('delete'))
            ->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
            ->setIcon('trash')
            ->setAlign('center')
            ->setRenderCondition(function ($datasource) {
                /** Action in planned status */
                $action = $this->actionModel->getActionStatus($datasource->action_id);
                return ($action == Types::PLANNED) ? true : false;
            })
            ->addParameters(array('action_id'=>$this->id));

          $grid->setTranslator($this->translator->translator());

        return $grid;
    }

    public function createComponentAddActionDocumentForm()
    {
        $form = new Form();

        $form->addHidden('id');
        $form->addHidden('action_id');
        $form->addUpload('file_id',$this->translator->translate('document'));

        $form->addSubmit('submit', $this->translator->translate('btn_write'));
        $form->addButton('cancel', $this->translator->translate('btn_cancel'));

        $form->setDefaults(array('id'=>$this->document_id,
                'action_id'=>$this->action_id));

        $form->onSuccess[] = [$this, 'saveActionDocument'];

        return $form;
    }

    public function handleDownloadActionDocument($id)
    {
        $this->document_id =  $id;
        $document = $this->fileModel->getFileName($id);
        $content = __DIR__ . '/../../../data/actions/documents/' . $document->hashfilename;
        $file = new FileResponse($content, $document->origfilename);
        $this->getPresenter()->sendResponse($file);
    }

    public function handleAddActionDocument($id)
    {
        $this->template->action_id = $id;
        $this->template->modalTemplate = 'addActionDocument.latte';
    }

    public function handleEditActionDocument($id)
    {
        $this->document_id =  $id;
        $this->template->action_id = $id;
        $this->template->modalTemplate = 'addActionDocument.latte';
    }

    public function handleDeleteActionDocument($id, $action_id)
    {
        $this->document_id =  $id;
        $this->action_id =  $action_id;
        $this->confirm_data->confirm_text = $this->translator->translate('delete_document');
        switch ($this->actionModel->getActionStatus($action_id))
        {
            case Types::PLANNED: $url = '/actions/planned/?id='.$action_id.'&do=actionDocuments'; break;
            case Types::RUNNING: $url = '/actions/running/?id='.$action_id.'&do=actionDocuments'; break;
            case Types::DONE: $url = '/actions/done/?id='.$action_id.'&do=actionDocuments'; break;
        }
        $this->showConfirmForm('delete_document',  $id, $url);
    }

	public function createComponentActionAllowParticipantsGrid($name)
	{
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$checkLimitAge = $this->settingsModel->getSettingsByModuleProperty('participants', 'CheckLimitAge', $organisation_id);
		$action = $this->actionModel->getAction($this->id);
		$action_date = new DateTime($action->starttime);
		$age_from = $action->agefrom;
		$age_to = $action->ageto > 0 ? $action->ageto : 99;
		
		if ($this->actionModel->getActionStatus($this->id) == Types::PLANNED) {
			$participants_model = ($checkLimitAge->value == 1) ? $this->participantsModel->getLimitedParticipants($action_date->format('Y-m-d'), $age_from, $age_to, $organisation_id)
				: $this->participantsModel->getAllParticipantsByOrganisation($organisation_id);
		} else {
			$participants_model = $this->actionModel->getActionParticipants($this->id, $organisation_id);
		}
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey("id");
		$grid->setDataSource($participants_model);
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		$grid->addColumnText('name', $this->translator->translate('firstname'))->setFilterText();
		$grid->addColumnText('surname', $this->translator->translate('surname'))->setFilterText();
		$grid->addColumnDateTime('birthday', $this->translator->translate('birthday'))->setFormat('d.m.Y');
//        $grid->addColumnText('note', $this->translator->translate('note'));
		
		if ($this->actionModel->getActionStatus($this->id) == Types::PLANNED) {
			$grid->addAction('addParticipant', '', 'addParticipant!', ['participant_id' => 'id'])
				->setTitle($this->translator->translate('addParticipant'))
				->setClass('btn-sm no-border ajax ' . Types::MB_OK)
				->setIcon('user-plus')
				->setAlign('center')
				->addParameters(['action_id' => $this->id])
				->setRenderCondition(function ($datasource) {
					$action = $this->actionModel->getAction($this->id);
					/** Limit action allocation */
					$allocation = count($this->actionModel->getActionParticipants($this->id, $datasource->organisation_id));
					if (($action->limit > 0) && ($allocation == $action->limit)) {
						return false;
					}
					/** Action participate condition */
					$actions = $this->participantsModel->getParticipantAction($datasource->id, $this->id);
					foreach ($actions as $action) {
						if ($action->id == $this->id) return false;
					}
					return true;
				});
			
			$grid->addAction('addWaitingParticipant', '', 'addWaitingParticipant!', ['participant_id' => 'id'])
				->setTitle($this->translator->translate('addWaitingParticipant'))
				->setClass('btn-sm no-border ajax ' . Types::MB_CANCEL)
				->setIcon('clipboard-medical')
				->setAlign('center')
				->addParameters(['action_id' => $this->id])
				->setRenderCondition(function ($datasource) {
					/** Action participate condition */
					$return = false;
					$waiting_list = $this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $datasource->organisation_id);
					if ($waiting_list->value == Types::ENABLED) {
						$action = $this->actionModel->getAction($this->id);
						/** Limit action allocation */
						$allocation = count($this->actionModel->getActionParticipants($this->id, $datasource->organisation_id));
						$onWaitingList = $this->participantsModel->isOnWaitingList($this->id, $datasource->id);
						/** Action participate condition */
						$actions = $this->participantsModel->getParticipantAction($datasource->id, $this->id);
						$onAction = false;
						foreach ($actions as $action) {
							if ($action->id == $this->id) $onAction = true;
						}
						
						if (($action->limit > 0) && ($allocation == $action->limit) && (!$onWaitingList) && (!$onAction)) {
							$return = true;
						}
					}
					return $return;
				});
			
			$grid->addAction('removeWaitingParticipant', '', 'removeWaitingParticipant!', ['participant_id' => 'id'])
				->setTitle($this->translator->translate('removeWaitingParticipant'))
				->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
				->setIcon('clipboard')
				->setAlign('center')
				->addParameters(['action_id' => $this->id])
				->setRenderCondition(function ($datasource) {
					/** Action participate condition */
					$return = false;
					$waiting_list = $this->settingsModel->getSettingsByModuleProperty('actions', 'WaitingList', $datasource->organisation_id);
					if ($waiting_list->value == Types::ENABLED) {
						$action = $this->actionModel->getAction($this->id);
						/** Limit action allocation */
						$allocation = count($this->actionModel->getActionParticipants($this->id, $datasource->organisation_id));
						$onWaitingList = $this->participantsModel->isOnWaitingList($this->id, $datasource->id);
						/** Action participate condition */
						$actions = $this->participantsModel->getParticipantAction($datasource->id, $this->id);
						$onAction = false;
						foreach ($actions as $action) {
							if ($action->id == $this->id) $onAction = true;
						}
						if (($action->limit > 0) && ($allocation == $action->limit) && ($onWaitingList) && (!$onAction)) {
							$return = true;
						}
					}
					return $return;
				});
			
			$grid->addAction('removeParticipant', '', 'removeParticipant!', ['participant_id' => 'id'])
				->setTitle($this->translator->translate('removeParticipant'))
				->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
				->setIcon('user-minus')
				->setAlign('center')
				->addParameters(['action_id' => $this->id])
				->setRenderCondition(function ($datasource) {
					/** Action participate condition */
					$actions = $this->participantsModel->getParticipantAction($datasource->id, $this->id);
					foreach ($actions as $action) {
						if ($action->id == $this->id) return true;
					}
					return false;
				});
		}
		
		$grid->addAction('participantDocuments', '', 'documents!', ['participant_id' => 'id'])
			->setTitle($this->translator->translate('participantDocuments'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('folder-tree')
			->setAlign('center')
			->addParameters(['action_id' => $this->id]);
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function handleAddParticipant($participant_id, $action_id)
	{
		try {
			$this->actionsService->addParticipantToAction($participant_id, $action_id);
			$this->flashMessage($this->translator->translate('participant_added'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('participant_not_added'), Types::DANGER);
		}
		$this->handleParticipants($action_id);
	}
	
	public function handleRemoveParticipant($participant_id, $action_id)
	{
		try {
			$this->actionsService->removeParticipantFromAction($participant_id, $action_id);
			$this->flashMessage($this->translator->translate('participant_removed'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('participant_not_removed'), Types::DANGER);
		}
		$this->handleParticipants($action_id);
	}
	
	public function handleAddWaitingParticipant($participant_id, $action_id)
	{
		try {
			$this->actionsService->addWaitingParticipant($participant_id, $action_id);
			$this->flashMessage($this->translator->translate('participant_added_waitinglist'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('participant_not_added_waitinglist'), Types::DANGER);
		}
		$this->handleParticipants($action_id);
	}
	
	public function handleRemoveWaitingParticipant($participant_id, $action_id)
	{
		try {
			$this->actionsService->removeWaitingParticipant($participant_id, $action_id);
			$this->flashMessage($this->translator->translate('participant_removed_waitinglist'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('participant_not_removed_waitinglist'), Types::DANGER);
		}
		$this->handleParticipants($action_id);
	}
	
	public function handleDocuments(int $participant_id, int $action_id)
	{
		$this->participant_id = $participant_id;
		$this->action_id = $action_id;
		
		$this->template->action_id = $action_id;
		$this->template->participant_id = $participant_id;
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
		$this->template->modalTemplate = 'documents.latte';
	}
	
	public function createComponentParticipantDocumentsGrid($name)
	{
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->participantsModel->getParticipantDocuments($this->participant_id, $this->action_id, $organisation_id));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('origfilename', $this->translator->translate('filename'));
		$grid->addColumnDateTime('created_at', $this->translator->translate('created_at'))->setFormat('d.m.Y');
		
		$grid->addAction('download', '', 'downloadDocument!', ['file_id' => 'id'])
			->setTitle($this->translator->translate('participantDocuments'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('download')
			->setAlign('center');
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function createComponentAddDocumentForm()
	{
		$form = new Form();
		
		$form->addHidden('action_id');
		$form->addHidden('participant_id');
		$form->addUpload('file_id');
		
		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
		$form->setDefaults(array('action_id' => $this->action_id,
			                     'participant_id' => $this->participant_id));
		$form->onSuccess[] = [$this, 'saveDocument'];
		return $form;
	}
	
	public function saveDocument(Form $form, ArrayHash $data)
	{
		try {
			$organisation_id = $this->user->identity->getData()['organisation_id'];
			$user_id = $this->user->identity->getId();
			$this->participantsService->addDocument($data, $organisation_id, $user_id);
			$this->flashMessage($this->translator->translate('document_added'), Types::SUCCESS);
			$this->handleDocuments($data->participant_id, $data->action_id);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('document_not_added'), Types::DANGER);
		}
	}
	
	public function handleAddDocument(int $participant_id, int $action_id)
	{
		$this->participant_id = $participant_id;
		$this->action_id = $action_id;
		
		$this->template->action_id = $action_id;
		$this->template->participant_id = $participant_id;
		$this->template->modalTemplate = 'addDocument.latte';
	}
	
	public function handleDownloadDocument($file_id)
	{
		$file = $this->fileModel->getFileName($file_id);
		$content = __DIR__ . '/../../../data/actions/participants/documents/' . $file->hashfilename;
		$file = new FileResponse($content, $file->origfilename);
		$this->getPresenter()->sendResponse($file);
	}
	
	public function handleReservation($id)
	{
		$this->id = $id;
		$this->template->action_id = $id;
		$this->template->modalTemplate = 'reservation.latte';
	}

    public function handleBorrowing($id)
    {
        $this->confirm_data->confirm_text = $this->translator->translate('borrowing');
        $this->showConfirmForm('borrowing', $id, '/actions/planned/?id='.$id.'&do=reservation');
    }

	public function createComponentReservedPartsGrid($name)
	{
		$grid = new DataGrid($this, $name);
		$grid->setPrimaryKey("rid");
		$grid->setDataSource($this->reservationModel->getReservationsByActionId($this->id, $this->user->identity->getData()['organisation_id']));
		$grid->setRefreshUrl(false);
		$grid->setPagination(false);
		
		$grid->addColumnText('part_no', $this->translator->translate('part_no'));
		$grid->addColumnText('description', $this->translator->translate('description'));
		$grid->addColumnText('name', $this->translator->translate('warehouse'));
		$grid->addColumnNumber('amount', $this->translator->translate('amount'));
		
		$grid->addAction('editReservation', '', 'editReservation!', ['rid'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('edit')
			->setAlign('center')
			->addParameters(array('action_id' => $this->id));
		
		$grid->addAction('deleteReservation', '', 'deleteReservation!', ['rid'])
			->setTitle($this->translator->translate('delete'))
			->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
			->setIcon('trash')
			->setAlign('center')
			->addParameters(array('action_id' => $this->id));
		
		$grid->setTranslator($this->translator->translator());
		return $grid;
	}
	
	public function handleAddReservation($action_id)
	{
        $this->action_id = $action_id;
		$this->template->action_id = $action_id;
	    $this->template->modalTemplate = 'addReservation.latte';
	}
	
	public function handleEditReservation($rid, $action_id)
	{
		$this->rid = $rid;
        $this->action_id = $action_id;
		$this->template->action_id = $action_id;
        $this->template->modalTemplate = 'addReservation.latte';
	}

    public function createComponentReservationForm()
    {
        $organisation_id = $this->user->identity->getData()['organisation_id'];
        $form = new Form();
        $form->addHidden('id');
        $form->addHidden('action_id');
        $parts = $this->warehouseModel->getAvailableParts($organisation_id);
        $s_parts = array();
        foreach ($parts as $part)
        {
            $s_parts[$part->id] = $part->part_no.' '.$part->description.' / '.$part->name;
        }
        $form->addSelect('part_id',$this->translator->translate('part_no'), $s_parts)->setDisabled($this->rid ? true:false);
        $form->addInteger('amount', $this->translator->translate('amount'));
	    $form->setDefaults(array('action_id'=>$this->action_id));

        if ($this->rid){
            $reservation = $this->reservationModel->getReservationPart($this->rid, $organisation_id);
            $form->setDefaults(array('id'=>$this->rid,
                'part_id'=>$reservation->part_id,
                'amount'=>$reservation->amount));
        }
	    
	    $form->addSubmit('submit', $this->translator->translate('btn_write'));
	    $form->addButton('cancel', $this->translator->translate('btn_cancel'));
		
        $form->onSuccess[] = [$this,'saveReservation'];

        return $form;
    }

	public function saveReservation(Form $form, ArrayHash $data)
	{
		try {
			$data->organisation_id = $this->user->identity->getData()['organisation_id'];
			$stuff = $this->stuffModel->getStuffByUserID($this->user->identity->getId());
			$data->stuff_id = $stuff->id;
			$this->reservationService->saveReservation($data);
			$this->flashMessage($this->translator->translate('reservation_saved'), Types::SUCCESS);
			$this->handleReservation($data->action_id);
		}
		catch (Exception $e)
		{
			$this->flashMessage($this->translator->translate('reservation_not_saved'), Types::DANGER);
		}
	}

	public function handleDeleteReservation($rid, $action_id)
	{
		try {
            $reservation = $this->reservationModel->getReservationByID($rid, $action_id);
			$this->reservationService->deleteReservation($rid, $this->user->identity->getData()['organisation_id']);
            $this->borrowingService->deleteBorrowing($reservation->part_id, $action_id);
			$this->flashMessage($this->translator->translate('reservation_deleted'), Types::SUCCESS);
			$this->handleReservation($action_id);
		} catch (\Exception $e)
		{
 			$this->flashMessage($this->translator->translate('reservation_not_deleted'), Types::DANGER);
		}
	}

	public function handleRefreshQty($part_id)
	{
		$organisation_id = $this->user->identity->getData()['organisation_id'];
		$parts = $this->warehouseModel->getAvailableParts($organisation_id, $part_id);
		$this->sendResponse(new JsonResponse($parts));
	}

	public function handleEditStuffType($id)
	{
	
	}
	
	public function handleDummy($id)
	{

	}
	
	public function handleAddStuffType($id)
	{
		$this->action_id = $id;
		$this->template->modalTemplate = 'stuffRole.latte';
	}
}