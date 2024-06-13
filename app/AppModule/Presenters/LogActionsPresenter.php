<?php

namespace App\App\Presenter;

use App\ApiModule\Model\ExternalAccessModel;
use App\Model\ActionModel;
use App\Model\InsuranceModel;
use App\Model\ParticipantsModel;
use App\Model\PillsModel;
use App\Model\Translator;
use App\Model\Types;
use Joseki\Application\Responses\PdfResponse;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\DataGrid;
use App\Model\ParentsModel;
use App\App\Service\LogActionService;
use App\Model\FileModel;
use App\App\Service\ParticipantsService;
use chillerlan\QRCode\QRCode;
use App\Model\OrganisationModel;
use App\Model\SettingsModel;
use App\App\Service\PillsService;
use App\ApiModule\Service\ValidateApiKeyService;
class LogActionsPresenter extends BasePresenter
{

	public $action_id;
	public $participant_id;
	public $injury_id;
	public $action_status;
	public $actionModel;
	public $insuranceModel;
	public $participantsModel;
	public $parentsModel;
	public $logActionService;
	public $participantService;
	public $validateApiKeyService;
	public $pillsModel;
	public $fileModel;
    public $organisationModel;
	public $translator;
    public $settingsModel;
	public $externalAccessModel;
	public $pillsService;
	public function __construct(ActionModel       	$actionModel,
								InsuranceModel    	$insuranceModel,
								ParticipantsModel 	$participantsModel,
								ParentsModel      	$parentsModel,
								LogActionService  	$logActionService,
								ParticipantsService $participantService,
								PillsModel        	$pillsModel,
								FileModel         	$fileModel,
                                OrganisationModel   $organisationModel,
                                SettingsModel       $settingsModel,
								ValidateApiKeyService $validateApiKeyService,
								ExternalAccessModel $externalAccessModel,
								PillsService        $pillsService,
								Translator        	$translator)
	{
		$this->actionModel = $actionModel;
		$this->insuranceModel = $insuranceModel;
		$this->participantsModel = $participantsModel;
		$this->parentsModel = $parentsModel;
		$this->logActionService = $logActionService;
		$this->participantService = $participantService;
		$this->pillsModel = $pillsModel;
		$this->fileModel = $fileModel;
        $this->organisationModel = $organisationModel;
        $this->settingsModel = $settingsModel;
		$this->validateApiKeyService = $validateApiKeyService;
		$this->externalAccessModel = $externalAccessModel;
		$this->pillsService = $pillsService;
		$this->translator = $translator;
	}

	public function actionRunningList()
	{
		$this->action_status = Types::RUNNING;
	}

	public function actionPlannedList()
	{
		$this->action_status = Types::PLANNED;
	}

	public function actionDoneList()
	{
		$this->action_status = Types::DONE;
	}

	public function createComponentStuffActionsGrid($name)
	{
		$grid = new DataGrid($this, $name);

		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->actionModel->listUserAction($this->user->identity->getData()['user_id'], $this->user->identity->getData()['organisation_id'], $this->action_status));
		$grid->setRefreshUrl(false);

		$grid->addColumnText('name', $this->translator->translate('name'));
//		$grid->addColumnText('motto', $this->translator->translate('motto'));
		$grid->addColumnNumber('limit', $this->translator->translate('limit'));
		$grid->addColumnNumber('agefrom', $this->translator->translate('agefrom'));
		$grid->addColumnNumber('ageto', $this->translator->translate('ageto'));
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

		$grid->addAction('participants', '', 'participants!', ['id'])
			->setTitle($this->translator->translate('edit'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('people-simple')
			->setAlign('center');

		$grid->setTranslator($this->translator->translator());

		return $grid;
	}

	public function createComponentActionParticipantsGrid($name)
	{
		$grid = new DataGrid($this, $name);

		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->actionModel->getActionParticipants($this->action_id, $this->user->identity->getData()['organisation_id']));
		$grid->setRefreshUrl(false);

		$grid->addColumnText('name', $this->translator->translate('firstname'));
		$grid->addColumnText('surname', $this->translator->translate('lastname'));
		$grid->addColumnText('birthday', $this->translator->translate('birthday'))->setRenderer(function ($datasource) {
			$birthday = new DateTime($datasource->birthday);
			if ($birthday->format('Y-m-d') == '-0001-11-30') {
				return '';
			}
			$action_interval_start = new DateTime($datasource->starttime);
			$action_interval_stop = new DateTime($datasource->stoptime);
			$tmp_birthday = new DateTime($action_interval_start->format('Y' . '-' . $birthday->format('m-d')));
			$alert = '';
			if (($action_interval_start <= $tmp_birthday)
				&& ($action_interval_stop >= $tmp_birthday)) {
				$alert = str_repeat('&nbsp;', 5) . '<i class="red fa-duotone fa-cake-candles"></i> ' .
					str_repeat('&nbsp;', 1) . '(' . ($action_interval_start->format('Y') - $birthday->format('Y')) . ')';
			}

			return $birthday->format('d.m.Y') . $alert;
		})->setTemplateEscaping(false);

		$grid->addColumnText('insurance_id', $this->translator->translate('insurance'))->setRenderer(function ($datasource) {
			if ($datasource->insurance_id) {
				$insurance = $this->insuranceModel->getInsuranceById($datasource->insurance_id);
				return $insurance->code . ' / ' . $insurance->name;
			}
			return '';
		});
		$grid->addColumnNumber('note', $this->translator->translate('note'));

		$grid->addAction('pills', '', 'pills!', ['id', 'action_id'])
			->setTitle($this->translator->translate('pills'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('pills')
			->setAlign('center');

		$grid->addAction('injury', '', 'injury!', ['id', 'action_id'])
			->setTitle($this->translator->translate('injury'))
			->setClass('btn-sm no-border ajax ' . Types::MB_DELETE)
			->setIcon('face-head-bandage')
			->setAlign('center')
			->setRenderCondition(function ($datasource) {
				$today = new DateTime();
				return ($datasource->starttime < $today) ? true : false;
			});

		$grid->addAction('doctor_message', '', 'injury!', ['id', 'action_id'])
			->setTitle($this->translator->translate('doctor_message'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('user-doctor-message')
			->setAlign('center')
			->setRenderCondition(function ($datasource) {
				$today = new DateTime();
				return ($datasource->stoptime <= $today) ? true : false;
			});

		$grid->addAction('print', '', 'print!', ['id', 'action_id'])
			->setTitle($this->translator->translate('print'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('print')
			->setAlign('center');

		$grid->setTranslator($this->translator->translator());

		return $grid;
	}

	public function createComponentParticipantRecordsGrid($name)
	{
		$grid = new DataGrid($this, $name);

		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->participantsModel->listActionParticipantRecords($this->participant_id, $this->action_id));
		$grid->setRefreshUrl(false);

		$grid->addColumnText('created', $this->translator->translate('created'))->setRenderer(function ($datasource) {
			$date = new DateTime($datasource->created);
			return $date->format('d.m.Y H:i');
		});

		$grid->addColumnText('description', $this->translator->translate('description'))->setRenderer(function ($datasource) {
			return substr($datasource->description, 0, 100);
		});

		$grid->addColumnText('file_id', $this->translator->translate('attachment'))->setRenderer(function ($datasource) {
			$attachment = $datasource->file_id ? '<i style ="cursor: default" class="btn fa fa-paperclip"></i>' : '';
			return $attachment;
		})->setAlign('center')
			->setTemplateEscaping(FALSE);

		$grid->addAction('detail', '', 'injuryDetail!', ['id', 'participants_id', 'action_id'])
			->setTitle($this->translator->translate('description'))
			->setClass('btn-sm no-border ajax ' . Types::MB_OK)
			->setIcon('magnifying-glass')
			->setAlign('center');

		$grid->setTranslator($this->translator->translator());

		return $grid;
	}

	public function createComponentParticipantPillsGrid($name)
	{
		$grid = new DataGrid($this, $name);

		$grid->setPrimaryKey("id");
		$grid->setDataSource($this->pillsModel->listActionParticipantPills($this->participant_id, $this->action_id));
		$grid->setRefreshUrl(false);

		$grid->addColumnText('pill_name', $this->translator->translate('pill_name'));
		$grid->addColumnText('dosage', $this->translator->translate('dosage'))->setRenderer(function ($datasource) {
			return Types::DOSAGE[$datasource->dosage];
		});
		$grid->addColumnText('last_apply', $this->translator->translate('last_apply'))->setRenderer(function ($datasource) {
			$date = new DateTime($datasource->last_apply);
			return $date->format('d.m.Y H:i');
		})->setAlign('right');
		if ($this->actionModel->getActionStatus($this->action_id) == Types::RUNNING) {
			$grid->addAction('pillApply', '', 'pillApply!', ['id', 'participant_id', 'action_id'])
				->setTitle($this->translator->translate('apply'))
				->setClass('btn-sm no-border ajax ' . Types::MB_OK)
				->setIcon('prescription-bottle-pill')
				->setAlign('center');
		}

		$grid->setTranslator($this->translator->translator());

		return $grid;
	}

	public function handleParticipants($id)
	{
		$this->action_id = $id;
		$this->template->modalTemplate = 'participants.latte';
	}

    /*
    
     */
	public function handlePills($id, $action_id)
	{
		$this->participant_id = $id;
		$this->action_id = $action_id;
		$this->template->modalTemplate = 'participantPills.latte';
		$this->template->action_id = $action_id;
		$this->template->participant_id = $id;
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
	}

	public function createComponentAddPillFormModal()
	{
		$form = new Form();

		$form->addHidden('action_id');
		$form->addHidden('participant_id');
		$form->addHidden('pill_id')->setAttribute('id','pill_id');
		$form->addText('pill_name', $this->translator->translate('pill_name'))
			->setAttribute('id','pill_autocomplete');
		$form->addSelect('dosage', $this->translator->translate('dosage'), Types::DOSAGE);

		$form->setDefaults(array('participant_id'=>$this->participant_id, 'action_id'=>$this->action_id));

		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));

		$form->onSuccess[] = [$this, 'addPill'];
		return $form;
	}
    
    /**
     *
     * Prida leky k ucastnikovi
     * @param Form $form
     * @param ArrayHash $data
     * @return void
     */
	public function addPill(Form $form, ArrayHash $data)
	{
		try {
			$this->participantService->addPill($data);
			$this->flashMessage($this->translator->translate('pill_added'),Types::SUCCESS);
			$this->handlePills($data->participant_id, $data->action_id);
		} catch (\Exception $e){
			$this->flashMessage($this->translator->translate('pill_not_added'),Types::DANGER);
		}
	}
 
	public function handleAddPill($id, $action_id)
	{
		$this->participant_id = $id;
		$this->action_id = $action_id;
		$this->template->action_id = $action_id;
		$this->template->participant_id = $id;
		$this->template->api_key = $this->externalAccessModel->getSpecifiedKeyOrganisation('Pills','read', $this->user->identity->getData()['organisation_id']);
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
		$this->template->modalTemplate = 'addPill.latte';
	}
 
	public function handleInjury($id, $action_id)
	{
		$this->participant_id = $id;
		$this->action_id = $action_id;
		$this->template->action_id = $action_id;
		$this->template->participant_id = $id;
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
		$this->template->modalTemplate = 'injury.latte';
	}
	
	
	/**
	 * @param $id
	 * @param $participant_id
	 * @param $action_id
	 * @return void
	 */
	public function handlePillApply($id, $participant_id, $action_id)
	{
		$data = array('action_id'=>$action_id, 'participant_id'=>$participant_id, 'pill_id'=>$id, 'user_id'=> $this->user->getId());
		try {
			$this->pillsService->pillApply($data);
			$this->flashMessage($this->translator->translate('pill_apply'), Types::SUCCESS);
		} catch (\Exception $e)
		{
			$this->flashMessage($this->translator->translate('pill_dont_apply'), Types::DANGER);
		}
		$this->handlePills($participant_id, $action_id);
	}

	public function handleInjuryDetail($id, $participants_id, $action_id)
	{
		$this->injury_id = $id;
		$this->participant_id = $participants_id;
		$this->action_id = $action_id;
		$injury = $this->participantsModel->getParticipantRecord($participants_id, $id);
		$file = (isset($injury->file_id)) ? $this->fileModel->getFileName($injury->file_id) : null;
		$this->template->action_id = $action_id;
		$this->template->participant_id = $participants_id;
		$this->template->injury = $injury;
		$this->template->file = $file;
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
		$this->template->modalTemplate = 'injuryDetail.latte';
	}

	public function handlePrint($id, $action_id)
	{
        $settings = $this->settingsModel->getGlobalSettings();
        $organisation_id = $this->user->identity->getData()['organisation_id'];
		$this->participant_id = $id;
		$participant = $this->participantsModel->getParticipantById($id, $organisation_id);
		$participant_records = $this->participantsModel->getParticipantsActionRecords($id, $action_id);
		$participant_action = $this->actionModel->getAction($action_id);
		$insurance = $this->insuranceModel->getInsuranceById($participant->insurance_id);
		$parent = ($participant->parents_id) ? $this->parentsModel->getParentByID($participant->parents_id) : null;
        $organisation = $this->organisationModel->getOrganisationById($organisation_id);
		$template = $this->createTemplate();
		$template->setTranslator($this->translator->translator());
		$template->setFile(__DIR__ . '/Templates/LogActions/participantCard.latte');
		$template->pills_records = $this->pillsModel->getPillsApply($id, $action_id);
		$template->participant = $participant;
		$template->participant_action = $participant_action;
		$template->participant_records = $participant_records;
		$template->insurance = $insurance;
		$template->parent = $parent;
        $template->organisation = $organisation;
        $qr = new QRCode();
        $url = $settings['web'].'logs/done/list/?id='.$id.'&action_id='.$action_id.'&do=print';
        $template->qr_code = "<img src='".$qr->render($url)."' alt='QR Code' />";
		$pdf = new PdfResponse($template);
        $pdf->setDocumentAuthor('Systém A.D. a M. - MK-SOFT &copy;2023');
        $pdf->setPageMargins('16,15,16,15,9,0');
        $pdf->setDocumentTitle('participant_card_' . $participant->surname . '_' . $participant->name);
		$this->getPresenter()->sendResponse($pdf);
	}

	public function createComponentAddInjuryFormModal()
	{
		$form = new Form();

		$form->addHidden('id');
		$form->addHidden('participant_id');
		$form->addHidden('action_id');

		$form->addTextArea('description', $this->translator->translate('injury_description'), 40, 10)
			->setAttribute('id','wysiwyg');
		$form->addUpload('file');

		$form->addSubmit('submit', $this->translator->translate('btn_write'));
		$form->addButton('cancel', $this->translator->translate('btn_cancel'));

		$form->setDefaults(array('participant_id' => $this->participant_id,
			'action_id' => $this->action_id));

		$form->onSuccess[] = [$this, 'saveInjury'];
		return $form;
	}

	public function saveInjury(Form $form, ArrayHash $data)
	{
		try {
			$organisation_id = $this->user->identity->getData()['organisation_id'];
			$this->logActionService->addInjury($data, $this->user->identity->getId(), $organisation_id);
			$this->flashMessage($this->translator->translate('injury_saved'), Types::SUCCESS);
		} catch (\Exception $e) {
			$this->flashMessage($this->translator->translate('injury_not_saved'), Types::DANGER);
		}
		$this->handleParticipants($data->action_id);
	}

	public function handleAddInjury($participant_id, $action_id)
	{
		$this->participant_id = $participant_id;
		$this->action_id = $action_id;
		$this->template->action_id = $action_id;
		$this->template->participant_id = $participant_id;
		$this->template->action_status = $this->actionModel->getActionStatus($action_id);
		$this->template->modalTemplate = 'addInjury.latte';
	}

	public function handleDownloadInjuryFile($file)
	{
		$content = __DIR__ . '/../../../data/actions/logs/' . $file;
		$file = new FileResponse($content);
		$this->getPresenter()->sendResponse($file);
	}
}